{{-- Onboarding Step 4: Documents (OPTIONAL) --}}
<div>
    <h4 class="mb-1">Documents</h4>
    <p class="text-muted small mb-4">
        Upload identification, certificates, or other documents. You can upload multiple files.
    </p>

    {{-- Uploaded Documents List --}}
    @if (count($uploadedDocuments) > 0)
        <div class="mb-4">
            <h6 class="text-secondary mb-2">Uploaded Documents ({{ count($uploadedDocuments) }})</h6>
            <div class="list-group list-group-flush">
                @foreach ($uploadedDocuments as $doc)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            <strong>{{ $doc['name'] }}</strong>
                            <span class="badge bg-light text-dark ms-2">{{ $doc['type'] }}</span>
                            <br>
                            <small class="text-muted">
                                Uploaded {{ \Carbon\Carbon::parse($doc['created_at'])->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Upload Form --}}
    <form wire:submit.prevent="upload">
        <div class="mb-3">
            <label for="document_title" class="form-label">Document Title <span class="text-danger">*</span></label>
            <input type="text" id="document_title" class="form-control @error('document_title') is-invalid @enderror"
                   wire:model="document_title" placeholder="e.g. National ID Card">
            @error('document_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
            <select id="document_type" class="form-select @error('document_type') is-invalid @enderror"
                    wire:model="document_type">
                <option value="">Select type...</option>
                <option value="identification">Identification</option>
                <option value="certificate">Certificate</option>
                <option value="contract">Employment Contract</option>
                <option value="cv">CV / Resume</option>
                <option value="other">Other</option>
            </select>
            @error('document_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="document_file" class="form-label">File <span class="text-danger">*</span></label>
            <input type="file" id="document_file" class="form-control @error('document_file') is-invalid @enderror"
                   wire:model="document_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            <small class="text-muted">Max 10MB. Accepted: PDF, JPG, PNG, DOC, DOCX</small>
            @error('document_file') <div class="invalid-feedback">{{ $message }}</div> @enderror

            <div wire:loading wire:target="document_file" class="mt-2">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                </div>
                <small class="text-muted">Uploading...</small>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-3">
            <button type="submit" class="btn btn-outline-primary" wire:loading.attr="disabled">
                <i class="fas fa-upload me-2"></i> Upload Document
            </button>
        </div>
    </form>

    <hr>

    <div class="d-flex justify-content-between mt-3">
        <button type="button" class="btn btn-outline-secondary" wire:click="skip">
            Skip for Now
        </button>
        <button type="button" class="btn btn-primary" wire:click="save">
            Continue
            <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</div>
