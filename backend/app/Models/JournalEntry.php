<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class JournalEntry extends Model
{
    use HasFactory, HasUuids, SoftDeletes, \App\Traits\Blameable;

    protected $fillable = [
        'business_id',
        'date',
        'description',
        'reference',
        'reference_type',
        'reference_id',
        'status', // draft, posted, archived
        'meta',
    ];

    protected $casts = [
        'date' => 'date',
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

    public function entries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
