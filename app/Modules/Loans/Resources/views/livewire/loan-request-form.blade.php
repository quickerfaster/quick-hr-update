<div>
    {{-- Error Message --}}
    @if ($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ $errorMessage }}
            <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
        </div>
    @endif

    {{-- Success Message --}}
    @if ($successMessage)
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle me-2"></i>{{ $successMessage }}
        </div>
    @endif

    {{-- Step Progress Indicator --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            @php $stepLabels = $this->getStepLabels(); @endphp
            @foreach ($stepLabels as $stepNum => $label)
                <div class="text-center flex-grow-1">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center
                            {{ $stepNum < $step ? 'bg-success text-white' : ($stepNum === $step ? 'bg-primary text-white' : 'bg-light text-muted') }}"
                            style="width: 32px; height: 32px; font-size: 14px; cursor: {{ $stepNum < $step ? 'pointer' : 'default' }};"
                            wire:click="goToStep({{ $stepNum }})">
                            @if ($stepNum < $step) <i class="fas fa-check"></i> @else {{ $stepNum }} @endif
                        </div>
                        @if ($stepNum < $totalSteps)
                            <div class="flex-grow-1 mx-2" style="height: 2px; background: {{ $stepNum < $step ? 'var(--bs-success)' : '#dee2e6' }};"></div>
                        @endif
                    </div>
                    <small class="d-block mt-1 {{ $stepNum === $step ? 'fw-bold text-primary' : 'text-muted' }}">{{ $label }}</small>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Step 1: Select Loan Type --}}
    @if ($step === 1)
        <div>
            <h5 class="mb-3"><i class="fas fa-tags me-2"></i>Select Loan Type</h5>
            <p class="text-muted small">Choose the type of loan you want to apply for.</p>
            @if (empty($availableLoanTypes))
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No loan types are currently available.</div>
            @else
                <div class="row g-3">
                    @foreach ($availableLoanTypes as $lt)
                        <div class="col-12 col-md-6">
                            <div class="card {{ $loanTypeId === $lt['id'] ? 'border-primary bg-light' : '' }} h-100" style="cursor: pointer;" wire:click="$set('loanTypeId', {{ $lt['id'] }})">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-1"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>{{ $lt['name'] }}</h6>
                                            @if (!empty($lt['description'])) <p class="text-muted small mb-2">{{ $lt['description'] }}</p> @endif
                                        </div>
                                        @if ($loanTypeId === $lt['id']) <span class="badge bg-primary"><i class="fas fa-check"></i></span> @endif
                                    </div>
                                    <div class="d-flex gap-3 small text-muted">
                                        <span><i class="fas fa-money-bill-wave me-1"></i>{{ number_format($lt['min_amount'], 2) }} - {{ number_format($lt['max_amount'], 2) }}</span>
                                        <span><i class="fas fa-calendar-alt me-1"></i>Up to {{ $lt['max_repayment_months'] }} mo</span>
                                        @if ($lt['default_interest_rate'] > 0) <span><i class="fas fa-percentage me-1"></i>{{ $lt['default_interest_rate'] }}%</span> @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Step 2: Amount & Reason --}}
    @if ($step === 2)
        <div>
            <h5 class="mb-3"><i class="fas fa-calculator me-2"></i>Amount & Reason</h5>
            @if ($selectedLoanType)
                <div class="alert alert-info small mb-3">
                    <strong>{{ $selectedLoanType['name'] }}</strong>: Min {{ number_format($selectedLoanType['min_amount'], 2) }} — Max {{ number_format($selectedLoanType['max_amount'], 2) }}
                    @if ($selectedLoanType['default_interest_rate'] > 0) &middot; {{ $selectedLoanType['default_interest_rate'] }}% p.a. @else &middot; Interest-free @endif
                </div>
            @endif
            <div class="mb-3">
                <label class="form-label fw-semibold">Loan Amount *</label>
                <div class="input-group"><span class="input-group-text">{{ $currencySymbol }}</span><input type="number" class="form-control" step="0.01" min="0" wire:model.live="amount" placeholder="Enter amount"></div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Reason *</label>
                <textarea class="form-control" rows="3" wire:model.live="reason" placeholder="Briefly explain why you need this loan..."></textarea>
            </div>
        </div>
    @endif

    {{-- Step 3: Repayment Terms --}}
    @if ($step === 3)
        <div>
            <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Repayment Terms</h5>
            <div class="mb-3">
                <label class="form-label fw-semibold">Repayment Period (months)</label>
                <select class="form-select" wire:model.live="repaymentMonths">
                    @php $maxMonths = $selectedLoanType['max_repayment_months'] ?? 12; @endphp
                    @for ($i = 1; $i <= $maxMonths; $i++) <option value="{{ $i }}">{{ $i }} month(s)</option> @endfor
                </select>
            </div>
            @if ($monthlyInstallment)
                <div class="card bg-light mb-3"><div class="card-body">
                    <h6 class="card-title"><i class="fas fa-receipt me-2"></i>Loan Summary</h6>
                    <div class="row g-3">
                        <div class="col-6 col-md-3"><small class="text-muted d-block">Principal</small><strong>{{ number_format($amount, 2) }}</strong></div>
                        <div class="col-6 col-md-3"><small class="text-muted d-block">Monthly EMI</small><strong class="text-primary">{{ number_format($monthlyInstallment, 2) }}</strong></div>
                        <div class="col-6 col-md-3"><small class="text-muted d-block">Total Interest</small><span>{{ number_format($totalInterest, 2) }}</span></div>
                        <div class="col-6 col-md-3"><small class="text-muted d-block">Total Repayable</small><strong class="text-danger">{{ number_format($totalRepayable, 2) }}</strong></div>
                    </div>
                </div></div>
            @endif
        </div>
    @endif

    {{-- Step 4: Supporting Documents (matches onboarding pattern) --}}
    @if ($step === 4)
        <div>
            <h5 class="mb-3"><i class="fas fa-paperclip me-2"></i>Supporting Documents</h5>
            <p class="text-muted small">Upload documents to support your request (optional). PDF, JPG, PNG, DOC, DOCX. Max 10MB per file.</p>

            {{-- Upload Area — uses #upload-zone and #file-input IDs matched by global navigation-layout JS --}}
            <div id="upload-zone" data-wire-id="{{ $this->getId() }}"
                class="upload-zone border rounded-3 p-4 mb-3 text-center cursor-pointer"
                style="border: 2px dashed #ccc; background: #fafbfc;">
                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                <p class="mb-1 fw-medium">Drag & drop files here or click to browse</p>
                <p class="text-xs text-muted mb-2">PDF, JPG, PNG, DOC, DOCX • Max 10 MB</p>
                <input type="file" id="file-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="form-control" onchange="if(window.__qfProcessLeaveFile) window.__qfProcessLeaveFile(this)">
                @error('newFile') <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p> @enderror
            </div>

            <div id="selected-file-info" class="mb-3" style="display: none;">
                <div class="d-flex align-items-center bg-light rounded p-2">
                    <i class="fas fa-file me-2 text-primary"></i>
                    <span id="selected-file-name" class="text-sm flex-grow-1"></span>
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="document.getElementById('file-input').value='';document.getElementById('selected-file-info').style.display='none';">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            @if ($uploading)
                <div class="text-center py-2 mb-3">
                    <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                    <span class="text-sm text-muted">Uploading document...</span>
                </div>
            @endif

            <div class="uploaded-documents">
                <h6 class="mb-3">Uploaded Documents
                    @if ($uploadedDocuments && $uploadedDocuments->count())
                        <span class="badge bg-primary ms-2">{{ $uploadedDocuments->count() }} / {{ $maxFiles }}</span>
                    @endif
                </h6>
                @if (!$uploadedDocuments || $uploadedDocuments->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                        <p>No documents uploaded yet. Use the upload area above.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light"><tr><th>#</th><th>File</th><th>Size</th><th class="text-end">Action</th></tr></thead>
                            <tbody>
                                @foreach ($uploadedDocuments as $index => $doc)
                                    <tr>
                                        <td>@if (in_array($doc->mime_type, ['image/jpeg','image/png','image/jpg'])) <i class="fas fa-image fa-2x text-muted"></i> @else <i class="fas fa-file-pdf fa-2x text-danger"></i> @endif</td>
                                        <td><span class="fw-medium">{{ $doc->file_name }}</span></td>
                                        <td class="text-nowrap text-muted small">{{ round($doc->size / 1024, 1) }} KB</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                wire:click="remove({{ $doc->id }})"
                                                wire:confirm="Remove {{ $doc->file_name }}?">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Step 5: Review & Submit --}}
    @if ($step === 5)
        <div>
            <h5 class="mb-3"><i class="fas fa-clipboard-check me-2"></i>Review & Submit</h5>
            <p class="text-muted small">Please review your loan request details before submitting.</p>
            <div class="card mb-3"><div class="card-body">
                <h6 class="card-title mb-3">Loan Request Summary</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><small class="text-muted d-block">Loan Type</small><strong>{{ $selectedLoanType['name'] ?? 'N/A' }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Amount</small><strong>{{ number_format($amount, 2) }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Repayment Period</small><strong>{{ $repaymentMonths }} month(s)</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Interest Rate</small><strong>{{ $selectedLoanType['default_interest_rate'] ?? 0 }}% p.a.</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Monthly Installment</small><strong class="text-primary">{{ number_format($monthlyInstallment, 2) }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Total Repayable</small><strong class="text-danger">{{ number_format($totalRepayable, 2) }}</strong></div>
                </div>
                @if ($reason) <div class="mb-3"><small class="text-muted d-block">Reason</small><p class="mb-0">{{ $reason }}</p></div> @endif
                @if ($uploadedDocuments && $uploadedDocuments->isNotEmpty())
                    <div class="mb-3"><small class="text-muted d-block">Documents ({{ $uploadedDocuments->count() }})</small>
                        <ul class="list-unstyled mb-0">@foreach ($uploadedDocuments as $doc) <li><i class="fas fa-file me-2 text-muted"></i>{{ $doc->file_name }}</li> @endforeach</ul>
                    </div>
                @endif
            </div></div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" wire:model.live="termsAgreed" id="termsAgreed">
                <label class="form-check-label small" for="termsAgreed">I confirm the information is accurate and I agree to the loan repayment terms.</label>
            </div>
        </div>
    @endif

    {{-- Navigation --}}
    <div class="d-flex justify-content-between mt-4">
        <div>@if ($step > 1) <button class="btn btn-outline-secondary" wire:click="previousStep"><i class="fas fa-arrow-left me-1"></i> Previous</button> @endif</div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-link text-decoration-none text-danger me-4 fw-bold p-0" wire:click="confirmCancel">Cancel</button>
            @if ($step < $totalSteps)
                <button class="btn btn-primary" wire:click="nextStep">Next <i class="fas fa-arrow-right ms-1"></i></button>
            @else
                <button class="btn btn-success" wire:click="submit"><i class="fas fa-paper-plane me-1"></i> Submit Request</button>
            @endif
        </div>
    </div>

    {{-- The global navigation-layout.blade.php handles #file-input change,
         #upload-zone click/drag-drop, and calls __qfProcessLeaveFile() --}}
    <style>
        .upload-zone { transition: border-color 0.2s, background 0.2s; cursor: pointer; }
        .upload-zone:hover { background: #f0f4f8; }
    </style>


<script>
(function () {
    if (window.__qfLoanUploadInit) return;
    window.__qfLoanUploadInit = true;
    document.addEventListener('click', function (e) {
        var zone = e.target.closest('#upload-zone');
        if (!zone) return;
        var input = document.getElementById('file-input');
        if (input && e.target !== input) input.click();
    });
    document.addEventListener('dragover', function (e) {
        var zone = e.target.closest('#upload-zone');
        if (!zone) return;
        e.preventDefault();
        zone.style.borderColor = '#0d6efd';
        zone.style.background = 'rgba(13, 110, 253, 0.05)';
    });
    document.addEventListener('dragleave', function (e) {
        var zone = e.target.closest('#upload-zone');
        if (!zone) return;
        e.preventDefault();
        zone.style.borderColor = '#ccc';
        zone.style.background = '#fafbfc';
    });
    document.addEventListener('drop', function (e) {
        var zone = e.target.closest('#upload-zone');
        if (!zone) return;
        e.preventDefault();
        zone.style.borderColor = '#ccc';
        zone.style.background = '#fafbfc';
        var input = document.getElementById('file-input');
        if (e.dataTransfer.files && e.dataTransfer.files[0] && input) {
            var dt = new DataTransfer();
            dt.items.add(e.dataTransfer.files[0]);
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        }
    });
})();
</script>
</div>
