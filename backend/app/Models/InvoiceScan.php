<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceScan extends Model
{
    use HasFactory, HasUuids, \App\Traits\Blameable;

    protected $fillable = [
        'business_id',
        'image_path',
        'vendor_name',
        'invoice_number',
        'invoice_date',
        'raw_ocr_text',
        'llm_response',
        'status',
        'error_message',
        'products_count',
        'invoice_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'llm_response' => 'array',
        'products_count' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('business', function ($builder) {
            if (auth()->check() && auth()->user()->currentBusinessId()) {
                $builder->where('business_id', auth()->user()->currentBusinessId());
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->currentBusinessId()) {
                $model->business_id = auth()->user()->currentBusinessId();
            }
        });
    }

    /**
     * Get the business that owns the scan.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get all temp products from this scan.
     */
    public function tempProducts(): HasMany
    {
        return $this->hasMany(TempProduct::class, 'scan_reference_id');
    }
}
