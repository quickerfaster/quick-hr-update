<div>
    @if ($leaveRequest)
        <div class="mb-3">
            <h6 class="text-sm text-muted mb-3">Upload supporting documents for your leave request (optional)</h6>
        </div>
        @livewire('leave-document-upload', ['leaveRequest' => $leaveRequest], key('wizard-docs-'.$recordId))
    @else
        <div class="alert alert-warning text-sm">
            Leave request not found. Please complete the previous step first.
        </div>
    @endif
</div>