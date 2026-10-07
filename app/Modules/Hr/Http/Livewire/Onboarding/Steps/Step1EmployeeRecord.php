<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;
use QuickerFaster\UILibrary\Services\ValueGenerator;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Onboarding Step 1: Employee Record (REQUIRED).
 *
 * Creates or updates the Employee model record for the authenticated user.
 * If the employee was pre-linked (e.g. from invitation), the form is
 * pre-filled and the user only needs to confirm.
 */
class Step1EmployeeRecord extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email= '';

    public string $phone = '';

    public string $hire_date = '';

    public bool $isPreLinked = false;

    public function mount(bool $isPreLinked = false, $employee = null): void
    {
        $user = Auth::user();
        $this->email = $user->email ?? '';
        $this->hire_date = date('Y-m-d');
        $this->isPreLinked = $isPreLinked;

        if ($employee && is_object($employee)) {
            $this->first_name = $employee->first_name ?? '';
            $this->last_name = $employee->last_name ?? '';
            $this->phone = $employee->phone ?? '';

            if ($employee->hire_date) {
                $this->hire_date = $employee->hire_date instanceof \Carbon\Carbon
                    ? $employee->hire_date->format('Y-m-d')
                    : $employee->hire_date;
            }
        } else {
            // Load existing employee data on remount (e.g., when navigating Back from Step 2)
            $existingEmployee = Employee::withoutCompanyScope()->where('user_id', $user->id)->first();

            if ($existingEmployee) {
                $this->first_name = $existingEmployee->first_name ?? '';
                $this->last_name = $existingEmployee->last_name ?? '';
                $this->phone = $existingEmployee->phone ?? '';

                if ($existingEmployee->hire_date) {
                    $this->hire_date = $existingEmployee->hire_date instanceof \Carbon\Carbon
                        ? $existingEmployee->hire_date->format('Y-m-d')
                        : $existingEmployee->hire_date;
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'hire_date'  => 'required|date',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        // Resolve company_id: use the user's first assigned company, or fall
        // back to the session company. This prevents employees from being
        // created without a company assignment during self-onboarding.
        $companyId = $this->resolveCompanyId($user);

        // When pre-linked (invitation flow), find the existing employee
        // by email and link it to the current user. The employee was
        // created by admin before the user account existed, so user_id
        // is null. We must NOT create a duplicate.
        if ($this->isPreLinked) {
            $employee = Employee::withoutCompanyScope()
                ->where('email', $user->email)
                ->whereNull('user_id')
                ->first();

            \Log::debug('[Step1EmployeeRecord] pre-linked lookup', [
                'isPreLinked'    => $this->isPreLinked,
                'user_id'        => $user->id,
                'user_email'     => $user->email,
                'found_employee' => $employee?->id ?? 'NOT FOUND',
                'all_by_email'   => Employee::withoutCompanyScope()->where('email', $user->email)->pluck('id', 'user_id')->toArray(),
            ]);

            if ($employee) {
                $employee->update([
                    'user_id'    => $user->id,
                    'first_name' => $this->first_name,
                    'last_name'  => $this->last_name,
                    'phone'      => $this->phone,
                    'hire_date'  => $this->hire_date,
                    'company_id' => $companyId ?: $employee->company_id,
                ]);

                $this->setOnboardingStatus($employee);

                $this->dispatch('stepComplete', employeeId: $employee->id, step: 1);
                $this->dispatch('stepSaved', employeeId: $employee->id);
                return;
            }
        }

        // Find existing employee: first by user_id, then by email (handles
        // pre-linked employees where user_id is still null), then by
        // employee_number match on email domain.
        $existingEmployee = Employee::withoutCompanyScope()->where('user_id', $user->id)->first();

        if (!$existingEmployee) {
            $existingEmployee = Employee::withoutCompanyScope()
                ->where('email', $user->email)
                ->first();

            // If the employee found by email already belongs to a different
            // user, don't try to reassign it — create a new record instead.
            // This prevents UNIQUE constraint violations when an admin
            // accidentally uses "New Invitation" for an already-registered
            // employee email.
            if ($existingEmployee && $existingEmployee->user_id && $existingEmployee->user_id != $user->id) {
                \Log::warning('[Step1EmployeeRecord] Employee email already linked to different user — creating new record', [
                    'employee_id' => $existingEmployee->id,
                    'existing_user_id' => $existingEmployee->user_id,
                    'new_user_id' => $user->id,
                    'email' => $user->email,
                ]);
                $existingEmployee = null;
            }
        }

        $employeeNumber = $existingEmployee?->employee_number;

        $baseAttributes = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email'=> $this->email,
            'phone' => $this->phone,
            'hire_date' => $this->hire_date,
        ];

        // Only set company_id for new records; preserve existing on update
        if (!$existingEmployee && $companyId) {
            $baseAttributes['company_id'] = $companyId;
        }

        if ($existingEmployee) {
            // Update the existing employee (found by user_id or email)
            $existingEmployee->update(array_merge(
                ['user_id' => $user->id],
                $baseAttributes,
            ));
            $employee = $existingEmployee;
        } elseif (empty($employeeNumber)) {
            $generator = app(ValueGenerator::class);
            $employeeNumber = $generator->generate(
                Employee::class,
                'employee_number',
                ['autoGenerate' => true],
            );

            $employee = Employee::withoutCompanyScope()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge(['employee_number' => $employeeNumber], $baseAttributes)
            );
        } else {
            $employee = Employee::withoutCompanyScope()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge(['employee_number' => $employeeNumber], $baseAttributes)
            );
        }

        // Set onboarding_status based on what the admin has already done.
        // Position creation is left to the admin — the wizard only handles
        // the employee record. If the admin already created a position
        // before sending the invitation, mark onboarding as complete.
        $this->setOnboardingStatus($employee);

        $this->dispatch('stepComplete', employeeId: $employee->id, step: 1);
        $this->dispatch('stepSaved', employeeId: $employee->id);
    }

    /**
     * Set the employee's onboarding_status based on whether the admin
     * has already created a position and assigned a company.
     *
     * - complete:         admin created position before invitation
     * - position_pending: has company, needs position (admin handles later)
     * - company_pending:  needs both company and position
     */
    protected function setOnboardingStatus($employee): void
    {
        $hasPosition = \App\Modules\Hr\Models\EmployeePosition::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->exists();

        $hasCompany = (bool) ($employee->company_id ?? null);

        if ($hasPosition) {
            $status = 'complete';
        } elseif ($hasCompany) {
            $status = 'position_pending';
        } else {
            $status = 'company_pending';
        }

        \DB::table('employees')->where('id', $employee->id)->update([
            'onboarding_status' => $status,
        ]);
    }

    /**
     * Resolve the best company_id for a new employee during self-onboarding.
     *
     * Priority:
     * 1. User's first assigned company (via Spatie or UserCompanyAssignment)
     * 2. Session current_company_id (if non-zero)
     * 3. null — employee will need manual company assignment later
     */
    protected function resolveCompanyId($user): ?int
    {
        // Check if the user model has a companies relationship (multi-company)
        if (method_exists($user, 'companies') && $user->companies()->exists()) {
            return $user->companies()->first()->id;
        }

        // Check for a direct company_id on the user model
        if (isset($user->company_id) && $user->company_id) {
            return (int) $user->company_id;
        }

        // Fall back to session company (skip "All Companies" / 0)
        $sessionCompanyId = (int) session('current_company_id', 0);
        if ($sessionCompanyId > 0) {
            return $sessionCompanyId;
        }

        return null;
    }

    public function render()
    {
        return view('hr::onboarding.steps.step1-employee-record');
    }
}
