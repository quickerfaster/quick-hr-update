<?php

namespace App\Modules\Policies\Models;

use QuickerFaster\UILibrary\Contracts\Documents\Documentable;
use QuickerFaster\UILibrary\Traits\HasCompanyScope;
use QuickerFaster\UILibrary\Traits\Documents\HasDocuments;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyPolicy extends Model implements Documentable
{
    use HasCompanyScope;
    use HasDocuments;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'company_policies';

    public $timestamps = true;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'category',
        'content',
        'version',
        'status',
        'effective_date',
        'review_date',
        'requires_acknowledgment',
        'acknowledgment_required_by',
        'priority',
        'distribution_scope',
        'created_by',
    ];

    protected $casts = [
        'requires_acknowledgment' => 'boolean',
        'effective_date' => 'date',
        'review_date' => 'date',
        'acknowledgment_required_by' => 'date',
        'distribution_scope' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
        'version' => '1.0',
        'priority' => 'medium',
        'requires_acknowledgment' => false,
    ];

    /*
    |--------------------------------------------------------------------------
    | Documentable Contract
    |--------------------------------------------------------------------------
    */

    public function getDocumentableId(): int|string
    {
        return $this->getKey();
    }

    public function getDocumentType(): string
    {
        return 'company_policy';
    }

    public function getDocumentStoragePath(): string
    {
        return 'policies/' . $this->id;
    }

    public function getDocumentTemplateData(): array
    {
        return [
            'policy_title' => $this->title,
            'policy_version' => $this->version,
            'policy_category' => $this->category,
            'effective_date' => $this->effective_date?->format('Y-m-d'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function acknowledgments(): HasMany
    {
        return $this->hasMany(PolicyAcknowledgment::class, 'company_policy_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeRequiringAcknowledgment($query)
    {
        return $query->where('requires_acknowledgment', true);
    }
}
