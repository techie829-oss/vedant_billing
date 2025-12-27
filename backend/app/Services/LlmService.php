<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmService
{
    protected string $baseUrl;
    protected string $model = 'llama3';

    public function __construct()
    {
        // Use Ollama API URL from environment, default to localhost
        $this->baseUrl = env('OLLAMA_API_URL', 'http://localhost:11434') . '/api/generate';
    }

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

REQUIRED FIELDS:
- merchant: The merchant/vendor name (string)
- amount: The total amount paid (numeric, just the number without currency symbol)
- date: Transaction date in YYYY-MM-DD format
- category: MUST be EXACTLY one of these values:
  * Rent
  * Food
  * Travel
  * Utilities
  * Salary
  * Office Supplies
  * Other
  
  Guidelines for category selection:
  - Restaurants, cafes, food delivery = "Food"
  - Cabs, flights, hotels = "Travel"
  - Electricity, water, internet = "Utilities"
  - Pens, paper, office items = "Office Supplies"
  - Employee wages = "Salary"
  - Building/property rent = "Rent"
  - If unsure, use "Other"
  
- notes: Combine merchant name and item details in this format: "[Merchant Name] - [Items/Description]"
  Example: "Tasty Bites Restaurant - Paneer Tikka, Veg Biryani, Butter Naan, Coke"

OPTIONAL FIELDS:
- tax: Tax amount if clearly mentioned (numeric)

Return ONLY a valid JSON object with these exact field names. Do not include markdown formatting or explanations.

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
