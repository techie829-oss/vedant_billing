<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Models\JournalEntry;
use App\Models\Ledger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecordPaymentToLedger implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;

        if ($payment->status !== 'completed') {
            return;
        }

        // 1. Find Accounts
        // Debit: Bank/Cash (Asset increases)
        // For now, let's look for a generic "Cash" or "Bank" ledger. 
        // If not found, fallback to creating one or logging error.
        $cashAccount = Ledger::where('business_id', $payment->business_id)
            ->where(function ($q) {
                $q->where('code', '1010')
                    ->orWhere('name', 'Cash')
                    ->orWhere('name', 'Bank');
            })->first();

        // Credit: Debtors (Asset decreases)
        $debtorsAccount = Ledger::where('business_id', $payment->business_id)
            ->where('code', 'DEBTORS')
            ->first();

        if (!$cashAccount || !$debtorsAccount) {
            \Log::error("Missing ledgers for payment {$payment->id}. Cash: " . ($cashAccount ? 'Found' : 'Missing') . ", Debtors: " . ($debtorsAccount ? 'Found' : 'Missing'));
            return;
        }

        // 2. Create Journal Entry
        DB::transaction(function () use ($payment, $cashAccount, $debtorsAccount) {
            $journalEntry = JournalEntry::create([
                'id' => Str::uuid(),
                'business_id' => $payment->business_id,
                'date' => $payment->date,
                'description' => 'Payment Received: ' . ($payment->reference ?? 'N/A'),
                'reference_type' => \App\Models\Payment::class,
                'reference_id' => $payment->id,
                'status' => 'posted',
            ]);

            // 3. Create Ledger Entries

            // DEBIT Cash (Asset increases)
            $journalEntry->entries()->create([
                'business_id' => $payment->business_id,
                'ledger_id' => $cashAccount->id,
                'type' => 'debit',
                'amount' => $payment->amount,
                'description' => 'Payment received',
            ]);

            // CREDIT Debtors (Asset decreases)
            $journalEntry->entries()->create([
                'business_id' => $payment->business_id,
                'ledger_id' => $debtorsAccount->id,
                'type' => 'credit',
                'amount' => $payment->amount,
                'description' => 'Payment applied to invoice(s)',
            ]);

            \Log::info("Created ledger entries for payment {$payment->id}");
        });
    }
}
