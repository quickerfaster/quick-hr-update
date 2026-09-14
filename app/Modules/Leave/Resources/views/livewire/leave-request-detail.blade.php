<div>
    @if (!$leaveRequest)
        <div class="alert alert-warning m-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Leave request not found. It may have been deleted or is not accessible.
        </div>
    @else
        {{-- Approval Panel --}}
        @if($leaveRequest->isUnderApproval() || $activeWorkflow)
            @livewire('qf.approval-panel', ['workflowId' => $activeWorkflow?->id, 'displayMode' => 'banner'])
            <br />
        @endif

        {{-- Field Groups (reuses the library's data-table-detail) --}}
        @livewire('qf.data-table-detail', [
            'configKey' => $configKey,
            'recordId' => $recordId,
            'inline' => true,
        ])

        {{-- Document Upload --}}
        @livewire('leave-document-upload', ['leaveRequest' => $leaveRequest])
    @endif
</div>
