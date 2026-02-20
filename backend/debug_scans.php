<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$scans = \App\Models\InvoiceScan::orderBy('id', 'desc')->take(5)->get();
foreach ($scans as $s) {
    echo "Scan ID: {$s->id} | Status: {$s->status} | Error: {$s->error_message}\n";
    if ($s->status === 'pending') {
        echo "--> Processing scan {$s->id} synchronously...\n";
        try {
            \App\Jobs\ProcessInvoiceScan::dispatchSync($s->id, $s->business_id);
            $s->refresh();
            echo "--> Finished. New status: {$s->status}\n";
        } catch (\Exception $e) {
            echo "--> Caught Exception: " . $e->getMessage() . "\n";
            $s->status = 'failed';
            $s->error_message = $e->getMessage();
            $s->save();
        }
    }
}
