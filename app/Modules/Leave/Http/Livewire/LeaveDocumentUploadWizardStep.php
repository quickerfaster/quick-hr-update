<?php

namespace App\Modules\Leave\Http\Livewire;

use Livewire\Component;
use App\Modules\Leave\Models\LeaveRequest;

class LeaveDocumentUploadWizardStep extends Component
{
    public $configKey;
    public $presetData = [];
    public $stepIndex;
    public $recordId;
    public ?LeaveRequest $leaveRequest = null;

    // Accepted but unused — the wizard blade passes these to all form steps
    public $stepGroups = [];
    public $customValidation = [];
    public $dynamicFields = [];
    public $draftSuccessMessage = null;

    protected $listeners = [
        'saveStepForm' => 'handleSaveStepForm',
    ];

    public function mount($configKey, $presetData = [], $stepIndex = 0, $recordId = null, $stepGroups = [], $customValidation = [], $dynamicFields = [], $draftSuccessMessage = null)
    {
        $this->configKey = $configKey;
        $this->presetData = $presetData;
        $this->stepIndex = $stepIndex;
        $this->recordId = $recordId;
        $this->stepGroups = $stepGroups;
        $this->customValidation = $customValidation;
        $this->dynamicFields = $dynamicFields;
        $this->draftSuccessMessage = $draftSuccessMessage;

        // Resolve the LeaveRequest from the wizard's preset data.
        // When step has 'requiresLink' => true, the wizard passes the source step's
        // record ID via getPresetDataForCurrentStep() using the linkFields.databaseField key
        // ('employee_id' — but contains the LeaveRequest ID from step 0).
        $leaveRequestId = $recordId
            ?? ($presetData['employee_id'] ?? null)
            ?? ($presetData['leave_request_id'] ?? null);

        if ($leaveRequestId) {
            $this->leaveRequest = LeaveRequest::find($leaveRequestId);
            $this->recordId = $leaveRequestId;
        }
    }

    /**
     * Handle the saveStepForm event from the wizard.
     * Document upload is optional, so we always signal completion.
     */
    public function handleSaveStepForm($stepIndex): void
    {
        if ($stepIndex == $this->stepIndex) {
            $this->dispatch('stepFormSaved', stepIndex: $this->stepIndex, recordId: $this->recordId);
        }
    }

    public function render()
    {
        return view('leave::livewire.leave-document-upload-wizard-step');
    }
}