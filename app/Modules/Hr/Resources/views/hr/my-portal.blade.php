@php
    use App\Modules\Hr\Models\Employee;
    use Illuminate\Support\Facades\Auth;

    $employee = Employee::with(['employeeProfile', 'employeePosition'])
        ->where('user_id', Auth::id())
        ->first();

    $dashboardParams = [];
    if ($employee) {
        $position = $employee->employeePosition;
        $profile = $employee->employeeProfile;

        // Resolve relationship display names using withoutGlobalScopes()
        // to bypass CompanyScope on related models (same fix as EmployeeDetail).
        $department = $position?->department_id
            ? \App\Modules\Hr\Models\Department::withoutGlobalScopes()->withTrashed()->find($position->department_id)
            : null;
        $jobTitle = $position?->job_title_id
            ? \App\Modules\Hr\Models\JobTitle::withoutGlobalScopes()->withTrashed()->find($position->job_title_id)
            : null;
        $manager = $position?->manager_id
            ? \App\Modules\Hr\Models\Employee::withoutGlobalScopes()->withTrashed()->find($position->manager_id)
            : null;

        $dashboardParams = [
            'employee_photo_url'  => $profile?->photo ? asset('storage/' . $profile->photo) : '',
            'employee_full_name'  => trim($employee->first_name . ' ' . $employee->last_name),
            'employee_id'         => $employee->id,
            'employee_number'     => $employee->employee_number ?? '',
            'employee_department' => $department?->name ?? $position?->department_id ?? '—',
            'employee_position'   => $jobTitle?->title ?? $jobTitle?->name ?? $position?->job_title_id ?? '—',
            'employee_manager'    => $manager
                ? trim(($manager->first_name ?? '') . ' ' . ($manager->last_name ?? ''))
                : ($position?->manager_id ?? '—'),
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

    @if ($employee)
        <div class="row g-3 mt-3">
            <div class="col-md-6 col-lg-4">
                <livewire:attendance.clock-in-out :employee-id="$employee->id" wire:key="clock-in-out-{{ $employee->id }}" />
            </div>
        </div>
    @endif
</x-qf::navigation-layout>
