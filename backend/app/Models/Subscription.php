<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, HasUuids, SoftDeletes, \App\Traits\Blameable;

    protected $fillable = [
        'business_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'ends_at',
        'current_cycle_start',
        'current_cycle_end',
        'canceled_at',
        'meta',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'current_cycle_start' => 'datetime',
        'current_cycle_end' => 'datetime',
        'canceled_at' => 'datetime',
        'meta' => 'array',
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

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive()
    {
        return $this->status === 'active' || $this->onTrial();
    }

    public function onTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
