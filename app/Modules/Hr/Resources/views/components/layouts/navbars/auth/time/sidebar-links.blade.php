{{-- Sidebar Links for Hr --}}

@include('hr::components.layouts.navbars.auth.time/sidebar-pre-links')

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
{{-- Generated Links --}}
{{-- Generated Links --}}
<li class="nav-item text-nowrap">
<a href="/hr/hr/dashboard-time-overview" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Overview">
<i class="fas fa-chart-bar me-2"></i>
@if ($state === 'full')
<span>Overview</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/reports" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Reports">
<i class="fas fa-file-alt me-2 me-2"></i>
@if ($state === 'full')
<span>Reports</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/attendances" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Attendance">
<i class="fas fa-user-clock me-2"></i>
@if ($state === 'full')
<span>Attendance</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/shift-schedules" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Shift Schedules">
<i class="fas fa-calendar-check me-2"></i>
@if ($state === 'full')
<span>Shift Schedules</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/holiday-calendars" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Holiday Calendars">
<i class="fas fa-calendar-alt me-2"></i>
@if ($state === 'full')
<span>Holiday Calendars</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/holidays" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Holidays">
<i class="fas fa-gift me-2"></i>
@if ($state === 'full')
<span>Holidays</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/dashboard-time-overview" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Overview">
<i class="fas fa-chart-bar me-2"></i>
@if ($state === 'full')
<span>Overview</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/reports" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Reports">
<i class="fas fa-file-alt me-2 me-2"></i>
@if ($state === 'full')
<span>Reports</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/attendances" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Attendance">
<i class="fas fa-user-clock me-2"></i>
@if ($state === 'full')
<span>Attendance</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/shift-schedules" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Shift Schedules">
<i class="fas fa-calendar-check me-2"></i>
@if ($state === 'full')
<span>Shift Schedules</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/holiday-calendars" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Holiday Calendars">
<i class="fas fa-calendar-alt me-2"></i>
@if ($state === 'full')
<span>Holiday Calendars</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
    <a href="/hr/hr/holidays" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
        data-bs-placement="right" title="Holidays">
        <i class="fas fa-gift me-2"></i>
        @if ($state === 'full')
            <span>Holidays</span>
        @endif
    </a>
</li>

@include('hr::components.layouts.navbars.auth.time/sidebar-post-links')
