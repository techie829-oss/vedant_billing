<?php

namespace Database\Seeders;

use App\Events\InvoiceFinalized;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Party;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class TenantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = Business::all();

        foreach ($businesses as $business) {
            $this->command->info("Seeding data for business: {$business->name}");

            // 1. Create Products
            if ($business->products()->count() < 5) {
                Product::factory()->count(10)->create([
                    'business_id' => $business->id,
                ]);
            }

            // 2. Create Customers
            if ($business->parties()->where('party_type', 'customer')->count() < 5) {
                Party::factory()->count(10)->create([
                    'business_id' => $business->id,
                    'party_type' => 'customer',
                ]);
            }

            // 3. Create Invoices
            $customers = $business->parties()->where('party_type', 'customer')->get();
            $products = $business->products()->get();

            if ($customers->count() > 0 && $products->count() > 0) {
                // Create 5 Draft Invoices
                Invoice::factory()->count(5)->create([
                    'business_id' => $business->id,
                    'party_id' => $customers->random()->id,
                    'status' => 'draft',
                ])->each(function ($invoice) use ($products) {
                    // Attach items
                    InvoiceItem::factory()->count(rand(1, 5))->create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $products->random()->id,
                    ]);
                    // Update totals (simplified)
                    $invoice->subtotal = $invoice->items()->sum('total');
                    $invoice->grand_total = $invoice->subtotal; // Ignoring tax calc for speed unless needed
                    $invoice->save();
                });

                // Create 5 Finalized Invoices (triggers Ledger Entries)
                $this->command->info(" Creating finalized invoices and dispatching events...");

                $finalizedInvoices = Invoice::factory()->count(5)->create([
                    'business_id' => $business->id,
                    'party_id' => $customers->random()->id,
                    'status' => 'sent', // Initially sent
                    'date' => now()->subDays(rand(1, 30)),
                ]);

                foreach ($finalizedInvoices as $invoice) {
                    // Attach items
                    InvoiceItem::factory()->count(rand(1, 5))->create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $products->random()->id,
                    ]);

                    // Recalculate totals properly
                    $subtotal = $invoice->items()->sum('total');
                    $invoice->update([
                        'subtotal' => $subtotal,
                        'grand_total' => $subtotal, // Assuming 0 tax for factory simple case
                        'meta' => [
                            'notes' => 'Seeded Invoice'
                        ]
                    ]);

                    // Dispatch Event to create Ledger Entries
                    // We must fire it manually since factory create() doesn't fire it automatically usually
                    // unless we added it to the model boot method (which we didn't).
                    Event::dispatch(new InvoiceFinalized($invoice));
                }
            }
        }
    }
}
