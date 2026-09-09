<?php

namespace App\Modules\Hr\Http\Livewire;

use QuickerFaster\UILibrary\Http\Livewire\DataTables\DataTableForm;
use App\Modules\Hr\Models\Employee;
use Livewire\Attributes\On;

class HrInvitationForm extends DataTableForm
{
    public ?int $employeeId = null;
    public ?Employee $selectedEmployee = null;

    /**
     * Listen for the employeeSelected event from SearchableEmployeeDropdown.
     */
    #[On('employeeSelected')]
    public function onEmployeeSelected(int $id): void
    {
        $this->employeeId = $id;
        $this->selectedEmployee = Employee::find($id);
    }

    /**
     * Override save to flash the employee ID into the session before
     * the parent save() fires the DataTableRecordSaved event.
     *
     * The PreLinkInvitationToEmployee listener picks up the session
     * value and links the invitation to the employee after creation.
     */
    public function save(): void
    {
        if ($this->employeeId) {
            session()->put('hr_invitation_employee_id', $this->employeeId);
        }

        parent::save();
    }

    /**
     * Override render to use the HR-specific form view that includes
     * the employee searchable dropdown.
     */
    public function render()
    {
        $displayGroups = empty($this->allowedGroups)
            ? $this->fieldGroups
            : array_intersect_key($this->fieldGroups, array_flip($this->allowedGroups));

        return view('hr::livewire.hr-invitation-form', [
            'displayGroups'    => $displayGroups,
            'fieldDefinitions' => $this->fieldDefinitions,
            'hiddenFields'     => $this->hiddenFields,
            'isEditMode'       => $this->isEditMode,
            'inline'           => $this->inline,
            'modalId'          => $this->modalId,
        ]);
    }
}
