<div>
    <div class="card mb-4">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center">
                <h6 class="mb-0">Supporting Documents</h6>
                <span class="badge bg-secondary ms-2">{{ count($documents) }}</span>
            </div>
        </div>
        <div class="card-body">
            {{-- Flash Message --}}
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show text-white" role="alert">
                    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                    <span class="alert-text">{{ session('message') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Upload Form --}}
            <div class="mb-4">
                <label for="document-upload" class="form-label">Upload Supporting Document</label>
                <div class="input-group">
                    <input
                        type="file"
                        id="document-upload"
                        class="form-control"
                        wire:model="newFile"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    >
                    <button
                        class="btn btn-primary mb-0"
                        wire:click="upload"
                        wire:loading.attr="disabled"
                        wire:target="newFile"
                    >
                        <span wire:loading.remove wire:target="upload">
                            <i class="fas fa-upload me-1"></i> Upload
                        </span>
                        <span wire:loading wire:target="upload">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Uploading...
                        </span>
                    </button>
                </div>
                @error('newFile')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                <div wire:loading wire:target="newFile" class="mt-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-primary" style="width: 100%;"></div>
                    </div>
                    <small class="text-muted">Preparing file...</small>
                </div>
                <small class="text-muted d-block mt-1">
                    Accepted: PDF, DOC, DOCX, JPG, PNG (max 10MB)
                </small>
            </div>

            {{-- Document List --}}
            @if (count($documents) > 0)
                <div class="table-responsive">
                    <table class="table table-flush align-items-center">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">File Name</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Size</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Uploaded</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $document)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-shape bg-light rounded-circle shadow text-center me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                <i class="fas fa-file text-secondary text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-weight-bold mb-0">{{ $document->file_name }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $size = $document->size;
                                            if ($size >= 1048576) {
                                                $displaySize = round($size / 1048576, 2) . ' MB';
                                            } elseif ($size >= 1024) {
                                                $displaySize = round($size / 1024, 2) . ' KB';
                                            } else {
                                                $displaySize = $size . ' B';
                                            }
                                        @endphp
                                        <span class="text-sm">{{ $displaySize }}</span>
                                    </td>
                                    <td>
                                        <span class="text-sm">{{ $document->created_at->format('M d, Y h:i A') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <button
                                            class="btn btn-link text-secondary mb-0"
                                            wire:click="preview({{ $document->id }})"
                                            title="Preview"
                                        >
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <a
                                            href="{{ $document->getDownloadUrl() }}"
                                            class="btn btn-link text-secondary mb-0"
                                            title="Download"
                                        >
                                            <i class="fas fa-download text-sm"></i>
                                        </a>
                                        <button
                                            class="btn btn-link text-danger mb-0"
                                            wire:click="delete({{ $document->id }})"
                                            wire:confirm="Are you sure you want to delete this document?"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <div class="icon icon-shape bg-light rounded-circle shadow text-center mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="fas fa-cloud-upload-alt text-secondary text-lg"></i>
                    </div>
                    <p class="text-sm text-secondary mb-0">No supporting documents uploaded yet.</p>
                    <p class="text-xs text-secondary">Upload documents to support this leave request.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Document Preview Modal --}}
    @livewire('qf.document-preview-modal')
</div>