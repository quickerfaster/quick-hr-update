<div>
    {{-- Flash Message --}}
    @if (session()->has('loan_message'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('loan_message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabs — same pattern as Leave Hub --}}
    <ul class="nav nav-tabs mb-3"
        x-data
        @update-url.window="history.replaceState(null, '', '?tab=' + $event.detail.tab)">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="switchTab('overview')">
                <i class="fas fa-chart-pie"></i> Overview
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'my-loans' ? 'active' : '' }}" wire:click="switchTab('my-loans')">
                <i class="fas fa-list"></i> My Loans
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}" wire:click="switchTab('request')">
                <i class="fas fa-calendar-plus"></i> Request
            </button>
        </li>
    </ul>

    <div>
        @if ($activeTab === 'overview')
            @livewire('qf.dashboard', [
                'configKey' => 'loans.dashboards.dashboard_my_loans',
                'parameters' => [
                    'employee_id' => $employeeId,
                    'employee_number' => $employeeNumber,
                ],
            ], key('my-loans-overview-'.$employeeId))

        @elseif ($activeTab === 'my-loans')
            @livewire('qf.data-table', [
                'configKey' => 'loans.loan',
                'queryFilters' => [['employee_id', '=', $employeeId]],
                'controls' => [
                    'search' => true,
                    'filterColumns' => true,
                    'addButton' => false,
                    'editable' => false,
                    'showHideColumns' => true,
                    'files' => ['export' => ['csv', 'pdf'], 'print' => true],
                ],
            ], key('my-loans-table-'.$employeeId))

        @else
            @livewire('qf.loans.request-form', [
                'employeeId' => $employeeId,
                'availableLoanTypes' => $availableLoanTypes,
                'loanId' => $resumeLoanId,
            ], key('my-loans-request-'.$employeeId.'-'.($resumeLoanId ?? 'new')))
        @endif
    </div>
</div>
