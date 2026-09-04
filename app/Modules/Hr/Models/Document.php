<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;

use Illuminate\Database\Eloquent\Model;


class Document extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'documents';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'name', 'type', 'document', 'file_path', 'file_name',
        'uploaded_at', 'expiry_date', 'description',
        'documentable_type', 'documentable_id'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'uploaded_at' => 'date',
        'expiry_date' => 'date'
    ];

    protected $attributes = [

    ];

    protected $dispatchesEvents = [

    ];

    /**
     * Validation rules for the model.
     */
    protected static $rules = [

    ];

    /**
     * Custom validation messages.
     */
    protected static $messages = [

    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->documentable_type) && !empty($document->employee_id)) {
                $document->documentable_type = \App\Modules\Hr\Models\Employee::class;
                $document->documentable_id = $document->employee_id;
            }

            // Copy file upload path to the NOT NULL database columns
            // The form field 'document' stores the upload path, but the
            // library's base migration requires 'file_path' and 'file_name'.
            if (empty($document->file_path) && !empty($document->document)) {
                $document->file_path = $document->document;
                $document->file_name = pathinfo($document->document, PATHINFO_BASENAME);
            }

            $document->uploaded_at = $document->uploaded_at ?? now();
        });
    }

    /**
     * Validate the model instance.
     */
    public function validate()
    {
        $validator = Validator::make($this->attributesToArray(), static::$rules, static::$messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

    /**
     * Save the model to the database with validation.
     */
    public function save(array $options = [])
    {
        $this->validate();
        return parent::save($options);
    }

    public function employee()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'employee_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }

    /**
     * Polymorphic relationship for the documentable entity.
     */
    public function documentable()
    {
        return $this->morphTo();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\DocumentFactory::new();
    }
}
