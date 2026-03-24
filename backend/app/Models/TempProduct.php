<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TempProduct extends Model
{
    use HasFactory, HasUuids, \App\Traits\Blameable;

    protected $fillable = [
        'business_id',
        'scan_reference_id',
        'name',
        'sku',
        'price',
        'discount',
        'discount_type',
        'quantity',
        'unit',
        'description',
        'hsn_code',
        'tax_rate',
        'cess_rate',
        'cess_amount',
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
        'cess_rate' => 'decimal:2',
        'cess_amount' => 'decimal:2',
        'confidence_score' => 'decimal:2',
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
