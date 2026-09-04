<div>
    {{-- Header --}}
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <h2>Leave Hub</h2>
    </div>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="switchTab('overview')">
                <i class="fas fa-chart-pie"></i> Overview
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'my-leaves' ? 'active' : '' }}" wire:click="switchTab('my-leaves')">
                <i class="fas fa-list"></i> My Leaves
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'apply' ? 'active' : '' }}" wire:click="switchTab('apply')">
                <i class="fas fa-calendar-plus"></i> Apply
            </button>
        </li>
    </ul>

    <div>
        @if ($activeTab === 'overview')
            @livewire('qf.dashboard', [
                'configKey' => 'hr.dashboards.dashboard_leave_hub',
                'parameters' => [
                    'employee_id' => $employeeId,
                    'employee_number' => $employeeNumber,
                ],
            ], key('leave-hub-overview-'.now()))
        @elseif ($activeTab === 'my-leaves')
            @livewire('qf.data-table', ['configKey' => 'leave.leave_request', 'queryFilters' => [['employee_id', '=', $employeeId]]], key('leave-hub-my-leaves-'.now()))
        @else
            @livewire('qf.wizard', ['configKey' => 'leave.wizards.employee_self_service', 'presetData' => ['employee_id' => $employeeId]], key('leave-hub-apply-'.now()))
        @endif
    </div>
</div>