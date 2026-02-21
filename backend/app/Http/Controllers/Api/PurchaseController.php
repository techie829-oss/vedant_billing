<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Party;
use App\Services\InvoiceOcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    protected InvoiceOcrService $ocrService;

    public function __construct(InvoiceOcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Step 1: Upload vendor invoice image → OCR + LLM extraction.
     * Returns extracted data for user review (does NOT create Invoice yet).
     */
    public function scan(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,webp|max:20480',
        ]);

        $businessId = auth()->user()->currentBusinessId();

        try {
            $result = $this->ocrService->scanPurchaseInvoice(
                $request->file('file'),
                $businessId
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not process invoice: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Step 2: Confirm scan — user has reviewed/edited extracted data.
     * Creates a real Invoice record with type=purchase_invoice.
     */
    public function confirmScan(Request $request)
    {
        $validated = $request->validate([
            'party_id' => 'nullable|exists:parties,id',
            'vendor_name' => 'nullable|string|max:255', // if no party_id, create/find vendor
            'vendor_gstin' => 'nullable|string|max:50',
            'vendor_address' => 'nullable|string',
            'invoice_number' => 'nullable|string|max:100',
            'date' => 'required|date',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
            'po_number' => 'nullable|string',
            'eway_bill_no' => 'nullable|string',
            'vehicle_no' => 'nullable|string',
            'challan_no' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.mrp' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.description' => 'nullable|string',
        ]);

        $businessId = auth()->user()->currentBusinessId();

        return DB::transaction(function () use ($validated, $businessId) {
            // Resolve or create vendor Party
            $partyId = $validated['party_id'] ?? null;

            if (!$partyId && !empty($validated['vendor_name'])) {
                // Try to find existing vendor by name
                $vendor = Party::where('business_id', $businessId)
                    ->where('party_type', 'vendor')
                    ->whereRaw('LOWER(name) = ?', [strtolower($validated['vendor_name'])])
                    ->first();

                if (!$vendor) {
                    // Auto-create vendor
                    $vendor = Party::create([
                        'business_id' => $businessId,
                        'party_type' => 'vendor',
                        'name' => $validated['vendor_name'],
                        'gstin' => $validated['vendor_gstin'] ?? null,
                        'billing_address' => $validated['vendor_address'] ?? null,
                        'status' => 'active',
                    ]);
                }

                $partyId = $vendor->id;
            }

            if (!$partyId) {
                return response()->json(['message' => 'Vendor is required.'], 422);
            }

            // Generate invoice number
            $invoiceNumber = $validated['invoice_number'] ?? $this->nextPurchaseNumber($businessId);

            // Create invoice
            $invoice = Invoice::create([
                'business_id' => $businessId,
                'type' => 'purchase_invoice',
                'party_id' => $partyId,
                'invoice_number' => $invoiceNumber,
                'date' => $validated['date'],
                'due_date' => $validated['due_date'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'po_number' => $validated['po_number'] ?? null,
                'eway_bill_no' => $validated['eway_bill_no'] ?? null,
                'vehicle_no' => $validated['vehicle_no'] ?? null,
                'challan_no' => $validated['challan_no'] ?? null,
            ]);

            // Create items and calculate totals
            $subtotal = 0;
            $taxTotal = 0;
            $discountTotal = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $price = $itemData['unit_price'];
                $discount = $itemData['discount'] ?? 0;
                $taxRate = $itemData['tax_rate'] ?? 0;

                $base = ($qty * $price) - $discount;
                $taxAmt = $base * ($taxRate / 100);
                $total = $base + $taxAmt;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $itemData['product_id'] ?? null, // Added
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? '',
                    'hsn_code' => $itemData['hsn_code'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'mrp' => $itemData['mrp'] ?? null,
                    'discount' => $discount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmt,
                    'total' => $total,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                ]);

                // Update inventory if mapped to a catalog product
                if (!empty($itemData['product_id'])) {
                    $product = \App\Models\Product::find($itemData['product_id']);
                    if ($product && $product->type === 'goods') {
                        $product->increment('current_stock', $qty);

                        \App\Models\InventoryTransaction::create([
                            'product_id' => $product->id,
                            'type' => 'purchase',
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'notes' => "Purchased via Scan: Invoice #{$invoice->invoice_number}",
                        ]);
                    }
                }

                $subtotal += $base + $discount; // Gross subtotal before discount
                $discountTotal += $discount;
                $taxTotal += $taxAmt;
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => ($subtotal - $discountTotal) + $taxTotal,
            ]);

            return response()->json([
                'message' => 'Purchase invoice created successfully.',
                'invoice' => $invoice->load(['party', 'items']),
            ], 201);
        });
    }

    /**
     * Generate next PUR/ number (shared with InvoiceController logic)
     */
    protected function nextPurchaseNumber(string $businessId): string
    {
        $count = Invoice::withTrashed()
            ->where('business_id', $businessId)
            ->where('type', 'purchase_invoice')
            ->count() + 1;

        $number = 'PUR/' . str_pad($count, 5, '0', STR_PAD_LEFT);

        while (Invoice::withTrashed()->where('invoice_number', $number)->exists()) {
            $count++;
            $number = 'PUR/' . str_pad($count, 5, '0', STR_PAD_LEFT);
        }

        return $number;
    }
}
