<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InvoiceScan;
use App\Models\Invoice;

class LinkOrphanScans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:link-orphan-scans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link existing InvoiceScans to their Invoices if they were converted before the invoice_id column was added.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scans = InvoiceScan::where('status', 'success')->whereNull('invoice_id')->get();
        $linked = 0;
        foreach ($scans as $scan) {
            $invoice = null;
            if ($scan->invoice_number) {
                $invoice = Invoice::where('business_id', $scan->business_id)
                    ->where('type', 'purchase_invoice')
                    ->where('invoice_number', 'ilike', '%' . $scan->invoice_number . '%')
                    ->first();
            } else if ($scan->vendor_name) {
                // Try to link by vendor name and date
                $party = \App\Models\Party::where('business_id', $scan->business_id)
                    ->where('name', 'ilike', '%' . $scan->vendor_name . '%')
                    ->first();
                if ($party && $scan->invoice_date) {
                    $invoice = Invoice::where('business_id', $scan->business_id)
                        ->where('type', 'purchase_invoice')
                        ->where('party_id', $party->id)
                        ->whereDate('date', $scan->invoice_date)
                        ->first();
                }
            }

            if ($invoice) {
                $scan->invoice_id = $invoice->id;
                $scan->save();
                $linked++;
                $this->info("Linked scan {$scan->id} to invoice {$invoice->id}");
            } else {
                $this->warn("Unlinked scan {$scan->id} | Vendor: {$scan->vendor_name} | Invoice No: {$scan->invoice_number}");
            }
        }

        $recentInvoices = Invoice::where('type', 'purchase_invoice')
            ->latest()
            ->take(5)
            ->get(['id', 'invoice_number', 'party_id']);
        $this->info("Recent 5 Purchase Invoices:");
        foreach ($recentInvoices as $inv) {
            $this->info("- Inv No: {$inv->invoice_number} (ID: {$inv->id})");
        }

        $this->info("Successfully linked $linked scans to invoices.");
    }
}
