<?php

namespace App\Modules\Policies\Models;

use App\Modules\Hr\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyAcknowledgment extends Model
{
    protected $table = 'policy_acknowledgments';

    public $timestamps = true;

    protected $fillable = [
        'company_policy_id',
        'employee_id',
        'user_id',
        'status',
        'acknowledged_at',
        'ip_address',
        'user_agent',
        'signature',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function companyPolicy(): BelongsTo
    {
        return $this->belongsTo(CompanyPolicy::class, 'company_policy_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('status', 'acknowledged');
    }
}
