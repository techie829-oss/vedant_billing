<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReceiptScanningService
{
    protected OcrService $ocrService;
    protected LlmService $llmService;

    public function __construct(OcrService $ocrService, LlmService $llmService)
    {
        $this->ocrService = $ocrService;
        $this->llmService = $llmService;
    }

    /**
     * Process an uploaded receipt image.
     * 
     * @param UploadedFile $file
     * @return array
     */
    public function scan(UploadedFile $file): array
    {
        // 1. Store temporarily (Tesseract needs local path)
        $path = $file->store('temp_receipts');
        $fullPath = Storage::path($path);

        try {
            // 2. Extract Text (OCR)
            $rawText = $this->ocrService->extractText($fullPath);

            if (empty(trim($rawText))) {
                throw new \Exception("No text could be found in the image.");
            }

            // 3. Structure Data (AI)
            $structuredData = $this->llmService->parseReceipt($rawText);

            return [
                'status' => 'success',
                'raw_text' => $rawText, // Debugging aid
                'data' => $structuredData
            ];

        } catch (\Exception $e) {
            Log::error("Receipt Scanning Error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        } finally {
            // 4. Cleanup
            Storage::delete($path);
        }
    }
}
