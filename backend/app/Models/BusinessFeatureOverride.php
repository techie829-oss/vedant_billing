<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BusinessFeatureOverride extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'feature_id',
        'limit',
        'expires_at',
    ];

    protected $casts = [
        'limit' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
