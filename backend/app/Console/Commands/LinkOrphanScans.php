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

        $this->info("Found {$scans->count()} successful scans without an invoice_id.");

        foreach ($scans as $scan) {
            $invoice = null;

            // Strategy 1: Exact or partial Invoice Number match within the same business
            if ($scan->invoice_number) {
                // Remove whitespace and make lowercase for better matching
                $cleanScanNo = preg_replace('/\s+/', '', strtolower($scan->invoice_number));

                // Get invoices for business
                $businessInvoices = Invoice::where('business_id', $scan->business_id)
                    ->where('type', 'purchase_invoice')
                    ->get();

                foreach ($businessInvoices as $binv) {
                    $cleanInvNo = preg_replace('/\s+/', '', strtolower($binv->invoice_number));
                    if ($cleanInvNo === $cleanScanNo || str_contains($cleanInvNo, $cleanScanNo)) {
                        $invoice = $binv;
                        break;
                    }
                }
            }

            // Strategy 2: Match to ANY purchase invoice created on the same day for this business
            if (!$invoice) {
                $scanDateStr = $scan->created_at->format('Y-m-d');
                $invoice = Invoice::where('business_id', $scan->business_id)
                    ->where('type', 'purchase_invoice')
                    ->whereDate('created_at', $scanDateStr)
                    ->latest()
                    ->first();
            }

            if ($invoice) {
                $scan->invoice_id = $invoice->id;
                $scan->save();
                $linked++;
                $this->info("✅ Linked Scan #{$scan->id} (Ref: {$scan->invoice_number}) to Invoice #{$invoice->id} (Ref: {$invoice->invoice_number})");
            } else {
                $this->warn("❌ Could not link Scan #{$scan->id} | Vendor: {$scan->vendor_name} | Invoice No: {$scan->invoice_number}");
            }
        }

        $this->info("Successfully linked $linked scans to invoices.");
    }
}
