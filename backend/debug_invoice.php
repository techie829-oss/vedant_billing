<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoice = \App\Models\Invoice::with('items')->latest()->first();

if ($invoice) {
    echo "INVOICE FOUND: " . $invoice->invoice_number . "\n";
    echo "EWAY BILL: " . ($invoice->eway_bill_no ?? 'NULL') . "\n";
    echo "VEHICLE: " . ($invoice->vehicle_no ?? 'NULL') . "\n";
    echo "PO: " . ($invoice->po_number ?? 'NULL') . "\n";
    echo "META: " . json_encode($invoice->meta, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "NO INVOICE FOUND\n";
}
