<?php

namespace App\Modules\Leave\Services;

use App\Modules\Leave\Models\LeaveBalance;
use Carbon\Carbon;

class LeaveBalanceResolver
{
    /**
     * Get leave balance for an employee, leave type, and year.
     *
     * @param int $employeeId
     * @param int $leaveTypeId
     * @param int $year
     * @return array{balance: float, leave_type_name: string}|null
     */
    public static function getBalance(int $employeeId, int $leaveTypeId, int $year): ?array
    {
        $balance = LeaveBalance::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (!$balance) {
            return null;
        }

        return [
            'balance' => (float) $balance->balance,
            'leave_type_name' => $balance->leaveType->name ?? 'Unknown',
        ];
    }
}