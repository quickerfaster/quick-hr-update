<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Hr\Models\Employee;

/**
 * UserTimezone — canonical timezone resolver for the clock-in/attendance flow.
 *
 * Resolves the effective timezone for an employee using the library's
 * documented three-tier cascade:
 *
 *   1. User preference   → auth()->user()->getSetting('timezone')
 *   2. Company timezone  → employee->company->timezone
 *   3. System default    → config('app.timezone', 'UTC')
 *
 * Timestamps are stored in UTC (best practice); this resolver is used to
 * convert UTC values to the user's local time for display and to compute
 * correct local "today" boundaries.
 */
class UserTimezone
{
    /**
     * Resolve the effective IANA timezone name for an employee.
     *
     * @param int|string|null $employeeId Integer employee_id or string employee_number.
     * @return string IANA timezone identifier (e.g. "Africa/Lagos").
     */
    public static function resolve(int|string|null $employeeId = null): string
    {
        // 1. User preference (My Preferences)
        $user = auth()->user();
        if ($user && method_exists($user, 'getSetting')) {
            $userTz = $user->getSetting('timezone');
            if ($userTz && self::isValid($userTz)) {
                return $userTz;
            }
        }

        // 2. Company timezone
        if ($employeeId !== null) {
            try {
                $employee = Employee::withoutCompanyScope()
                    ->with('company')
                    ->find(is_numeric($employeeId) ? (int) $employeeId : null);

                if (!$employee && is_string($employeeId) && !is_numeric($employeeId)) {
                    $employee = Employee::withoutCompanyScope()
                        ->with('company')
                        ->where('employee_number', $employeeId)
                        ->first();
                }

                $companyTz = $employee?->company?->timezone;
                if ($companyTz && self::isValid($companyTz)) {
                    return $companyTz;
                }
            } catch (\Throwable) {
                // Fall through to system default on any relationship error.
            }
        }

        // 3. System default
        $systemTz = config('app.timezone', 'UTC');

        return self::isValid($systemTz) ? $systemTz : 'UTC';
    }

    /**
     * Validate that a value is a plausible IANA timezone identifier.
     */
    protected static function isValid(mixed $value): bool
    {
        return is_string($value) && $value !== '' && in_array($value, \DateTimeZone::listIdentifiers(), true);
    }
}
