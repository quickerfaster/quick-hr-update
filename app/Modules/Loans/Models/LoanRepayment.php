<?php

namespace App\Modules\Loans\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $table = 'loan_repayments';

    public $timestamps = true;

    protected $fillable = [
        'loan_id',
        'payroll_run_id',
        'amount',
        'principal',
        'interest',
        'due_date',
        'paid_date',
        'status',
        'payment_method',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'principal' => 'decimal:2',
        'interest' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $appends = ['currency_code'];

    /**
     * Currency code accessor — resolves through the parent loan.
     * The loan's own currency_code accessor handles the company lookup.
     */
    public function getCurrencyCodeAttribute(): string
    {
        // Try the relationship first (works when eager-loaded)
        if ($this->relationLoaded('loan') && $this->loan) {
            return $this->loan->currency_code;
        }

        // Fallback: query the loan directly
        $loan = \App\Modules\Loans\Models\Loan::where('id', $this->loan_id)->first();
        if ($loan) {
            return $loan->currency_code;
        }

        return 'USD';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id');
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

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeDueBy($query, string $date)
    {
        return $query->where('due_date', '<=', $date)->where('status', 'pending');
    }
}
