<?php

namespace App\Modules\Attendance\Http\Livewire;

use Livewire\Component;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Hr\Models\Employee;
use Illuminate\Support\Facades\Auth;

/**
 * Absence Report Form — Employee Self-Service
 *
 * Dedicated Livewire component for the "Report Absence" wizard step.
 * Renders a simple form with only the fields an employee needs:
 * absence type, start date, expected duration, and reason.
 *
 * Completely decoupled from the attendance.php data config —
 * no groups, no admin fields leaking in.
 */
class AbsenceReportForm extends Component
{
    public int $stepIndex = 0;
    public ?int $employeeId = null;
    public string $absenceType = 'sick_leave';
    public string $startDate = '';
    public string $expectedDuration = '1';  // max days: 1,3,5,14,28,60 or 0=uncertain
    public string $absenceReason = '';
    public bool $createLeaveRequest = true;  // default ON — ensures balance tracking
    public bool $notifyManager = true;

    protected $listeners = ['saveStepForm' => 'save'];

    public function mount(int $stepIndex, ?int $employeeId = null, array $presetData = []): void
    {
        $this->stepIndex = $stepIndex;
        $this->employeeId = $employeeId ?? ($presetData['employee_id'] ?? null);
        $this->startDate = date('Y-m-d');
    }

    public function save(int $stepIndex): void
    {
        if ($stepIndex !== $this->stepIndex) {
            return;
        }

        $this->validate([
            'absenceType' => 'required|string',
            'startDate' => 'required|date|before_or_equal:today',
            'absenceReason' => 'required|string|min:10|max:500',
        ], [
            'absenceType.required' => 'Please select an absence type.',
            'startDate.required' => 'Please select a start date.',
            'startDate.before_or_equal' => 'Start date cannot be in the future.',
            'absenceReason.required' => 'Please provide a reason for your absence.',
            'absenceReason.min' => 'Reason must be at least 10 characters.',
        ]);

        // Store form data in session for wizard completion
        session()->put("absence_report_{$this->stepIndex}", [
            'absence_type' => $this->absenceType,
            'start_date' => $this->startDate,
            'expected_duration' => $this->expectedDuration,
            'absence_reason' => $this->absenceReason,
            'create_leave_request' => $this->createLeaveRequest,
            'notify_manager' => $this->notifyManager,
        ]);

        // Signal wizard to advance (recordId: 0 = no DB record created yet)
        $this->dispatch('stepFormSaved', recordId: 0, stepIndex: $this->stepIndex);
    }

    public function render()
    {
        return view('attendance::livewire.absence-report-form');
    }
}
