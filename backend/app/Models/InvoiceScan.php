<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceScan extends Model
{
    use HasFactory, HasUuids;

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
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'llm_response' => 'array',
        'products_count' => 'integer',
    ];

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
