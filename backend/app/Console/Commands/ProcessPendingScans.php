<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InvoiceScan;
use App\Jobs\ProcessInvoiceScan;

class ProcessPendingScans extends Command
{
    protected $signature = 'scans:debug';
    protected $description = 'Debug stuck scans';

    public function handle()
    {
        $scans = InvoiceScan::orderBy('id', 'desc')->take(5)->get();
        foreach ($scans as $s) {
            $this->info("Scan ID: {$s->id} | Status: {$s->status} | Error: {$s->error_message}");
            if ($s->status === 'pending') {
                $this->info("--> Processing scan {$s->id} synchronously...");
                try {
                    ProcessInvoiceScan::dispatchSync($s->id, $s->business_id);
                    $s->refresh();
                    $this->info("--> Finished. New status: {$s->status}");
                } catch (\Exception $e) {
                    $this->error("--> Caught Exception: " . $e->getMessage());
                    $s->status = 'failed';
                    $s->error_message = $e->getMessage();
                    $s->save();
                }
            }
        }
    }
}
