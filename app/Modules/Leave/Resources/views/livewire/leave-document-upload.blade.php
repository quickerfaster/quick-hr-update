<div>
    <div class="leave-document-wrapper">
        {{-- Wizard mode: show upload form --}}
        @if($stepIndex !== null)
            {{-- Section Header --}}
            <div class="mb-4">
                <h5 class="mb-1">{{ __('Upload Supporting Documents') }}</h5>
                <p class="text-sm text-muted">
                    {{ __('Upload medical certificates, supporting letters, or other documents to support this leave request. Supported formats: PDF, JPG, PNG, DOC, DOCX. Max :size MB per file. Max :count files.', ['size' => intdiv($maxFileSize, 1024), 'count' => $maxFiles]) }}
                </p>
            </div>

            {{-- Upload Area --}}
            <div
                id="upload-zone"
                data-wire-id="{{ $this->getId() }}"
                class="upload-zone border rounded-3 p-4 mb-3 text-center cursor-pointer transition"
                style="border: 2px dashed #ccc; background: #fafbfc;"
            >
                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                <p class="mb-1 fw-medium">{{ __('Drag & drop files here or click to browse') }}</p>
                <p class="text-xs text-muted mb-2">
                    {{ __('PDF, JPG, PNG, DOC, DOCX • Max :size MB', ['size' => intdiv($maxFileSize, 1024)]) }}
                </p>

                <input
                    type="file"
                    id="file-input"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    class="form-control"
                    onchange="if(window.__qfProcessLeaveFile) window.__qfProcessLeaveFile(this)"
                />

                @error('newFile')
                    <p class="text-danger text-xs mt-1 mb-0">{{ $message }}</p>
                @enderror
            </div>

            {{-- Selected File Info --}}
            <div id="selected-file-info" class="mb-3" style="display: none;">
                <div class="d-flex align-items-center bg-light rounded p-2">
                    <i class="fas fa-file me-2 text-primary"></i>
                    <span id="selected-file-name" class="text-sm flex-grow-1"></span>
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="window.clearLeaveFile()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Upload feedback --}}
            @if ($uploading)
                <div class="text-center py-2 mb-3">
                    <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                    <span class="text-sm text-muted">{{ __('Uploading document...') }}</span>
                </div>
            @endif

            {{-- Document Type Selector --}}
            <div class="mb-4">
                <label for="documentType" class="form-label">{{ __('Document Type') }}</label>
                <select
                    id="documentType"
                    wire:model.live="documentType"
                    class="form-select @error('documentType') is-invalid @enderror"
                >
                    @foreach ($documentTypeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('documentType')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted d-block mt-1">
                    {{ __('Select a document type, then choose a file to upload automatically.') }}
                </small>
            </div>
        @endif

        {{-- Uploaded Documents List (shown in both modes) --}}
        <div class="uploaded-documents">
            <h6 class="mb-3">
                {{ __('Supporting Documents') }}
                @if($documents && $documents->count())
                    <span class="badge bg-primary ms-2">{{ $documents->count() }}</span>
                @endif
            </h6>

            @if(!$documents || $documents->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                    <p>
                        @if($stepIndex !== null)
                            {{ __('No documents uploaded yet. Use the upload area above to add files.') }}
                        @else
                            {{ __('No supporting documents attached to this leave request.') }}
                        @endif
                    </p>
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
                                @if($stepIndex !== null)
                                    <th class="text-end">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $index => $doc)
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
                                            href="#"
                                            onclick="Livewire.dispatch('openDocumentPreview', { payload: { fileUrl: '{{ $this->getFileUrl($doc->file_path) }}', fileName: '{{ $doc->file_name }}' } }); return false;"
                                            class="text-decoration-none fw-medium"
                                            style="cursor: pointer;"
                                        >
                                            {{ $doc->file_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            {{ $documentTypeOptions[$doc->document_type] ?? ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">{{ $this->formatFileSize($doc->size) }}</td>
                                    <td class="text-nowrap text-muted" style="font-size: 0.85rem;">
                                        {{ $doc->created_at ? $doc->created_at->diffForHumans() : '—' }}
                                    </td>
                                    @if($stepIndex !== null)
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
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- JS for file upload is in the parent wizard blade
         (resources/views/vendor/qf/livewire/wizards/wizard.blade.php)
         using document-level event delegation. This ensures it runs
         once when the wizard first loads and survives all child
         component recreation cycles. --}}

    {{-- Document Preview Modal is global in navigation-layout.blade.php --}}

    <style>
        .upload-zone {
            transition: border-color 0.2s, background 0.2s;
            cursor: pointer;
        }
        .upload-zone:hover {
            background: #f0f4f8;
        }
        .leave-document-wrapper .table td,
        .leave-document-wrapper .table th {
            vertical-align: middle;
        }
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</div>
