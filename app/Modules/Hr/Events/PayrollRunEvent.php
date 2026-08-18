<?php

namespace App\Modules\Hr\Events;

use QuickerFaster\UILibrary\Events\DataTableRecordSaved;

/**
 * HR-specific specialization of the library's generic DataTable record event.
 *
 * Kept as a distinct event class so payroll-run consumers can register a
 * targeted listener without coupling to the generic DataTableRecordSaved FQCN.
 */
class PayrollRunEvent extends DataTableRecordSaved
{
}
