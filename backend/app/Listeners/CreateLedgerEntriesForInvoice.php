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

        // Skip stock updates for certain non-accounting types
        if (in_array($invoice->type, ['proforma_invoice', 'delivery_challan', 'quote'])) {
            return;
        }

        $existingEntry = JournalEntry::where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->exists();

        if ($existingEntry) {
            Log::info("Ledger entries already exist for invoice {$invoice->id}, skipping.");
            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                $businessId = $invoice->business_id;

                // 1. Resolve Ledgers
                $debtorsLedger = Ledger::where('business_id', $businessId)->where('code', 'DEBTORS')->first();
                $creditorsLedger = Ledger::where('business_id', $businessId)->where('code', 'CREDITORS')->first();
                $salesLedger = Ledger::where('business_id', $businessId)->where('code', 'SALES')->first();
                $purchaseLedger = Ledger::where('business_id', $businessId)->where('code', 'PURCHASES')->first();
                $taxLedger = Ledger::where('business_id', $businessId)->where('code', 'GST_PAYABLE')->first();

                if (!$debtorsLedger || !$salesLedger || !$creditorsLedger || !$purchaseLedger) {
                    throw new \Exception("Core ledgers (Debtors/Creditors/Sales/Purchases) not found for business {$businessId}");
                }

                // 2. Create Journal Entry
                $journalEntry = JournalEntry::create([
                    'business_id' => $businessId,
                    'date' => $invoice->date,
                    'description' => $this->getJournalDescription($invoice),
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                    'status' => 'posted'
                ]);

                // 3. Mapping Logic
                if (in_array($invoice->type, ['tax_invoice', 'bill_of_supply', 'invoice'])) {
                    // SALES TRANSACTION
                    // Debit: Debtors (Asset +)
                    $this->entry($businessId, $journalEntry->id, $debtorsLedger->id, 'debit', $invoice->grand_total);
                    
                    // Credit: Sales (Revenue +)
                    $this->entry($businessId, $journalEntry->id, $salesLedger->id, 'credit', $invoice->subtotal);

                    // Credit: Tax (Liability +)
                    if ($invoice->tax_total > 0) {
                        $this->entry($businessId, $journalEntry->id, $taxLedger->id ?? $salesLedger->id, 'credit', $invoice->tax_total);
                    }
                } 
                elseif ($invoice->type === 'purchase_invoice') {
                    // PURCHASE TRANSACTION
                    // Debit: Purchases (Expense +)
                    $this->entry($businessId, $journalEntry->id, $purchaseLedger->id, 'debit', $invoice->subtotal);

                    // Debit: Tax (Asset + / Input Tax Credit)
                    if ($invoice->tax_total > 0) {
                        $this->entry($businessId, $journalEntry->id, $taxLedger->id ?? $purchaseLedger->id, 'debit', $invoice->tax_total);
                    }

                    // Credit: Creditors (Liability +)
                    $this->entry($businessId, $journalEntry->id, $creditorsLedger->id, 'credit', $invoice->grand_total);
                } 
                elseif ($invoice->type === 'credit_note') {
                    // SALES RETURN
                    // Debit: Sales (Revenue -)
                    $this->entry($businessId, $journalEntry->id, $salesLedger->id, 'debit', $invoice->subtotal);

                    // Debit: Tax (Liability -)
                    if ($invoice->tax_total > 0) {
                        $this->entry($businessId, $journalEntry->id, $taxLedger->id ?? $salesLedger->id, 'debit', $invoice->tax_total);
                    }

                    // Credit: Debtors (Asset -)
                    $this->entry($businessId, $journalEntry->id, $debtorsLedger->id, 'credit', $invoice->grand_total);
                }

                Log::info("Created ledger entries for {$invoice->type} {$invoice->invoice_number}");
            });
        } catch (\Exception $e) {
            Log::error("Failed to create ledger entries for invoice {$invoice->id}: " . $e->getMessage());
        }
    }

    protected function entry($businessId, $journalId, $ledgerId, $type, $amount)
    {
        if ($amount <= 0) return;
        
        LedgerEntry::create([
            'business_id' => $businessId,
            'journal_entry_id' => $journalId,
            'ledger_id' => $ledgerId,
            'type' => $type,
            'amount' => $amount,
        ]);
    }

    protected function getJournalDescription(Invoice $invoice): string
    {
        $typeName = str_replace('_', ' ', ucfirst($invoice->type));
        $desc = "{$typeName} {$invoice->invoice_number}";
        if ($invoice->parent_id && $invoice->parent) {
            $desc .= " (Ref: {$invoice->parent->invoice_number})";
        }
        return $desc;
    }
}
