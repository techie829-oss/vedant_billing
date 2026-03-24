<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    /**
     * Get inventory history for a product or all products.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', InventoryTransaction::class);

        $query = InventoryTransaction::with(['product', 'party'])
            ->orderBy('created_at', 'desc');

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Store a new inventory transaction (Add/Reduce Stock).
     */
    public function store(Request $request)
    {
        Gate::authorize('create', InventoryTransaction::class);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:purchase,sale,adjustment,return',
            'quantity' => 'required|numeric', // Can be negative for removal? Or enforce positive and handle logic?
            // Usually "adjustment" implies manual fix. 
            // "purchase" = positive
            // "sale" = negative (usually auto-handled by invoice)
            // Let's assume input quantity is always absolute, and type determines +/- sign, 
            // OR let frontend send signed values. 
            // Safer to let frontend send ABSOLUTE quantity and backend determines sign based on type/action.
            // But for "Adjustment", it could be + or -.
            // Let's stick to:
            // Purchase/Return(In) = +
            // Sale/Return(Out) = -
            // Adjustment = +/-

            // For simplicity in this manual tool:
            // If type is 'purchase' -> Add
            // If type is 'return' (to vendor) -> Subtract
            // If type is 'sale' (manual record?) -> Subtract
            // Let's just validate quantity is non-zero.
            'unit_price' => 'nullable|numeric|min:0',
            'party_id' => 'nullable|exists:parties,id',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::findOrFail($request->product_id);

            $quantity = $request->quantity;

            // Allow frontend to explicitly set sign, OR enforce based on type.
            // Requirement: "Stock In/Out".
            // If user selects "Stock In", sends +Qty.
            // If user selects "Stock Out", sends -Qty.

            InventoryTransaction::create([
                'product_id' => $request->product_id,
                'type' => $request->type,
                'quantity' => $quantity,
                'unit_price' => $request->unit_price,
                'party_id' => $request->party_id,
                'notes' => $request->notes,
                'business_id' => $product->business_id, // Safety
            ]);

            // LOCK and RE-FETCH for atomic increment
            $product = Product::where('id', $request->product_id)->lockForUpdate()->first();
            $product->increment('current_stock', $quantity);

            // Optional: Update Product "Purchase Price" if this is a Purchase
            if ($request->type === 'purchase' && $request->unit_price) {
                $product->update(['purchase_price' => $request->unit_price]);
            }
        });

        return response()->json(['message' => 'Inventory updated successfully']);
    }
}
