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
        $validated = $request->validate([
            'update_inventory' => 'boolean',
        ]);

        $businessId = $request->header('X-Business-ID');

        DB::transaction(function () use ($tempProduct, $businessId, $validated) {
            // Create new product
            $product = Product::create([
                'id' => Str::uuid(),
                'business_id' => $businessId,
                'name' => $tempProduct->name,
                'sku' => $tempProduct->sku,
                'purchase_price' => $tempProduct->price,
                'sale_price' => $tempProduct->price, // Default to purchase price
                'current_stock' => ($validated['update_inventory'] ?? false) ? $tempProduct->quantity : 0,
                'unit' => $tempProduct->unit ?? 'pcs',
                'description' => $tempProduct->description,
                'hsn_code' => $tempProduct->hsn_code,
                'tax_rate' => $tempProduct->tax_rate ?? 0,
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
