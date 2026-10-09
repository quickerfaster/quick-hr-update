<?php

namespace App\Modules\Leave\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Modules\Leave\Models\LeaveRequest;

/**
 * Leave Request Document Upload — used both standalone (on the leave request
 * detail page) and as an optional step inside the leave request wizard.
 *
 * Self-contained multi-file uploader. Uploads are immediate — each file is
 * persisted to disk and a polymorphic Document record is created on every
 * upload action via the HasDocuments trait (DocumentEngine).
 *
 * State persistence: Documents are stored in the database and re-queried on
 * mount(). The wizard blade destroys/recreates the component on step
 * navigation via :key(), so mount() always loads fresh from the DB.
 *
 * File input handling uses wire:ignore + vanilla JS (Livewire.find().upload())
 * to avoid Alpine.js expression evaluation errors and Livewire re-render
 * issues that would clear the file selection. This pattern is proven in the
 * onboarding Step4Documents component.
 */
class LeaveDocumentUpload extends Component
{
    use WithFileUploads;

    /** @var LeaveRequest|null */
    public ?LeaveRequest $leaveRequest = null;

    /** @var \Illuminate\Http\UploadedFile|null */
    public $newFile = null;

    /** @var \Illuminate\Database\Eloquent\Collection Cached uploaded documents */
    public $documents;

    /** @var string Document category selected by user */
    public string $documentType = 'supporting_document';

    /** @var int Maximum files allowed */
    public int $maxFiles = 10;

    /** @var bool True while a file upload is in progress */
    public bool $uploading = false;

    /** @var int Maximum file size in KB (10240 = 10MB) */
    public int $maxFileSize = 10240;

    /** @var array Allowed MIME types */
    public array $allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /** @var array Document type options */
    public array $documentTypeOptions = [
        'medical_certificate' => 'Medical Certificate',
        'supporting_document' => 'Supporting Document',
        'other' => 'Other',
    ];

    /** @var int|null Wizard step index (set when rendered inside a wizard) */
    public ?int $stepIndex = null;

    /** @var int|null Primary model ID from the wizard */
    public ?int $primaryModelId = null;

    /** @var string|null Wizard session ID (for session-based fallback) */
    public ?string $wizardId = null;

    protected $listeners = [
        'documentUploaded' => 'refreshDocuments',
        'saveStepForm' => 'save',
    ];

    protected function rules()
    {
        return [
            'newFile' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:' . $this->maxFileSize,
            ],
            'documentType' => 'required|string|in:medical_certificate,supporting_document,other',
        ];
    }

    protected $messages = [
        'newFile.mimes' => 'Only PDF, JPG, PNG, DOC, and DOCX files are allowed.',
        'newFile.max' => 'File must not exceed :max KB.',
        'documentType.in' => 'Please select a valid document type.',
    ];

    /**
     * Accept either a LeaveRequest model, an int ID, or wizard parameters.
     * When nested inside wizard steps, the wizard passes recordId, primaryModelId,
     * and stepIndex to avoid cross-component model serialization failures.
     */
    public function mount(
        int|LeaveRequest|null $leaveRequest = null,
        ?int $recordId = null,
        ?int $stepIndex = null,
        ?int $primaryModelId = null,
        ?string $wizardId = null,
        ?string $configKey = null,
        ?array $presetData = null
    ): void {
        $this->stepIndex = $stepIndex;
        $this->primaryModelId = $primaryModelId;
        $this->wizardId = $wizardId;

        $this->resolveLeaveRequest($leaveRequest, $recordId);
    }

    /**
     * Hydrate is called on every request — including when Livewire reuses
     * a component instance without calling mount() (e.g. when navigating
     * back to a wizard step with the same :key). We re-resolve the
     * LeaveRequest and reload documents to ensure state is never stale.
     */
    public function hydrate(): void
    {
        $this->resolveLeaveRequest();
    }

    /**
     * Livewire hook: called automatically when a file is uploaded to
     * $this->newFile via component.upload('newFile', file, ...).
     * This happens in the SAME request as the file upload, so $this->newFile
     * is still available. We process the upload immediately.
     */
    public function updatedNewFile(): void
    {
        $this->uploadDocument();
    }

    /**
     * Resolve the LeaveRequest from available sources.
     * Prefers: direct model > recordId > primaryModelId > wizard session.
     */
    protected function resolveLeaveRequest(
        int|LeaveRequest|null $leaveRequest = null,
        ?int $recordId = null
    ): void {
        if ($leaveRequest instanceof LeaveRequest) {
            $this->leaveRequest = $leaveRequest;
        } elseif ($recordId) {
            $this->leaveRequest = LeaveRequest::findOrFail($recordId);
        } elseif ($this->primaryModelId) {
            $this->leaveRequest = LeaveRequest::find($this->primaryModelId);
        } elseif ($this->wizardId && session()->has($this->wizardId)) {
            $sessionData = session()->get($this->wizardId);
            $sessionPrimaryId = $sessionData['primaryModelId'] ?? null;
            if ($sessionPrimaryId) {
                $this->leaveRequest = LeaveRequest::find($sessionPrimaryId);
            }
        }

        if ($this->leaveRequest) {
            $this->loadDocuments();
        } else {
            $this->documents = collect();
        }
    }

    /**
     * Load documents for the current leave request from the database.
     */
    protected function loadDocuments(): void
    {
        if (!$this->leaveRequest || !$this->leaveRequest->getKey()) {
            $this->documents = collect();
            return;
        }

        $this->documents = $this->leaveRequest->getDocuments();
    }

    public function refreshDocuments(): void
    {
        $this->loadDocuments();
    }

    /**
     * Upload a single file via the HasDocuments trait (DocumentEngine).
     *
     * Called by the vanilla JS file handler after the file is transferred
     * to $this->newFile via Livewire.find().upload().
     */
    public function uploadDocument(): void
    {
        $this->uploading = true;

        if (!$this->leaveRequest) {
            $this->uploading = false;
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => __('Please save the request details first before uploading documents.'),
            ]);
            return;
        }

        $this->validate();

        if (!$this->newFile) {
            $this->uploading = false;
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => __('Please select a file to upload.'),
            ]);
            return;
        }

        // Detect duplicate: compare file name, size, and MIME type
        // against ALL existing documents for this leave request.
        // This catches duplicates regardless of how many times the
        // JS change event fires or how far apart the requests are.
        $existing = $this->leaveRequest->documents()
            ->where('file_name', $this->newFile->getClientOriginalName())
            ->where('size', $this->newFile->getSize())
            ->where('mime_type', $this->newFile->getMimeType())
            ->exists();

        if ($existing) {
            $this->newFile = null;
            $this->uploading = false;
            return;
        }

        // Enforce file count limit
        if ($this->documents->count() >= $this->maxFiles) {
            $this->uploading = false;
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('You can upload a maximum of :count files.', ['count' => $this->maxFiles]),
            ]);
            return;
        }

        try {
            // Upload via the HasDocuments trait (uses DocumentEngine).
            // DocumentEngine sets document_type from $entity->getDocumentType()
            // which always returns 'leave_request' for LeaveRequest. We override
            // it below with the user-selected category.
            $document = $this->leaveRequest->uploadDocument($this->newFile);

            // Override document_type with the user's selection
            $document->forceFill([
                'document_type' => $this->documentType,
            ])->save();

            $this->newFile = null;
            $this->uploading = false;
        } catch (\Exception $e) {
            \Log::error('LeaveDocumentUpload upload failed', [
                'error' => $e->getMessage(),
                'leave_request_id' => $this->leaveRequest->getKey(),
                'file' => $this->newFile ? $this->newFile->getClientOriginalName() : null,
            ]);

            $this->uploading = false;

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('Upload failed: ') . $e->getMessage(),
            ]);
            return;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __(':file uploaded successfully.', ['file' => $document->file_name]),
        ]);

        $this->uploading = false;

        // Reload from DB so the list reflects current state
        $this->loadDocuments();
    }

    /**
     * Remove a document (soft delete via DocumentEngine).
     */
    public function remove(int $documentId): void
    {
        if (!$this->leaveRequest) {
            return;
        }

        $document = $this->leaveRequest->documents()->find($documentId);

        if (!$document) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('Document not found.'),
            ]);
            return;
        }

        $fileName = $document->file_name;

        try {
            $this->leaveRequest->deleteDocument($document);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __(':file removed.', ['file' => $fileName]),
            ]);
        } catch (\Exception $e) {
            \Log::error('LeaveDocumentUpload remove failed', [
                'error' => $e->getMessage(),
                'document_id' => $documentId,
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('Failed to remove document.'),
            ]);
        }

        $this->loadDocuments();
    }

    /**
     * Called by the wizard when user clicks "Save & Continue".
     * Emits stepFormSaved with the leave request ID so the wizard can advance.
     */
    public function save(?int $stepIndex = null): void
    {
        // Only respond if the step index matches (when called from wizard)
        if ($stepIndex !== null && $this->stepIndex !== null && $stepIndex !== $this->stepIndex) {
            return;
        }

        $this->loadDocuments();

        $recordId = $this->leaveRequest?->getKey() ?? $this->primaryModelId ?? 0;

        $this->dispatch('stepFormSaved', recordId: $recordId, stepIndex: $this->stepIndex ?? 0);
    }

    /**
     * Get the URL for a stored file (for preview/download).
     */
    public function getFileUrl(string $filePath): string
    {
        return asset('storage/' . $filePath);
    }

    /**
     * Determine the icon class for a given MIME type.
     */
    public function getFileIcon(?string $mimeType): string
    {
        if ($mimeType === null) {
            return 'fa-file';
        }

        $icons = [
            'application/pdf' => 'fa-file-pdf',
            'application/msword' => 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
            'image/jpeg' => 'fa-file-image',
            'image/png' => 'fa-file-image',
            'image/jpg' => 'fa-file-image',
        ];

        return $icons[$mimeType] ?? 'fa-file';
    }

    /**
     * Format bytes to human-readable size.
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function render()
    {
        return view('leave::livewire.leave-document-upload');
    }
}
