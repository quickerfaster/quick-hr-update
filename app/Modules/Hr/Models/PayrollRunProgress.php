<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Model;


class PayrollRunProgress extends Model
{
    use HasCompanyScope;
    protected $table = 'payroll_run_progress';
    protected $fillable = ['company_id', 'payroll_run_id', 'total_employees', 'processed_employees', 'status'];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }
}
