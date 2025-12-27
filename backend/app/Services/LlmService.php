<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmService
{
    protected string $baseUrl = 'http://localhost:11434/api/generate';
    protected string $model = 'llama3';

    /**
     * Parse raw receipt text into structured JSON.
     *
     * @param string $rawText
     * @return array
     */
    public function parseReceipt(string $rawText): array
    {
        Log::info("Sending text to Ollama (Length: " . strlen($rawText) . ")");

        $prompt = <<<EOT
You are a receipt data extraction assistant.
Extract the following fields from the raw OCR text below:
- merchant_name (string)
- date (YYYY-MM-DD)
- total_amount (numeric)
- tax_amount (numeric, optional)
- category (string, guess based on items e.g., 'Food', 'Travel', 'Office Supplies')

Return ONLY a valid JSON object. Do not include markdown formatting or explanations.

RAW TEXT:
{$rawText}
EOT;

        try {
            $response = Http::timeout(60)->post($this->baseUrl, [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json' // Enforce JSON mode
            ]);

            if ($response->failed()) {
                throw new \Exception("Ollama API Error: " . $response->body());
            }

            $json = $response->json();
            $content = $json['response'] ?? '{}';

            Log::info("Ollama Response: " . substr($content, 0, 100) . "...");

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Fallback: try to strip markdown code blocks if Llama adds them
                $cleanContent = preg_replace('/```json|```/', '', $content);
                $data = json_decode($cleanContent, true);
            }

            return $data ?? [];

        } catch (\Exception $e) {
            Log::error("LLM Extraction Failed: " . $e->getMessage());
            return [];
        }
    }
}
