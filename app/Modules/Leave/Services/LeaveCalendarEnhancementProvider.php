<?php

namespace App\Modules\Leave\Services;

use QuickerFaster\UILibrary\Contracts\FieldTypes\CalendarEnhancementProvider;
use App\Modules\Holiday\Models\Holiday;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Hr\Models\Employee;
use Carbon\Carbon;

class LeaveCalendarEnhancementProvider implements CalendarEnhancementProvider
{
    /**
     * Get holiday dates for the calendar.
     *
     * @param string|null $companyId
     * @return array<string, string>
     */
    public function getHolidays(?string $companyId = null): array
    {
        $companyId = $companyId ?? (string) session('current_company_id', '0');

        if ($companyId === '0') {
            return [];
        }

        try {
            $holidays = Holiday::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereNotNull('date')
                ->get(['date', 'name']);

            $result = [];
            foreach ($holidays as $holiday) {
                $dateStr = $holiday->date instanceof Carbon
                    ? $holiday->date->format('Y-m-d')
                    : $holiday->date;
                $result[$dateStr] = $holiday->name;
            }

            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get team absence date ranges for the calendar.
     *
     * @param string|null $companyId
     * @param int|null $excludeUserId
     * @return array<int, array{from: string, to: string, label: string}>
     */
    public function getTeamAbsences(?string $companyId = null, ?int $excludeUserId = null): array
    {
        $companyId = $companyId ?? (string) session('current_company_id', '0');

        if ($companyId === '0') {
            return [];
        }

        $user = auth()->user();
        if (!$user) {
            return [];
        }

        try {
            // Find the current user's employee ID to exclude
            $currentEmployeeId = $excludeUserId;
            if ($currentEmployeeId === null) {
                $employee = Employee::query()
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhere('email', $user->email);
                    })
                    ->first();
                $currentEmployeeId = $employee?->id;
            }

            $query = LeaveRequest::query()
                ->where('company_id', $companyId)
                ->where('status', 'Approved')
                ->whereNotNull('start_date')
                ->whereNotNull('end_date');

            if ($currentEmployeeId) {
                $query->where('employee_id', '!=', $currentEmployeeId);
            }

            $requests = $query->get(['employee_id', 'start_date', 'end_date']);

            // Group by date to count overlapping teammates
            $dateCounts = [];
            foreach ($requests as $lr) {
                $start = $lr->start_date instanceof Carbon
                    ? $lr->start_date->format('Y-m-d')
                    : $lr->start_date;
                $end = $lr->end_date instanceof Carbon
                    ? $lr->end_date->format('Y-m-d')
                    : $lr->end_date;

                $current = Carbon::parse($start);
                $endDate = Carbon::parse($end);
                while ($current->lte($endDate)) {
                    $key = $current->format('Y-m-d');
                    $dateCounts[$key] = ($dateCounts[$key] ?? 0) + 1;
                    $current->addDay();
                }
            }

            // Convert to flatpickr-compatible format
            $absences = [];
            foreach ($dateCounts as $date => $count) {
                $absences[] = [
                    'from' => $date,
                    'to' => $date,
                    'label' => $count . ' teammate' . ($count !== 1 ? 's' : '') . ' off',
                ];
            }

            return $absences;
        } catch (\Throwable $e) {
            return [];
        }
    }
}