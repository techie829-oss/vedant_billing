<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LedgerEntry extends Model
{
    use HasFactory, HasUuids, \App\Traits\Blameable;

    protected $fillable = [
        'business_id',
        'journal_entry_id',
        'ledger_id',
        'type', // debit, credit
        'amount', // decimal (Rupees)
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }
}
