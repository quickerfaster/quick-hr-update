<?php

namespace App\Modules\Organization\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use QuickerFaster\UILibrary\Core\Organization\Models\Company;

class UserCompanyAssignment extends Component
{
    // Mode: 'single' or 'bulk'
    public $mode = 'single';

    // Single-user properties
    public $search = '';
    public $selectedUserId = null;
    public $assignedCompanyIds = [];
    public $users = [];
    public $selectedUser = null;
    public $saved = false;

    // Bulk properties
    public $bulkSearch = '';
    public $bulkUsers = [];
    public $selectedUserIds = [];
    public $selectedUsers = [];
    public $bulkAssignedCompanyIds = [];
    public $bulkSaved = false;

    // Shared
    public $companies = [];

    public function mount($user = null)
    {
        $this->companies = Company::orderBy('name')->get();

        if ($user) {
            $this->selectedUserId = $user;
            $this->loadUser($user);
        }
    }

    // ========================================
    // Mode Switching
    // ========================================

    public function switchMode($mode)
    {
        $this->mode = $mode;
        $this->resetValidation();
    }

    // ========================================
    // Single-User Methods
    // ========================================

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

    // ========================================
    // Bulk Assignment Methods
    // ========================================

    public function updatedBulkSearch()
    {
        if (strlen($this->bulkSearch) < 2) {
            $this->bulkUsers = [];
            return;
        }

        // Exclude already-selected users from search results
        $this->bulkUsers = User::where(function ($query) {
                $query->where('name', 'like', "%{$this->bulkSearch}%")
                      ->orWhere('email', 'like', "%{$this->bulkSearch}%");
            })
            ->when(!empty($this->selectedUserIds), function ($query) {
                $query->whereNotIn('id', $this->selectedUserIds);
            })
            ->limit(20)
            ->get(['id', 'name', 'email']);
    }

    public function addUserToBulk($userId)
    {
        if (in_array($userId, $this->selectedUserIds)) {
            return;
        }

        $user = User::find($userId, ['id', 'name', 'email']);
        if (!$user) {
            return;
        }

        $this->selectedUserIds[] = $userId;
        $this->selectedUsers[$userId] = $user->toArray();
        $this->bulkSearch = '';
        $this->bulkUsers = [];
    }

    public function removeUserFromBulk($userId)
    {
        $this->selectedUserIds = array_values(array_diff($this->selectedUserIds, [$userId]));
        unset($this->selectedUsers[$userId]);
    }

    public function clearBulk()
    {
        $this->selectedUserIds = [];
        $this->selectedUsers = [];
        $this->bulkAssignedCompanyIds = [];
        $this->bulkSaved = false;
    }

    public function bulkSave()
    {
        if (empty($this->selectedUserIds) || empty($this->bulkAssignedCompanyIds)) {
            return;
        }

        $count = 0;
        foreach ($this->selectedUserIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                // syncWithoutDetaching: adds new companies, preserves existing assignments
                $user->companies()->syncWithoutDetaching($this->bulkAssignedCompanyIds);
                $count++;
            }
        }

        $this->bulkSaved = true;
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Assigned " . count($this->bulkAssignedCompanyIds) . " companies to {$count} users."
        ]);
    }

    // ========================================
    // Render
    // ========================================

    public function render()
    {
        return view('organization::livewire.user-company-assignment');
    }
}
