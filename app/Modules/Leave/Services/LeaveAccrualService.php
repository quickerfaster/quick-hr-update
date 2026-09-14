<?php

namespace App\Modules\Leave\Services;

use App\Modules\Leave\Models\LeaveBalance;
use App\Modules\Hr\Models\Employee;
use App\Modules\Leave\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveAccrualService
{
    /**
     * Calculate and update accruals for all employees.
     *
     * @return array{accrued: int, skipped: int, errors: int}
     */
    public function runMonthlyAccrual(int $month, int $year): array
    {
        $employees = Employee::where('status', 'Active')->get();

        $accrued = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($employees as $employee) {
            $result = $this->accrueForEmployee($employee, $month, $year);

            if ($result['accrued']) {
                $accrued++;
            } elseif ($result['reason'] === 'already_accrued') {
                $skipped++;
            } else {
                $errors++;
            }
        }

        return [
            'accrued' => $accrued,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Accrue leave for a single employee.
     *
     * @return array{accrued: bool, reason: string}
     */
    public function accrueForEmployee(Employee $employee, int $month, int $year): array
    {
        // Get active leave types that accrue
        $leaveTypes = LeaveType::where('is_active', true)
            ->where('deducts_from_balance', true)
            ->get();

        $accrued = false;

        foreach ($leaveTypes as $leaveType) {
            $balance = LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ],
                [
                    'balance' => 0.00,
                    'accrual_rate' => 1.67, // 20 days/year ÷ 12 months
                    'accrual_frequency' => 'Monthly',
                ]
            );

            // Skip if already accrued this month
            if ($balance->last_accrual_date &&
                Carbon::parse($balance->last_accrual_date)->format('Y-m') === $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)) {
                return ['accrued' => false, 'reason' => 'already_accrued'];
            }

            // Grant full annual balance upfront for "None" frequency
            if (in_array($balance->accrual_frequency, [null, 'None'], true)) {
                // Grant full annual balance upfront if not yet granted
                if ((float) $balance->balance === 0.0) {
                    $annualAllowance = config("leave.annual_allowances.{$leaveType->code}", config('leave.annual_allowances.default', 20));

                    DB::transaction(function () use ($balance, $annualAllowance) {
                        $balance->update([
                            'balance' => $annualAllowance,
                            'last_accrual_date' => now(),
                        ]);
                    });
                }
                $accrued = true;
                continue;
            }

            // Apply monthly accrual within a transaction
            DB::transaction(function () use ($balance) {
                $balance->increment('balance', $balance->accrual_rate);

                // Cap at maximum if specified
                if ($balance->max_balance) {
                    $balance->refresh();
                    $balance->balance = min($balance->balance, $balance->max_balance);
                }

                $balance->last_accrual_date = now();
                $balance->save();
            });

            $accrued = true;
        }

        return ['accrued' => $accrued, 'reason' => $accrued ? 'accrued' : 'no_accrual'];
    }
}
