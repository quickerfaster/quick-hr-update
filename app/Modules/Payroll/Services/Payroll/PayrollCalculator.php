<?php

namespace App\Modules\Payroll\Services\Payroll;

use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollPayslip;
use App\Modules\Payroll\Models\PayslipItem;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Payroll\Models\EmployeeAdjustmentProfile;
use App\Modules\Payroll\Models\PayrollRunAdjustment;
use App\Modules\Payroll\Models\PayrollPolicy;
use App\Modules\Payroll\Models\PayrollPolicyAssignment;
use App\Modules\Hr\Models\Company;
use App\Modules\Attendance\Models\WorkPattern;
use App\Modules\Attendance\Models\AttendancePolicy;
use App\Modules\Attendance\Traits\HasPayPeriods;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollCalculator
{
    use HasPayPeriods;
    protected PayrollRun $run;

    /**
     * Main calculation entry point – processes payroll run in chunks.
     */
    public function calculate(PayrollRun $run): void
    {
        $this->run = $run;

        // → Route multi-company runs to the specialized method
        if ($run->is_multi_company) {
            $this->calculateMultiCompany($run);
            return;
        }

        // Get total employee count
        $totalEmployees = EmployeePosition::withoutCompanyScope()->where('pay_schedule_id', $this->run->pay_schedule_id)
            ->where('employment_status', 'Active')
            ->count();

        // Create or reset progress record (outside transaction, so immediately visible)
        \App\Modules\Payroll\Models\PayrollRunProgress::withoutCompanyScope()->updateOrCreate(
            ['payroll_run_id' => $this->run->id],
            [
                'total_employees' => $totalEmployees,
                'processed_employees' => 0,
                'status' => 'processing',
            ]
        );

        // Delete previous payslips & items (inside a transaction – but this is quick)
        DB::transaction(function () {
            PayrollPayslip::withoutCompanyScope()->where('payroll_run_id', $this->run->id)->delete();
        });

        // Process employees in chunks – each employee’s data saved in its own transaction
        EmployeePosition::withoutCompanyScope()->where('pay_schedule_id', $this->run->pay_schedule_id)
            ->where('employment_status', 'Active')
            ->with([
                'employee' => function ($q) {
                    $q->withoutCompanyScope();
                },
                'location',
                'employee.employeeProfile',
                'employee.user',
            ])
            ->chunk(100, function ($positions) {
                foreach ($positions as $position) {
                    // Save payslip in a small transaction (for atomicity per employee)
                    DB::transaction(function () use ($position) {
                        $this->calculateForEmployee($position);
                    });

                    // Update progress (outside transaction, commits immediately)
                    \App\Modules\Payroll\Models\PayrollRunProgress::withoutCompanyScope()->where('payroll_run_id', $this->run->id)
                        ->increment('processed_employees');
                }
            });

        // Mark as completed
        \App\Modules\Payroll\Models\PayrollRunProgress::withoutCompanyScope()->where('payroll_run_id', $this->run->id)
            ->update(['status' => 'completed']);

        // Finally, update the run totals (inside a transaction, but done once at the end)
        $this->updateRunTotals();
    }


public function setRun(PayrollRun $run): void
{
    $this->run = $run;
}











/**
 * Calculate payslip for a single employee.
 * Check if attendance integration is enabled globally.
 */
protected function isAttendanceIntegrationEnabled(): bool
{
    return (bool) config('quick_hr_payroll.attendance_integration.enabled', true);
}


/**
 * Get attendance summary for an employee within a date range.
 */
protected function getAttendanceSummary(int $employeeId, Carbon $start, Carbon $end): array
{
    $attendances = \App\Modules\Attendance\Models\Attendance::withoutCompanyScope()
        ->where('employee_id', $employeeId)
        ->whereBetween('date', [$start, $end])
        ->get();

    $summary = [
        'regular_hours' => 0,
        'overtime_hours' => 0,
        'double_time_hours' => 0,
        'worked_days' => 0,
    ];

    foreach ($attendances as $day) {
        if ($day->net_hours > 0 || $day->status !== 'absent' || $day->is_paid_absence) {
            $summary['worked_days']++;
            $regular = $day->regular_hours ?? 0;
            $overtime = $day->overtime_hours ?? 0;
            $double = $day->double_time_hours ?? 0;

            // FALLBACK: if breakdown missing, use net_hours as regular
            if ($regular == 0 && $overtime == 0 && $double == 0 && $day->net_hours > 0) {
                $regular = $day->net_hours;
            }

            $summary['regular_hours'] += $regular;
            $summary['overtime_hours'] += $overtime;
            $summary['double_time_hours'] += $double;
        }
    }
    return $summary;
}


/**
 * Calculate the number of workdays in a given period based on work pattern.
 */
protected function getWorkdaysInPeriod(Carbon $start, Carbon $end, ?int $workPatternId): int
{
    if ($workPatternId) {
        $workPattern = \App\Modules\Attendance\Models\WorkPattern::find($workPatternId);
        if ($workPattern && !empty($workPattern->applicable_days)) {
            // Normalize applicable_days to an array of integers.
            // DB may store as comma-separated string "1,2,3,4,5" or JSON array [1,2,3,4,5].
            // 1=Monday, 2=Tuesday, ..., 7=Sunday
            // Carbon::dayOfWeek returns 1 (Monday) through 7 (Sunday).
            $applicableDays = $workPattern->applicable_days;
            if (is_string($applicableDays)) {
                $applicableDays = array_map('intval', explode(',', $applicableDays));
            }

            $days = 0;
            $current = $start->copy();
            while ($current <= $end) {
                if (in_array($current->dayOfWeek, $applicableDays)) {
                    $days++;
                }
                $current->addDay();
            }
            return $days;
        }
    }

    // Fallback: count weekdays (Mon–Fri) in the period.
    $days = 0;
    $current = $start->copy();
    while ($current <= $end) {
        if ($current->isWeekday()) {
            $days++;
        }
        $current->addDay();
    }
    return $days;
}

/**
 * Resolve the work pattern ID for an employee position.
 * Looks up EmployeeWorkPattern for the employee that overlaps the payroll period.
 * Falls back to the default work pattern if no employee-specific assignment exists.
 */
protected function resolveWorkPatternId(EmployeePosition $position): ?int
{
    // First try employee‑specific assignment
    $assignment = \App\Modules\Attendance\Models\EmployeeWorkPattern::withoutCompanyScope()
        ->where('employee_id', $position->employee_id)
        ->where('start_date', '<=', $this->run->period_end)
        ->where(function ($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', $this->run->period_start);
        })
        ->orderBy('start_date', 'desc')
        ->first();

    if ($assignment) {
        return $assignment->work_pattern_id;
    }

    // Fallback to default work pattern
    $defaultPattern = WorkPattern::withoutCompanyScope()
        ->where('is_default', true)
        ->where('is_active', true)
        ->where('effective_date', '<=', $this->run->period_end)
        ->where(function ($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', $this->run->period_start);
        })
        ->when($position->employee->company_id, function ($query, $companyId) {
            return $query->where('company_id', $companyId);
        })
        ->first();

    return $defaultPattern?->id;
}

    /**
     * Get the attendance policy applicable to an employee.
     * Checks employee position assignment first, then falls back to default policy.
     */
    protected function getAttendancePolicyForEmployee(EmployeePosition $position): ?AttendancePolicy
    {
        // First try the policy directly assigned to the position
        if ($position->attendance_policy_id) {
            $policy = AttendancePolicy::withoutCompanyScope()
                ->where('id', $position->attendance_policy_id)
                ->where('is_active', true)
                ->first();
            if ($policy) {
                return $policy;
            }
        }

        // Fallback to default attendance policy for the company
        return AttendancePolicy::withoutCompanyScope()
            ->where('is_default', true)
            ->where('is_active', true)
            ->where('effective_date', '<=', $this->run->period_end)
            ->where(function ($q) {
                $q->whereNull('expiration_date')->orWhere('expiration_date', '>=', $this->run->period_start);
            })
            ->when($position->employee->company_id, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            })
            ->first();
    }

    /**
     * Update payroll run totals after all employees processed.
     */
    public function updateRunTotals(): void
    {
        $totals = PayrollPayslip::withoutCompanyScope()->where('payroll_run_id', $this->run->id)
            ->selectRaw('SUM(gross_pay) as total_gross, SUM(total_deductions) as total_deductions, SUM(net_pay) as total_net')
            ->first();

        $totalEmployerContributions = PayslipItem::withoutCompanyScope()->whereHas('payslip', function($q) {
            $q->where('payroll_run_id', $this->run->id);
        })->where('type', 'employer_contribution')->sum('amount');

        $totalTaxes = PayslipItem::withoutCompanyScope()->whereHas('payslip', function($q) {
            $q->where('payroll_run_id', $this->run->id);
        })->where('type', 'tax')->sum('amount');

        // NOTE: calculation_status is now set by ProcessEmployeeBatch when the last
        // batch finishes, so payslips appear immediately without waiting for finalization.
        $this->run->update([
            'total_gross_pay' => $totals->total_gross ?? 0,
            'total_deductions' => $totals->total_deductions ?? 0,
            'total_cash_required' => $totals->total_net ?? 0,
            'total_employer_contributions' => $totalEmployerContributions,
            'total_taxes' => $totalTaxes,
        ]);
    }

    /**
     * Helper to create a line item array, now with optional metadata.
     */
    protected function makeItem(?int $policyId, string $type, string $label, float $amount, ?int $adjustmentId = null, array $metadata = []): array
    {
        return [
            'policy_id' => $policyId,
            'type' => $type,
            'label' => $label,
            'amount' => $amount,
            'adjustment_id' => $adjustmentId,
            'employee_adjustment_profile_id' => null,
            'calculation_metadata' => empty($metadata) ? null : json_encode($metadata),
        ];
    }














    /**
     * Get number of days a policy is active within the payroll period.
     */
    protected function getActiveDaysInRun(PayrollPolicy $policy, Carbon $periodStart, Carbon $periodEnd): int
    {
        $policyStart = $policy->effective_date;
        $policyEnd = $policy->expiry_date ?? $periodEnd;

        // If policy starts after period end or ends before period start, zero days
        if ($policyStart > $periodEnd || ($policyEnd && $policyEnd < $periodStart)) {
            return 0;
        }

        $activeStart = $policyStart > $periodStart ? $policyStart : $periodStart;
        $activeEnd = $policyEnd < $periodEnd ? $policyEnd : $periodEnd;

        return $activeStart->diffInDays($activeEnd) + 1; // inclusive
    }

    /**
     * Resolve the effective policy by traversing parent chain and merging overrides.
     */
    protected function resolveEffectivePolicy(PayrollPolicy $policy): PayrollPolicy
    {
        if (!$policy->parent_policy_id) {
            return $policy;
        }

        // Load parent recursively
        $parent = $policy->parentPolicy;
        if (!$parent) {
            return $policy;
        }
        $effectiveParent = $this->resolveEffectivePolicy($parent);

        // Clone the child to avoid modifying database instance
        $effective = clone $policy;

        // Merge fields: child takes precedence, fallback to parent
        $fieldsToMerge = [
            'calculation_logic',
            'effect',
            'employer_ratio',
            'is_statutory',
            'country_code',
            'state_code',
            'type',
            'name'
        ];
        foreach ($fieldsToMerge as $field) {
            if (empty($effective->$field) && !is_null($effectiveParent->$field)) {
                $effective->$field = $effectiveParent->$field;
            }
        }

        // Special handling for dates: effective = max(child, parent), expiry = min(child, parent)
        $childStart = $policy->effective_date;
        $parentStart = $effectiveParent->effective_date;
        $effective->effective_date = $childStart > $parentStart ? $childStart : $parentStart;

        $childEnd = $policy->expiry_date;
        $parentEnd = $effectiveParent->expiry_date;
        if ($childEnd && $parentEnd) {
            $effective->expiry_date = $childEnd < $parentEnd ? $childEnd : $parentEnd;
        } elseif ($childEnd) {
            $effective->expiry_date = $childEnd;
        } else {
            $effective->expiry_date = $parentEnd;
        }

        return $effective;
    }

    /**
     * Resolve applicable policies for an employee based on assignments and global rules.
     */
    protected function resolvePoliciesForEmployee(EmployeePosition $position): \Illuminate\Support\Collection
    {
        // 1. Get assignments (with their policies)
        $assignments = PayrollPolicyAssignment::withoutCompanyScope()->with('payrollPolicy')
            ->whereIn('assignable_type', [
                'App\Modules\Hr\Models\Company',
                'App\Modules\Hr\Models\Location',
                'App\Modules\Hr\Models\Department',
                'App\Modules\Attendance\Models\Shift',
                'App\Modules\Hr\Models\EmployeeGroup',
            ])
            ->where(function ($q) use ($position) {
                $companyId = optional($position->employee->company)->id;
                $locationId = $position->location_id;
                $departmentId = $position->department_id;
                $shiftId = $position->shift_id;
                $employeeGroupId = $position->employee->employee_group_id;

                $q->where(function ($q2) use ($companyId) {
                    $q2->where('assignable_type', 'App\Modules\Hr\Models\Company')
                        ->where('assignable_id', $companyId);
                })->orWhere(function ($q2) use ($locationId) {
                    $q2->where('assignable_type', 'App\Modules\Hr\Models\Location')
                        ->where('assignable_id', $locationId);
                })->orWhere(function ($q2) use ($departmentId) {
                    $q2->where('assignable_type', 'App\Modules\Hr\Models\Department')
                        ->where('assignable_id', $departmentId);
                })->orWhere(function ($q2) use ($shiftId) {
                    $q2->where('assignable_type', 'App\Modules\Attendance\Models\Shift')
                        ->where('assignable_id', $shiftId);
                })->orWhere(function ($q2) use ($employeeGroupId) {
                    $q2->where('assignable_type', 'App\Modules\Hr\Models\EmployeeGroup')
                        ->where('assignable_id', $employeeGroupId);
                });
            })
            ->where('effective_date', '<=', $this->run->period_end)
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $this->run->period_start);
            })
            ->orderBy('priority', 'desc')
            ->get();

        // 2. Get IDs of policies that have ANY assignment (not just those that match this employee)
        $assignedPolicyIds = PayrollPolicyAssignment::withoutCompanyScope()->whereIn('assignable_type', [
            'App\Modules\Hr\Models\Company',
            'App\Modules\Hr\Models\Location',
            'App\Modules\Hr\Models\Department',
            'App\Modules\Attendance\Models\Shift',
            'App\Modules\Hr\Models\EmployeeGroup',
        ])
            ->where('effective_date', '<=', $this->run->period_end)
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $this->run->period_start);
            })
            ->pluck('payroll_policy_id')
            ->unique()
            ->toArray();

        // 3. Global policies: exclude those that have any assignment
        $countryCode = $position->location->country_code ?? null;
        $stateCode = $position->location->state_code ?? null;

        $globalPolicies = PayrollPolicy::withoutCompanyScope()->where('is_active', true)
            ->where('effective_date', '<=', $this->run->period_end)
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $this->run->period_start);
            })
            ->whereNotIn('id', $assignedPolicyIds)
            ->where(function ($q) use ($countryCode) {
                $q->where('country_code', $countryCode)->orWhereNull('country_code');
            })
            ->where(function ($q) use ($stateCode) {
                $q->where('state_code', $stateCode)->orWhereNull('state_code');
            })
            ->get();

        // 4. Merge (assignment policies take precedence)
        return $assignments->pluck('payrollPolicy')->merge($globalPolicies)->unique('id');
    }

/**
 * Apply policy logic to calculate amount, with optional proration factor.
 * Now supports 'base' key in calculation_logic: 'base_salary' or 'gross_pay'.
 */
protected function applyPolicyLogic(
    PayrollPolicy $policy,
    array $items,
    float $baseSalary,
    float $grossPayBase,
    float $prorationFactor = 1.0,
    ?EmployeePosition $position = null
): array
{
    $logic = json_decode($policy->calculation_logic, true);
    if (!$logic) {
        return ['employee' => 0, 'employer' => 0];
    }

    // Determine effective base
    $baseKey = $logic['base'] ?? 'base_salary';
    $effectiveBase = ($baseKey === 'gross_pay') ? $grossPayBase : $baseSalary;

    // --- TAX POLICY ---
    if ($policy->type === 'tax') {
        $frequency = $position?->pay_frequency ?? 'Monthly';
        $annualIncome = $this->annualizeAmount($effectiveBase, $frequency);


        $tax = 0;
        $bands = collect($logic['bands'] ?? [])->sortBy('start')->values()->toArray();

        foreach ($bands as $band) {
            $start = (float) ($band['start'] ?? 0);
            $end = $band['end'] ?? PHP_FLOAT_MAX;
            $rate = (float) ($band['rate'] ?? 0) / 100;

            if ($annualIncome <= $start) {
                break;
            }

            $upper = min($annualIncome, $end);
            $taxable = max(0, $upper - $start);
            $tax += $taxable * $rate;

            if ($annualIncome <= $end) {
                break;
            }
        }


        $periodTax = $this->deAnnualizeAmount($tax, $frequency);
        $periodTax *= $prorationFactor;

        return ['employee' => $periodTax, 'employer' => 0];
    }

    // --- NON-TAX POLICIES ---
    $calcType = $logic['calculation_type'] ?? 'percentage';
    $employeeValue = $logic['employee_value'] ?? 0;
    $employerValue = $logic['employer_value'] ?? 0;

    if ($calcType === 'fixed') {
        $employeeAmount = $employeeValue;
        $employerAmount = $employerValue;
    } else { // percentage
        $employeeAmount = $effectiveBase * ($employeeValue / 100);
        $employerAmount = $effectiveBase * ($employerValue / 100);
    }

    $employeeAmount *= $prorationFactor;
    $employerAmount *= $prorationFactor;

    return ['employee' => $employeeAmount, 'employer' => $employerAmount];
}






protected function annualizeSalary(float $salary, string $frequency): float
{
    return match ($frequency) {
        'Monthly'      => $salary * 12,
        'Semi-monthly' => $salary * 24,
        'Bi-weekly'    => $salary * 26,
        'Weekly'       => $salary * 52,
        'Daily'        => $salary * 365, // or 260 if you use business days
        default        => $salary * 12,  // fallback
    };
}

    /**
     * Generate unique payslip number.
     */
    protected function generatePayslipNumber(string $employeeNumber): string
    {
        return 'PS-' . $employeeNumber . '-' . $this->run->id . '-' . now()->format('YmdHis');
    }

    // -----------------------------------------------------------------
    // Multi-Company Payroll Methods
    // -----------------------------------------------------------------


/**
 * Calculate payroll for a multi-company run.
 *
 * Iterates companies sequentially, processing each company's employees
 * independently within its own transaction boundary. A failure in one
 * company does not roll back successfully processed companies.
 *
 * @param PayrollRun $run  The multi-company payroll run (is_multi_company = true)
 */
public function calculateMultiCompany(PayrollRun $run): void
{
    $this->run = $run;

    // ----------------------------------------------------------------
    // 1. Determine which companies have active employees
    //    (NO pay_schedule_id filter – we want ALL companies)
    // ----------------------------------------------------------------
    $companyIds = EmployeePosition::withoutCompanyScope()
        ->where('employment_status', 'Active')
        ->whereHas('employee', function ($q) {
            $q->withoutCompanyScope();
        })
        ->join('employees', 'employee_positions.employee_id', '=', 'employees.id')
        ->select('employees.company_id')
        ->distinct()
        ->pluck('company_id')
        ->filter() // remove nulls
        ->values();

    if ($companyIds->isEmpty()) {
        Log::warning("Multi-company payroll run {$this->run->id}: No companies with active employees found.");
        $this->run->update([
            'calculation_status' => 'failed',
            'failure_reason' => 'No companies have active employees.',
            'failed_at' => now(),
        ]);
        return;
    }

    // ----------------------------------------------------------------
    // 2. Count total employees across ALL companies (for progress)
    // ----------------------------------------------------------------
    $totalEmployees = EmployeePosition::withoutCompanyScope()
        ->where('employment_status', 'Active')
        ->whereHas('employee', function ($q) {
            $q->withoutCompanyScope();
        })
        ->count();

    // 3. Initialize overall progress
    \App\Modules\Payroll\Models\PayrollRunProgress::withoutCompanyScope()->updateOrCreate(
        ['payroll_run_id' => $this->run->id],
        [
            'total_employees' => $totalEmployees,
            'processed_employees' => 0,
            'status' => 'processing',
        ]
    );

    // 4. Clear all existing payslips (single transaction, quick)
    DB::transaction(function () {
        PayrollPayslip::withoutCompanyScope()->where('payroll_run_id', $this->run->id)->delete();
    });

    // 5. Process each company sequentially
    $perCompanySummaries = [];
    $failedCompanies = [];
    $totalCompanies = $companyIds->count();

    foreach ($companyIds as $companyId) {
        try {
            // Fetch positions for this company (NO pay_schedule_id)
            $companyPositions = EmployeePosition::withoutCompanyScope()
                ->where('employment_status', 'Active')
                ->whereHas('employee', function ($q) use ($companyId) {
                    $q->withoutCompanyScope()->where('company_id', $companyId);
                })
                ->with([
                    'employee' => function ($q) {
                        $q->withoutCompanyScope();
                    },
                    'location',
                    'employee.employeeProfile',
                    'employee.user',
                ])
                ->get();

            if ($companyPositions->isEmpty()) {
                Log::info("Multi-company: Company #{$companyId} has no active positions, skipping.");
                continue;
            }

            $summary = $this->processCompany(
                $companyId,
                $companyPositions,
                $totalCompanies
            );
            $perCompanySummaries[] = $summary;

        } catch (\Exception $e) {
            Log::error("Multi-company payroll: Company #{$companyId} failed", [
                'error' => $e->getMessage(),
                'run_id' => $this->run->id,
                'trace' => $e->getTraceAsString(),
            ]);
            $failedCompanies[] = [
                'company_id' => $companyId,
                'company_name' => Company::find($companyId)?->name ?? 'Unknown',
                'status' => 'failed',
                'error' => substr($e->getMessage(), 0, 500),
            ];
        }
    }

    // 6. Mark progress complete
    \App\Modules\Payroll\Models\PayrollRunProgress::withoutCompanyScope()->where('payroll_run_id', $this->run->id)
        ->update(['status' => 'completed']);

    // 7. Update run totals and per-company summaries
    $this->updateRunTotals();

    $this->run->update([
        'per_company_summaries' => json_encode([
            'companies' => $perCompanySummaries,
            'failed_companies' => $failedCompanies,
        ]),
    ]);

    // 8. Determine final calculation status
    if (!empty($failedCompanies) && empty($perCompanySummaries)) {
        $this->run->update([
            'calculation_status' => 'failed',
            'failure_reason' => 'All ' . count($failedCompanies) . ' companies failed processing.',
            'failed_at' => now(),
        ]);
    } elseif (!empty($failedCompanies)) {
        $this->run->update([
            'calculation_status' => 'completed_with_errors',
            'failure_reason' => 'Partial failure: ' . count($failedCompanies)
                . ' of ' . $totalCompanies . ' companies failed.',
        ]);
    } else {
        $this->run->update(['calculation_status' => 'completed']);
    }

    Log::info("Multi-company payroll run {$this->run->id} completed", [
        'companies_processed' => count($perCompanySummaries),
        'companies_failed' => count($failedCompanies),
        'total_employees' => $totalEmployees,
    ]);
}




    /**
     * Process all employees for a single company within a multi-company run.
     *
     * Uses the existing calculateForEmployee() method for per-employee logic.
     * Each employee's payslip is committed in its own transaction.
     *
     * @param int                     $companyId       The company being processed
     * @param \Illuminate\Support\Collection $positions  EmployeePosition instances for this company
     * @param int                     $totalCompanies  Total number of companies in the run
     * @return array  Company summary for per_company_summaries JSON
     */
    protected function processCompany(
        int $companyId,
        Collection $positions,
        int $totalCompanies
    ): array {
        $company = Company::find($companyId);

        if (!$company) {
            \Log::warning("PayrollCalculator: Company #{$companyId} not found during payroll run", [
                'run_id'    => $this->run->id ?? null,
                'company_id' => $companyId,
            ]);

            return [
                'company_id'      => $companyId,
                'company_name'    => 'Unknown Company (deleted)',
                'employee_count'  => 0,
                'processed_count' => 0,
                'failed_count'    => 0,
                'status'          => 'skipped',
                'employees'       => [],
                'errors'          => ['Company not found in database. It may have been deleted during processing.'],
            ];
        }

        $companyName = $company->name;

        Log::info("Processing company: {$companyName} (#{$companyId})", [
            'run_id' => $this->run->id,
            'employee_count' => $positions->count(),
        ]);

        $processedCount = 0;
        $companyGrossPay = 0;
        $companyDeductions = 0;
        $companyTaxes = 0;
        $companyEmployerContributions = 0;
        $companyNetPay = 0;

        // Process employees in chunks (reuse existing chunk size of 100)
        $positions->chunk(100)->each(function ($chunk) use (
            &$processedCount,
            &$companyGrossPay,
            &$companyDeductions,
            &$companyTaxes,
            &$companyEmployerContributions,
            &$companyNetPay,
            $companyId
        ) {
            foreach ($chunk as $position) {
                DB::transaction(function () use (
                    $position,
                    &$processedCount,
                    &$companyGrossPay,
                    &$companyDeductions,
                    &$companyTaxes,
                    &$companyEmployerContributions,
                    &$companyNetPay,
                    $companyId
                ) {
                    // Use the existing single-employee calculator
                    $payslip = $this->calculateForEmployee($position);

                    // Override the payslip's company_id to the employee's actual company
                    // (calculateForEmployee may leave it null or set from session context)
                    $payslip->update(['company_id' => $companyId]);

                    $companyGrossPay += $payslip->gross_pay;
                    $companyDeductions += $payslip->total_deductions;
                    $companyTaxes += $payslip->total_taxes;
                    $companyNetPay += $payslip->net_pay;
                    $companyEmployerContributions += $payslip->employer_contribution_total ?? 0;
                });

                $processedCount++;

                // Update overall progress (outside transaction, commits immediately)
                \App\Modules\Payroll\Models\PayrollRunProgress::withoutCompanyScope()
                    ->where('payroll_run_id', $this->run->id)
                    ->increment('processed_employees');

                // ALSO sync to payroll_runs
                \App\Modules\Payroll\Models\PayrollRun::withoutCompanyScope()
                    ->where('id', $this->run->id)
                    ->increment('processed_employees');
            }
        });

        Log::info("Company {$companyName} (#{$companyId}) processed: {$processedCount} employees", [
            'run_id' => $this->run->id,
            'gross_pay' => $companyGrossPay,
            'net_pay' => $companyNetPay,
        ]);

        return [
            'company_id' => $companyId,
            'company_name' => $companyName,
            'status' => 'completed',
            'total_employees' => $processedCount,
            'gross_pay' => round($companyGrossPay, 2),
            'total_deductions' => round($companyDeductions, 2),
            'total_taxes' => round($companyTaxes, 2),
            'net_pay' => round($companyNetPay, 2),
            'employer_contributions' => round($companyEmployerContributions, 2),
            'processed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Calculate payslip for a single employee.
     *
     * Determines gross pay based on pay type and attendance integration,
     * applies policies, and creates the payslip with line items.
     *
     * @param EmployeePosition $position
     * @return PayrollPayslip
     */
/**
 * Calculate payslip for a single employee.
 */
public function calculateForEmployee(EmployeePosition $position): PayrollPayslip
{
    $employeeId = $position->employee_id;
    $payType = $position->pay_type;
    $hourlyRate = $position->hourly_rate ?? 0;
    $baseSalary = $position->base_salary ?? 0;
    $periodStart = $this->run->period_start;
    $periodEnd = $this->run->period_end;
    $items = [];

    // -------------------------------------------------------------
    // 1. Determine if attendance integration is active
    // -------------------------------------------------------------
    $attendanceEnabled = $this->isAttendanceIntegrationEnabled();

    // If disabled, force pay_type to 'salaried_full' for calculation purposes
    $effectivePayType = $attendanceEnabled ? $payType : 'salaried_full';

    // -------------------------------------------------------------
    // 2. Compute gross pay based on effective pay type
    // -------------------------------------------------------------
    $grossPay = 0;
    $regularPay = 0;
    $overtimePay = 0;
    $attendanceSummary = [];

    if ($effectivePayType === 'salaried_full') {
        // Salaried full – use base salary as is
        $grossPay = $baseSalary;
        $regularPay = $baseSalary;
    } else {
        // For salaried_daily and hourly, we need attendance data
        if (!$attendanceEnabled) {
            // If attendance is disabled, treat as salaried_full
            $grossPay = $baseSalary;
            $regularPay = $baseSalary;
        } else {
            // Fetch attendance summary for the period
            $attendanceSummary = $this->getAttendanceSummary($employeeId, $periodStart, $periodEnd);

            if ($effectivePayType === 'salaried_daily') {
                // Calculate daily rate
                $workPatternId = $this->resolveWorkPatternId($position);
                $totalWorkdays = $this->getWorkdaysInPeriod($periodStart, $periodEnd, $workPatternId);
                $dailyRate = $totalWorkdays > 0 ? $baseSalary / $totalWorkdays : 0;
                $workedDays = $attendanceSummary['worked_days'] ?? 0;
                $grossPay = $dailyRate * $workedDays;
                $regularPay = $grossPay;





            } elseif ($effectivePayType === 'hourly') {
                $regularHours = $attendanceSummary['regular_hours'] ?? 0;
                $overtimeHours = $attendanceSummary['overtime_hours'] ?? 0;
                $doubleTimeHours = $attendanceSummary['double_time_hours'] ?? 0;

                // Read overtime multipliers from attendance policy
                $attendancePolicy = $this->getAttendancePolicyForEmployee($position);
                $overtimeMultiplier = $attendancePolicy->overtime_multiplier ?? config('quick_hr_payroll.default_overtime_multiplier', 1.5);
                $doubleTimeMultiplier = $attendancePolicy->double_time_multiplier ?? config('quick_hr_payroll.default_double_time_multiplier', 2.0);

                $regularPay = $regularHours * $hourlyRate;
                $overtimePay = ($overtimeHours * $hourlyRate * $overtimeMultiplier) + ($doubleTimeHours * $hourlyRate * $doubleTimeMultiplier);
                $grossPay = $regularPay + $overtimePay;
            } else {
                // Fallback – shouldn't happen
                $grossPay = $baseSalary;
                $regularPay = $baseSalary;
            }
        }
    }






    // -------------------------------------------------------------
    // 3. Add base salary and overtime line items
    // -------------------------------------------------------------
    $items[] = $this->makeItem(null, 'earning', 'Base Salary', $regularPay);
    if ($effectivePayType === 'hourly' && $overtimePay > 0) {
        $items[] = $this->makeItem(null, 'earning', 'Overtime Pay', $overtimePay);
    }

    // (Optional) For salaried_daily, you might store worked_days in metadata.
    // We'll add a note in the payslip notes later.

    // -------------------------------------------------------------
    // 4. Recurring adjustments (EmployeeAdjustmentProfile)
    // -------------------------------------------------------------
    $recurring = EmployeeAdjustmentProfile::withoutCompanyScope()
        ->where('employee_id', $employeeId)
        ->where('is_active', true)
        ->where('effective_date', '<=', $this->run->period_end)
        ->where(function ($q) {
            $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $this->run->period_start);
        })->get();

    $overridePolicies = [];
    $standaloneAdjustments = collect();

    foreach ($recurring as $adj) {
        if ($adj->policy_id) {
            $policy = $adj->policy;
            if (!$policy || !$policy->is_active) continue;
            $overridePolicy = clone $policy;
            $overridePolicy->calculation_logic = json_encode([
                'calculation_type' => $adj->calculation_type,
                'employee_value' => (float) $adj->value,
                'employer_value' => (float) $adj->value,
            ]);
            $overridePolicy->name = $adj->label ?: $policy->name;
            $overridePolicies[$policy->id] = $overridePolicy;
        } else {
            $standaloneAdjustments->push($adj);
        }
    }

    // Process standalone adjustments (no policy link)
    foreach ($standaloneAdjustments as $adj) {
        $amount = strtolower($adj->calculation_type) === 'percentage'
            ? $grossPay * ($adj->value / 100)
            : $adj->value;
        $type = strtolower($adj->type) === 'earning' ? 'earning' : 'deduction';
        $items[] = $this->makeItem(null, $type, $adj->label, $amount);
    }

    // -------------------------------------------------------------
    // 5. One‑time adjustments for this run
    // -------------------------------------------------------------
    $oneTime = PayrollRunAdjustment::withoutGlobalScope(\QuickerFaster\UILibrary\Scopes\CompanyScope::class)
        ->where('payroll_run_id', $this->run->id)
        ->where('employee_id', $employeeId)
        ->get();

    foreach ($oneTime as $adj) {
        $amount = $adj->amount;
        if ($amount == 0) continue;
        switch (strtolower($adj->type)) {
            case 'bonus':
            case 'commission':
            case 'reimbursement':
                $type = 'earning';
                $absAmount = abs($amount);
                break;
            case 'deduction':
                $type = 'deduction';
                $absAmount = abs($amount);
                break;
            case 'correction':
                $type = $amount > 0 ? 'earning' : 'deduction';
                $absAmount = abs($amount);
                break;
            default:
                continue 2;
        }
        $items[] = $this->makeItem(null, $type, ucfirst($adj->type) . ': ' . $adj->label, $absAmount, $adj->id);
    }

    // -------------------------------------------------------------
    // 6. Resolve assigned & global policies
    // -------------------------------------------------------------
    $normalPolicies = $this->resolvePoliciesForEmployee($position);

    // Merge policies, giving precedence to overrides
    $allPolicies = [];
    foreach ($normalPolicies as $policy) {
        if (!$policy) continue;
        $allPolicies[$policy->id] = $policy;
    }
    foreach ($overridePolicies as $id => $overridePolicy) {
        $allPolicies[$id] = $overridePolicy;
    }

    // -------------------------------------------------------------
    // 7. Apply policies with proration, using $grossPay as base
    // -------------------------------------------------------------
    $periodStart = $this->run->period_start;
    $periodEnd = $this->run->period_end;
    $totalDays = $periodStart->diffInDays($periodEnd) + 1;

    // Compute gross pay base (total earnings after adjustments)
    $grossPayBase = collect($items)->where('type', 'earning')->sum('amount');

    foreach ($allPolicies as $policy) {
        $effectivePolicy = $this->resolveEffectivePolicy($policy);

        $activeDays = $this->getActiveDaysInRun($effectivePolicy, $periodStart, $periodEnd);
        if ($activeDays <= 0) continue;
        $prorationFactor = $activeDays / $totalDays;

        // Get employee and employer amounts – pass $grossPay as base
        $amounts = $this->applyPolicyLogic($effectivePolicy, $items, $grossPay, $grossPayBase, $prorationFactor, $position);


        // Build metadata for auditing
        $metadata = [
            'proration_factor' => $prorationFactor,
            'effective_policy_id' => $effectivePolicy->id,
        ];

        if ($effectivePolicy->type !== 'tax') {
            $logic = json_decode($effectivePolicy->calculation_logic, true);
            $metadata['calculation_type'] = $logic['calculation_type'] ?? 'percentage';
            $metadata['employee_value'] = $logic['employee_value'] ?? 0;
            $metadata['employer_value'] = $logic['employer_value'] ?? 0;
        } else {
            $metadata['calculation_type'] = 'tax_bands';
            $logic = json_decode($effectivePolicy->calculation_logic, true);
            $metadata['bands'] = $logic['bands'] ?? [];
        }

        // Employee share
        if ($amounts['employee'] != 0) {
            $itemType = match ($effectivePolicy->effect) {
                'addition' => 'earning',
                default => ($effectivePolicy->type === 'tax' ? 'tax' : 'deduction'),
            };
            if ($effectivePolicy->type === 'tax') {
                $label = $effectivePolicy->name;
            } else {
                $calcType = $metadata['calculation_type'];
                $val = $metadata['employee_value'];
                $suffix = $calcType === 'percentage' ? number_format($val, 2) . '%' : number_format($val, 2);
                $label = $effectivePolicy->name . " (Employee: {$suffix})";
            }
            $items[] = $this->makeItem($policy->id, $itemType, $label, $amounts['employee'], null, $metadata);
        }

        // Employer share (informational)
        if ($amounts['employer'] != 0) {
            $calcType = $metadata['calculation_type'] ?? 'percentage';
            $val = $metadata['employer_value'] ?? 0;
            $suffix = $calcType === 'percentage' ? number_format($val, 2) . '%' : number_format($val, 2);
            $label = $effectivePolicy->name . " (Employer: {$suffix})";
            $items[] = $this->makeItem($policy->id, 'employer_contribution', $label, $amounts['employer'], null, $metadata);
        }
    }

    // -------------------------------------------------------------
    // 8. Calculate totals
    // -------------------------------------------------------------
    $grossPayTotal = collect($items)->whereIn('type', ['earning'])->sum('amount');
    $totalDeductions = collect($items)->whereIn('type', ['deduction'])->sum('amount');
    $totalTaxes = collect($items)->where('type', 'tax')->sum('amount');
    $netPay = $grossPayTotal - $totalDeductions - $totalTaxes;

    $companyId = $position->employee->company_id ?? $this->run->company_id;

    // -------------------------------------------------------------
    // 9. Create payslip
    // -------------------------------------------------------------
    $payslip = PayrollPayslip::create([
        'company_id' => $companyId,
        'payslip_number' => $this->generatePayslipNumber($position->employee->employee_number),
        'payroll_run_id' => $this->run->id,
        'employee_id' => $employeeId,
        'base_salary' => $baseSalary,
        'gross_pay' => $grossPayTotal,
        'total_deductions' => $totalDeductions + $totalTaxes,
        'total_taxes' => $totalTaxes,
        'total_benefit_deductions' => $totalDeductions,
        'net_pay' => $netPay,
        'payment_status' => 'pending',
    ]);

    // Create line items
    foreach ($items as $item) {
        PayslipItem::create([
            'company_id' => $companyId,
            'payslip_id' => $payslip->id,
            'type' => $item['type'],
            'label' => $item['label'],
            'amount' => $item['amount'],
            'policy_id' => $item['policy_id'],
            'adjustment_id' => $item['adjustment_id'] ?? null,
            'employee_adjustment_profile_id' => $item['employee_adjustment_profile_id'] ?? null,
            'calculation_metadata' => $item['calculation_metadata'] ?? null,
        ]);
    }

    return $payslip;
}

}
