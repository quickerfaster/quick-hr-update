<?php

namespace App\Modules\Hr\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;

class LeaveHub extends Component
{
    public string $activeTab = 'overview';

    public ?int $employeeId = null;

    public ?string $employeeNumber = null;

    public ?string $employeeName = null;

    public function mount(): void
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if (!$employee) {
            abort(403, 'No employee record found for your account.');
        }

        $this->employeeId = $employee->id;
        $this->employeeNumber = $employee->employee_number;
        $this->employeeName = $employee->full_name ?? $employee->first_name . ' ' . $employee->last_name;

        // Read tab from query string to persist active tab on refresh
        if (request()->has('tab') && in_array(request()->query('tab'), ['overview', 'my-leaves', 'apply'])) {
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
        return view('hr::livewire.leave-hub');
    }
}