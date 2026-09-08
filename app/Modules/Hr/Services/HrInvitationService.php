<?php

namespace App\Modules\Hr\Services;

use QuickerFaster\UILibrary\Services\Invitations\InvitationService;
use QuickerFaster\UILibrary\Models\Invitation;
use App\Modules\Hr\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class HrInvitationService
{
    public function __construct(
        protected InvitationService $invitationService
    ) {}

    /**
     * Create an invitation pre-linked to an employee.
     */
    public function createWithEmployeeLink(
        string $email,
        string $role,
        int $employeeId,
        ?string $message = null,
        ?int $createdBy = null
    ): Invitation {
        $employee = Employee::findOrFail($employeeId);

        return $this->invitationService->create(
            email: $email,
            role: $role,
            message: $message,
            invitable: $employee,
            createdBy: $createdBy
        );
    }

    /**
     * Attempt to link a user to an employee record after invitation acceptance.
     *
     * Strategy (in order):
     * 1. If invitation has a pre-linked invitable (employee), use it directly
     * 2. Try email matching: invitation.email ↔ Employee.email
     * 3. If no match, return null (admin must manually link)
     */
    public function linkOnAccept(Invitation $invitation, Model $user): ?Employee
    {
        // Strategy 1: Pre-linked employee
        if ($invitation->invitable && $invitation->invitable instanceof Employee) {
            $employee = $invitation->invitable;
            $employee->update(['user_id' => $user->id]);

            return $employee;
        }

        // Strategy 2: Email matching
        $employee = $this->matchByEmail($invitation->email);
        if ($employee) {
            $employee->update(['user_id' => $user->id]);

            return $employee;
        }

        return null;
    }

    /**
     * Find an employee by email address.
     * Returns null if zero or multiple matches.
     */
    public function matchByEmail(string $email): ?Employee
    {
        $employees = Employee::where('email', $email)->get();

        if ($employees->count() === 1) {
            return $employees->first();
        }

        return null;
    }

    /**
     * Get invitations that need manual linking (accepted but no employee linked).
     */
    public function getUnlinkedInvitations()
    {
        return Invitation::where('status', 'accepted')
            ->whereNull('invitable_type')
            ->get();
    }
}
