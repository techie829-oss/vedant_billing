<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmService
{
  protected string $provider;
  protected string $ollamaBaseUrl;
  protected string $ollamaModel;
  protected string $geminiApiKey;
  protected string $geminiModel;

  public function __construct()
  {
    $this->provider = env('LLM_PROVIDER', 'ollama');

    // Ollama config
    $this->ollamaBaseUrl = env('OLLAMA_API_URL', 'http://localhost:11434') . '/api/generate';
    $this->ollamaModel = env('OLLAMA_MODEL', 'llama3:latest');

    // Gemini config
    $this->geminiApiKey = env('GEMINI_API_KEY', '');
    $this->geminiModel = env('GEMINI_MODEL', 'gemini-1.5-flash');
  }

  /**
   * Parse raw receipt text into structured JSON.
   *
   * @param string $rawText
   * @return array
   */
  public function parseReceipt(string $rawText): array
  {
    Log::info("Sending text to {$this->provider} (Length: " . strlen($rawText) . ")");

    $prompt = <<<EOT
You are a receipt data extraction assistant.

IMPORTANT: If the image contains MULTIPLE RECEIPTS, handle them as follows:
- merchant: Use "Multiple Receipts" as the merchant name
- amount: Sum of ALL receipt totals (grand total of everything)
- date: Use the most recent date from all receipts
- category: Choose based on the highest value receipt
- notes: List EACH receipt separately with format: "[Merchant1] - ₹[Amount1] - [Items1]; [Merchant2] - ₹[Amount2] - [Items2]; ..."

Extract the following fields from the receipt text below:

REQUIRED FIELDS:
- merchant: The merchant/vendor name (string). Use "Multiple Receipts" if image has multiple receipts.
- amount: The GRAND TOTAL of ALL receipts combined (numeric, just the number without currency symbol)
- date: Transaction date in YYYY-MM-DD format (most recent if multiple)
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
  - Medical/Doctor = "Other"
  - Electronics/Shopping = "Other"
  - Multiple receipts = choose based on highest amount receipt
  - If unsure, use "Other"
  
- notes: 
  * For SINGLE receipt: "[Merchant Name] - ₹[Amount] - [Items/Description]"
    Example: "Tasty Bites Restaurant - ₹1026.60 - Paneer Tikka, Veg Biryani, Butter Naan, Coke"
  
  * For MULTIPLE receipts: List each receipt separately with semicolon separator
    Example: "Dr. Sanjay Sharma - ₹800.00 - Consultation, Antibiotics, Pain Reliever; Shankar Electronics - ₹92400.00 - Laptop, Smart TV, Power Bank; Abhishek Grocery - ₹570.00 - Rice, Milk, Soap, Biscuits"

OPTIONAL FIELDS:
- tax: Total tax amount from all receipts (numeric)

Return ONLY a valid JSON object with these exact field names. Do not include markdown formatting or explanations.

RAW TEXT:
{$rawText}
EOT;

    return $this->executeLlmRequest($prompt, "Receipt");
  }

  /**
   * Parse purchase invoice text and/or image to extract vendor and product line items.
   *
   * @param string $rawText
   * @param string|null $imagePath
   * @return array
   */
  public function parseInvoice(string $rawText, ?string $imagePath = null): array
  {
    $hasImage = $imagePath && file_exists($imagePath);
    Log::info("Sending invoice " . ($hasImage ? "image+text" : "text") . " to {$this->provider} (Text Length: " . strlen($rawText) . ")");

    $prompt = <<<EOT
You are a purchase invoice data extraction assistant.
Extract the following information from the invoice text below:

REQUIRED FIELDS:
- vendor_name: Vendor/supplier name (string)
- vendor_gstin: Vendor's GSTIN/Tax ID if available (string, nullable)
- vendor_address: Vendor's full address if available (string, nullable)
- invoice_number: Invoice number/reference (string)
- invoice_date: Invoice date in YYYY-MM-DD format
- items: Array of product line items, each containing:
  * name: Product name (string)
  * sku: Product code/SKU if available (string, nullable)
  * quantity: Quantity ordered (numeric)
  * unit: Unit of measurement (kg, pcs, box, etc.) (string, nullable)
  * price: Unit price (numeric)
  * mrp: Maximum Retail Price if listed separately (numeric, nullable)
  * discount: Discount amount applied to this item (numeric, default 0)
  * hsn_code: HSN/SAC code if available (string, nullable)
  * tax_rate: Tax rate percentage if available (numeric, nullable)
  * batch_number: Manufacturing batch number (string, nullable)
  * expiry_date: Manufacturing or Expiry date (string, nullable)
  * description: Any additional product details (string, nullable)

IMPORTANT:
- Extract ALL line items from the invoice
- `price` MUST be the per-unit rate/price. NEVER extract the 'Total Amount', 'Taxable Amount', or 'Net Amount' into the price field. If a row has Rate=50 and Total=1000, the price is 50.
- Quantity should be numeric. Sometimes quantity is under "Qty" or "NOB" (Number of Bottles). Use NOB if Qty is in cases.
- If unit not specified, leave as null
- Return price, mrp and discount as decimal numbers without currency symbols
- For FMCG/Beverages, `mrp` is often explicitly listed in an "MRP" or "MRP/Bottle" column. Do NOT miss this.
- `batch_number` is often listed under "Batch No" and `expiry_date` under "Mfg Date" or "Exp Date". 
- If multiple discounts exist per item (e.g., Discount + SPL Discount), sum them up into the single 'discount' field
- If CGST and SGST are listed separately, SUM them for the 'tax_rate' (e.g., 20% CGST + 20% SGST = 40)

Example output:
{
  "vendor_name": "ABC Suppliers Ltd",
  "vendor_gstin": "27AADCB2230M1Z2",
  "vendor_address": "123 Business Road, Mumbai, Maharashtra 400001",
  "invoice_number": "INV-2025-001",
  "invoice_date": "2025-12-27",
  "items": [
    {
      "name": "Laptop HP EliteBook 840",
      "sku": "HP-EB-840",
      "quantity": 2,
      "unit": "pcs",
      "price": 50000.00,
      "mrp": 55000.00,
      "discount": 1000.00,
      "hsn_code": "8471",
      "tax_rate": 18,
      "batch_number": "BNBAA024",
      "expiry_date": "2026-01-22",
      "description": "14-inch, i5, 16GB RAM"
    },
    {
      "name": "Wireless Mouse",
      "sku": "MS-WL-01",
      "quantity": 5,
      "unit": "pcs",
      "price": 450.00,
      "mrp": null,
      "discount": 0.00,
      "hsn_code": "8471",
      "tax_rate": 18,
      "batch_number": null,
      "expiry_date": null,
      "description": null
    }
  ]
}
}

Return ONLY a valid JSON object. Do not include markdown formatting or explanations.

INVOICE TEXT:
{$rawText}
EOT;

    return $this->executeLlmRequest($prompt, "Invoice", $imagePath);
  }

  /**
   * Execute the LLM request based on the configured provider
   * 
   * @param string $prompt
   * @param string $context
   * @param string|null $imagePath Optional path to the image file to bypass OCR
   */
  protected function executeLlmRequest(string $prompt, string $context, ?string $imagePath = null): array
  {
    if ($this->provider === 'gemini') {
      return $this->callGemini($prompt, $context, $imagePath);
    }
    return $this->callOllama($prompt, $context);
  }

  /**
   * Execute request using Ollama
   * (Ollama currently only uses text, image support requires multimodal models like llava)
   */
  protected function callOllama(string $prompt, string $context): array
  {
    try {
      $response = Http::timeout(1800)->post($this->ollamaBaseUrl, [
        'model' => $this->ollamaModel,
        'prompt' => $prompt,
        'stream' => false,
        'format' => 'json'
      ]);

      if ($response->failed()) {
        throw new \Exception("Ollama API Error: " . $response->body());
      }

      $json = $response->json();
      $content = $json['response'] ?? '{}';

      Log::info("Ollama {$context} Response: " . substr($content, 0, 200) . "...");

      return $this->cleanAndDecodeJson($content);

    } catch (\Exception $e) {
      Log::error("Ollama {$context} Extraction Failed: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Execute request using Google Gemini API
   * Uses Vision API if imagePath is provided, bypassing previous OCR steps
   */
  protected function callGemini(string $prompt, string $context, ?string $imagePath = null): array
  {
    try {
      if (empty($this->geminiApiKey)) {
        throw new \Exception("GEMINI_API_KEY is not configured in .env");
      }

      $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";

      $parts = [];
      $parts[] = ['text' => $prompt];

      // Add image data if provided (native Gemini Vision OCR)
      if ($imagePath && file_exists($imagePath)) {
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';
        $base64 = base64_encode(file_get_contents($imagePath));
        $parts[] = [
          'inlineData' => [
            'mimeType' => $mimeType,
            'data' => $base64
          ]
        ];
        Log::info("Gemini {$context} Request: Included image for native Vision parsing");
      }

      $response = Http::timeout(120)->withHeaders([
        'Content-Type' => 'application/json',
      ])->post($url, [
            'contents' => [
              [
                'parts' => $parts
              ]
            ],
            'generationConfig' => [
              'responseMimeType' => 'application/json',
            ]
          ]);

      if ($response->failed()) {
        throw new \Exception("Gemini API Error: " . $response->body());
      }

      $data = $response->json();
      $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

      Log::info("Gemini {$context} Response: " . substr($content, 0, 200) . "...");

      return $this->cleanAndDecodeJson($content);

    } catch (\Exception $e) {
      Log::error("Gemini {$context} Extraction Failed: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Helper to clean markdown from JSON and decode it
   */
  protected function cleanAndDecodeJson(string $content): array
  {
    $data = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      $cleanContent = preg_replace('/```json|```/', '', $content);
      $data = json_decode($cleanContent, true);
    }

    return $data ?? [];
  }
}
