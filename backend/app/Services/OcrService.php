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

            $ocrPath = $imagePath;
            $isPdf = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) === 'pdf';
            $tempPngPath = null;

            if ($isPdf) {
                Log::info("Converting PDF to PNG for OCR: {$imagePath}");
                $tempPngPath = sys_get_temp_dir() . '/' . uniqid('ocr_') . '.png';

                // Try MacOS native sips first
                $command = sprintf('sips -s format png "%s" --out "%s" 2>&1', $imagePath, $tempPngPath);
                exec($command, $output, $returnVar);

                if ($returnVar !== 0 || !file_exists($tempPngPath)) {
                    // Try Imagick as fallback
                    if (class_exists('Imagick')) {
                        try {
                            $imagick = new \Imagick();
                            $imagick->setResolution(300, 300);
                            $imagick->readImage($imagePath . '[0]'); // First page
                            $imagick->setImageFormat('png');
                            $imagick->writeImage($tempPngPath);
                            $imagick->clear();
                        } catch (\Exception $e) {
                            throw new \Exception("PDF Conversion Failed: " . $e->getMessage());
                        }
                    } else {
                        Log::error("sips output: " . implode("\n", $output));
                        throw new \Exception("Could not convert PDF to image. 'sips' command failed and Imagick not installed.");
                    }
                }

                $ocrPath = $tempPngPath;
            }

            Log::info("Starting OCR on: {$ocrPath}");

            $tesseract = new TesseractOCR($ocrPath);

            // Use custom temp dir to safely avoid macOS tempnam() fallback warnings
            $tempDir = storage_path('app/ocr_temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tesseract->tempDir($tempDir);

            // Use custom tesseract path from environment if specified
            // Otherwise defaults to system PATH
            $tesseractPath = env('TESSERACT_PATH');
            if ($tesseractPath && file_exists($tesseractPath)) {
                $tesseract->executable($tesseractPath);
            }

            $text = $tesseract->run();

            if ($tempPngPath && file_exists($tempPngPath)) {
                unlink($tempPngPath);
            }

            Log::info("OCR Complete. Length: " . strlen($text));

            return $text;
        } catch (\Exception $e) {
            Log::error("OCR Failed: " . $e->getMessage());
            throw $e; // Re-throw to be handled by controller/pipeline
        }
    }
}
