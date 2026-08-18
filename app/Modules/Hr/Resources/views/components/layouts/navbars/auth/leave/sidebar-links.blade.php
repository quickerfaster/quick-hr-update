{{-- Sidebar Links for Hr --}}

@include('hr::components.layouts.navbars.auth.leave/sidebar-pre-links')

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
<a href="/hr/hr/dashboard-leave-overview" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Overview">
<i class="fas fa-chart-bar me-2"></i>
@if ($state === 'full')
<span>Overview</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/leave-types" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Leave Types">
<i class="fas fa-tags me-2"></i>
@if ($state === 'full')
<span>Leave Types</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/leave-requests" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Leave Requests">
<i class="fas fa-calendar-alt me-2"></i>
@if ($state === 'full')
<span>Leave Requests</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/leave-balances" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Leave Balances">
<i class="fas fa-balance-scale me-2"></i>
@if ($state === 'full')
<span>Leave Balances</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/leave-approvers" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Approval Rules">
<i class="fas fa-user-check me-2"></i>
@if ($state === 'full')
<span>Approval Rules</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/dashboard-leave-overview" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Overview">
<i class="fas fa-chart-bar me-2"></i>
@if ($state === 'full')
<span>Overview</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/leave-types" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Leave Types">
<i class="fas fa-tags me-2"></i>
@if ($state === 'full')
<span>Leave Types</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/hr/leave-requests" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Leave Requests">
<i class="fas fa-calendar-alt me-2"></i>
@if ($state === 'full')
<span>Leave Requests</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
<a href="/hr/leave-balances" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
data-bs-placement="right" title="Leave Balances">
<i class="fas fa-balance-scale me-2"></i>
@if ($state === 'full')
<span>Leave Balances</span>
@endif
</a>
</li>
<li class="nav-item text-nowrap">
    <a href="/hr/hr/leave-approvers" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip" wire:ignore.self
        data-bs-placement="right" title="Approval Rules">
        <i class="fas fa-user-check me-2"></i>
        @if ($state === 'full')
            <span>Approval Rules</span>
        @endif
    </a>
</li>

@include('hr::components.layouts.navbars.auth.leave/sidebar-post-links')
