<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'sku',
        'hsn_code',
        'type', // goods, service
        'sale_price',
        'purchase_price',
        'current_stock',
        'unit',
        'hsn_code',
        'description',
        'tax_rate',
        'status',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'current_stock' => 'decimal:2',
    ];

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

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function scopeGoods($query)
    {
        return $query->where('type', 'goods');
    }

    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
