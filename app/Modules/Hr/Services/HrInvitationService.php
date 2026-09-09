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
     *
     * Looks up the employee's department and uses a department-specific
     * email template if one is configured.
     */
    public function createWithEmployeeLink(
        string $email,
        string $role,
        int $employeeId,
        ?string $message = null,
        ?int $createdBy = null
    ): Invitation {
        $employee = Employee::findOrFail($employeeId);

        // Resolve department-specific template
        $template = $this->resolveTemplate($employee);

        // Build a personalized message using the template
        $personalizedMessage = $this->buildTemplateMessage($template, $employee, $message);

        return $this->invitationService->create(
            email: $email,
            role: $role,
            message: $personalizedMessage,
            invitable: $employee,
            createdBy: $createdBy
        );
    }

    /**
     * Resolve the invitation email template for an employee's department.
     *
     * Returns the department-specific template config, or the default.
     */
    public function resolveTemplate(Employee $employee): array
    {
        $templates = config('hr.invitation_templates', []);
        $default = $templates['default'] ?? [
            'subject' => "You've been invited to join {company_name}",
            'greeting' => 'Hello,',
        ];

        $position = $employee->employeePosition;

        if (! $position || ! $position->department_id) {
            return $default;
        }

        $department = $position->department;

        if (! $department) {
            return $default;
        }

        // Normalize department name to a key
        $key = strtolower(str_replace(' ', '_', $department->name));

        return $templates[$key] ?? $default;
    }

    /**
     * Build a personalized message from a template.
     */
    protected function buildTemplateMessage(array $template, Employee $employee, ?string $customMessage = null): ?string
    {
        $companyName = config('app.name', 'QuickerFaster');
        $employeeName = trim($employee->first_name . ' ' . $employee->last_name);

        $position = $employee->employeePosition;
        $departmentName = $position?->department?->name ?? '';

        $replacements = [
            '{company_name}' => $companyName,
            '{employee_name}' => $employeeName,
            '{department}' => $departmentName,
            '{role}' => '',
        ];

        $greeting = strtr($template['greeting'] ?? 'Hello,', $replacements);

        $parts = [$greeting];

        if ($customMessage) {
            $parts[] = $customMessage;
        }

        $parts[] = "You've been invited to join {$companyName}.";

        if ($departmentName) {
            $parts[] = "Department: {$departmentName}";
        }

        return implode("\n\n", $parts);
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
