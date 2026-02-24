<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TempProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TempProductController extends Controller
{
    /**
     * Get all pending temp products for review.
     */
    public function index(Request $request)
    {
        $businessId = $request->header('X-Business-ID');

        $tempProducts = TempProduct::where('business_id', $businessId)
            ->where('status', 'pending')
            ->with(['invoiceScan', 'matchedProduct'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tempProducts,
        ]);
    }

    /**
     * Match temp product to existing product and optionally update inventory.
     */
    public function match(Request $request, TempProduct $tempProduct)
    {
        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'update_inventory' => 'boolean',
        ]);

        DB::transaction(function () use ($tempProduct, $validated) {
            // Update temp product status
            $tempProduct->update([
                'matched_product_id' => $validated['product_id'],
                'status' => 'matched',
            ]);

            // Update inventory if requested
            if ($validated['update_inventory'] ?? false) {
                $product = Product::find($validated['product_id']);
                $product->increment('current_stock', (float) $tempProduct->quantity);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Product matched successfully',
        ]);
    }

    /**
     * Add temp product as a new product.
     */
    public function addNew(Request $request, TempProduct $tempProduct)
    {
        \Illuminate\Support\Facades\Log::info('AddNew called with:', $request->all());
        try {
            $validated = $request->validate([
                'update_inventory' => 'boolean',
                'name' => 'nullable|string',
                'sku' => 'nullable|string',
                'price' => 'nullable|numeric',
                'quantity' => 'nullable|numeric',
                'unit' => 'nullable|string',
                'hsn_code' => 'nullable|string',
                'tax_rate' => 'nullable|numeric',
                'cess_rate' => 'nullable|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed in AddNew: ' . json_encode($e->errors()));
            throw $e;
        }

        $businessId = $request->header('X-Business-ID');

        try {
            DB::transaction(function () use ($tempProduct, $businessId, $validated) {
                // Determine final values, preferring validated overrides if provided
                $name = isset($validated['name']) ? $validated['name'] : $tempProduct->name;
                $sku = isset($validated['sku']) ? $validated['sku'] : $tempProduct->sku;
                $price = isset($validated['price']) ? $validated['price'] : $tempProduct->price;
                $quantity = isset($validated['quantity']) ? $validated['quantity'] : ($tempProduct->quantity ?? 0);
                $unit = isset($validated['unit']) ? $validated['unit'] : ($tempProduct->unit ?? 'pcs');
                $hsn_code = isset($validated['hsn_code']) ? $validated['hsn_code'] : $tempProduct->hsn_code;
                $tax_rate = isset($validated['tax_rate']) ? $validated['tax_rate'] : ($tempProduct->tax_rate ?? 0);
                $cess_rate = isset($validated['cess_rate']) ? $validated['cess_rate'] : ($tempProduct->cess_rate ?? 0);

                // Create new product
                $product = Product::create([
                    'id' => Str::uuid(),
                    'business_id' => $businessId,
                    'name' => $name,
                    'sku' => $sku,
                    'purchase_price' => $price,
                    'sale_price' => $price, // Default to purchase price
                    'current_stock' => 0, // Stock will be added when Purchase Invoice is created
                    'unit' => $unit,
                    'description' => $tempProduct->description,
                    'hsn_code' => $hsn_code,
                    'tax_rate' => $tax_rate,
                    'cess_rate' => $cess_rate,
                ]);

                // Update temp product
                $tempProduct->update([
                    'matched_product_id' => $product->id,
                    'status' => 'added',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to add temp product as new: " . $e->getMessage() . " Trace: " . $e->getTraceAsString(), ['temp_product_id' => $tempProduct->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject/delete a temp product.
     */
    public function destroy(TempProduct $tempProduct)
    {
        $tempProduct->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Product rejected',
        ]);
    }
}
