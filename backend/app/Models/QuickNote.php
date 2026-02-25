<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuickNote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'business_id',
        'user_id',
        'type',
        'title',
        'description',
        'customer_name',
        'customer_mobile',
        'content',
        'total_amount',
    ];

    protected $casts = [
        'content' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
