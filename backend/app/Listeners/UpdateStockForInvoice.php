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

        // Skip stock updates for non-inventory types
        if (in_array($invoice->type, ['proforma_invoice', 'delivery_challan', 'quote'])) {
            return;
        }

        // Load items if not loaded
        if (!$invoice->relationLoaded('items')) {
            $invoice->load('items');
        }

        DB::transaction(function () use ($invoice) {
            foreach ($invoice->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product && $product->type === 'goods') {
                        $this->processStock($invoice, $product, $item);
                    }
                }
            }
        });
    }

    protected function processStock($invoice, $product, $item)
    {
        $qty = $item->quantity;
        $type = 'adjustment';
        $stockChange = 0;

        if (in_array($invoice->type, ['tax_invoice', 'invoice', 'bill_of_supply'])) {
            // SALE: Decrease Stock
            $stockChange = -$qty;
            $type = 'sale';
            $product->decrement('current_stock', $qty);
        } elseif ($invoice->type === 'purchase_invoice') {
            // PURCHASE: Increase Stock
            $stockChange = $qty;
            $type = 'purchase';
            $product->increment('current_stock', $qty);
        } elseif ($invoice->type === 'credit_note') {
            // RETURN: Increase Stock
            $stockChange = $qty;
            $type = 'return';
            $product->increment('current_stock', $qty);
        }

        if ($stockChange !== 0) {
            \App\Models\InventoryTransaction::create([
                'business_id' => $invoice->business_id,
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $stockChange,
                'unit_price' => $item->unit_price,
                'reference_id' => $invoice->id,
                'reference_type' => get_class($invoice),
                'notes' => ucfirst($type) . " for {$invoice->type} #{$invoice->invoice_number}",
            ]);

            Log::info("Stock updated for product {$product->id} by {$stockChange} ({$invoice->type}: {$invoice->invoice_number})");
        }
    }
}
