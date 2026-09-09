<?php

namespace App\Modules\Hr\Listeners;

use QuickerFaster\UILibrary\Events\DataTableRecordSaved;
use QuickerFaster\UILibrary\Listeners\DataTableRecordListener;
use QuickerFaster\UILibrary\Models\Invitation;
use App\Modules\Hr\Models\Employee;

class PreLinkInvitationToEmployee extends DataTableRecordListener
{
    /**
     * When an Invitation is created, check for a session-flashed employee ID
     * and pre-link the invitation to that employee.
     */
    protected function handleCreated(DataTableRecordSaved $event): void
    {
        if ($event->model !== Invitation::class) {
            return;
        }

        $employeeId = session()->get('hr_invitation_employee_id');
        if (! $employeeId) {
            return;
        }

        $employee = Employee::find($employeeId);
        if (! $employee) {
            return;
        }

        $invitation = Invitation::find($event->newRecord['id'] ?? null);
        if (! $invitation) {
            return;
        }

        $invitation->update([
            'invitable_type' => $employee->getInvitableType(),
            'invitable_id'   => $employee->getInvitableId(),
        ]);

        session()->forget('hr_invitation_employee_id');
    }
}
