<?php

namespace App\Modules\Leave\Http\Livewire;

use QuickerFaster\UILibrary\Http\Livewire\Wizards\WizardForm;
use App\Modules\Leave\Models\LeaveBalance;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Leave\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveWizardForm extends WizardForm
{
    /**
     * Custom validation: check that the employee has sufficient leave balance.
     */
    protected function checkLeaveBalance(): void
    {
        $employeeId = $this->fields['employee_id'] ?? null;
        $leaveTypeId = $this->fields['leave_type_id'] ?? null;
        $startDate = $this->fields['start_date'] ?? null;
        $endDate = $this->fields['end_date'] ?? null;

        if (!$employeeId || !$leaveTypeId || !$startDate || !$endDate) {
            return;
        }

        $year = date('Y', strtotime($startDate));

        $balance = LeaveBalance::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (!$balance) {
            $this->addError('leave_type_id', 'No leave balance found for this leave type.');
            return;
        }

        $workingDays = $this->calculateWorkingDays($startDate, $endDate);

        if ((float) $balance->balance < $workingDays) {
            $this->addError(
                'end_date',
                "Insufficient balance. You have {$balance->balance} days remaining but requested {$workingDays} days."
            );
        }

        // P2: max_days_per_request enforcement
        $leaveType = LeaveType::find($leaveTypeId);

        if ($leaveType && $leaveType->max_days_per_request && $workingDays > $leaveType->max_days_per_request) {
            $this->addError(
                'end_date',
                "This leave type allows a maximum of {$leaveType->max_days_per_request} days per request. You requested {$workingDays} days."
            );
        }
    }

    /**
     * Custom validation: check for overlapping approved leave requests.
     */
    protected function checkDateConflicts(): void
    {
        $employeeId = $this->fields['employee_id'] ?? null;
        $startDate = $this->fields['start_date'] ?? null;
        $endDate = $this->fields['end_date'] ?? null;

        if (!$employeeId || !$startDate || !$endDate) {
            return;
        }

        $conflict = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->whereIn('status', ['Approved', 'approved'])
            ->exists();

        if ($conflict) {
            $this->addError('start_date', 'You already have an approved leave request overlapping these dates.');
        }
    }

    /**
     * Real-time conflict detection for the UI (non-blocking warning).
     */
    protected function detectDateConflicts(): void
    {
        $employeeId = $this->fields['employee_id'] ?? null;
        $startDate = $this->fields['start_date'] ?? null;
        $endDate = $this->fields['end_date'] ?? null;

        if (!$employeeId || !$startDate || !$endDate) {
            $this->conflictWarnings = [];
            return;
        }

        $conflicts = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->whereIn('status', ['Approved', 'approved'])
            ->get(['start_date', 'end_date']);

        if ($conflicts->isEmpty()) {
            $this->conflictWarnings = [];
            return;
        }

        $messages = [];
        foreach ($conflicts as $conflict) {
            $start = Carbon::parse($conflict->start_date);
            $end = Carbon::parse($conflict->end_date);

            $messages[] = sprintf(
                '⚠️ You have an approved leave request overlapping these dates (%s–%s).',
                $start->format('M j'),
                $end->format('M j, Y')
            );
        }

        $this->conflictWarnings = $messages;
    }

    /**
     * Get available leave types with balance info for the dropdown.
     */
    public function getAvailableLeaveTypes(string $fieldName): array
    {
        $employeeId = $this->fields['employee_id'] ?? null;
        $year = date('Y');

        if (!empty($this->fields['start_date'])) {
            $year = date('Y', strtotime($this->fields['start_date']));
        }

        $leaveTypes = LeaveType::withoutCompanyScope()
            ->orderBy('name')
            ->get();

        $options = [];

        foreach ($leaveTypes as $leaveType) {
            $balanceDays = 0;

            if ($employeeId) {
                $balance = LeaveBalance::query()
                    ->where('employee_id', $employeeId)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $year)
                    ->first();

                if ($balance) {
                    $balanceDays = (float) $balance->balance;
                }
            }

            $options[$leaveType->id] = "{$leaveType->name} ({$balanceDays} days balance left)";
        }

        return $options;
    }

    /**
     * Get leave type info for the currently selected leave_type_id.
     */
    public function getLeaveTypeInfo(): ?array
    {
        $leaveTypeId = $this->fields['leave_type_id'] ?? null;

        if (!$leaveTypeId) {
            return null;
        }

        $leaveType = LeaveType::find($leaveTypeId);

        if (!$leaveType) {
            return null;
        }

        return [
            'description' => $leaveType->description,
            'requires_approval' => (bool) $leaveType->requires_approval,
            'deducts_from_balance' => (bool) $leaveType->deducts_from_balance,
            'max_days_per_request' => $leaveType->max_days_per_request,
        ];
    }

    /**
     * Get field info for the hints system (showInfo hint).
     */
    public function getFieldInfo(string $fieldName): ?array
    {
        if ($fieldName === 'leave_type_id') {
            return $this->getLeaveTypeInfo();
        }

        return null;
    }

    /**
     * Get field duration for the hints system (showDuration hint).
     */
    public function getFieldDuration(string $fieldName): ?float
    {
        if ($fieldName === 'end_date') {
            return $this->getWorkingDaysCount();
        }

        return null;
    }

    /**
     * Get field conflicts for the hints system (showConflicts hint).
     */
    public function getFieldConflicts(string $fieldName): array
    {
        if ($fieldName === 'end_date') {
            return $this->conflictWarnings;
        }

        return [];
    }
}
