<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;

class OcrService
{
    /**
     * Extract raw text from an image.
     *
     * @param string $imagePath Absolute path to the image file
     * @return string
     */
    public function extractText(string $imagePath): string
    {
        try {
            if (!file_exists($imagePath)) {
                throw new \Exception("Image not found at path: {$imagePath}");
            }

            Log::info("Starting OCR on: {$imagePath}");

            $tesseract = new TesseractOCR($imagePath);

            // Use custom tesseract path from environment if specified
            // Otherwise defaults to system PATH
            $tesseractPath = env('TESSERACT_PATH');
            if ($tesseractPath && file_exists($tesseractPath)) {
                $tesseract->executable($tesseractPath);
            }

            $text = $tesseract->run();

            Log::info("OCR Complete. Length: " . strlen($text));

            return $text;
        } catch (\Exception $e) {
            Log::error("OCR Failed: " . $e->getMessage());
            throw $e; // Re-throw to be handled by controller/pipeline
        }
    }
}
