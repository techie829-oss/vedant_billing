<?php

namespace App\Listeners;

use App\Events\InvoiceFinalized;
use App\Models\JournalEntry;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateLedgerEntriesForInvoice
{
    /**
     * Handle the event.
     */
    public function handle(InvoiceFinalized $event): void
    {
        $invoice = $event->invoice;

        // Prevent duplicate entries if invoice is already finalized
        $existingEntry = JournalEntry::where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->exists();

        if ($existingEntry) {
            Log::info("Ledger entries already exist for invoice {$invoice->id}, skipping.");
            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                // Check if ledgers exist
                $debtorsLedger = Ledger::where('business_id', $invoice->business_id)
                    ->where('code', 'DEBTORS')
                    ->first();

                if (!$debtorsLedger) {
                    throw new \Exception("DEBTORS ledger not found for business {$invoice->business_id}");
                }

                $salesLedger = Ledger::where('business_id', $invoice->business_id)
                    ->where('code', 'SALES')
                    ->first();

                if (!$salesLedger) {
                    throw new \Exception("SALES ledger not found for business {$invoice->business_id}");
                }

                // Create journal entry
                $journalEntry = JournalEntry::create([
                    'business_id' => $invoice->business_id,
                    'date' => $invoice->date,
                    'description' => $invoice->type === 'credit_note'
                        ? "Credit Note {$invoice->invoice_number} (Ref: " . ($invoice->parent ? $invoice->parent->invoice_number : 'N/A') . ")"
                        : "Invoice {$invoice->invoice_number}",
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                ]);

                if ($invoice->type === 'invoice') {
                    // Invoice Logic
                    // Debit: Debtors (Asset increases - Customer owes us)
                    LedgerEntry::create([
                        'business_id' => $invoice->business_id,
                        'journal_entry_id' => $journalEntry->id,
                        'ledger_id' => $debtorsLedger->id,
                        'type' => 'debit',
                        'amount' => $invoice->grand_total,
                    ]);

                    // Credit: Sales (Revenue increases)
                    LedgerEntry::create([
                        'business_id' => $invoice->business_id,
                        'journal_entry_id' => $journalEntry->id,
                        'ledger_id' => $salesLedger->id,
                        'type' => 'credit',
                        'amount' => $invoice->grand_total,
                    ]);
                } else {
                    // Credit Note Logic (Reverse)
                    // Debit: Sales (Revenue decreases) - Ideally "Sales Return" ledger, but using Sales for now
                    LedgerEntry::create([
                        'business_id' => $invoice->business_id,
                        'journal_entry_id' => $journalEntry->id,
                        'ledger_id' => $salesLedger->id,
                        'type' => 'debit',
                        'amount' => $invoice->grand_total,
                    ]);

                    // Credit: Debtors (Asset decreases - Customer credits)
                    LedgerEntry::create([
                        'business_id' => $invoice->business_id,
                        'journal_entry_id' => $journalEntry->id,
                        'ledger_id' => $debtorsLedger->id,
                        'type' => 'credit',
                        'amount' => $invoice->grand_total,
                    ]);
                }

                Log::info("Created ledger entries for {$invoice->type} {$invoice->invoice_number} (ID: {$invoice->id})");
            });
        } catch (\Exception $e) {
            Log::error("Failed to create ledger entries for invoice {$invoice->id}: " . $e->getMessage());
        }
    }
}
