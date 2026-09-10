<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding\Steps;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Modules\Hr\Models\Employee;
use QuickerFaster\UILibrary\Models\Document;

/**
 * Onboarding Step 4: Documents (OPTIONAL, skippable).
 *
 * Self-contained multi-file uploader that bypasses DocumentEngine.
 * Uploads are immediate -- each file is persisted to disk and a
 * polymorphic Document record is created on every upload action.
 *
 * State persistence: Documents are stored in the database and
 * re-queried on mount(). The wizard blade destroys/recreates the
 * component on step navigation, so mount() always loads fresh.
 */
class Step4Documents extends Component
{
    use WithFileUploads;

    /** @var Employee */
    public $employee;

    /** @var \Illuminate\Http\UploadedFile|null */
    public $newFile = null;

    /** @var string Document category selected by user */
    public $documentType = 'identification';

    /** @var \Illuminate\Database\Eloquent\Collection Cached uploaded documents */
    public $uploadedDocuments;

    /** @var int Maximum files allowed */
    public int $maxFiles = 10;

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

    /** @var string Disk for file storage */
    protected string $disk = 'public';

    /** @var string Subdirectory under disk for employee documents */
    protected string $storagePath = 'documents';

    protected $listeners = [
        'saveStep'   => 'save',
        'skipStep'   => 'skip',
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
            'documentType' => 'required|string|in:identification,certificate,contract,cv,other',
        ];
    }

    protected $messages = [
        'newFile.mimes' => 'Only PDF, JPG, PNG, DOC, and DOCX files are allowed.',
        'newFile.max'   => 'File must not exceed :max KB.',
        'documentType.in' => 'Please select a valid document type.',
    ];

    public function mount(?Employee $employee = null)
    {
        $this->employee = $employee;
        if ($this->employee) {
            $this->loadDocuments();
        }
    }

    /**
     * Load documents for the current employee from the database.
     */
    protected function loadDocuments(): void
    {
        if (!$this->employee || !$this->employee->getKey()) {
            $this->uploadedDocuments = collect();
            return;
        }

        $this->uploadedDocuments = Document::where('documentable_type', Employee::class)
            ->where('documentable_id', $this->employee->getKey())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Upload a single file directly to storage and create a DB record.
     */
    public function upload(): void
    {
        $this->validate();

        if (!$this->newFile) {
            $this->dispatch('notify', [
                'type'    => 'warning',
                'message' => __('Please select a file to upload.'),
            ]);
            return;
        }

        // Enforce file count limit
        if ($this->uploadedDocuments->count() >= $this->maxFiles) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => __('You can upload a maximum of :count files.', ['count' => $this->maxFiles]),
            ]);
            return;
        }

        try {
            // Build storage path: documents/{employee_id}/timestamp_originalname
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $this->newFile->getClientOriginalName());
            $path = $this->storagePath . '/' . $this->employee->getKey() . '/' . $filename;

            // Store the file
            $storedPath = $this->newFile->storeAs(
                $this->storagePath . '/' . $this->employee->getKey(),
                $filename,
                $this->disk
            );

            // Create polymorphic document record
            $document = Document::create([
                'documentable_type' => Employee::class,
                'documentable_id'   => $this->employee->getKey(),
                'name'              => $this->newFile->getClientOriginalName(),
                'file_path'         => $storedPath,
                'file_name'         => $this->newFile->getClientOriginalName(),
                'mime_type'         => $this->newFile->getMimeType(),
                'size'              => $this->newFile->getSize(),
                'document_type'     => $this->documentType,
                'disk'              => $this->disk,
            ]);

            // Set HR domain column
            $document->forceFill([
                'employee_id' => $this->employee->getKey(),
            ])->save();

            $this->newFile = null;
        } catch (\Exception $e) {
            // If the file was stored but DB insert failed, clean up
            // the orphaned file
            if (isset($storedPath) && Storage::disk($this->disk)->exists($storedPath)) {
                Storage::disk($this->disk)->delete($storedPath);
            }

            \Log::error('Step4Documents upload failed', [
                'error'       => $e->getMessage(),
                'employee_id' => $this->employee->getKey(),
                'file'        => $this->newFile ? $this->newFile->getClientOriginalName() : null,
            ]);

            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => __('Upload failed: ') . $e->getMessage(),
            ]);

            return;
        }

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => __(':file uploaded successfully.', ['file' => $filename]),
        ]);

        // Reload from DB so the list reflects current state
        $this->loadDocuments();
    }

    /**
     * Remove a document (soft delete).
     */
    public function remove(int $documentId): void
    {
        $document = Document::where('documentable_type', Employee::class)
            ->where('documentable_id', $this->employee->getKey())
            ->find($documentId);

        if (!$document) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => __('Document not found.'),
            ]);
            return;
        }

        $fileName = $document->file_name;

        try {
            $document->delete(); // Soft delete

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => __(':file removed.', ['file' => $fileName]),
            ]);
        } catch (\Exception $e) {
            \Log::error('Step4Documents remove failed', [
                'error'       => $e->getMessage(),
                'document_id' => $documentId,
            ]);

            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => __('Failed to remove document.'),
            ]);
        }

        $this->loadDocuments();
    }

    /**
     * Called by the wizard when user clicks "Save & Continue".
     * Emits stepComplete with step number 4 (wizard uses 1-indexed step numbers).
     */
    public function save(): void
    {
        $this->loadDocuments();

        $this->dispatch('stepComplete', step: 4);
    }

    /**
     * Called by the wizard when user clicks "Skip".
     */
    public function skip(): void
    {
        $this->dispatch('skipStep');
    }

    /**
     * Get the URL for a stored file (for preview/download).
     */
    public function getFileUrl(string $filePath): string
    {
        return Storage::disk($this->disk)->url($filePath);
    }

    /**
     * Determine the icon class for a given MIME type.
     */
    public function getFileIcon(string $mimeType): string
    {
        $icons = [
            'application/pdf'                                                                                          => 'fa-file-pdf',
            'application/msword'                                                                                       => 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
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
        return view('hr::onboarding.steps.step4-documents');
    }
}
