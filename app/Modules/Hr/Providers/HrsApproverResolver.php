<?php

namespace App\Modules\Hr\Providers;

use App\Modules\Hr\Models\Employee;
use QuickerFaster\UILibrary\Contracts\Approvals\ApproverResolver;

/**
 * HR-specific implementation of the ApproverResolver contract.
 *
 * The HR app's users table has no company_id column — the User→Company chain
 * goes through User → Employee (by user_id) → Company (by company_id).
 *
 * This resolver uses the Employee model to verify workspace membership
 * instead of querying a tenancy column directly on the users table.
 *
 * When $workspaceId is null, behaviour falls back to global Spatie role
 * resolution (identical to DefaultApproverResolver).
 *
 * This class is domain-agnostic — it works for ALL modules that need
 * workspace-scoped approval in the HR app (Payroll, Leave, etc.).
 */
class HrsApproverResolver implements ApproverResolver
{
    /**
     * Resolve a mixed list of user IDs and role names into a flat list of
     * user IDs, optionally scoped to a single workspace via the Employee model.
     *
     * Convention:
     *   - int    → already-resolved user ID. When unscoped, passed through
     *              as-is. When scoped, verified against Employee before
     *              inclusion.
     *   - string → role name; the Spatie role model is queried by `name` and
     *              every user holding that role is collected. When a workspace
     *              is supplied, only users who have an Employee record in that
     *              workspace are included.
     *
     * @param array<int|string> $roleIds Mixed user IDs (int) and role names (string).
     * @param string|null $workspaceId Optional workspace (company_id) scope.
     * @return int[] Flat list of resolved user IDs.
     */
    public function resolve(array $roleIds, ?string $workspaceId = null): array
    {
        if ($roleIds === []) {
            return [];
        }

        // No workspace scope → delegate to global Spatie resolution
        // (identical behaviour to DefaultApproverResolver).
        if ($workspaceId === null) {
            return $this->resolveUnscoped($roleIds);
        }

        return $this->resolveScoped($roleIds, $workspaceId);
    }

    /**
     * Resolve without workspace scoping — identical behaviour to
     * DefaultApproverResolver.
     *
     * @param array<int|string> $roleIds
     * @return int[]
     */
    protected function resolveUnscoped(array $roleIds): array
    {
        $userIds = [];
        $roleNames = [];

        foreach ($roleIds as $id) {
            if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                // Integer (or numeric string) → already-resolved user ID.
                $userIds[] = (int) $id;
            } else {
                // String → role name to resolve.
                $roleNames[] = $id;
            }
        }

        if ($roleNames !== []) {
            $roleModel = config('permission.models.role', \Spatie\Permission\Models\Role::class);

            $roles = $roleModel::query()
                ->whereIn('name', $roleNames)
                ->get();

            foreach ($roles as $role) {
                foreach ($role->users as $user) {
                    $userId = method_exists($user, 'getAuthIdentifier')
                        ? $user->getAuthIdentifier()
                        : ($user->id ?? null);

                    if ($userId !== null) {
                        $userIds[] = (int) $userId;
                    }
                }
            }
        }

        return array_values(array_unique($userIds));
    }

    /**
     * Resolve with workspace scoping via the Employee model.
     *
     * @param array<int|string> $roleIds
     * @param string $workspaceId The company_id to scope to.
     * @return int[]
     */
    protected function resolveScoped(array $roleIds, string $workspaceId): array
    {
        $userIds = [];
        $roleNames = [];

        foreach ($roleIds as $id) {
            if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                // Integer → verify the user has an Employee record in this
                // workspace before including them.
                $userId = (int) $id;

                if ($this->userHasEmployeeInWorkspace($userId, $workspaceId)) {
                    $userIds[] = $userId;
                }
            } else {
                // String → role name to resolve within the workspace.
                $roleNames[] = $id;
            }
        }

        if ($roleNames !== []) {
            $userIds = array_merge(
                $userIds,
                $this->resolveRoleNamesScoped($roleNames, $workspaceId)
            );
        }

        return array_values(array_unique($userIds));
    }

    /**
     * Check whether a user has an Employee record in the given workspace.
     *
     * @param int $userId
     * @param string $workspaceId The company_id.
     * @return bool
     */
    protected function userHasEmployeeInWorkspace(int $userId, string $workspaceId): bool
    {
        return Employee::where('user_id', $userId)
            ->where('company_id', $workspaceId)
            ->exists();
    }

    /**
     * Resolve role names to the IDs of users holding those roles who also
     * have an Employee record in the given workspace.
     *
     * @param string[] $roleNames
     * @param string $workspaceId The company_id.
     * @return int[]
     */
    protected function resolveRoleNamesScoped(array $roleNames, string $workspaceId): array
    {
        $roleModel = config('permission.models.role', \Spatie\Permission\Models\Role::class);

        $roles = $roleModel::query()
            ->whereIn('name', $roleNames)
            ->get();

        $userIds = [];

        foreach ($roles as $role) {
            // Collect all user IDs holding this role.
            $roleUserIds = [];

            foreach ($role->users as $user) {
                $userId = method_exists($user, 'getAuthIdentifier')
                    ? $user->getAuthIdentifier()
                    : ($user->id ?? null);

                if ($userId !== null) {
                    $roleUserIds[] = (int) $userId;
                }
            }

            if ($roleUserIds === []) {
                continue;
            }

            // Filter to only users who have an Employee record in this workspace.
            $scopedUserIds = Employee::whereIn('user_id', $roleUserIds)
                ->where('company_id', $workspaceId)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            $userIds = array_merge($userIds, $scopedUserIds);
        }

        return $userIds;
    }
}