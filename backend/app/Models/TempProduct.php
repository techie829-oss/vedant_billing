<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TempProduct extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'business_id',
        'scan_reference_id',
        'name',
        'sku',
        'price',
        'mrp',
        'discount',
        'quantity',
        'unit',
        'description',
        'hsn_code',
        'tax_rate',
        'batch_number',
        'expiry_date',
        'matched_product_id',
        'status',
        'confidence_score',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'confidence_score' => 'decimal:2',
    ];

    /**
     * Get the business that owns the temp product.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the invoice scan this temp product belongs to.
     */
    public function invoiceScan(): BelongsTo
    {
        return $this->belongsTo(InvoiceScan::class, 'scan_reference_id');
    }

    /**
     * Get the matched product (if any).
     */
    public function matchedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'matched_product_id');
    }
}
