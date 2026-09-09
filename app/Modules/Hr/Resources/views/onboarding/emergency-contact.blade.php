{{-- Onboarding Step 3: Emergency Contact --}}
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            {{-- Progress Indicator --}}
            <div class="text-center mb-4">
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar" role="progressbar" style="width: 50%;" aria-valuenow="3" aria-valuemin="0" aria-valuemax="6"></div>
                </div>
                <small class="text-muted mt-2 d-block">Step 3 of 6 — Emergency Contact</small>
            </div>

            {{-- Step Card --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                    <div class="mb-3">
                        <i class="fas fa-phone-alt fa-2x text-warning"></i>
                    </div>
                    <h4 class="mb-1">Emergency Contact</h4>
                    <p class="text-muted small mb-0">
                        Add an emergency contact in case we need to reach someone on your behalf.
                    </p>
                </div>
                <div class="card-body p-4">
                    @livewire('qf.onboarding.emergency-contact')
                </div>
            </div>

        </div>
    </div>
</div>
