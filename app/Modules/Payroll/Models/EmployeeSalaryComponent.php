<?php

namespace App\Modules\Payroll\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A per-employee salary component (e.g. basic, housing, transport).
 *
 * Component keys are country-agnostic; the set of valid keys and their
 * aggregation into groups (e.g. 'pensionable') is defined in
 * config('quick_hr_payroll.component_groups').
 */
class EmployeeSalaryComponent extends Model
{
    use HasCompanyScope;

    protected $table = 'employee_salary_components';

    protected $fillable = [
        'company_id',
        'employee_id',
        'component_key',
        'amount',
        'currency_code',
        'effective_date',
        'expiry_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class);
    }
}
