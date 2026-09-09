<?php

namespace App\Modules\Hr\Http\Livewire;

use QuickerFaster\UILibrary\Http\Livewire\DataTables\DataTableForm;

class HrEmployeeForm extends DataTableForm
{
    public bool $sendInvitation = false;

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
        }

        parent::save();
    }

    /**
     * Override render to use the HR-specific employee form view that
     * includes the "Send Invitation" checkbox.
     */
    public function render()
    {
        $displayGroups = empty($this->allowedGroups)
            ? $this->fieldGroups
            : array_intersect_key($this->fieldGroups, array_flip($this->allowedGroups));

        return view('hr::livewire.hr-employee-form', [
            'displayGroups'    => $displayGroups,
            'fieldDefinitions' => $this->fieldDefinitions,
            'hiddenFields'     => $this->hiddenFields,
            'isEditMode'       => $this->isEditMode,
            'inline'           => $this->inline,
            'modalId'          => $this->modalId,
        ]);
    }
}
