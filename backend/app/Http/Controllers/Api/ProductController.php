<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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
        return response()->json($product->load('transactions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
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
        if ($product->type === 'service') {
            return response()->json(['message' => 'Cannot adjust stock for service products.'], 422);
        }

        $validated = $request->validate([
            'type' => 'required|in:purchase,sale,adjustment,return',
            'quantity' => 'required|numeric|not_in:0',
            'notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($product, $validated) {
            InventoryTransaction::create([
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
                InventoryTransaction::latest()->first()->update(['quantity' => $qty]);
            }

            $product->increment('current_stock', $qty);

            return response()->json([
                'message' => 'Stock adjusted successfully.',
                'current_stock' => $product->refresh()->current_stock
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
}
