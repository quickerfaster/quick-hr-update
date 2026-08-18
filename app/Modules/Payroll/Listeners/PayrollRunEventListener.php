<?php

namespace App\Modules\Payroll\Listeners;

use QuickerFaster\UILibrary\Events\DataTableRecordSaved;
use QuickerFaster\UILibrary\Listeners\DataTableRecordListener;

class PayrollRunEventListener extends DataTableRecordListener
{
    protected function handleUpdated(DataTableRecordSaved $event): void
    {
        if (!str_contains($event->model, 'PayrollRun')) {
            return;
        }

        // Specific handling for PayrollRun status changes.
        // Business logic to be added as needed.
    }
}
