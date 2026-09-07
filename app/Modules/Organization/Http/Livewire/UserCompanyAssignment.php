<?php

namespace App\Modules\Organization\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use QuickerFaster\UILibrary\Core\Organization\Models\Company;
use QuickerFaster\UILibrary\Services\Search\SearchEngine;

class UserCompanyAssignment extends Component
{
    public $search = '';
    public $selectedUserId = null;
    public $assignedCompanyIds = [];
    public $users = [];
    public $companies = [];
    public $selectedUser = null;
    public $saved = false;

    protected $listeners = ['userSelected' => 'selectUser'];

    public function mount($user = null)
    {
        $this->companies = Company::orderBy('name')->get();

        if ($user) {
            $this->selectedUserId = $user;
            $this->loadUser($user);
        }
    }

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->users = [];
            return;
        }

        $this->users = User::where('name', 'like', "%{$this->search}%")
            ->orWhere('email', 'like', "%{$this->search}%")
            ->limit(20)
            ->get(['id', 'name', 'email']);
    }

    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->search = '';
        $this->users = [];
        $this->loadUser($userId);
    }

    public function clearUser()
    {
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->assignedCompanyIds = [];
        $this->saved = false;
    }

    public function save()
    {
        if (!$this->selectedUserId) {
            return;
        }

        $user = User::findOrFail($this->selectedUserId);
        $user->companies()->sync($this->assignedCompanyIds);

        $this->saved = true;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Company assignments saved successfully.']);
    }

    protected function loadUser($userId)
    {
        $this->selectedUser = User::with('companies')->find($userId);

        if ($this->selectedUser) {
            $this->assignedCompanyIds = $this->selectedUser->companies->pluck('id')->toArray();
        }

        $this->saved = false;
    }

    public function render()
    {
        return view('organization::livewire.user-company-assignment');
    }
}
