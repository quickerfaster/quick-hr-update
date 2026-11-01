<div>
    <div class="mb-4">
        <h5 class="mb-2">
            <i class="fas fa-gavel me-2 text-primary"></i>Company Policies
        </h5>
        <p class="text-muted small mb-0">
            Please review and acknowledge the following company policies.
            @if (!$allAcknowledged)
                <span class="text-warning fw-semibold">
                    You must acknowledge all required policies to complete this step.
                </span>
            @endif
        </p>
    </div>

    @if ($policies && $policies->isNotEmpty())
        <div class="row g-3">
            @foreach ($policies as $policy)
                @php
                    $isAcknowledged = in_array($policy->id, $acknowledgedIds);
                @endphp
                <div class="col-12">
                    <div class="card border-{{ $isAcknowledged ? 'success' : 'warning' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <h6 class="card-title mb-1">
                                        {{ $policy->title }}
                                        @if ($isAcknowledged)
                                            <i class="fas fa-check-circle text-success ms-2"></i>
                                        @endif
                                    </h6>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-secondary text-uppercase small">
                                            {{ $policy->category }}
                                        </span>
                                        <small class="text-muted">v{{ $policy->version }}</small>
                                        @if ($policy->priority === 'critical' || $policy->priority === 'high')
                                            <span class="badge bg-danger text-uppercase small">
                                                {{ $policy->priority }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($policy->description)
                                        <p class="card-text text-muted small mt-2 mb-0">
                                            {{ $policy->description }}
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    @if ($isAcknowledged)
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-check me-1"></i> Acknowledged
                                        </span>
                                    @else
                                        <button class="btn btn-sm btn-warning"
                                            wire:click="viewPolicy({{ $policy->id }})">
                                            <i class="fas fa-book-open me-1"></i> Read & Acknowledge
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($allAcknowledged)
            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle me-2"></i>
                All required policies have been acknowledged. You can proceed to the next step.
            </div>
        @endif
    @else
        <div class="text-center py-4">
            <i class="fas fa-file-contract fa-2x text-muted mb-2"></i>
            <p class="text-muted mb-0">
                No policies require your acknowledgment at this time.
            </p>
            <p class="text-muted small">
                You can skip this step and proceed.
            </p>
        </div>
    @endif

    {{-- Policy Reading Modal --}}
    @if ($viewingPolicyId)
        @php
            $modalPolicy = \App\Modules\Policies\Models\CompanyPolicy::find($viewingPolicyId);
            $alreadyAcknowledged = $modalPolicy && in_array($modalPolicy->id, $acknowledgedIds);
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
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <span class="badge bg-secondary text-uppercase me-2">
                                    {{ $modalPolicy->category }}
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

                            <div class="policy-content border rounded p-3 bg-light"
                                style="max-height: 350px; overflow-y: auto;">
                                @if ($modalPolicy->content)
                                    {!! $modalPolicy->content !!}
                                @else
                                    <p class="text-muted text-center py-3">
                                        <i class="fas fa-file-pdf fa-2x mb-2 d-block"></i>
                                        Policy content is available as a PDF document.
                                    </p>
                                @endif
                            </div>

                            @if (!$alreadyAcknowledged)
                                <div class="mt-3">
                                    <label class="form-label fw-semibold">
                                        Type your full name to acknowledge:
                                    </label>
                                    <input type="text" class="form-control" placeholder="Your full name"
                                        wire:model.live="signature">
                                    <small class="text-muted">
                                        By acknowledging, you confirm that you have read and understood this policy.
                                    </small>
                                </div>
                            @else
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
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                    Close
                                </button>
                                @if (!$alreadyAcknowledged)
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
            <div class="modal-backdrop fade show" wire:click="closeModal"></div>
        @endif
    @endif

    {{-- Navigation: Save & Finish (disabled until all policies acknowledged) --}}
    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-success px-4"
                wire:click="$dispatch('saveStep')"
                @if (!$allAcknowledged && $policies && $policies->isNotEmpty()) disabled @endif
                title="@if (!$allAcknowledged && $policies && $policies->isNotEmpty()) Please acknowledge all required policies before finishing @endif">
            <i class="fas fa-check me-1"></i> {{ __('Save & Finish') }}
        </button>
    </div>
</div>
