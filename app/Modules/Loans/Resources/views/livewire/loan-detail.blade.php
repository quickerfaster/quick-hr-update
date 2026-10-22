<div>
    @if (!$loan)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>Loan not found.
        </div>
    @else

    {{-- Header --}}
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3 mt-3">
        <div>
            <h3 class="mb-1">
                <i class="fas fa-hand-holding-usd me-2 text-primary"></i>
                {{ $loan->loan_number }}
            </h3>
            <span>
                <span class="badge {{ $this->getStatusBadgeClass($loan->status) }} text-uppercase me-2">
                    {{ str_replace('_', ' ', $loan->status) }}
                </span>
                @if ($loan->loanType)
                    <span class="text-muted small">{{ $loan->loanType->name }}</span>
                @endif
                @if ($loan->employee)
                    <span class="text-muted small ms-2">
                        &middot; {{ $loan->employee->first_name }} {{ $loan->employee->last_name }}
                    </span>
                @endif
            </span>
        </div>
        <div>
            <a href="{{ url('/hr/loans') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Loans
            </a>
        </div>
    </div>

    {{-- Approval Panel — top of page, only when actively under approval (matches Leave + Payroll pattern) --}}
    @if ($loan->isUnderApproval())
        @livewire('qf.approval-panel', ['workflowId' => $loan->workflow->id, 'displayMode' => 'banner'])
        <br />
    @endif

    {{-- Status Banner — shown when NOT under active approval --}}
    @if (!$loan->isUnderApproval())
        @php
            $statusAlertClass = match($loan->status) {
                'approved' => 'info',
                'active' => 'success',
                'completed' => 'success',
                'rejected' => 'danger',
                'cancelled' => 'secondary',
                'draft' => 'secondary',
                default => 'secondary',
            };
        @endphp
        <div class="alert alert-{{ $statusAlertClass }} mb-3">
            <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
            @if ($loan->approval_date)
                &middot; Approved on {{ $loan->approval_date->format('M d, Y') }}
                @if ($loan->approvedBy)
                    by {{ $loan->approvedBy->name }}
                @endif
            @endif
            @if ($loan->request_date && $loan->status === 'pending_approval')
                &middot; Requested on {{ $loan->request_date->format('M d, Y') }}
            @endif
        </div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'info' ? 'active' : '' }}" href="#"
                wire:click.prevent="setTab('info')">
                <i class="fas fa-info-circle me-1"></i> Loan Details
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'repayments' ? 'active' : '' }}" href="#"
                wire:click.prevent="setTab('repayments')">
                <i class="fas fa-calendar-check me-1"></i> Repayment Schedule
            </button>
        </li>
    </ul>

    {{-- Tab: Loan Details --}}
    @if ($activeTab === 'info')
        <div class="row g-4">
            {{-- Loan Information --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Loan Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Loan Number</td>
                                <td><strong>{{ $loan->loan_number }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Loan Type</td>
                                <td>{{ $loan->loanType?->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Employee</td>
                                <td>{{ $loan->employee?->first_name }} {{ $loan->employee?->last_name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Request Date</td>
                                <td>{{ $loan->request_date?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Approval Date</td>
                                <td>{{ $loan->approval_date?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Approved By</td>
                                <td>{{ $loan->approvedBy?->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Financial Details --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Financial Details</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Principal Amount</td>
                                <td><strong>{{ $currencySymbol }}{{ number_format($loan->amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Interest Rate</td>
                                <td>{{ $loan->interest_rate }}% p.a.</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Repayment Period</td>
                                <td>{{ $loan->repayment_months }} month(s)</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Monthly Installment</td>
                                <td><strong class="text-primary">{{ $currencySymbol }}{{ number_format($loan->monthly_installment, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Repayable</td>
                                <td><strong class="text-danger">{{ $currencySymbol }}{{ number_format($loan->total_repayable, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Paid Amount</td>
                                <td><span class="text-success">{{ $currencySymbol }}{{ number_format($loan->paid_amount, 2) }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Outstanding Balance</td>
                                <td><strong class="text-warning">{{ $currencySymbol }}{{ number_format($loan->balance, 2) }}</strong></td>
                            </tr>
                        </table>

                        {{-- Progress bar --}}
                        @if ($loan->total_repayable > 0)
                            @php $progress = round(($loan->paid_amount / $loan->total_repayable) * 100); @endphp
                            <div class="mt-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Repayment Progress</span>
                                    <span>{{ $progress }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Reason & Notes --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Reason & Notes</h6>
                    </div>
                    <div class="card-body">
                        @if ($loan->reason)
                            <div class="mb-3">
                                <strong class="text-muted small">Employee's Reason:</strong>
                                <p class="mb-0">{{ $loan->reason }}</p>
                            </div>
                        @endif
                        @if ($loan->notes)
                            <div>
                                <strong class="text-muted small">Admin Notes:</strong>
                                <p class="mb-0">{{ $loan->notes }}</p>
                            </div>
                        @endif
                        @if (!$loan->reason && !$loan->notes)
                            <p class="text-muted mb-0">No additional notes.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Tab: Repayment Schedule --}}
    @if ($activeTab === 'repayments')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Repayment Schedule</h6>
                <span class="badge bg-info">
                    {{ $repayments->where('status', 'paid')->count() }} / {{ $repayments->count() }} Paid
                </span>
            </div>
            <div class="card-body p-0">
                @if ($repayments->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Principal</th>
                                    <th>Interest</th>
                                    <th>Status</th>
                                    <th>Paid Date</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($repayments as $index => $repayment)
                                    <tr class="{{ $repayment->status === 'overdue' ? 'table-danger' : ($repayment->status === 'paid' ? 'table-success' : '') }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $repayment->due_date->format('M d, Y') }}</td>
                                        <td>{{ $currencySymbol }}{{ number_format($repayment->amount, 2) }}</td>
                                        <td>{{ $currencySymbol }}{{ number_format($repayment->principal, 2) }}</td>
                                        <td>{{ $currencySymbol }}{{ number_format($repayment->interest, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $this->getRepaymentStatusBadgeClass($repayment->status) }}">
                                                {{ ucfirst($repayment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $repayment->paid_date?->format('M d, Y') ?? '—' }}</td>
                                        <td>{{ $repayment->payment_method ? str_replace('_', ' ', ucfirst($repayment->payment_method)) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No repayment schedule generated yet.</p>
                        <small class="text-muted">The schedule is created when the loan is activated.</small>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Supporting Documents — always visible below tabs, matches Leave detail pattern --}}
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>Supporting Documents
                @if ($loan && $loan->documents->isNotEmpty())
                    <span class="badge bg-primary ms-2">{{ $loan->documents->count() }}</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if ($loan && $loan->documents->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>File</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($loan->documents as $index => $doc)
                                <tr>
                                    <td>
                                        @if (in_array($doc->mime_type, ['image/jpeg', 'image/png', 'image/jpg']))
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        @elseif ($doc->mime_type === 'application/pdf')
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        @elseif (in_array($doc->mime_type, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                            <i class="fas fa-file-word fa-2x text-primary"></i>
                                        @else
                                            <i class="fas fa-file fa-2x text-muted"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="#"
                                            onclick="Livewire.dispatch('openDocumentPreview', { payload: { fileUrl: '{{ asset('storage/' . $doc->file_path) }}', fileName: '{{ $doc->file_name }}' } }); return false;"
                                            class="text-decoration-none fw-medium">
                                            {{ $doc->file_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap text-muted small">
                                        {{ $doc->size >= 1048576 ? number_format($doc->size / 1048576, 2) . ' MB' : ($doc->size >= 1024 ? number_format($doc->size / 1024, 2) . ' KB' : $doc->size . ' B') }}
                                    </td>
                                    <td class="text-nowrap text-muted small">
                                        {{ $doc->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ asset('storage/' . $doc->file_path) }}"
                                            download="{{ $doc->file_name }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                    <p class="mb-0">No supporting documents attached to this loan request.</p>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
