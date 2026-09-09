<?php

namespace App\Modules\Hr\Services;

use QuickerFaster\UILibrary\Models\Invitation;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use Illuminate\Support\Collection;

/**
 * Department-scoped invitation service.
 *
 * Extends HrInvitationService to filter invitations by the current
 * user's department/team. HR managers can only see and manage
 * invitations for employees in their own department.
 */
class DepartmentScopedInvitationService extends HrInvitationService
{
    /**
     * Get the current user's department IDs.
     *
     * Resolves the authenticated user → employee record → position → department.
     * Returns an empty array if the user has no employee record or position.
     */
    protected function getUserDepartmentIds(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $employee = Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return [];
        }

        $position = $employee->employeePosition;

        if (! $position || ! $position->department_id) {
            return [];
        }

        return [$position->department_id];
    }

    /**
     * Get the current user's team IDs.
     */
    protected function getUserTeamIds(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $employee = Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return [];
        }

        return $employee->teams()->pluck('teams.id')->toArray();
    }

    /**
     * Scope invitations to the current user's department/team.
     *
     * Filters invitations where the invitable employee belongs to
     * the same department or team as the current user.
     */
    public function scopeByDepartment($query)
    {
        $departmentIds = $this->getUserDepartmentIds();
        $teamIds = $this->getUserTeamIds();

        if (empty($departmentIds) && empty($teamIds)) {
            // No department/team context — return empty
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($departmentIds, $teamIds) {
            // Invitations linked to employees in the user's department
            if (! empty($departmentIds)) {
                $q->whereHasMorph('invitable', [Employee::class], function ($subQuery) use ($departmentIds) {
                    $subQuery->whereHas('employeePosition', function ($posQuery) use ($departmentIds) {
                        $posQuery->whereIn('department_id', $departmentIds);
                    });
                });
            }

            // Invitations linked to employees in the user's teams
            if (! empty($teamIds)) {
                $q->orWhereHasMorph('invitable', [Employee::class], function ($subQuery) use ($teamIds) {
                    $subQuery->whereHas('teams', function ($teamQuery) use ($teamIds) {
                        $teamQuery->whereIn('teams.id', $teamIds);
                    });
                });
            }
        });
    }

    /**
     * Get department-scoped pending invitations.
     */
    public function getPendingInvitations(): Collection
    {
        return $this->scopeByDepartment(
            Invitation::where('status', Invitation::STATUS_PENDING)
        )->get();
    }

    /**
     * Get department-scoped accepted invitations.
     */
    public function getAcceptedInvitations(): Collection
    {
        return $this->scopeByDepartment(
            Invitation::where('status', Invitation::STATUS_ACCEPTED)
        )->get();
    }

    /**
     * Get all department-scoped invitations.
     */
    public function getAllInvitations()
    {
        return $this->scopeByDepartment(Invitation::query());
    }

    /**
     * Check if the current user can manage a specific invitation.
     *
     * Returns true if the invitation's invitable employee belongs to
     * the same department or team as the current user.
     */
    public function canManage(Invitation $invitation): bool
    {
        $departmentIds = $this->getUserDepartmentIds();
        $teamIds = $this->getUserTeamIds();

        if (empty($departmentIds) && empty($teamIds)) {
            return false;
        }

        if (! $invitation->invitable || ! ($invitation->invitable instanceof Employee)) {
            return false;
        }

        $employee = $invitation->invitable;

        // Check department
        if (! empty($departmentIds)) {
            $position = $employee->employeePosition;
            if ($position && in_array($position->department_id, $departmentIds)) {
                return true;
            }
        }

        // Check teams
        if (! empty($teamIds)) {
            $employeeTeamIds = $employee->teams()->pluck('teams.id')->toArray();
            if (! empty(array_intersect($employeeTeamIds, $teamIds))) {
                return true;
            }
        }

        return false;
    }
}
