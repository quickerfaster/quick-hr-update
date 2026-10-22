<?php

namespace App\Modules\Loans\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanType extends Model
{
    use HasCompanyScope;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'loan_types';

    public $timestamps = true;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'min_amount',
        'max_amount',
        'default_interest_rate',
        'max_repayment_months',
        'requires_approval',
        'is_active',
        'eligibility_rules',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'default_interest_rate' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'eligibility_rules' => 'array',
    ];

    protected $attributes = [
        'requires_approval' => true,
        'is_active' => true,
        'default_interest_rate' => 0,
        'max_repayment_months' => 12,
    ];

    protected $appends = ['currency_code'];

    public function getCurrencyCodeAttribute(): string
    {
        $companyId = $this->company_id;

        if ($companyId === null && $this->exists) {
            $companyId = static::where('id', $this->id)->value('company_id');
        }

        if (!$companyId) {
            return 'USD';
        }

        return \App\Modules\Hr\Models\Company::where('id', $companyId)
            ->value('currency_code') ?? 'USD';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'loan_type_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
