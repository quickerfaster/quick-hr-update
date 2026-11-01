<?php

namespace App\Modules\Hr\Http\Livewire;

use Livewire\Component;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Hr\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AdjustAttendanceMvp — MVP component for manually adjusting attendance records.
 *
 * Provides a simple form for managers/HR admins to edit key attendance fields
 * (net_hours, status, regular_hours, overtime_hours, double_time_hours,
 * is_approved, notes) on an existing attendance record.
 */
class AdjustAttendanceMvp extends Component
{
    public ?int $attendanceId = null;

    // Attendance fields
    public ?string $date = null;
    public ?string $employeeName = null;
    public ?string $employeeNumber = null;
    public string $status = 'present';
    public float $net_hours = 0;
    public float $regular_hours = 0;
    public float $overtime_hours = 0;
    public float $double_time_hours = 0;
    public bool $is_approved = false;
    public bool $needs_review = false;
    public ?string $notes = null;

    public bool $isLoaded = false;
    public bool $isSaved = false;
    public ?string $errorMessage = null;

    protected array $availableStatuses = [
        'present',
        'absent',
        'late',
        'half_day',
        'incomplete',
        'early_departure',
        'unscheduled',
        'holiday',
        'leave',
    ];

    public function mount(?int $attendanceId = null): void
    {
        $this->attendanceId = $attendanceId;

        if ($this->attendanceId) {
            $this->loadAttendance();
        }
    }

    public function loadAttendance(): void
    {
        $attendance = Attendance::withoutCompanyScope()
            ->with('employee')
            ->find($this->attendanceId);

        if (!$attendance) {
            $this->errorMessage = 'Attendance record not found.';
            return;
        }

        $this->date = $attendance->date?->format('Y-m-d');
        $this->employeeName = $attendance->employee
            ? $attendance->employee->first_name . ' ' . $attendance->employee->last_name
            : 'Unknown';
        $this->employeeNumber = $attendance->employee?->employee_number;
        $this->status = $attendance->status ?? 'present';
        $this->net_hours = (float) ($attendance->net_hours ?? 0);
        $this->regular_hours = (float) ($attendance->regular_hours ?? 0);
        $this->overtime_hours = (float) ($attendance->overtime_hours ?? 0);
        $this->double_time_hours = (float) ($attendance->double_time_hours ?? 0);
        $this->is_approved = (bool) $attendance->is_approved;
        $this->needs_review = (bool) $attendance->needs_review;
        $this->notes = $attendance->notes;

        $this->isLoaded = true;
    }

    public function save(): void
    {
        if (!$this->attendanceId) {
            $this->errorMessage = 'No attendance record specified.';
            return;
        }

        $attendance = Attendance::withoutCompanyScope()->find($this->attendanceId);

        if (!$attendance) {
            $this->errorMessage = 'Attendance record not found.';
            return;
        }

        $attendance->update([
            'status' => $this->status,
            'net_hours' => $this->net_hours,
            'regular_hours' => $this->regular_hours,
            'overtime_hours' => $this->overtime_hours,
            'double_time_hours' => $this->double_time_hours,
            'is_approved' => $this->is_approved,
            'needs_review' => $this->needs_review,
            'notes' => $this->notes,
            'calculation_method' => 'manual',
        ]);

        Log::info('Attendance record adjusted manually', [
            'attendance_id' => $this->attendanceId,
            'adjusted_by' => Auth::id(),
            'changes' => [
                'status' => $this->status,
                'net_hours' => $this->net_hours,
                'is_approved' => $this->is_approved,
            ],
        ]);

        $this->isSaved = true;
        $this->errorMessage = null;

        $this->dispatch('attendance-adjusted', [
            'attendance_id' => $this->attendanceId,
        ]);
    }

    public function render()
    {
        return view('hr::livewire.adjust-attendance-mvp', [
            'availableStatuses' => $this->availableStatuses,
        ]);
    }
}
