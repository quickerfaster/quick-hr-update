<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-primary mb-0">
            <i class="fas fa-envelope me-2"></i> Account & Invitation
        </h5>
    </div>
    <div class="card-body p-4">

        {{-- Status: Account Active --}}
        @if ($hasUser)
            <div class="d-flex align-items-center mb-3">
                <div class="bg-success-subtle rounded-circle p-2 me-3 d-inline-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class="fas fa-check-circle text-success fa-lg"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-success mb-0">Account Active</h6>
                    <small class="text-muted">
                        Linked to user: <strong>{{ $employee->user->email ?? $employee->email }}</strong>
                    </small>
                </div>
            </div>

        {{-- Status: Pending Invitation --}}
        @elseif ($pendingInvitation)
            <div class="d-flex align-items-center mb-3">
                <div class="bg-warning-subtle rounded-circle p-2 me-3 d-inline-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class="fas fa-clock text-warning fa-lg"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-warning mb-0">Invitation Pending</h6>
                    <small class="text-muted">
                        Sent {{ $pendingInvitation->created_at->diffForHumans() }}
                        &middot; Expires {{ $pendingInvitation->expires_at?->diffForHumans() ?? 'N/A' }}
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button wire:click="resendInvitation"
                    class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-paper-plane me-1"></i> Resend Invitation
                </button>
            </div>

        {{-- Status: No Invitation, No User --}}
        @else
            <div class="d-flex align-items-center mb-3">
                <div class="bg-secondary-subtle rounded-circle p-2 me-3 d-inline-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class="fas fa-user-slash text-secondary fa-lg"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-secondary mb-0">No Account</h6>
                    <small class="text-muted">
                        This employee does not have a user account yet.
                    </small>
                </div>
            </div>

            <div class="mt-3">
                <button wire:click="sendInvitation"
                    class="btn btn-sm btn-primary">
                    <i class="fas fa-paper-plane me-1"></i> Send Invitation
                </button>
            </div>
        @endif

        {{-- Flash Message --}}
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert">
                <i class="fas fa-check-circle me-1"></i>
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

    </div>
</div>
