<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding\Steps;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;
use QuickerFaster\UILibrary\Services\Documents\DocumentEngine;

/**
 * Onboarding Step 4: Documents (OPTIONAL, skippable).
 *
 * Allows the employee to upload documents such as ID, certificates,
 * or employment contracts. Uses Livewire's file upload support
 * backed by the UI Library's DocumentEngine for polymorphic storage.
 * Uploads are immediate—each document is saved on upload.
 */
class Step4Documents extends Component
{
    use WithFileUploads;

    /** @var \Livewire\TemporaryUploadedFile|null */
    public $document_file = null;

    public string $document_title = '';

    public string $document_type = '';

    public array $uploadedDocuments = [];

    protected DocumentEngine $engine;

    public function boot(DocumentEngine $engine): void
    {
        $this->engine = $engine;
    }

    public function mount(): void
    {
        $this->reloadDocuments();
    }

    /**
     * Reload documents on every Livewire hydration (e.g. back-navigation
     * re-hydrates the cached component without calling mount()).
     */
    public function hydrate(): void
    {
        $this->reloadDocuments();
    }

    /**
     * Reload the uploaded documents list from the database.
     *
     * Uses withoutCompanyScope() on the Employee lookup and then the
     * library's DocumentEngine which queries via polymorphic
     * documentable_type/documentable_id, so onboarding always sees all
     * documents even before a full company context is established.
     */
    protected function reloadDocuments(): void
    {
        $employee = Employee::withoutCompanyScope()->where('user_id', Auth::id())->first();

        if ($employee) {
            $this->uploadedDocuments = $this->engine
                ->getDocuments($employee)
                ->map(fn ($doc) => [
                    'id'         => $doc->id,
                    'name'       => $doc->name,
                    'type'       => $doc->document_type,
                    'created_at' => $doc->created_at,
                ])
                ->toArray();
        } else {
            $this->uploadedDocuments = [];
        }
    }

    public function rules(): array
    {
        return [
            'document_file'  => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'document_title' => 'required|string|max:255',
            'document_type'  => 'required|string|max:100',
        ];
    }

    /**
     * Upload a document via the library's DocumentEngine.
     *
     * The Employee model now implements Documentable and uses the
     * HasDocuments trait, so the engine saves the file to the
     * configured disk using the polymorphic documentable relationship.
     */
    public function upload(): void
    {
        $this->validate();

        $employee = Employee::withoutCompanyScope()->where('user_id', Auth::id())->first();

        if ($employee && $this->document_file) {
            $this->engine->upload(
                $employee,
                $this->document_file,
                $this->document_title,
            );

            $this->reloadDocuments();

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => __('Document uploaded successfully.'),
            ]);
        }

        // Reset form fields for next upload
        $this->reset(['document_file', 'document_title', 'document_type']);
    }

    public function save(): void
    {
        $this->dispatch('stepComplete', step: 4);
    }

    public function skip(): void
    {
        $this->dispatch('skipStep');
    }

    public function render()
    {
        return view('hr::onboarding.steps.step4-documents');
    }
}
