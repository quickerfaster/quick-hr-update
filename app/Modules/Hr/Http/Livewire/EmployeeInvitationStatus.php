<?php

namespace App\Modules\Hr\Http\Livewire;

use Livewire\Component;
use App\Modules\Hr\Models\Employee;
use QuickerFaster\UILibrary\Models\Invitation;
use QuickerFaster\UILibrary\Services\Invitations\InvitationService;

class EmployeeInvitationStatus extends Component
{
    public Employee $employee;
    public ?Invitation $pendingInvitation = null;
    public bool $hasUser = false;

    protected InvitationService $invitationService;

    public function boot(InvitationService $invitationService): void
    {
        $this->invitationService = $invitationService;
    }

    public function mount(Employee $employee): void
    {
        $this->employee = $employee;
        $this->hasUser = (bool) $employee->user_id;

        $this->pendingInvitation = Invitation::where('invitable_type', 'employee')
            ->where('invitable_id', $employee->id)
            ->where('status', 'pending')
            ->first();
    }

    public function sendInvitation(): void
    {
        $this->invitationService->create(
            email: $this->employee->email,
            role: 'employee',
            invitable: $this->employee,
            createdBy: auth()->id()
        );

        $this->pendingInvitation = Invitation::where('invitable_type', 'employee')
            ->where('invitable_id', $this->employee->id)
            ->where('status', 'pending')
            ->first();

        session()->flash('message', 'Invitation sent to ' . $this->employee->email);
    }

    public function resendInvitation(): void
    {
        if ($this->pendingInvitation) {
            $this->invitationService->resend($this->pendingInvitation);
            session()->flash('message', 'Invitation resent to ' . $this->employee->email);
        }
    }

    public function render()
    {
        return view('hr::livewire.employee-invitation-status');
    }
}
