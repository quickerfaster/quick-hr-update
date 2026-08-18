{{-- Sidebar Links for Hr --}}

@include('hr::components.layouts.navbars.auth.policies/sidebar-pre-links')

{{-- Generated Links --}}
{{-- Generated Links --}}
{{-- Generated Links --}}
{{-- Generated Links --}}
{{-- Generated Links --}}
{{-- Generated Links --}}
{{-- Generated Links --}}
{{-- Generated Links --}}
{{-- Generated Links --}}
{{-- Generated Links --}}
<li class="nav-item text-nowrap">
<a href="/hr/hr/attendance-policies" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Attendance Policies">
<i class="fas fa-gavel me-2"></i>
@if ($state === 'full')
<span>Attendance Policies</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/dashboard-policies-overview" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Overview">
<i class="fas fa-chart-bar me-2"></i>
@if ($state === 'full')
<span>Overview</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/policy-assignments" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Policy Assignments">
<i class="fas fa-tasks me-2"></i>
@if ($state === 'full')
<span>Policy Assignments</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/work-patterns" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Work Patterns">
<i class="fas fa-calendar-week me-2"></i>
@if ($state === 'full')
<span>Work Patterns</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/employee-work-patterns" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Employee Work Patterns">
<i class="fas fa-user-tag me-2"></i>
@if ($state === 'full')
<span>Employee Work Patterns</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/attendance-policies" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Attendance Policies">
<i class="fas fa-gavel me-2"></i>
@if ($state === 'full')
<span>Attendance Policies</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/dashboard-policies-overview" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Overview">
<i class="fas fa-chart-bar me-2"></i>
@if ($state === 'full')
<span>Overview</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/policy-assignments" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Policy Assignments">
<i class="fas fa-tasks me-2"></i>
@if ($state === 'full')
<span>Policy Assignments</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/work-patterns" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Work Patterns">
<i class="fas fa-calendar-week me-2"></i>
@if ($state === 'full')
<span>Work Patterns</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
    <a href="/hr/hr/employee-work-patterns" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
        data-bs-placement="right" title="Employee Work Patterns">
        <i class="fas fa-user-tag me-2"></i>
        @if ($state === 'full')
            <span>Employee Work Patterns</span>
        @endif
    </a>
</li>

@include('hr::components.layouts.navbars.auth.policies/sidebar-post-links')
