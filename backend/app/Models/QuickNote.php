<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuickNote extends Model
{
    use HasFactory, HasUuids, SoftDeletes, \App\Traits\Blameable;

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

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
