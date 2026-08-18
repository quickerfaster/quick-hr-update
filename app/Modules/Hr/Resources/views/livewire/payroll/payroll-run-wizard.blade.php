<div wire:key="payroll-run-wizard">
    <div class="wizard-page-wrapper d-flex justify-content-center py-5"
        style="min-height: 100vh; background-color: #f8f9fa;">
        <div class="wizard-container w-100" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">

            {{-- Progress bar --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-end mb-2">
                    <div>
                        <span class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">
                            Step {{ $currentStep }} of 3
                        </span>
                        <h2 class="fw-bold mb-0">
                            @switch($currentStep)
                                @case(1)
                                    Verification
                                @break

                                @case(2)
                                    Adjustments
                                @break

                                @case(3)
                                    Review & Preview
                                @break
                            @endswitch
                        </h2>
                    </div>
                    <div class="text-muted small">
                        {{ round(($currentStep / 3) * 100) }}% Complete
                    </div>
                </div>

                <div class="progress" style="height: 8px; background-color: #e9ecef; border-radius: 10px;">
                    <div class="progress-bar bg-primary shadow-none"
                        style="width: {{ ($currentStep / 3) * 100 }}%; border-radius: 10px;">
                    </div>
                </div>
            </div>

            {{-- Step content --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">

                    {{-- STEP 1 --}}
                    <div class="{{ $currentStep === 1 ? '' : 'd-none' }}">
                        <h4>Payroll Details</h4>

                        <div class="row mt-3">
                            <div class="col-md-12 mb-4">
                                <label>Payroll Run Title</label>
                                <input type="text" wire:model="title" class="form-control" />

                                @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label>Pay Schedule</label>
                                <select wire:model.live="pay_schedule_id" class="form-control">
                                    <option value="">Select...</option>
                                    @foreach (\App\Modules\Hr\Models\PaySchedule::where('is_active', true)->get() as $schedule)
                                        <option value="{{ $schedule->id }}">
                                            {{ $schedule->name }} ({{ $schedule->frequency }})
                                        </option>
                                    @endforeach
                                </select>

                                @error('pay_schedule_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label>Period Start</label>
                                <input type="date" wire:model="period_start" class="form-control">
                                @error('period_start')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label>Period End</label>
                                <input type="date" wire:model="period_end" class="form-control">
                                @error('period_end')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        @if ($this->isAllCompaniesMode())
                            <div class="mb-3">
                                <label class="form-label fw-bold">Process Payroll For</label>

                                {{-- Radio: Single Company --}}
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payrollScope" id="scopeSingle"
                                        value="0" wire:model.live="isMultiCompany"
                                        wire:click="$set('isMultiCompany', false)">
                                    <label class="form-check-label" for="scopeSingle">
                                        Single Company
                                    </label>
                                </div>

                                {{-- Company dropdown (shown only when Single Company is selected) --}}
                                @if (!$this->isMultiCompany)
                                    <div class="ms-4 mb-3 p-3 border rounded bg-light">
                                        <label for="companyId" class="form-label">Select Company <span
                                                class="text-danger">*</span></label>
                                        <select id="companyId"
                                            class="form-select @error('companyId') is-invalid @enderror"
                                            wire:model="companyId">
                                            <option value="">-- Select Company --</option>
                                            @foreach ($this->companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('companyId')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                {{-- Radio: All Companies --}}
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payrollScope" id="scopeAll"
                                        value="1" wire:model.live="isMultiCompany"
                                        wire:click="$set('isMultiCompany', true)">
                                    <label class="form-check-label" for="scopeAll">
                                        All Companies
                                    </label>
                                </div>

                                {{-- Info box: shown when All Companies is selected --}}
                                @if ($this->isMultiCompany)
                                    <div class="ms-4 p-3 border rounded bg-info bg-opacity-10">
                                        @if ($this->eligibleCompanyCount > 0)
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-info-circle text-info me-2"></i>
                                                <strong>{{ $this->eligibleCompanyCount }}
                                                    compan{{ $this->eligibleCompanyCount === 1 ? 'y' : 'ies' }}</strong>
                                                <span class="ms-1">will be processed with</span>
                                                <strong class="ms-1">{{ $this->totalEligibleEmployees }}
                                                    employee{{ $this->totalEligibleEmployees === 1 ? '' : 's' }}</strong>
                                                <span class="ms-1">across all companies.</span>
                                            </div>
                                            <small class="text-muted d-block">
                                                Payslips will be generated per entity. Statutory reports remain separate
                                                per legal entity.
                                            </small>
                                            <div class="mt-2 small">
                                                @foreach ($this->eligibleCompanies as $ec)
                                                    <span class="badge bg-light text-dark me-1 mb-1">
                                                        {{ $ec['company_name'] }}: {{ $ec['employee_count'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center text-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                No active employees found across any company.
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @error('isMultiCompany')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>

                    {{-- STEP 2 --}}
                    <div class="{{ $currentStep === 2 ? '' : 'd-none' }}">
                        @if ($currentStep === 2 && $payrollRunId)
                            <livewire:qf.payroll-wizard-adjustments :stepIndex="2" :payrollRunId="$payrollRunId"
                                wire:key="adjustments-{{ $payrollRunId }}" />
                        @endif
                    </div>

                    {{-- STEP 3 --}}
                    <div class="{{ $currentStep === 3 ? '' : 'd-none' }}">
                        @if ($currentStep === 3 && $payrollRunId)
                            <livewire:qf.payroll-wizard-preview :stepIndex="3" :payrollRunId="$payrollRunId"
                                wire:key="preview-{{ $payrollRunId }}" />
                        @endif
                    </div>

                </div>
            </div>

            {{-- Navigation --}}
            @if (!$isProcessing)
                <div class="d-flex justify-content-between align-items-center mt-4">

                    {{-- Back --}}
                    <button type="button" class="btn btn-link text-decoration-none text-muted fw-bold p-0"
                        wire:click="goToStep({{ $currentStep - 1 }})"
                        wire:loading.attr="disabled"
                        wire:target="goToStep"
                        @if ($currentStep <= 1 || $isProcessing) disabled
                        style="opacity: {{ $currentStep <= 1 ? '0' : '0.5' }}; pointer-events: none;" @endif>
                        <span wire:loading.remove wire:target="goToStep">
                            <i class="fas fa-chevron-left me-1"></i> Back
                        </span>
                        <span wire:loading wire:target="goToStep">
                            <i class="fas fa-spinner fa-spin me-1"></i> Loading...
                        </span>
                    </button>


                    <div class="d-flex align-items-center">

                        {{-- Cancel --}}
                        <button type="button" class="btn btn-link text-decoration-none text-danger me-4 fw-bold p-0"
                            wire:click="confirmCancel()"
                            wire:loading.attr="disabled"
                            wire:target="confirmCancel"
                            @if ($isProcessing) disabled @endif>
                            <span wire:loading.remove wire:target="confirmCancel">Cancel</span>
                            <span wire:loading wire:target="confirmCancel">
                                <i class="fas fa-spinner fa-spin me-1"></i> Cancelling...
                            </span>
                        </button>

                        {{-- Next --}}
                        @if ($currentStep == 1)
                            <button type="button" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold"
                                wire:click="goToStep2"
                                wire:loading.attr="disabled"
                                wire:target="goToStep2"
                                @if ($isProcessing) disabled @endif>
                                <span wire:loading.remove wire:target="goToStep2">
                                    Continue <i class="fas fa-chevron-right ms-2"></i>
                                </span>
                                <span wire:loading wire:target="goToStep2">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Processing...
                                </span>
                            </button>
                        @elseif($currentStep == 2)
                            <button type="button" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold"
                                wire:click="$dispatch('saveAdjustments')"
                                wire:loading.attr="disabled"
                                wire:target="saveAdjustments"
                                @if ($isProcessing) disabled @endif>
                                <span wire:loading.remove wire:target="saveAdjustments">
                                    Save & Continue <i class="fas fa-chevron-right ms-2"></i>
                                </span>
                                <span wire:loading wire:target="saveAdjustments">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Saving...
                                </span>
                            </button>
                        @elseif($currentStep == 3)
                            <button type="button" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold"
                                wire:click="$dispatch('savePreview')"
                                wire:loading.attr="disabled"
                                wire:target="savePreview"
                                @if ($isProcessing) disabled @endif>
                                <span wire:loading.remove wire:target="savePreview">
                                    Complete Setup <i class="fas fa-check ms-2"></i>
                                </span>
                                <span wire:loading wire:target="savePreview">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Finalizing...
                                </span>
                            </button>
                        @endif

                    </div>


                </div>
            @endif


        </div>
    </div>
</div>
