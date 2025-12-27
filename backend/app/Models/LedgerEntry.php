<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LedgerEntry extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'business_id',
        'journal_entry_id',
        'ledger_id',
        'type', // debit, credit
        'amount', // decimal (Rupees)
        'description',
    ];

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
