<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($business) {
            $business->slug = \Illuminate\Support\Str::slug($business->name);
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'status',
        'mobile',
        'address',
        'gstin',
        'pan',
        'website',
        'bank_name',
        'account_number',
        'ifsc_code',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'business_users')
            ->using(BusinessUser::class)
            ->withPivot(['role', 'status', 'joined_at', 'deleted_at'])
            ->withTimestamps();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->latest();
    }

    public function featureOverrides()
    {
        return $this->hasMany(BusinessFeatureOverride::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parties()
    {
        return $this->hasMany(Party::class);
    }
}
