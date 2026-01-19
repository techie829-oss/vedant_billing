<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Party;
use App\Models\Business;

class DocumentTypesSeeder extends Seeder
{
    public function run()
    {
        // Get Chitra business specifically
        $business = Business::where('name', 'Chitra')->first();

        if (!$business) {
            // Fallback to first business if Chitra doesn't exist
            $business = Business::first();
        }

        $party = Party::where('business_id', $business->id)->first();

        // Create a sample party if Chitra doesn't have any
        if (!$party && $business->name === 'Chitra') {
            $party = Party::create([
                'business_id' => $business->id,
                'party_type' => 'customer',
                'name' => 'Sample Customer',
                'email' => 'customer@example.com',
                'phone' => '9876543210',
                'billing_address' => json_encode([
                    'street' => '123 Main Street',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'pincode' => '400001'
                ])
            ]);
            $this->command->info("Created sample customer for {$business->name}");
        }

        if (!$business || !$party) {
            $this->command->error('Please ensure you have at least one business and one party in the database.');
            return;
        }

        $documentTypes = [
            [
                'type' => 'tax_invoice',
                'invoice_number' => 'INV/00001',
                'title' => 'Tax Invoice Sample'
            ],
            [
                'type' => 'bill_of_supply',
                'invoice_number' => 'BS/00001',
                'title' => 'Bill of Supply Sample'
            ],
            [
                'type' => 'proforma_invoice',
                'invoice_number' => 'PI/00001',
                'title' => 'Quotation Sample'
            ],
            [
                'type' => 'credit_note',
                'invoice_number' => 'CN/00001',
                'title' => 'Credit Note Sample'
            ],
            [
                'type' => 'debit_note',
                'invoice_number' => 'DN/00001',
                'title' => 'Debit Note Sample'
            ],
            [
                'type' => 'delivery_challan',
                'invoice_number' => 'DC/00001',
                'title' => 'Delivery Challan Sample'
            ]
        ];

        foreach ($documentTypes as $docType) {
            // Check if already exists
            $exists = Invoice::where('business_id', $business->id)
                ->where('type', $docType['type'])
                ->exists();

            if ($exists) {
                $this->command->info("Skipping {$docType['title']} - already exists");
                continue;
            }

            $invoice = Invoice::create([
                'business_id' => $business->id,
                'party_id' => $party->id,
                'type' => $docType['type'],
                'invoice_number' => $docType['invoice_number'],
                'date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
                'subtotal' => 1000.00,
                'tax_total' => 180.00,
                'discount_total' => 0.00,
                'grand_total' => 1180.00,
                'paid_amount' => 0.00,
                'notes' => 'This is a sample ' . strtolower($docType['title']) . ' for testing purposes.',
                'terms' => 'Standard terms and conditions apply.',
                'meta' => json_encode($docType['type'] === 'bill_of_supply' ? ['copy_type' => 'original'] : [])
            ]);

            // Create invoice item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => null,
                'name' => 'Sample Product',
                'description' => 'Sample product for ' . $docType['title'],
                'quantity' => 1,
                'unit_price' => 1000.00,
                'discount' => 0.00,
                'total' => 1000.00,
                'tax_rate' => 18.00,
                'tax_amount' => 180.00,
                'hsn_code' => '1234'
            ]);

            $this->command->info("Created {$docType['title']}");
        }

        $this->command->info("\n✅ Sample documents created successfully!");
        $this->command->info("You can now test all document types in the application.");
    }
}
