<?php

namespace App\Modules\Leave\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Modules\Leave\Models\LeaveRequest;

class LeaveDocumentUpload extends Component
{
    use WithFileUploads;

    public ?LeaveRequest $leaveRequest = null;
    public $newFile;
    public $documents = [];

    protected $listeners = [
        'documentUploaded' => 'refreshDocuments',
    ];

    /**
     * Accept either a LeaveRequest model or an int ID.
     * When nested inside wizard steps, passing an ID avoids
     * cross-component model serialization failures.
     */
    public function mount(int|LeaveRequest $leaveRequest)
    {
        if ($leaveRequest instanceof LeaveRequest) {
            $this->leaveRequest = $leaveRequest;
        } else {
            $this->leaveRequest = LeaveRequest::findOrFail($leaveRequest);
        }
        $this->refreshDocuments();
    }

    public function refreshDocuments()
    {
        $this->documents = $this->leaveRequest->getDocuments();
    }

    public function upload()
    {
        $this->validate([
            'newFile' => 'required|file|max:10240',
        ]);

        $this->leaveRequest->uploadDocument($this->newFile);
        $this->newFile = null;
        $this->refreshDocuments();

        session()->flash('message', 'Document uploaded successfully.');
    }

    public function delete($documentId)
    {
        $document = $this->leaveRequest->documents()->findOrFail($documentId);
        $this->leaveRequest->deleteDocument($document);
        $this->refreshDocuments();

        session()->flash('message', 'Document deleted.');
    }

    public function preview($documentId)
    {
        $document = $this->leaveRequest->documents()->findOrFail($documentId);

        $this->dispatch('openDocumentPreview', [
            'fileUrl' => $document->getUrl(),
            'fileName' => $document->file_name,
        ]);
    }

    public function render()
    {
        return view('leave::livewire.leave-document-upload');
    }
}