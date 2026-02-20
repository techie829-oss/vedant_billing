<?php

namespace App\Services;

use App\Models\InvoiceScan;
use App\Models\Party;
use App\Models\TempProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceOcrService
{
    protected OcrService $ocrService;
    protected LlmService $llmService;
    protected ProductMatchingService $matchingService;

    public function __construct(
        OcrService $ocrService,
        LlmService $llmService,
        ProductMatchingService $matchingService
    ) {
        $this->ocrService = $ocrService;
        $this->llmService = $llmService;
        $this->matchingService = $matchingService;
    }

    /**
     * Scan a vendor's purchase invoice: OCR + LLM extraction.
     * Returns extracted data for user review (no Invoice record created here).
     *
     * @param UploadedFile $file
     * @param string $businessId
     * @return array
     */
    public function scanPurchaseInvoice(UploadedFile $file, string $businessId): array
    {
        $path = $file->store('purchase_invoice_scans', 'public');
        $fullPath = Storage::disk('public')->path($path);

        Log::info("Purchase invoice scan started: {$path}");

        // OCR
        $rawText = $this->ocrService->extractText($fullPath);

        if (empty(trim($rawText))) {
            throw new \Exception("No text could be extracted from the invoice image. Please ensure the image is clear.");
        }

        // LLM extraction
        $data = $this->llmService->parseInvoice($rawText);

        if (empty($data)) {
            throw new \Exception("Could not extract invoice data from the image. Please try a clearer image.");
        }

        // Parse date safely
        $invoiceDate = null;
        if (!empty($data['invoice_date'])) {
            try {
                $clean = str_replace(['O', 'o'], '0', $data['invoice_date']);
                $invoiceDate = \Carbon\Carbon::parse($clean)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("Could not parse purchase invoice date: {$data['invoice_date']}");
            }
        }

        // Try to auto-match vendor by name in existing parties
        $vendorId = null;
        $vendorName = $data['vendor_name'] ?? null;

        if ($vendorName) {
            $vendor = Party::where('business_id', $businessId)
                ->where('party_type', 'vendor')
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($vendorName) . '%'])
                ->first();

            if ($vendor) {
                $vendorId = $vendor->id;
                $vendorName = $vendor->name; // use canonical name
            }
        }

        // Normalize items
        $items = [];
        foreach ($data['items'] ?? [] as $item) {
            $items[] = [
                'name' => $item['name'] ?? 'Unknown Item',
                'description' => $item['description'] ?? null,
                'hsn_code' => $item['hsn_code'] ?? null,
                'quantity' => (float) ($item['quantity'] ?? 1),
                'unit_price' => (float) ($item['price'] ?? 0),
                'tax_rate' => (float) ($item['tax_rate'] ?? 0),
            ];
        }

        return [
            'vendor_name' => $vendorName,
            'vendor_id' => $vendorId,    // null if not matched
            'invoice_number' => $data['invoice_number'] ?? null,
            'invoice_date' => $invoiceDate ?? now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'raw_text' => $rawText,
            'items' => $items,
        ];
    }

    /**
     * Upload invoice image and create scan record (synchronous).
     * Returns scan ID for background processing.
     *
     * @param UploadedFile $file
     * @param string $businessId
     * @return InvoiceScan
     */
    public function uploadInvoice(UploadedFile $file, string $businessId): InvoiceScan
    {
        // Store image permanently
        $path = $file->store('invoice_scans', 'public');

        // Create invoice scan record
        $scan = InvoiceScan::create([
            'id' => Str::uuid(),
            'business_id' => $businessId,
            'image_path' => $path,
            'status' => 'pending',
        ]);

        return $scan;
    }

    /**
     * Process an existing invoice scan (for background jobs).
     *
     * @param InvoiceScan $scan
     * @param string $businessId
     * @return array
     */
    public function processExistingScan(InvoiceScan $scan, string $businessId): array
    {
        $fullPath = Storage::disk('public')->path($scan->image_path);

        try {
            // Extract text with OCR
            $rawText = $this->ocrService->extractText($fullPath);

            if (empty(trim($rawText))) {
                throw new \Exception("No text could be extracted from the invoice image.");
            }

            $scan->update(['raw_ocr_text' => $rawText]);

            // Parse invoice with LLM
            $invoiceData = $this->llmService->parseInvoice($rawText);

            if (empty($invoiceData) || !isset($invoiceData['items'])) {
                throw new \Exception("Could not parse invoice data. Please ensure the image is clear and contains product information.");
            }

            $invoiceDate = null;
            if (!empty($invoiceData['invoice_date'])) {
                try {
                    $cleanDate = str_replace(['O', 'o'], '0', $invoiceData['invoice_date']);
                    $invoiceDate = \Carbon\Carbon::parse($cleanDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning("Could not parse invoice date: {$invoiceData['invoice_date']}");
                }
            }

            $scan->update([
                'llm_response' => $invoiceData,
                'vendor_name' => $invoiceData['vendor_name'] ?? null,
                'invoice_number' => $invoiceData['invoice_number'] ?? null,
                'invoice_date' => $invoiceDate,
                'products_count' => count($invoiceData['items'] ?? []),
            ]);

            // Create temp_products and find matches
            $tempProducts = [];
            foreach ($invoiceData['items'] as $item) {
                $tempProduct = TempProduct::create([
                    'id' => Str::uuid(),
                    'business_id' => $businessId,
                    'scan_reference_id' => $scan->id,
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'description' => $item['description'] ?? null,
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'tax_rate' => $item['tax_rate'] ?? null,
                    'status' => 'pending',
                ]);

                // Find potential matches
                $matches = $this->matchingService->findMatches($tempProduct, $businessId);

                // If perfect match found, auto-suggest
                if (!empty($matches) && $matches[0]['confidence'] >= 0.95) {
                    $tempProduct->update([
                        'matched_product_id' => $matches[0]['product_id'],
                        'confidence_score' => $matches[0]['confidence'],
                    ]);
                }

                $tempProducts[] = [
                    'temp_product' => $tempProduct,
                    'suggested_matches' => $matches,
                ];
            }

            $scan->update(['status' => 'success']);

            return [
                'scan_id' => $scan->id,
                'vendor' => $scan->vendor_name,
                'invoice_no' => $scan->invoice_number,
                'date' => $scan->invoice_date,
                'temp_products' => $tempProducts,
            ];

        } catch (\Exception $e) {
            Log::error("Invoice Processing Error: " . $e->getMessage());

            $scan->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Scan a purchase invoice (legacy - use uploadInvoice + queue instead).
     *
     * @param UploadedFile $file
     * @param string $businessId
     * @return array
     * @deprecated Use uploadInvoice() and dispatch ProcessInvoiceScan job
     */
    public function scanInvoice(UploadedFile $file, string $businessId): array
    {
        // 1. Store image permanently
        $path = $file->store('invoice_scans', 'public');
        $fullPath = Storage::disk('public')->path($path);

        // 2. Create invoice scan record
        $scan = InvoiceScan::create([
            'id' => Str::uuid(),
            'business_id' => $businessId,
            'image_path' => $path,
            'status' => 'pending',
        ]);

        try {
            // 3. Extract text with OCR
            $rawText = $this->ocrService->extractText($fullPath);

            if (empty(trim($rawText))) {
                throw new \Exception("No text could be extracted from the invoice image.");
            }

            $scan->update(['raw_ocr_text' => $rawText]);

            // 4. Parse invoice with LLM
            $invoiceData = $this->llmService->parseInvoice($rawText);

            if (empty($invoiceData) || !isset($invoiceData['items'])) {
                throw new \Exception("Could not parse invoice data. Please ensure the image is clear and contains product information.");
            }

            $scan->update([
                'llm_response' => $invoiceData,
                'vendor_name' => $invoiceData['vendor_name'] ?? null,
                'invoice_number' => $invoiceData['invoice_number'] ?? null,
                'invoice_date' => $invoiceData['invoice_date'] ?? null,
                'products_count' => count($invoiceData['items'] ?? []),
            ]);

            // 5. Create temp_products and find matches
            $tempProducts = [];
            foreach ($invoiceData['items'] as $item) {
                $tempProduct = TempProduct::create([
                    'id' => Str::uuid(),
                    'business_id' => $businessId,
                    'scan_reference_id' => $scan->id,
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'description' => $item['description'] ?? null,
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'tax_rate' => $item['tax_rate'] ?? null,
                    'status' => 'pending',
                ]);

                // Find potential matches
                $matches = $this->matchingService->findMatches($tempProduct, $businessId);

                // If perfect match found, auto-suggest
                if (!empty($matches) && $matches[0]['confidence'] >= 0.95) {
                    $tempProduct->update([
                        'matched_product_id' => $matches[0]['product_id'],
                        'confidence_score' => $matches[0]['confidence'],
                    ]);
                }

                $tempProducts[] = [
                    'temp_product' => $tempProduct,
                    'suggested_matches' => $matches,
                ];
            }

            $scan->update(['status' => 'success']);

            return [
                'scan_id' => $scan->id,
                'vendor' => $scan->vendor_name,
                'invoice_no' => $scan->invoice_number,
                'date' => $scan->invoice_date,
                'temp_products' => $tempProducts,
            ];

        } catch (\Exception $e) {
            Log::error("Invoice Scanning Error: " . $e->getMessage());

            $scan->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Retry a failed scan using saved OCR text.
     *
     * @param string $scanId
     * @return array
     */
    public function retryScan(string $scanId): array
    {
        $scan = InvoiceScan::findOrFail($scanId);

        if (!$scan->raw_ocr_text) {
            throw new \Exception("No OCR text available for retry. Please re-upload the image.");
        }

        // Delete existing temp products
        $scan->tempProducts()->delete();

        // Re-parse with LLM
        $invoiceData = $this->llmService->parseInvoice($scan->raw_ocr_text);

        if (empty($invoiceData) || !isset($invoiceData['items'])) {
            throw new \Exception("Could not parse invoice data on retry.");
        }

        $scan->update([
            'llm_response' => $invoiceData,
            'vendor_name' => $invoiceData['vendor_name'] ?? null,
            'invoice_number' => $invoiceData['invoice_number'] ?? null,
            'invoice_date' => $invoiceData['invoice_date'] ?? null,
            'products_count' => count($invoiceData['items'] ?? []),
            'status' => 'success',
            'error_message' => null,
        ]);

        // Recreate temp products with matches
        $tempProducts = [];
        foreach ($invoiceData['items'] as $item) {
            $tempProduct = TempProduct::create([
                'id' => Str::uuid(),
                'business_id' => $scan->business_id,
                'scan_reference_id' => $scan->id,
                'name' => $item['name'],
                'sku' => $item['sku'] ?? null,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? null,
                'description' => $item['description'] ?? null,
                'hsn_code' => $item['hsn_code'] ?? null,
                'tax_rate' => $item['tax_rate'] ?? null,
                'status' => 'pending',
            ]);

            $matches = $this->matchingService->findMatches($tempProduct, $scan->business_id);

            if (!empty($matches) && $matches[0]['confidence'] >= 0.95) {
                $tempProduct->update([
                    'matched_product_id' => $matches[0]['product_id'],
                    'confidence_score' => $matches[0]['confidence'],
                ]);
            }

            $tempProducts[] = [
                'temp_product' => $tempProduct,
                'suggested_matches' => $matches,
            ];
        }

        return [
            'scan_id' => $scan->id,
            'vendor' => $scan->vendor_name,
            'invoice_no' => $scan->invoice_number,
            'date' => $scan->invoice_date,
            'temp_products' => $tempProducts,
        ];
    }
}
