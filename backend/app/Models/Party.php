<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use HasFactory, HasUuids, SoftDeletes, \App\Traits\Blameable;

    protected $fillable = [
        'business_id',
        'party_type',
        'name',
        'email',
        'phone',
        'gstin',
        'pan',
        'billing_address',
        'shipping_address',
        'opening_balance',
        'current_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('business', function ($builder) {
            $user = request()->user();
            if ($user && $user->currentBusinessId()) {
                $builder->where('business_id', $user->currentBusinessId());
            }
        });

        static::creating(function ($model) {
            $user = request()->user();
            if ($user && $user->currentBusinessId()) {
                $model->business_id = $user->currentBusinessId();
            }
        });

        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                \App\Models\Invoice::where('party_id', $model->id)->forceDelete();
                \App\Models\Payment::where('customer_id', $model->id)->forceDelete();
            } else {
                \App\Models\Invoice::where('party_id', $model->id)->delete();
                \App\Models\Payment::where('customer_id', $model->id)->delete();
            }
        });

        static::restoring(function ($model) {
            \App\Models\Invoice::withTrashed()->where('party_id', $model->id)->restore();
            \App\Models\Payment::withTrashed()->where('customer_id', $model->id)->restore();
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function scopeCustomers($query)
    {
        return $query->where('party_type', 'customer');
    }

    public function scopeVendors($query)
    {
        return $query->where('party_type', 'vendor');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
