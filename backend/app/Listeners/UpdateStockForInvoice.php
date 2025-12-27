<?php

namespace App\Listeners;

use App\Events\InvoiceFinalized;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateStockForInvoice
{
    /**
     * Handle the event.
     */
    public function handle(InvoiceFinalized $event): void
    {
        $invoice = $event->invoice;

        // Skip stock updates for Quotes/Estimates
        if ($invoice->type === 'quote') {
            return;
        }

        // Load items if not loaded
        if (!$invoice->relationLoaded('items')) {
            $invoice->load('items');
        }

        foreach ($invoice->items as $item) {
            if ($item->product_id) {
                $product = Product::find($item->product_id);
                if ($product) {
                    if ($invoice->type === 'invoice') {
                        // Decrease Stock
                        $product->decrement('current_stock', $item->quantity);
                        Log::info("Stock decreased for product {$product->id} by {$item->quantity} (Invoice: {$invoice->invoice_number})");
                    } elseif ($invoice->type === 'credit_note') {
                        // Increase Stock
                        $product->increment('current_stock', $item->quantity);
                        Log::info("Stock increased for product {$product->id} by {$item->quantity} (Credit Note: {$invoice->invoice_number})");
                    }
                }
            }
        }
    }
}
