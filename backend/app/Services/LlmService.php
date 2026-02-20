<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmService
{
  protected string $baseUrl;
  protected string $model = 'llama3:latest';

  public function __construct()
  {
    // Use Ollama API URL from environment, default to localhost
    $this->baseUrl = env('OLLAMA_API_URL', 'http://localhost:11434') . '/api/generate';
    $this->model = env('OLLAMA_MODEL', 'llama3:latest');
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

    try {
      // Increase timeout for first request (Ollama loads model into memory)
      // First request: 10-20 seconds, subsequent: 3-8 seconds
      $response = Http::timeout(120)->post($this->baseUrl, [
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

  /**
   * Parse purchase invoice text to extract vendor and product line items.
   *
   * @param string $rawText
   * @return array
   */
  public function parseInvoice(string $rawText): array
  {
    Log::info("Sending invoice text to Ollama (Length: " . strlen($rawText) . ")");

    $prompt = <<<EOT
You are a purchase invoice data extraction assistant.
Extract the following information from the invoice text below:

REQUIRED FIELDS:
- vendor_name: Vendor/supplier name (string)
- invoice_number: Invoice number/reference (string)
- invoice_date: Invoice date in YYYY-MM-DD format
- items: Array of product line items, each containing:
  * name: Product name (string)
  * sku: Product code/SKU if available (string, nullable)
  * quantity: Quantity ordered (numeric)
  * unit: Unit of measurement (kg, pcs, box, etc.) (string, nullable)
  * price: Unit price (numeric)
  * hsn_code: HSN/SAC code if available (string, nullable)
  * tax_rate: Tax rate percentage if available (numeric, nullable)
  * description: Any additional product details (string, nullable)

IMPORTANT:
- Extract ALL line items from the invoice
- Price should be unit price, not total
- Quantity should be numeric (convert "2 pcs" to quantity=2, unit="pcs")
- If unit not specified, leave as null
- Return price as decimal number without currency symbols

Example output:
{
  "vendor_name": "ABC Suppliers Ltd",
  "invoice_number": "INV-2025-001",
  "invoice_date": "2025-12-27",
  "items": [
    {
      "name": "Laptop HP EliteBook 840",
      "sku": "HP-EB-840",
      "quantity": 2,
      "unit": "pcs",
      "price": 55000.00,
      "hsn_code": "8471",
      "tax_rate": 18,
      "description": "14-inch, i5, 16GB RAM"
    },
    {
      "name": "Wireless Mouse",
      "sku": "MS-WL-01",
      "quantity": 5,
      "unit": "pcs",
      "price": 450.00,
      "hsn_code": "8471",
      "tax_rate": 18,
      "description": null
    }
  ]
}

Return ONLY a valid JSON object. Do not include markdown formatting or explanations.

INVOICE TEXT:
{$rawText}
EOT;

    try {
      // Increase timeout for invoice parsing (can be longer text)
      $response = Http::timeout(1800)->post($this->baseUrl, [
        'model' => $this->model,
        'prompt' => $prompt,
        'stream' => false,
        'format' => 'json'
      ]);

      if ($response->failed()) {
        throw new \Exception("Ollama API Error: " . $response->body());
      }

      $json = $response->json();
      $content = $json['response'] ?? '{}';

      Log::info("Ollama Invoice Response: " . substr($content, 0, 200) . "...");

      $data = json_decode($content, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        $cleanContent = preg_replace('/```json|```/', '', $content);
        $data = json_decode($cleanContent, true);
      }

      return $data ?? [];

    } catch (\Exception $e) {
      Log::error("Invoice LLM Extraction Failed: " . $e->getMessage());
      return [];
    }
  }
}
