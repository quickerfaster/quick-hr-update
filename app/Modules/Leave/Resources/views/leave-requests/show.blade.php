@php
    use QuickerFaster\UILibrary\Services\Config\ConfigResolver;
    use App\Modules\Leave\Models\LeaveRequest;

    $resolver = app(ConfigResolver::class, ['configKey' => "leave.leave_request"]);
    $config = $resolver->getConfig();
    $customComponent = !empty($config['detailComponent']) ? $config['detailComponent'] : 'qf.data-table-detail';

    /** @var LeaveRequest|null $record */
    $record = LeaveRequest::find($recordId);
    $activeWorkflow = $record?->activeWorkflow;
    $showApprovalUi = $record && ($record->isUnderApproval() || $activeWorkflow);
@endphp

<x-qf::navigation-layout configKey="leave.leave_request" context="requests" moduleName="leave" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    @if($showApprovalUi)
        @livewire('qf.approval-panel', ['workflowId' => $activeWorkflow?->id, 'displayMode' => 'banner'])
    @endif

    @livewire($customComponent, ["inline" => true, "recordId" => $recordId, "configKey" => "leave.leave_request", "returnParams" => $returnParams])

    @if($record)
        @livewire('leave-document-upload', ['leaveRequest' => $record])
    @endif
</x-qf::navigation-layout>