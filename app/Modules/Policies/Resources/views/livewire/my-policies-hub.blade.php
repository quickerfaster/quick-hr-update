<div>
    {{-- Header with search and filter --}}
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
        <h4 class="mb-0">
            <i class="fas fa-gavel me-2 text-primary"></i>Company Policies
        </h4>
        <div class="d-flex gap-2">
            <div class="input-group input-group-sm" style="max-width: 250px;">
                <span class="input-group-text bg-white">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control" placeholder="Search policies..."
                    wire:model.live.debounce.300ms="search">
            </div>
            <select class="form-select form-select-sm" style="max-width: 180px;"
                wire:model.live="categoryFilter">
                <option value="">All Categories</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Pending Acknowledgment Section --}}
    @if ($pendingPolicies && $pendingPolicies->isNotEmpty())
        <div class="mb-4">
            <h5 class="text-warning mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Pending Acknowledgment
                <span class="badge bg-warning text-dark ms-2">{{ $pendingPolicies->count() }}</span>
            </h5>
            <div class="row g-3">
                @foreach ($pendingPolicies as $policy)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-warning h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">{{ $policy->title }}</h6>
                                    <span class="badge bg-warning text-dark text-uppercase small">
                                        {{ $categories[$policy->category] ?? $policy->category }}
                                    </span>
                                </div>
                                @if ($policy->description)
                                    <p class="card-text text-muted small mb-3">
                                        {{ Str::limit($policy->description, 120) }}
                                    </p>
                                @endif
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        v{{ $policy->version }}
                                        @if ($policy->effective_date)
                                            &middot; Effective {{ $policy->effective_date->format('M d, Y') }}
                                        @endif
                                    </small>
                                    <button class="btn btn-sm btn-warning"
                                        wire:click="openAcknowledgment({{ $policy->id }})">
                                        <i class="fas fa-check-circle me-1"></i> Read & Acknowledge
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Acknowledged / No Acknowledgment Required Section --}}
    @if ($acknowledgedPolicies && $acknowledgedPolicies->isNotEmpty())
        <div class="mb-4">
            <h5 class="text-success mb-3">
                <i class="fas fa-check-circle me-2"></i>
                Acknowledged & Information
                <span class="badge bg-success ms-2">{{ $acknowledgedPolicies->count() }}</span>
            </h5>
            <div class="row g-3">
                @foreach ($acknowledgedPolicies as $policy)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-success h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">{{ $policy->title }}</h6>
                                    <span class="badge bg-success text-uppercase small">
                                        {{ $categories[$policy->category] ?? $policy->category }}
                                    </span>
                                </div>
                                @if ($policy->description)
                                    <p class="card-text text-muted small mb-3">
                                        {{ Str::limit($policy->description, 120) }}
                                    </p>
                                @endif
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        v{{ $policy->version }}
                                        @if ($policy->effective_date)
                                            &middot; Effective {{ $policy->effective_date->format('M d, Y') }}
                                        @endif
                                    </small>
                                    <button class="btn btn-sm btn-outline-success"
                                        wire:click="openAcknowledgment({{ $policy->id }})">
                                        <i class="fas fa-eye me-1"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if ($pendingPolicies->isEmpty() && $acknowledgedPolicies->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No policies found</h5>
            <p class="text-muted small">
                @if ($search || $categoryFilter)
                    Try adjusting your search or filter criteria.
                @else
                    No company policies have been published yet.
                @endif
            </p>
        </div>
    @endif

    {{-- Acknowledgment Modal --}}
    @if ($acknowledgingPolicyId)
        @php
            $modalPolicy = \App\Modules\Policies\Models\CompanyPolicy::find($acknowledgingPolicyId);
            $alreadyAcknowledged = $modalPolicy && \App\Modules\Policies\Models\PolicyAcknowledgment::where('company_policy_id', $modalPolicy->id)
                ->where('employee_id', $employeeId)
                ->where('status', 'acknowledged')
                ->exists();
        @endphp
        @if ($modalPolicy)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);"
                wire:ignore.self>
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-file-contract me-2"></i>{{ $modalPolicy->title }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeAcknowledgment"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <span class="badge bg-secondary text-uppercase me-2">
                                    {{ $categories[$modalPolicy->category] ?? $modalPolicy->category }}
                                </span>
                                <span class="badge bg-info me-2">v{{ $modalPolicy->version }}</span>
                                @if ($modalPolicy->priority === 'critical' || $modalPolicy->priority === 'high')
                                    <span class="badge bg-danger text-uppercase">{{ $modalPolicy->priority }}</span>
                                @endif
                            </div>

                            @if ($modalPolicy->description)
                                <div class="alert alert-light border mb-3">
                                    <strong>Summary:</strong> {{ $modalPolicy->description }}
                                </div>
                            @endif

                            <div class="policy-content border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                @if ($modalPolicy->content)
                                    {!! $modalPolicy->content !!}
                                @else
                                    <p class="text-muted text-center py-3">
                                        <i class="fas fa-file-pdf fa-2x mb-2 d-block"></i>
                                        Policy content is available as a PDF document.
                                    </p>
                                @endif
                            </div>

                            @if ($modalPolicy->requires_acknowledgment && !$alreadyAcknowledged)
                                <div class="mt-3">
                                    <label class="form-label fw-semibold">
                                        Type your full name to acknowledge:
                                    </label>
                                    <input type="text" class="form-control" placeholder="Your full name"
                                        wire:model.live="signature">
                                    <small class="text-muted">
                                        By acknowledging, you confirm that you have read and understood this policy.
                                        This will be recorded with your name, date, and IP address.
                                    </small>
                                </div>
                            @endif

                            @if ($alreadyAcknowledged)
                                <div class="alert alert-success mt-3 mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    You have already acknowledged this policy.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer justify-content-between">
                            <div>
                                <a href="/policies/{{ $modalPolicy->id }}/print" target="_blank" class="btn btn-outline-secondary btn-sm" title="Open printable version">
                                    <i class="fas fa-print me-1"></i> Print / Download
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary" wire:click="closeAcknowledgment">
                                    Close
                                </button>
                                @if ($modalPolicy->requires_acknowledgment && !$alreadyAcknowledged)
                                    <button type="button" class="btn btn-primary"
                                        wire:click="acknowledge"
                                        @if (!$signature) disabled @endif>
                                        <i class="fas fa-check-circle me-1"></i> I Acknowledge
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show" wire:click="closeAcknowledgment"></div>
        @endif
    @endif
</div>
