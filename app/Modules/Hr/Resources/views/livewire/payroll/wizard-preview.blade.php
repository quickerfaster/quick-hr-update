<div>
    {{-- Processing (with polling) --}}
    @if ($isPolling)
        <div class="card mb-4" wire:poll.1s="checkCalculationStatus">
            <div class="card-body text-center">
                <h5 class="mb-3">Processing Payroll...</h5>
                <div class="progress mb-4" style="height: 28px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary py-3"
                        role="progressbar"
                        style="width: {{ $totalEmployees > 0 ? round(($processedEmployees / $totalEmployees) * 100) : 0 }}%"
                        aria-valuenow="{{ $totalEmployees > 0 ? round(($processedEmployees / $totalEmployees) * 100) : 0 }}"
                        aria-valuemin="0" aria-valuemax="100">
                        <strong
                            class="fs-5">{{ $totalEmployees > 0 ? round(($processedEmployees / $totalEmployees) * 100) : 0 }}%
                        </strong>
                    </div>
                </div>
                @if ($totalEmployees > 0)
                    <p class="text-muted mt-2">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Processed {{ number_format($processedEmployees) }} of {{ number_format($totalEmployees) }}
                        employees...
                    </p>
                @elseif ($calculationStatus === 'pending')
                    <p class="text-muted mt-2">
                        <i class="fas fa-hourglass-start me-2"></i>
                        Your payroll calculation has been queued and will begin shortly.
                        The progress bar will update automatically.
                    </p>
                @elseif ($calculationStatus === 'processing' && $totalEmployees == 0)
                    <p class="text-muted mt-2">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Preparing to process employees... This may take a moment.
                    </p>
                @endif
                <p class="text-muted small mt-3">This may take a few minutes. Please do not close this page.</p>
            </div>
        </div>
    @endif

    {{-- Failed (no polling) --}}
    @if ($calculationStatus === 'failed')
        <div class="card mb-4 border-danger">
            <div class="card-body text-center">
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Calculation failed.</strong> The error has been logged.
                </div>
                <button wire:click="retryCalculation"
                        wire:loading.attr="disabled"
                        wire:target="retryCalculation"
                        class="btn btn-primary">
                    <span wire:loading.remove wire:target="retryCalculation">
                        <i class="fas fa-redo-alt me-2"></i> Retry Calculation
                    </span>
                    <span wire:loading wire:target="retryCalculation">
                        <i class="fas fa-spinner fa-spin me-2"></i> Retrying...
                    </span>
                </button>
                <p class="text-muted small mt-2">You can also go back and adjust data before retrying.</p>
            </div>
        </div>
    @endif

    {{-- Computing totals (calculation complete, finalization pending) --}}
    {{-- - @if (($calculationStatus === 'completed' || ($processedEmployees >= $totalEmployees && $totalEmployees > 0)) && !$isFinalized)
        <div class="card mb-4" wire:poll.2s="checkCalculationStatus">
            <div class="card-body text-center">
                <p class="mb-0 text-muted">
                    <i class="fas fa-calculator me-2"></i>
                    Computing totals…
                </p>
            </div>
        </div>
    @endif
    --}}

    {{-- Completed or all employees processed (show preview) --}}
    @if (!$isPolling && (in_array($calculationStatus, ['completed', 'finalized']) || ($processedEmployees >= $totalEmployees && $totalEmployees > 0)))
        <h4>Review Payroll Run</h4>
        <p class="text-muted">Period: {{ $previewData['period_start'] ?? '' }} – {{ $previewData['period_end'] ?? '' }}
        </p>

        <div class="alert alert-info">
            <strong>Total Cash Required:</strong>
            {{ $previewData['employees'][0]['currency_symbol'] ?? '$' }}{{ number_format($previewData['total_cash_required'] ?? 0, 2) }}
        </div>

        {{-- Filters --}}
        <div class="row mb-3 g-2">
            <div class="col-md-3">
                <label class="form-label">Company</label>
                @if ($isMultiCompany)
                    <select wire:model.live="filterCompany" class="form-select form-select-sm">
                        <option value="">All Companies</option>
                        @foreach ($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="form-control form-control-sm bg-light text-muted d-flex align-items-center"
                        style="border: 1px solid #dee2e6; height: 31px;">
                        <i class="fas fa-building me-1"></i>
                        <span>{{ $companyName ?? 'N/A' }}</span>
                    </div>
                @endif
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select wire:model.live="filterDepartment" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Location</label>
                <select wire:model.live="filterLocation" class="form-select form-select-sm">
                    <option value="">All Locations</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select wire:model.live="filterEmploymentStatus" class="form-select form-select-sm">
                    <option value="Active">Active</option>
                    <option value="On Leave">On Leave</option>
                    <option value="Terminated">Terminated</option>
                    <option value="All">All</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                    placeholder="Name or Employee #">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button wire:click="resetFilters"
                        wire:loading.attr="disabled"
                        wire:target="resetFilters"
                        class="btn btn-sm btn-secondary w-100">
                    <span wire:loading.remove wire:target="resetFilters">
                        <i class="fas fa-undo-alt"></i> Reset Filters
                    </span>
                    <span wire:loading wire:target="resetFilters">
                        <i class="fas fa-spinner fa-spin"></i> Resetting...
                    </span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>Showing {{ $payslips->total() }} payslips</div>
                @if (!empty($search))
                    <div>Search results for: <strong>{{ $search }}</strong></div>
                @endif
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th wire:click="sortBy('employee_name')" style="cursor: pointer;">
                            Employee
                            @if ($sortField === 'employee_name')
                                @if ($sortDirection === 'asc')
                                    <i class="fas fa-sort-up"></i>
                                @else
                                    <i class="fas fa-sort-down"></i>
                                @endif
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('gross_pay')" style="cursor: pointer;">
                            Gross Pay
                            @if ($sortField === 'gross_pay')
                                @if ($sortDirection === 'asc')
                                    <i class="fas fa-sort-up"></i>
                                @else
                                    <i class="fas fa-sort-down"></i>
                                @endif
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('total_deductions')" style="cursor: pointer;">
                            Deductions
                            @if ($sortField === 'total_deductions')
                                @if ($sortDirection === 'asc')
                                    <i class="fas fa-sort-up"></i>
                                @else
                                    <i class="fas fa-sort-down"></i>
                                @endif
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('net_pay')" style="cursor: pointer;">
                            Net Pay
                            @if ($sortField === 'net_pay')
                                @if ($sortDirection === 'asc')
                                    <i class="fas fa-sort-up"></i>
                                @else
                                    <i class="fas fa-sort-down"></i>
                                @endif
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payslips as $payslip)
                        @php
                            $position = $payslip->employee->employeePosition;
                            $currencySymbol = $this->getCurrencySymbol($position->salary_currency ?? 'USD');
                        @endphp
                        <tr wire:key="payslip-{{ $payslip->id }}">
                            <td>{{ $payslip->employee->first_name }} {{ $payslip->employee->last_name }}
                                ({{ $payslip->employee->employee_number }})
                            </td>


                            <td>{{ $currencySymbol }}{{ number_format($payslip->gross_pay, 2) }}</td>
                            <td>{{ $currencySymbol }}{{ number_format($payslip->total_deductions, 2) }}</td>
                            <td>{{ $currencySymbol }}{{ number_format($payslip->net_pay, 2) }}</td>
                            <td>
                                <button wire:click="toggleDetails({{ $payslip->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleDetails"
                                    class="btn btn-sm btn-outline-info">
                                    <span wire:loading.remove wire:target="toggleDetails">
                                        <i class="fas fa-list"></i>
                                    </span>
                                    <span wire:loading wire:target="toggleDetails">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                            </td>
                        </tr>
                        @if ($expandedPayslipId === $payslip->id)
                            <tr>
                                <td colspan="5" class="p-0">
                                    <div class="p-3 bg-light">
                                        @include(
                                            'hr::livewire.payroll.partials.payroll_payslip_items_table',
                                            [
                                                'items' => $lazyItemsCache[$payslip->id] ?? [],
                                                'currencySymbol' => $currencySymbol,
                                            ]
                                        )
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No payslips found for the selected
                                filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($payslips->hasPages())
            <div class="mt-3">
                {{ $payslips->links() }}
            </div>
        @endif

        <div class="mt-3 text-muted small">
            <i class="fas fa-check-circle text-success"></i> All calculations are final. Click <strong>Complete
                Setup</strong> to finalise.
        </div>
    @endif

</div>
