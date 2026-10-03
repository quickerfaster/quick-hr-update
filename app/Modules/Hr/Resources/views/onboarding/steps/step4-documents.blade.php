<div>
    <div class="step4-document-wrapper">
    {{-- Section Header --}}
    <div class="mb-4">
        <h5 class="mb-1">{{ __('Upload Supporting Documents') }}</h5>
        <p class="text-sm text-muted">
            {{ __('Upload identification, certificates, contracts, CV, or other relevant documents. Supported formats: PDF, JPG, PNG, DOC, DOCX. Max 10MB per file. Max :count files.', ['count' => $maxFiles]) }}
        </p>
    </div>

    {{-- Upload Area --}}
    <div
        id="upload-zone"
        class="upload-zone border rounded-3 p-4 mb-3 text-center cursor-pointer transition"
        style="border: 2px dashed #ccc; background: #fafbfc;"
    >
        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
        <p class="mb-1 fw-medium">{{ __('Drag & drop files here or click to browse') }}</p>
        <p class="text-xs text-muted mb-2">
            {{ __('PDF, JPG, PNG, DOC, DOCX • Max :size MB', ['size' => intdiv($maxFileSize, 1024)]) }}
        </p>

        {{-- File input — wire:ignore prevents Alpine/Livewire from
             replacing the DOM during re-renders, which would clear
             the file selection. Upload handled via raw JS API. --}}
        <div wire:ignore>
            <input
                type="file"
                id="file-input"
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                class="form-control"
            />
        </div>

        @error('newFile')
            <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p>
        @enderror
    </div>

    {{-- Selected File Info --}}
    <div id="selected-file-info" class="mb-3" style="display: none;">
        <div class="d-flex align-items-center bg-light rounded p-2">
            <i class="fas fa-file me-2 text-primary"></i>
            <span id="selected-file-name" class="text-sm flex-grow-1"></span>
            <button type="button" class="btn btn-sm btn-link text-danger" onclick="window.clearFile()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    {{-- Upload feedback — shown while uploadDocument() is processing --}}
    @if ($uploading)
        <div class="text-center py-2 mb-3">
            <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
            <span class="text-sm text-muted">{{ __('Uploading document...') }}</span>
        </div>
    @endif

    {{-- Document Type Selector + Upload Button --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <label for="documentType" class="form-label">{{ __('Document Type') }}</label>
            <select
                id="documentType"
                wire:model.live="documentType"
                class="form-select @error('documentType') is-invalid @enderror"
            >
                <option value="identification">{{ __('Identification') }}</option>
                <option value="certificate">{{ __('Certificate') }}</option>
                <option value="contract">{{ __('Contract') }}</option>
                <option value="cv">{{ __('CV / Resumè') }}</option>
                <option value="other">{{ __('Other') }}</option>
            </select>
            @error('documentType')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button
                type="button"
                class="btn btn-primary w-100"
                wire:click="uploadDocument"
                @if ($uploading) disabled @endif
            >
                @if ($uploading)
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    {{ __('Uploading...') }}
                @else
                    <i class="fas fa-upload me-1"></i> {{ __('Upload Document') }}
                @endif
            </button>
        </div>
    </div>

    {{-- Uploaded Documents List --}}
    <div class="uploaded-documents">
        <h6 class="mb-3">
            {{ __('Uploaded Documents') }}
            @if($uploadedDocuments && $uploadedDocuments->count())
                <span class="badge bg-primary ms-2">{{ $uploadedDocuments->count() }}</span>
            @endif
        </h6>

        @if(!$uploadedDocuments || $uploadedDocuments->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                <p>{{ __('No documents uploaded yet. Use the upload area above to add files.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>{{ __('File') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Size') }}</th>
                            <th>{{ __('Uploaded') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uploadedDocuments as $index => $doc)
                            <tr>
                                <td>
                                    @if(in_array($doc->mime_type, ['image/jpeg', 'image/png', 'image/jpg']))
                                        <img
                                            src="{{ $this->getFileUrl($doc->file_path) }}"
                                            alt="{{ $doc->file_name }}"
                                            class="rounded"
                                            style="width: 40px; height: 40px; object-fit: cover;"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                                        />
                                        <i class="fas fa-image fa-2x text-muted" style="display: none;"></i>
                                    @else
                                        <i class="fas {{ $this->getFileIcon($doc->mime_type) }} fa-2x text-muted"></i>
                                    @endif
                                </td>
                                <td>
                                    <a
                                        href="{{ $this->getFileUrl($doc->file_path) }}"
                                        target="_blank"
                                        class="text-decoration-none fw-medium"
                                    >
                                        {{ $doc->file_name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info text-white">
                                        {{ ucfirst($doc->document_type) }}
                                    </span>
                                </td>
                                <td class="text-nowrap">{{ $this->formatFileSize($doc->size) }}</td>
                                <td class="text-nowrap text-muted" style="font-size: 0.85rem;">
                                    {{ $doc->created_at ? $doc->created_at->diffForHumans() : '—' }}
                                </td>
                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        wire:click="remove({{ $doc->id }})"
                                        wire:confirm="{{ __('Are you sure you want to remove :file?', ['file' => $doc->file_name]) }}"
                                        wire:loading.attr="disabled"
                                        wire:target="remove({{ $doc->id }})"
                                    >
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

{{-- Vanilla JS for drag-and-drop and file selection display --}}
<script>
document.addEventListener('livewire:initialized', function () {
    const zone = document.getElementById('upload-zone');
    const input = document.getElementById('file-input');
    const info = document.getElementById('selected-file-info');
    const nameSpan = document.getElementById('selected-file-name');

    if (!zone || !input) return;

    // Click zone → open file dialog
    zone.addEventListener('click', function (e) {
        if (e.target !== input) {
            input.click();
        }
    });

    // Show selected filename + upload via raw Livewire JS API.
    // Using Livewire.find().upload() instead of @this.upload()
    // avoids Alpine expression evaluation errors in nested components.
    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            nameSpan.textContent = file.name;
            info.style.display = 'block';

            // Find the Livewire component instance and call upload()
            const el = input.closest('[wire\\:id]');
            if (el) {
                const component = window.Livewire.find(el.getAttribute('wire:id'));
                if (component) {
                    component.upload('newFile', file,
                        function () { /* success */ },
                        function (error) {
                            console.error('Upload failed:', error);
                            info.style.display = 'none';
                        }
                    );
                }
            }
        } else {
            info.style.display = 'none';
        }
    });

    // Drag-and-drop: transfer file to the file input, then trigger
    // change event so wire:model picks it up naturally.
    zone.addEventListener('dragover', function (e) {
        e.preventDefault();
        zone.style.borderColor = '#0d6efd';
        zone.style.background = 'rgba(13, 110, 253, 0.05)';
    });

    zone.addEventListener('dragleave', function (e) {
        e.preventDefault();
        zone.style.borderColor = '#ccc';
        zone.style.background = '#fafbfc';
    });

    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.style.borderColor = '#ccc';
        zone.style.background = '#fafbfc';

        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            // Assign the dropped file to the file input and dispatch change
            const dt = new DataTransfer();
            dt.items.add(e.dataTransfer.files[0]);
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        }
    });

    // Clear file selection (exposed on window for onclick handler)
    window.clearFile = function () {
        const input = document.getElementById('file-input');
        const info = document.getElementById('selected-file-info');
        if (input) input.value = '';
        if (info) info.style.display = 'none';
    };
});
</script>

{{-- Navigation: Save & Continue + Skip (optional step) --}}
<div class="d-flex justify-content-between mt-4 pt-3 border-top">
    <button type="button" class="btn btn-link text-decoration-none text-muted fw-bold p-0"
            wire:click="$dispatch('skipStep')">
        {{ __('Skip this step') }}
    </button>
    <button type="button" class="btn btn-primary px-4"
            wire:click="save">
        <i class="fas fa-arrow-right me-1"></i> {{ __('Save & Continue') }}
    </button>
</div>

<style>
    .upload-zone {
        transition: border-color 0.2s, background 0.2s;
        cursor: pointer;
    }
    .upload-zone:hover {
        background: #f0f4f8;
    }
    .step4-document-wrapper .table td,
    .step4-document-wrapper .table th {
        vertical-align: middle;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>
</div>
