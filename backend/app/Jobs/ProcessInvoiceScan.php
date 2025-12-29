<?php

namespace App\Jobs;

use App\Models\InvoiceScan;
use App\Services\InvoiceOcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInvoiceScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutes max

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $scanId,
        public string $businessId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(InvoiceOcrService $invoiceOcrService): void
    {
        $scan = InvoiceScan::find($this->scanId);

        if (!$scan) {
            Log::error("InvoiceScan not found: {$this->scanId}");
            return;
        }

        try {
            // Process the already uploaded file
            $result = $invoiceOcrService->processExistingScan($scan, $this->businessId);

            $scan->update([
                'status' => 'success',
                'products_count' => count($result['temp_products'] ?? []),
            ]);

        } catch (\Exception $e) {
            Log::error("Invoice scan job failed: " . $e->getMessage());

            $scan->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
