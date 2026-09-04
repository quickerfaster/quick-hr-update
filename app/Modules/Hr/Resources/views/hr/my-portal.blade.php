@php
    use App\Modules\Hr\Models\Employee;
    use Illuminate\Support\Facades\Auth;

    $employee = Employee::with(['employeeProfile', 'employeePosition.department', 'employeePosition.jobTitle', 'employeePosition.manager'])
        ->where('user_id', Auth::id())
        ->first();

    $dashboardParams = [];
    if ($employee) {
        $position = $employee->employeePosition;
        $profile = $employee->employeeProfile;

        $dashboardParams = [
            'employee_photo_url'  => $profile?->photo ? asset('storage/' . $profile->photo) : '',
            'employee_full_name'  => trim($employee->first_name . ' ' . $employee->last_name),
            'employee_id'         => $employee->id,
            'employee_number'     => $employee->employee_number ?? '',
            'employee_department' => $position?->department?->name ?? '—',
            'employee_position'   => $position?->jobTitle?->name ?? '—',
            'employee_manager'    => $position?->manager
                ? trim(($position->manager->first_name ?? '') . ' ' . ($position->manager->last_name ?? ''))
                : '—',
            'employee_hire_date'  => $employee->hire_date?->format('M d, Y') ?? '—',
        ];
    }
@endphp

<x-qf::navigation-layout
    configKey="hr.dashboards.dashboard_my_portal"
    context="my-portal"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="hr.dashboards.dashboard_my_portal" :parameters="$dashboardParams" />
</x-qf::navigation-layout>
