<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use App\Models\Party;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceItem;

class ExactInvoiceSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting invoice data seeding...');

        // Step 1: Create User
        $user = User::firstOrCreate(
            ['email' => 'admin@rschitra.com'],
            [
                'name' => 'R/S Chitra Enterprises',
                'password' => bcrypt('password123')
            ]
        );
        $this->command->info("✓ User created: {$user->email}");

        // Step 2: Create Business (Tenant)
        $business = Business::firstOrCreate(
            ['name' => 'R/S CHITRA ENTERPRISES'],
            [
                'address' => "Kewal Purwa Nakaha\nCity : Lakhimpur, Uttar Pradesh - 262728",
                'gstin' => '09CUVPM6712J1ZV',
                'pan' => 'CUVPM6712J',
                'mobile' => '6386040903',
                'website' => 'https://rschitra.com',
                'bank_name' => 'STATE BANK OF INDIA',
                'account_number' => '42755113672',
                'ifsc_code' => 'SBIN0018929',
                'meta' => [
                    'state' => 'Uttar Pradesh',
                    'state_code' => '09',
                    'city' => 'Lakhimpur',
                    'pincode' => '262728',
                    'invoice_layout' => 'classic',
                    'account_holder_name' => 'R/S CHITRA ENTERPRISES',
                    'show_bank_details' => true,
                    'default_notes' => 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.',
                    'default_terms' => 'This is computer generated Invoice',
                    'email' => 'chitra@example.com',
                    'phone' => '6386040903'
                ]
            ]
        );

        // Attach user to business if not already attached
        if (!$business->users()->where('user_id', $user->id)->exists()) {
            $business->users()->attach($user->id, [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now()
            ]);
        }

        $this->command->info("✓ Business created: {$business->name}");

        // Step 3: Create Customer (Party)
        $customer = Party::withoutGlobalScopes()->firstOrCreate(
            [
                'business_id' => $business->id,
                'name' => 'Prabhat Electric Company'
            ],
            [
                'party_type' => 'customer',
                'gstin' => '09AETPK5319K1ZV',
                'phone' => '9415437377',
                'email' => 'prabhat@example.com',
                'billing_address' => [
                    'street' => 'Patel, Sitapur',
                    'city' => 'Sitapur',
                    'state' => 'Uttar Pradesh',
                    'zip' => '261001',
                    'country' => 'India'
                ],
                'shipping_address' => [
                    'street' => 'Patel, Sitapur',
                    'city' => 'Sitapur',
                    'state' => 'Uttar Pradesh',
                    'zip' => '261001',
                    'country' => 'India'
                ],
                'opening_balance' => 0,
                'status' => 'active'
            ]
        );
        $this->command->info("✓ Customer created: {$customer->name}");

        // Step 4: Create Products
        $productsData = [
            [
                'name' => '1/2 Module Metal Box RRep',
                'hsn_code' => '85389000',
                'unit_price' => 25.24,
                'original_price' => 85.00,
            ],
            [
                'name' => '3 Module Metal Box RRep',
                'hsn_code' => '85389000',
                'unit_price' => 28.51,
                'original_price' => 96.00,
            ],
            [
                'name' => '6/8 Module Metal Box RRep',
                'hsn_code' => '85389000',
                'unit_price' => 50.49,
                'original_price' => 170.00,
            ],
            [
                'name' => '8-Module Metal Box RRep (H)',
                'hsn_code' => '85389000',
                'unit_price' => 70.60,
                'original_price' => 227.00,
            ],
            [
                'name' => '12/16 Module Metal Box RRep',
                'hsn_code' => '85389000',
                'unit_price' => 84.35,
                'original_price' => 284.00,
            ],
            [
                'name' => '8 Module Metal -(V)',
                'hsn_code' => '85389000',
                'unit_price' => 70.69,
                'original_price' => 238.00,
            ]
        ];

        $createdProducts = [];
        foreach ($productsData as $productData) {
            $product = Product::withoutGlobalScopes()->firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $productData['name']
                ],
                [
                    'hsn_code' => $productData['hsn_code'],
                    'sale_price' => $productData['unit_price'],
                    'tax_rate' => 18.00,
                    'description' => '',
                    'unit' => 'nos',
                    'type' => 'goods',
                    'status' => 'active'
                ]
            );
            $createdProducts[] = $product;
            $this->command->info("✓ Product created: {$product->name}");
        }

        // Step 5: Create Invoice
        $invoice = Invoice::withoutGlobalScopes()->firstOrCreate(
            [
                'business_id' => $business->id,
                'invoice_number' => 'Invoice 34'
            ],
            [
                'party_id' => $customer->id,
                'type' => 'invoice',
                'date' => '2024-10-14',
                'due_date' => '2024-10-29',
                'status' => 'sent',
                'subtotal' => 18028.40,
                'tax_total' => 3245.12,
                'discount_total' => 42406.60,
                'grand_total' => 21274.00,
                'paid_amount' => 0,
                'notes' => 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.',
                'terms' => 'This is computer generated Invoice',
                'meta' => [
                    'payment_terms' => 'Immediate',
                    'eway_bill_number' => '',
                    'vehicle_number' => '',
                ]
            ]
        );

        $this->command->info("✓ Invoice created: {$invoice->invoice_number}");

        // Step 6: Create Invoice Items (only if invoice has no items yet)
        if ($invoice->items()->count() === 0) {
            $invoiceItemsData = [
                [
                    'product' => $createdProducts[0],
                    'quantity' => 80.00,
                    'unit_price' => 25.24,
                    'discount' => 59.76,
                    'tax_rate' => 18.00,
                    'tax_amount' => 363.46,
                    'total' => 2019.20
                ],
                [
                    'product' => $createdProducts[1],
                    'quantity' => 60.00,
                    'unit_price' => 28.51,
                    'discount' => 67.49,
                    'tax_rate' => 18.00,
                    'tax_amount' => 259.61,
                    'total' => 1710.60
                ],
                [
                    'product' => $createdProducts[2],
                    'quantity' => 210.00,
                    'unit_price' => 50.49,
                    'discount' => 119.51,
                    'tax_rate' => 18.00,
                    'tax_amount' => 1609.34,
                    'total' => 10608.80
                ],
                [
                    'product' => $createdProducts[3],
                    'quantity' => 25.00,
                    'unit_price' => 70.60,
                    'discount' => 156.40,
                    'tax_rate' => 18.00,
                    'tax_amount' => 267.90,
                    'total' => 1765.00
                ],
                [
                    'product' => $createdProducts[4],
                    'quantity' => 60.00,
                    'unit_price' => 84.35,
                    'discount' => 199.65,
                    'tax_rate' => 18.00,
                    'tax_amount' => 768.31,
                    'total' => 5061.00
                ],
                [
                    'product' => $createdProducts[5],
                    'quantity' => 20.00,
                    'unit_price' => 70.69,
                    'discount' => 167.31,
                    'tax_rate' => 18.00,
                    'tax_amount' => 214.49,
                    'total' => 1413.80
                ]
            ];

            foreach ($invoiceItemsData as $itemData) {
                $item = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $itemData['product']->id,
                    'name' => $itemData['product']->name,
                    'description' => '',
                    'hsn_code' => $itemData['product']->hsn_code,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $itemData['discount'],
                    'tax_rate' => $itemData['tax_rate'],
                    'tax_amount' => $itemData['tax_amount'],
                    'total' => $itemData['total']
                ]);
                $this->command->info("✓ Invoice item created: {$item->name} x {$item->quantity}");
            }
        } else {
            $this->command->info("! Invoice items already exist, skipping");
        }

        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✓✓✓ SEEDING COMPLETE! ✓✓✓');
        $this->command->info('========================================');
        $this->command->info("Business: {$business->name}");
        $this->command->info("Customer: {$customer->name}");
        $this->command->info("Invoice: {$invoice->invoice_number}");
        $this->command->info("Total Amount: ₹{$invoice->grand_total}");
        $this->command->info('========================================');
        $this->command->newLine();
        $this->command->info('Login Credentials:');
        $this->command->info("Email: {$user->email}");
        $this->command->info('Password: password123');
        $this->command->info('========================================');
    }
}
