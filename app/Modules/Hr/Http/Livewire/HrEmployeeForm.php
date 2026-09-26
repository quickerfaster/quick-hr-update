<?php

namespace App\Modules\Hr\Http\Livewire;

use QuickerFaster\UILibrary\Http\Livewire\DataTables\DataTableForm;

class HrEmployeeForm extends DataTableForm
{
    public bool $sendInvitation = false;

    public string $inviteRole = 'employee';

    public ?int $inviteCompanyId = null;

    public function mount(
        string $configKey,
        ?int $recordId = null,
        bool $inline = false,
        ?string $modalId = null,
        array $returnParams = [],
        array $allowedGroups = [],
        array $prefilledData = [],
        ?string $crudType = null
    ): void {
        parent::mount($configKey, $recordId, $inline, $modalId, $returnParams, $allowedGroups, $prefilledData, $crudType);

        // Default invite company to the session company (skip "All Companies" / 0)
        $sessionCompanyId = (int) session('current_company_id', 0);
        if ($sessionCompanyId > 0) {
            $this->inviteCompanyId = $sessionCompanyId;
        }
    }

    /**
     * Override save to flash the "send invitation" flag into the session
     * before the parent save() fires the DataTableRecordSaved event.
     *
     * The AutoInviteOnEmployeeCreate listener picks up the session
     * value and creates a pre-linked invitation after the employee is saved.
     */
    public function save(): void
    {
        if ($this->sendInvitation) {
            session()->put('hr_send_invitation_on_create', true);
            session()->put('hr_invite_role', $this->inviteRole);
            session()->put('hr_invite_company_id', $this->inviteCompanyId);
        }

        parent::save();
    }

    /**
     * Override render to use the HR-specific employee form view that
     * includes the "Send Invitation" checkbox, role selector, and company selector.
     */
    public function render()
    {
        $displayGroups = empty($this->allowedGroups)
            ? $this->fieldGroups
            : array_intersect_key($this->fieldGroups, array_flip($this->allowedGroups));

        $roles = \QuickerFaster\UILibrary\Services\AccessControl\AuthorizationService::getAssignableRoles();

        // In single-company mode, only show the current company.
        // In "All Companies" mode, show all companies so the admin can choose.
        $sessionCompanyId = (int) session('current_company_id', 0);
        if ($sessionCompanyId > 0) {
            $companies = \App\Modules\Hr\Models\Company::where('id', $sessionCompanyId)
                ->pluck('name', 'id')->toArray();
        } else {
            $companies = \App\Modules\Hr\Models\Company::orderBy('name')
                ->pluck('name', 'id')->toArray();
        }

        return view('hr::livewire.hr-employee-form', [
            'displayGroups'     => $displayGroups,
            'fieldDefinitions'  => $this->fieldDefinitions,
            'hiddenFields'      => $this->hiddenFields,
            'isEditMode'        => $this->isEditMode,
            'inline'            => $this->inline,
            'modalId'           => $this->modalId,
            'roles'             => $roles,
            'companies'         => $companies,
            'sessionCompanyId'  => $sessionCompanyId,
        ]);
    }
}
