<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentAllocation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'business_id',
        'payment_id',
        'invoice_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // Placeholder for Invoice relationship (Phase 5)
    // public function invoice() { ... }
}
