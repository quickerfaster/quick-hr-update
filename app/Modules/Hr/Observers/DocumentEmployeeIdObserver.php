<?php

namespace App\Modules\Hr\Observers;

use QuickerFaster\UILibrary\Models\Document;
use App\Modules\Hr\Models\Employee;

/**
 * Bridges the library Document model's polymorphic columns to the
 * HR-specific employee_id column on the shared documents table.
 *
 * The library DocumentEngine and HasDocuments trait only populate
 * documentable_type / documentable_id. This observer ensures that
 * when the documentable entity is an Employee, the employee_id
 * column is also populated, so the document appears in the ESS
 * "my-profile" documents tab (which filters by employee_id).
 *
 * Registered in HrsServiceProvider::boot().
 */
class DocumentEmployeeIdObserver
{
    /**
     * Populate employee_id when a library Document is created for
     * an Employee documentable entity.
     */
    public function creating(Document $document): void
    {
        if ($document->documentable_type !== Employee::class) {
            return;
        }

        if (empty($document->employee_id)) {
            $document->employee_id = $document->documentable_id;
        }

        // company_id is required for HasCompanyScope on the HR Document
        // model used by the DataTable at /hr/documents.
        if (empty($document->company_id)) {
            $employee = Employee::withoutCompanyScope()->find($document->documentable_id);
            if ($employee && $employee->company_id) {
                $document->company_id = $employee->company_id;
            }
        }
    }
}
