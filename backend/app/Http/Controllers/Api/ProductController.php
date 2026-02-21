<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Product::class);

        $query = Product::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Product::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100', // Unique check scoped to tenant ideally, handled by app logic or composite index
            'type' => 'required|in:goods,service',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'hsn_code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'current_stock' => 'nullable|numeric|min:0', // Accept current_stock as opening
            'opening_stock' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Use current_stock if provided as opening_stock preference, else opening_stock
            $openingStock = $validated['current_stock'] ?? $validated['opening_stock'] ?? 0;

            // Remove auxiliary fields for create
            $createData = collect($validated)->except(['current_stock', 'opening_stock'])->toArray();
            $createData['current_stock'] = 0; // Initialize 0, add via transaction

            $product = Product::create($createData);

            // Handle Initial Stock if provided and type is goods
            if ($validated['type'] === 'goods' && $openingStock > 0) {
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity' => $openingStock,
                    'notes' => 'Opening Stock',
                ]);

                $product->increment('current_stock', $openingStock);
            }

            return response()->json($product, 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        Gate::authorize('view', $product);

        return response()->json($product->load('transactions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        Gate::authorize('update', $product);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'nullable|string|max:100',
            'sale_price' => 'sometimes|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'hsn_code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'sometimes|in:active,inactive',
            'current_stock' => 'sometimes|numeric|min:0', // Allow direct update
        ]);

        return DB::transaction(function () use ($product, $validated) {
            // Handle Stock Update if provided and different
            if (isset($validated['current_stock']) && $product->type === 'goods') {
                $newStock = $validated['current_stock'];
                $oldStock = $product->current_stock;

                if ($newStock != $oldStock) {
                    $diff = $newStock - $oldStock;
                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'type' => 'adjustment',
                        'quantity' => $diff,
                        'unit_price' => $validated['purchase_price'] ?? $product->purchase_price,
                        'notes' => 'Manual Correction via Edit',
                    ]);
                    // increment/decrement handles concurrency better than set, but we are inside transaction
                    // Actually $product->update will set it, but we want to log it first.
                    // If we pass 'current_stock' to update(), it sets it. We just add the log.
                }
            }

            $product->update($validated);

            return response()->json($product);
        });
    }

    /**
     * Adjust stock manually.
     */
    public function adjustStock(Request $request, Product $product)
    {
        Gate::authorize('update', $product); // Adjust stock is an update

        if ($product->type === 'service') {
            return response()->json(['message' => 'Cannot adjust stock for service products.'], 422);
        }

        $validated = $request->validate([
            'type' => 'required|in:purchase,sale,adjustment,return',
            'quantity' => 'required|numeric|not_in:0',
            'notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($product, $validated) {
            $transaction = InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'notes' => $validated['notes'],
            ]);

            // Update cached stock
            // Note: If transaction is 'sale' or 'return' (outward), quantity should be negative ideally or handled by type logic.
            // Simplified logic: The API expects the quantity to be the *change* amount. 
            // -5 for remove, +5 for add.
            // Or we can enforce sign based on type? 
            // Let's assume input 'quantity' is the explicit change vector.
            // But usually 'purchase' implies +, 'sale' implies -.

            // Let's enforce logic for safety:
            $qty = $validated['quantity'];
            if ($validated['type'] === 'sale' && $qty > 0)
                $qty = -$qty;
            if ($validated['type'] === 'purchase' && $qty < 0)
                $qty = abs($qty); // Purchase adds stock

            // Re-save with corrected sign if needed, but for now we just use $qty for increment
            if ($qty != $validated['quantity']) {
                // Creating log with corrected sign
                $transaction->update(['quantity' => $qty]);
            }

            $product->increment('current_stock', $qty);

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'transaction' => $transaction,
            ]);
        });
    }

    /**
     * Scan a purchase invoice and extract products (non-blocking with queue).
     */
    public function scanInvoice(Request $request, \App\Services\InvoiceOcrService $invoiceOcrService)
    {
        $request->validate([
            'invoice' => 'required|file|mimes:jpeg,png,jpg,pdf|max:30720', // Max 30MB
        ]);

        $businessId = $request->header('X-Business-ID');

        try {
            // Upload file and create scan record (fast)
            $scan = $invoiceOcrService->uploadInvoice($request->file('invoice'), $businessId);

            // Dispatch background job to process (slow OCR/LLM work)
            \App\Jobs\ProcessInvoiceScan::dispatch($scan->id, $businessId);

            return response()->json([
                'success' => true,
                'scan_id' => $scan->id,
                'message' => 'Invoice uploaded. Processing in background...',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get status of an invoice scan.
     */
    public function getScanStatus(string $scanId)
    {
        $scan = \App\Models\InvoiceScan::with('tempProducts.matchedProduct')->find($scanId);

        if (!$scan) {
            return response()->json([
                'success' => false,
                'message' => 'Scan not found',
            ], 404);
        }

        // Build response based on status
        $response = [
            'success' => true,
            'status' => $scan->status,
            'scan_id' => $scan->id,
        ];

        if ($scan->status === 'success') {
            // Load temp products with matches
            $tempProducts = $scan->tempProducts->map(function ($tempProduct) use ($scan) {
                $matches = (new \App\Services\ProductMatchingService())->findMatches($tempProduct, $scan->business_id);

                return [
                    'temp_product' => $tempProduct,
                    'suggested_matches' => $matches,
                ];
            });

            $response['data'] = [
                'scan_id' => $scan->id,
                'vendor' => $scan->vendor_name,
                'vendor_gstin' => $scan->llm_response['vendor_gstin'] ?? null,
                'vendor_address' => $scan->llm_response['vendor_address'] ?? null,
                'invoice_no' => $scan->invoice_number,
                'date' => $scan->invoice_date,
                'temp_products' => $tempProducts,
            ];
        } elseif ($scan->status === 'failed') {
            $response['error'] = $scan->error_message;
        }

        return response()->json($response);
    }

    /**
     * Get all invoice scans with filters.
     */
    public function getAllScans(Request $request)
    {
        $businessId = $request->header('X-Business-ID');

        $query = \App\Models\InvoiceScan::where('business_id', $businessId)
            ->withCount('tempProducts');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $scans = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $scans,
        ]);
    }

    /**
     * Retry a failed invoice scan.
     */
    public function retryScan(string $scanId, \App\Services\InvoiceOcrService $invoiceOcrService)
    {
        $scan = \App\Models\InvoiceScan::find($scanId);

        if (!$scan) {
            return response()->json([
                'success' => false,
                'message' => 'Scan not found',
            ], 404);
        }

        try {
            // Reset status and dispatch new job
            $scan->update([
                'status' => 'pending',
                'error_message' => null,
            ]);

            \App\Jobs\ProcessInvoiceScan::dispatch($scan->id, $scan->business_id);

            return response()->json([
                'success' => true,
                'message' => 'Scan retry queued',
                'scan_id' => $scan->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an invoice scan and its temp products.
     */
    public function deleteScan(string $scanId)
    {
        $scan = \App\Models\InvoiceScan::find($scanId);

        if (!$scan) {
            return response()->json([
                'success' => false,
                'message' => 'Scan not found',
            ], 404);
        }

        // Delete temp products first
        $scan->tempProducts()->delete();

        // Delete the scan
        $scan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Scan deleted successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);

        $product->delete();
        return response()->noContent();
    }
}
