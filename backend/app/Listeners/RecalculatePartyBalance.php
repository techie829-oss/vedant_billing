<?php

namespace App\Listeners;

use App\Models\Party;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculatePartyBalance
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $partyId = null;

        if (isset($event->invoice)) {
            $partyId = $event->invoice->party_id;
        } elseif (isset($event->payment)) {
            $partyId = $event->payment->customer_id;
        }

        if (!$partyId) {
            return;
        }

        try {
            DB::transaction(function () use ($partyId) {
                $party = Party::findOrFail($partyId);

                // 1. Total Invoiced (Asset +) - Only for sales related types
                // Note: Purchase invoices also increase what we owe (Liability +), 
                // but for "Customers", we focus on sales.
                // A better approach is to separate "Receivables" and "Payables".
                
                $totalInvoiced = Invoice::where('party_id', $partyId)
                    ->where('status', '!=', 'draft')
                    ->whereIn('type', ['tax_invoice', 'invoice', 'bill_of_supply'])
                    ->sum('grand_total');

                $totalCreditNotes = Invoice::where('party_id', $partyId)
                    ->where('status', '!=', 'draft')
                    ->where('type', 'credit_note')
                    ->sum('grand_total');

                $totalPaid = Payment::where('customer_id', $partyId)
                    ->where('status', 'completed')
                    ->sum('amount');

                // Balance = Opening + Invoiced - CreditNotes - Paid
                $newBalance = ($party->opening_balance ?? 0) + $totalInvoiced - $totalCreditNotes - $totalPaid;

                $party->update(['current_balance' => $newBalance]);

                Log::info("Recalculated balance for Party {$partyId}: {$newBalance}");
            });
        } catch (\Exception $e) {
            Log::error("Failed to recalculate balance for Party {$partyId}: " . $e->getMessage());
        }
    }
}
