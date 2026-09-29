<?php

namespace App\Modules\Leave\Widgets;

use Illuminate\Support\Carbon;
use QuickerFaster\UILibrary\Services\Filters\FilterService;
use QuickerFaster\UILibrary\Traits\Widgets\ResolvesDateStrings;

/**
 * TeamWhoIsOutWidgetProcessor — renders a list of team members who are
 * on leave today or within a configurable date range.
 *
 * Moved from the UI library to the consuming app's Leave module as part
 * of the library boundary cleanup (2026-09-28). The widget is HR-specific
 * and fails the library's two-domain test.
 *
 * Widget definition (model-based):
 *   [
 *       'type'             => 'team_whos_out',
 *       'title'            => 'Team Who\'s Out',
 *       'icon'             => 'fas fa-users-slash',
 *       'color'            => 'warning',
 *       'date_range'       => 'today',
 *       'limit'            => 10,
 *       'width'            => 6,
 *       'model'            => 'App\\Modules\\Leave\\Models\\LeaveRequest',
 *       'conditions'       => [['status', '=', 'Approved']],
 *       'date_field'       => 'start_date',
 *       'end_date_field'   => 'end_date',
 *       'status_field'     => 'status',
 *       'approved_status'  => 'Approved',
 *       'employee_relation'=> 'employee',
 *       'leave_type_relation' => 'leaveType',
 *   ]
 */
class TeamWhoIsOutWidgetProcessor
{
    use ResolvesDateStrings;

    public function process(array $definition): array
    {
        $dateRange = $definition['date_range'] ?? 'today';
        $limit = (int) ($definition['limit'] ?? 10);

        $members = $definition['data'] ?? [];

        if (empty($members) && !empty($definition['model'])) {
            $members = $this->queryModel($definition);
        }

        if (empty($members)) {
            return [
                'type'        => 'team_whos_out',
                'title'       => $definition['title'] ?? 'Team Who\'s Out',
                'description' => $definition['description'] ?? null,
                'icon'        => $definition['icon'] ?? 'fas fa-users-slash',
                'color'       => $definition['color'] ?? 'warning',
                'date_range'  => $dateRange,
                'members'     => [],
                'empty_state' => $definition['empty_state'] ?? 'Everyone is in today! 🎉',
                'width'       => $definition['width'] ?? 6,
            ];
        }

        $members = array_slice($members, 0, $limit);

        return [
            'type'        => 'team_whos_out',
            'title'       => $definition['title'] ?? 'Team Who\'s Out',
            'description' => $definition['description'] ?? null,
            'icon'        => $definition['icon'] ?? 'fas fa-users-slash',
            'color'       => $definition['color'] ?? 'warning',
            'date_range'  => $dateRange,
            'members'     => $members,
            'empty_state' => $definition['empty_state'] ?? 'Everyone is in today! 🎉',
            'width'       => $definition['width'] ?? 6,
        ];
    }

    protected function queryModel(array $definition): array
    {
        $modelClass = $definition['model'];

        if (!class_exists($modelClass)) {
            return [];
        }

        $dateField = $definition['date_field'] ?? 'start_date';
        $endDateField = $definition['end_date_field'] ?? 'end_date';
        $statusField = $definition['status_field'] ?? 'status';
        $approvedStatus = $definition['approved_status'] ?? 'Approved';
        $employeeRelation = $definition['employee_relation'] ?? 'employee';
        $leaveTypeRelation = $definition['leave_type_relation'] ?? 'leaveType';
        $limit = (int) ($definition['limit'] ?? 10);

        $today = Carbon::today();

        $query = $modelClass::query();

        $query->with([$employeeRelation, $leaveTypeRelation]);

        $conditions = $definition['conditions'] ?? [];
        if (!empty($conditions)) {
            $filterService = new FilterService();
            $filterService->applySimpleFilters($query, $conditions);
        }

        $query->where($dateField, '<=', $today)
              ->where($endDateField, '>=', $today);

        $query->orderBy($endDateField, 'asc')
              ->limit($limit);

        $records = $query->get();

        return $records->map(function ($record) use (
            $employeeRelation,
            $leaveTypeRelation,
            $dateField,
            $endDateField
        ) {
            $employee = $record->{$employeeRelation};
            $leaveType = $record->{$leaveTypeRelation};

            $startDate = $record->{$dateField};
            $endDate = $record->{$endDateField};

            return [
                'name'       => $employee
                    ? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))
                    : 'Unknown',
                'photo_url'  => $employee->photo_url ?? null,
                'leave_type' => $leaveType->name ?? null,
                'leave_color'=> $this->leaveTypeColor($leaveType->name ?? null),
                'dates'      => $this->formatDateRange($startDate, $endDate),
                'return_date'=> $this->formatReturnDate($endDate),
                'color'      => 'secondary',
            ];
        })->toArray();
    }

    protected function formatDateRange($startDate, $endDate): string
    {
        if (!$startDate || !$endDate) {
            return '';
        }

        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        if ($start->isSameDay($end)) {
            return $start->format('M j');
        }

        if ($start->format('M') === $end->format('M')) {
            return $start->format('M j') . ' – ' . $end->format('j');
        }

        return $start->format('M j') . ' – ' . $end->format('M j');
    }

    protected function formatReturnDate($endDate): ?string
    {
        if (!$endDate) {
            return null;
        }

        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        $returnDate = $end->copy()->addDay();

        if ($returnDate->isWeekend()) {
            $returnDate = $returnDate->next(Carbon::MONDAY);
        }

        return 'Returns ' . $returnDate->format('M j');
    }

    protected function leaveTypeColor(?string $typeName): string
    {
        return match (strtolower($typeName ?? '')) {
            'sick', 'sick leave' => 'danger',
            'annual', 'annual leave', 'vacation' => 'primary',
            'maternity', 'paternity', 'parental' => 'info',
            'bereavement', 'compassionate' => 'dark',
            'unpaid' => 'secondary',
            default => 'warning',
        };
    }
}
