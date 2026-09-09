{{-- Consolidated Employee Onboarding Wizard --}}
<div class="onboarding-wizard" x-data="{
    get stepStatuses() {
        const completed = @js($completedSteps);
        const skipped = @js($skippedSteps);
        const currentKey = '{{ $currentStepConfig['key'] ?? '' }}';
        const steps = @js($steps);

        return steps.map(s => {
            if (skipped[s.key]) return 'skipped';
            if (completed[s.key]) return 'complete';
            if (s.key === currentKey) return 'current';
            return 'pending';
        });
    }
}">
    <div class="container-fluid">
        <div class="row g-0" style="min-height: calc(100vh - 60px);">

            {{-- ===================================================== --}}
            {{-- Sidebar: Step Indicator (Desktop: 250px left panel)  --}}
            {{-- ===================================================== --}}
            <div class="col-md-3 col-lg-2 d-none d-md-block bg-light border-end"
                 style="padding-top: 2rem;">
                <div class="px-3">
                    <h6 class="text-uppercase text-muted small fw-bold mb-4">
                        Onboarding Progress
                    </h6>

                    <nav class="step-indicator">
                        @foreach ($steps as $index => $step)
                            @php
                                $stepNum = $index + 1;
                                $status = $this->getStepStatus($step['key']);
                                $isActive = $stepNum === $currentStep;
                            @endphp

                            <div class="step-item d-flex mb-3 {{ $isActive ? 'active' : '' }}"
                                 style="cursor: {{ $status === 'complete' || $status === 'skipped' ? 'pointer' : 'default' }};"
                                 @if ($status === 'complete' || $status === 'skipped')
                                     wire:click="goToStep({{ $stepNum }})"
                                 @endif
                            >
                                {{-- Status Icon --}}
                                <div class="step-icon me-3 flex-shrink-0"
                                     style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                                            @if ($status === 'complete')
                                                background: #198754; color: white;
                                            @elseif ($status === 'current' || $isActive)
                                                background: #0d6efd; color: white;
                                            @elseif ($status === 'skipped')
                                                background: #adb5bd; color: white;
                                            @else
                                                background: #e9ecef; color: #6c757d;
                                            @endif
                                            font-size: 14px; font-weight: bold; transition: all 0.2s;">
                                    @if ($status === 'complete')
                                        <i class="fas fa-check"></i>
                                    @elseif ($status === 'skipped')
                                        <i class="fas fa-redo-alt" style="font-size: 12px;"></i>
                                    @else
                                        {{ $stepNum }}
                                    @endif
                                </div>

                                {{-- Step Label --}}
                                <div class="step-label" style="line-height: 1.3;">
                                    <div class="fw-semibold {{ $isActive ? 'text-primary' : '' }}"
                                         style="font-size: 14px;">
                                        {{ $step['label'] ?? 'Step ' . $stepNum }}
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        @if ($status === 'complete')
                                            <span class="text-success">Complete</span>
                                        @elseif ($status === 'current' || $isActive)
                                            <span class="text-primary">In Progress</span>
                                        @elseif ($status === 'skipped')
                                            <span class="text-muted">Skipped</span>
                                        @else
                                            @if (! empty($step['required']))
                                                <span class="text-danger">Required</span>
                                            @else
                                                <span class="text-muted">Optional</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </nav>

                    {{-- Progress Bar --}}
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Progress</span>
                            <span>{{ $this->progressPercent }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $this->progressPercent }}%;"
                                 aria-valuenow="{{ $this->progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    {{-- Finish button (always at bottom of sidebar) --}}
                    <div class="mt-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100"
                                wire:click="finish">
                            <i class="fas fa-home me-1"></i> Go to Dashboard
                        </button>
                    </div>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{-- Mobile: Horizontal Step Dots (visible on small screens) --}}
            {{-- ===================================================== --}}
            <div class="col-12 d-md-none bg-light border-bottom px-3 py-2">
                <div class="d-flex justify-content-center align-items-center gap-1">
                    @foreach ($steps as $index => $step)
                        @php
                            $stepNum = $index + 1;
                            $status = $this->getStepStatus($step['key']);
                        @endphp
                        <div class="step-dot"
                             style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                                    font-size: 11px; font-weight: bold;
                                    @if ($status === 'complete')
                                        background: #198754; color: white;
                                    @elseif ($status === 'current')
                                        background: #0d6efd; color: white;
                                    @elseif ($status === 'skipped')
                                        background: #adb5bd; color: white;
                                    @else
                                        background: #e9ecef; color: #6c757d;
                                    @endif
                                    ">
                            @if ($status === 'complete')
                                <i class="fas fa-check" style="font-size: 10px;"></i>
                            @elseif ($status === 'skipped')
                                <i class="fas fa-redo-alt" style="font-size: 9px;"></i>
                            @else
                                {{ $stepNum }}
                            @endif
                        </div>
                        @if ($index < count($steps) - 1)
                            <div style="width: 16px; height: 2px; background: #dee2e6;"></div>
                        @endif
                    @endforeach
                </div>
                <div class="text-center mt-1">
                    <small class="fw-semibold text-primary">
                        Step {{ $currentStep }} of {{ count($steps) }}: {{ $currentStepConfig['label'] ?? '' }}
                    </small>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{-- Main Content Area --}}
            {{-- ===================================================== --}}
            <div class="col-md-9 col-lg-10 d-flex flex-column">
                <div class="flex-grow-1 p-4 d-flex flex-column justify-content-center"
                     style="max-width: 720px; margin: 0 auto; width: 100%;">

                    {{-- Step Content Card --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge {{ $this->currentStepRequired ? 'bg-danger' : 'bg-secondary' }} bg-opacity-10 {{ $this->currentStepRequired ? 'text-danger' : 'text-secondary' }} text-uppercase small">
                                    {{ $this->currentStepRequired ? 'Required' : 'Optional' }}
                                </span>
                                <span class="text-muted small">
                                    Step {{ $currentStep }} of {{ count($steps) }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            {{-- Render the current step's Livewire component --}}
                            @php
                                $stepComponents = [
                                    1 => 'qf.onboarding.step1-employee-record',
                                    2 => 'qf.onboarding.step2-employee-profile',
                                    3 => 'qf.onboarding.step3-payroll-banking',
                                    4 => 'qf.onboarding.step4-documents',
                                    5 => 'qf.onboarding.step5-preferences',
                                ];
                                $componentName = $stepComponents[$currentStep] ?? 'qf.onboarding.step1-employee-record';

                                // Build params for the step component
                                $params = [];
                                if ($currentStep === 1) {
                                    $params['isPreLinked'] = $isPreLinked;
                                    $params['employee'] = $employee;
                                }
                            @endphp

                            @livewire($componentName, $params, key('step-' . $currentStep . '-' . ($employeeId ?? 'new')))

                        </div>
                    </div>

                    {{-- Navigation Buttons (below card) --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            @if ($currentStep > 1)
                                <button type="button" class="btn btn-outline-secondary"
                                        wire:click="goToStep({{ $currentStep - 1 }})">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Bottom Bar (mobile sticky) --}}
                <div class="border-top bg-white p-3 d-md-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">{{ $this->progressPercent }}% complete</span>
                        <div class="progress flex-grow-1 mx-3" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $this->progressPercent }}%;"></div>
                        </div>
                        @if ($currentStep < count($steps))
                            <button type="button" class="btn btn-primary btn-sm"
                                    wire:click="nextStep" {{ ! $this->isCurrentStepComplete() ? 'disabled' : '' }}>
                                Continue
                            </button>
                        @else
                            <button type="button" class="btn btn-success btn-sm" wire:click="finish">
                                Finish
                            </button>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Listen for step events from child components --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('stepComplete', (event) => {
                @this.call('onStepComplete', event.step);
                // Auto-advance after a brief delay so the user sees the success
                setTimeout(() => {
                    @this.call('nextStep');
                }, 400);
            });

            Livewire.on('skipStep', () => {
                @this.call('skipStep');
            });

            Livewire.on('stepSaved', (event) => {
                @this.set('employeeId', event.employeeId);
            });
        });
    </script>
</div>
