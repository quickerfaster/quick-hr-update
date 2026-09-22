<?php

namespace App\Modules\Hr\Listeners;

use QuickerFaster\UILibrary\Events\DataTableRecordSaved;
use QuickerFaster\UILibrary\Listeners\DataTableRecordListener;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Services\HrInvitationService;

class AutoInviteOnEmployeeCreate extends DataTableRecordListener
{
    public function __construct(
        protected HrInvitationService $hrInvitationService
    ) {}

    /**
     * When an Employee record is created and the "send invitation" checkbox
     * was checked, automatically create a pre-linked invitation.
     */
    protected function handleCreated(DataTableRecordSaved $event): void
    {
        if ($event->model !== Employee::class) {
            return;
        }

        if (! session()->get('hr_send_invitation_on_create')) {
            return;
        }

        $employee = Employee::find($event->newRecord['id'] ?? null);
        if (! $employee || ! $employee->email) {
            return;
        }

        $this->hrInvitationService->createWithEmployeeLink(
            email: $employee->email,
            role: session('hr_invite_role', 'employee'),
            employeeId: $employee->id,
            createdBy: auth()->id(),
            companyId: session('hr_invite_company_id') ?: $employee->company_id,
        );

        // Assign company to the employee if one was selected during invitation
        $inviteCompanyId = session('hr_invite_company_id');
        if ($inviteCompanyId && !$employee->company_id) {
            $employee->updateQuietly(['company_id' => (int) $inviteCompanyId]);
        }

        session()->forget('hr_send_invitation_on_create');
        session()->forget('hr_invite_role');
        session()->forget('hr_invite_company_id');
    }
}
