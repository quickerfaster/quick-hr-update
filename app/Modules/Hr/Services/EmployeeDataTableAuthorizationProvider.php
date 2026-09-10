<?php

namespace App\Modules\Hr\Services;

use QuickerFaster\UILibrary\Services\DataTables\DefaultAuthorizationProvider;
use QuickerFaster\UILibrary\Services\AccessControl\AuthorizationService;
use Illuminate\Contracts\Auth\Authenticatable;

class EmployeeDataTableAuthorizationProvider extends DefaultAuthorizationProvider
{
    /**
     * Override to allow employees to view their own data tables.
     *
     * The library's DefaultAuthorizationProvider only checks admin roles
     * and Spatie permissions. This override adds an employee ownership
     * bypass: if the user resolves to an employee record, they can view
     * data tables (the queryFilters already scope results to their
     * employee_id, so they only see their own data).
     */
    public function canAccessView(Authenticatable $user, string $viewName): bool
    {
        // Keep the admin bypass from the parent
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        // Employee ownership bypass: if the user is linked to an
        // employee record, allow view-level access. The data table's
        // queryFilters already scope results to their employee_id.
        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        // Fall back to Spatie permission check
        return $user->can('view_' . $viewName);
    }

    public function canView(Authenticatable $user, object $record): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewName($record);

        return $user->can('view_' . $viewName);
    }

    public function canCreate(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('create_' . $viewName);
    }

    public function canUpdate(Authenticatable $user, object $record): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewName($record);

        return $user->can('edit_' . $viewName);
    }

    public function canDelete(Authenticatable $user, object $record): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewName($record);

        return $user->can('delete_' . $viewName);
    }

    public function canRestore(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('restore_' . $viewName);
    }

    public function canForceDelete(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('force_delete_' . $viewName);
    }

    public function canPerformAction(Authenticatable $user, string $action, object $record): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewName($record);

        return $user->can($action . '_' . $viewName);
    }

    public function canExport(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('export_' . $viewName);
    }

    public function canImport(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('import_' . $viewName);
    }

    public function canPrint(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('print_' . $viewName);
    }

    public function canBulkDelete(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('delete_' . $viewName);
    }

    public function canBulkRestore(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('restore_' . $viewName);
    }

    public function canBulkForceDelete(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('force_delete_' . $viewName);
    }

    public function canBulkExport(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('export_' . $viewName);
    }

    public function canBulkUpdate(Authenticatable $user, string $modelClass): bool
    {
        if (AuthorizationService::isBypassAllowed($user)) {
            return true;
        }

        if (AuthorizationService::$resolveUserEmployeeId !== null) {
            $employeeId = call_user_func(AuthorizationService::$resolveUserEmployeeId, $user);
            if ($employeeId !== null) {
                return true;
            }
        }

        $viewName = $this->resolveViewNameFromClass($modelClass);

        return $user->can('edit_' . $viewName);
    }
}
