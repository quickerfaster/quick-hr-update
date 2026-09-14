<?php

namespace App\Modules\Leave\Http\Livewire;

use Livewire\Component;
use App\Modules\Leave\Models\LeaveRequest;
use QuickerFaster\UILibrary\Concerns\ResolvesModels;

class LeaveRequestDetail extends Component
{
    use ResolvesModels;

    public int $recordId;
    public string $configKey;
    public ?LeaveRequest $leaveRequest = null;
    public array $returnParams = [];

    protected $listeners = [
        'refreshDetail' => '$refresh',
    ];

    public function mount(int $recordId, string $configKey, array $returnParams = []): void
    {
        $this->recordId = $recordId;
        $this->configKey = $configKey;
        $this->returnParams = $returnParams;

        $this->leaveRequest = $this->resolveModel(LeaveRequest::class, $recordId, [
            function ($query) {
                return $query->with(['employee', 'leaveType', 'workflow']);
            },
        ]);
    }

    public function render()
    {
        return view('leave::livewire.leave-request-detail', [
            'leaveRequest' => $this->leaveRequest,
            'activeWorkflow' => $this->leaveRequest?->activeWorkflow,
        ]);
    }
}
