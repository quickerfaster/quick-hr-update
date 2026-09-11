<div>
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <h2>Leave Hub</h2>
    </div>

    <ul class="nav nav-tabs mb-3"
        x-data
        @update-url.window="history.replaceState(null, '', '?tab=' + $event.detail.tab)">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'all-requests' ? 'active' : '' }}" wire:click="switchTab('all-requests')">
                <i class="fas fa-list"></i> All Requests
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'apply-for-employee' ? 'active' : '' }}" wire:click="switchTab('apply-for-employee')">
                <i class="fas fa-calendar-plus"></i> Apply for Employee
            </button>
        </li>
    </ul>

    <div>
        @if ($activeTab === 'all-requests')
            @livewire('qf.data-table', [
                'configKey' => 'leave.leave_request',
            ], key('admin-leave-hub-all-requests'))
        @else
            @livewire('qf.wizard', [
                'configKey' => 'leave.wizards.employee_self_service',
                'returnPath' => '/leave/leave-hub?tab=all-requests',
            ], key('admin-leave-hub-apply'))
        @endif
    </div>
</div>
