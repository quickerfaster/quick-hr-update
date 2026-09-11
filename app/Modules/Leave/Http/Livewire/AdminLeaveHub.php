<?php

namespace App\Modules\Leave\Http\Livewire;

use Livewire\Component;

class AdminLeaveHub extends Component
{
    public string $activeTab = 'overview';

    public function mount(): void
    {
        if (request()->has('tab') && in_array(request()->query('tab'), ['overview', 'all-requests', 'apply-for-employee'], true)) {
            $this->activeTab = request()->query('tab');
        }
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->dispatch('update-url', tab: $tab);
    }

    public function render()
    {
        return view('leave::livewire.admin-leave-hub');
    }
}
