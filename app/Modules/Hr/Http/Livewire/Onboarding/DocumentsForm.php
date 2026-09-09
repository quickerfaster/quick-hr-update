<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\Document;

/**
 * Onboarding Step 5: Upload required documents.
 *
 * Allows the employee to upload documents such as ID, certificates,
 * or employment contracts. Uses Livewire's file upload support.
 */
class DocumentsForm extends Component
{
    use WithFileUploads;

    /** @var \Livewire\TemporaryUploadedFile|null */
    public $document_file = null;

    public string $document_title = '';

    public string $document_type = '';

    public array $uploadedDocuments = [];

    public function mount(): void
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            $this->uploadedDocuments = $employee->documents()
                ->select('id', 'title', 'type', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }
    }

    public function rules(): array
    {
        return [
            'document_file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'document_title' => 'required|string|max:255',
            'document_type' => 'required|string|max:100',
        ];
    }

    public function upload(): void
    {
        $this->validate();

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee && $this->document_file) {
            $path = $this->document_file->store('employee-documents', 'public');

            $document = new Document();
            $document->employee_id = $employee->id;
            $document->title = $this->document_title;
            $document->type = $this->document_type;
            $document->file_path = $path;
            $document->file_name = $this->document_file->getClientOriginalName();
            $document->file_size = $this->document_file->getSize();
            $document->save();

            // Refresh the uploaded documents list
            $this->uploadedDocuments = $employee->documents()
                ->select('id', 'title', 'type', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }

        // Reset form fields for next upload
        $this->reset(['document_file', 'document_title', 'document_type']);
    }

    public function save(): void
    {
        $this->redirectToNextStep();
    }

    public function skip(): void
    {
        $this->redirectToNextStep();
    }

    protected function redirectToNextStep(): void
    {
        $this->redirect(route('hr.onboarding.notification-preferences'));
    }

    public function render()
    {
        return view('hr::onboarding.documents');
    }
}
