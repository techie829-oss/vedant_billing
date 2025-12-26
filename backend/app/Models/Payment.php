<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'customer_id',
        'amount',
        'currency',
        'date',
        'method',
        'reference',
        'status',
        'notes',
        'meta',
    ];

    protected $casts = [
        'date' => 'date',
        'meta' => 'array',
        'amount' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function customer()
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }
}
