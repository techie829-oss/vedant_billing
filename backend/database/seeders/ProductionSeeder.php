<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\InUser;
use App\Models\Business;
use App\Models\Party;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's database for production.
     */
    public function run(): void
    {
        $this->command->info('Starting Production Seeding...');

        // 1. Seed Essential Configuration
        $this->call([
            PlanSeeder::class,
            GstStateSeeder::class,
        ]);

        // 2. Create Admin User (Protected by Env Vars)
        $adminEmail = env('ADMIN_EMAIL', 'chitra@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password123'); // Fallback only if env not set, but env IS set in prod

        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'R/S Chitra Enterprises',
                'password' => Hash::make($adminPassword),
            ]
        );

        // Create Internal Profile for Admin (Super Admin)
        InUser::firstOrCreate(
            ['user_id' => $user->id],
            [
                'access_level' => 'super_admin',
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Admin User Created: {$user->email}");

        // 3. Create Business (R/S CHITRA ENTERPRISES)
        $business = Business::firstOrCreate(
            ['name' => 'R/S CHITRA ENTERPRISES'],
            [
                'address' => "Kewal Purwa Nakaha\nCity : Lakhimpur, Uttar Pradesh - 262728",
                'gstin' => '09CUVPM6712J1ZV',
                'pan' => 'CUVPM6712J',
                'mobile' => '6386040903',
                'website' => '',
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
                    'email' => $adminEmail,
                    'phone' => '6386040903'
                ]
            ]
        );

        // Attach user to business
        if (!$business->users()->where('user_id', $user->id)->exists()) {
            $business->users()->attach($user->id, [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now()
            ]);
        }
        $this->command->info("✅ Business Created: {$business->name}");

        // 4. Seed Chart of Accounts (Now that business exists)
        $this->call(ChartOfAccountsSeeder::class);


        // 5. Seed Initial Data (Customer, Products, Invoice from ExactInvoiceSeeder)
        // Create Customer (Party)
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

        // Create Products
        $productsData = [
            ['name' => '1/2 Module Metal Box RRep', 'hsn_code' => '85389000', 'unit_price' => 25.24],
            ['name' => '3 Module Metal Box RRep', 'hsn_code' => '85389000', 'unit_price' => 28.51],
            ['name' => '6/8 Module Metal Box RRep', 'hsn_code' => '85389000', 'unit_price' => 50.49],
            ['name' => '8-Module Metal Box RRep (H)', 'hsn_code' => '85389000', 'unit_price' => 70.60],
            ['name' => '12/16 Module Metal Box RRep', 'hsn_code' => '85389000', 'unit_price' => 84.35],
            ['name' => '8 Module Metal -(V)', 'hsn_code' => '85389000', 'unit_price' => 70.69]
        ];

        $createdProducts = [];
        foreach ($productsData as $productData) {
            $createdProducts[] = Product::withoutGlobalScopes()->firstOrCreate(
                ['business_id' => $business->id, 'name' => $productData['name']],
                [
                    'hsn_code' => $productData['hsn_code'],
                    'sale_price' => $productData['unit_price'],
                    'tax_rate' => 18.00,
                    'unit' => 'nos',
                    'type' => 'goods',
                    'status' => 'active'
                ]
            );
        }

        // Create Invoice
        $invoice = Invoice::withoutGlobalScopes()->firstOrCreate(
            ['business_id' => $business->id, 'invoice_number' => 'Invoice 34'],
            [
                'party_id' => $customer->id,
                'type' => 'invoice',
                'date' => '2024-10-14',
                'due_date' => '2024-10-29',
                'status' => 'sent',
                'subtotal' => 18028.40,
                'tax_total' => 3245.12,
                'discount_total' => 42406.60, // Large discount as per original seeder
                'grand_total' => 21274.00,
                'notes' => 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.',
                'terms' => 'This is computer generated Invoice',
                'meta' => ['payment_terms' => 'Immediate']
            ]
        );

        // Create Invoice Items if empty
        if ($invoice->items()->count() === 0) {
            $invoiceItemsData = [
                ['product' => $createdProducts[0], 'quantity' => 80.00, 'unit_price' => 25.24, 'discount' => 59.76, 'tax_amount' => 363.46, 'total' => 2019.20],
                ['product' => $createdProducts[1], 'quantity' => 60.00, 'unit_price' => 28.51, 'discount' => 67.49, 'tax_amount' => 259.61, 'total' => 1710.60],
                ['product' => $createdProducts[2], 'quantity' => 210.00, 'unit_price' => 50.49, 'discount' => 119.51, 'tax_amount' => 1609.34, 'total' => 10608.80],
                ['product' => $createdProducts[3], 'quantity' => 25.00, 'unit_price' => 70.60, 'discount' => 156.40, 'tax_amount' => 267.90, 'total' => 1765.00],
                ['product' => $createdProducts[4], 'quantity' => 60.00, 'unit_price' => 84.35, 'discount' => 199.65, 'tax_amount' => 768.31, 'total' => 5061.00],
                ['product' => $createdProducts[5], 'quantity' => 20.00, 'unit_price' => 70.69, 'discount' => 167.31, 'tax_amount' => 214.49, 'total' => 1413.80]
            ];

            foreach ($invoiceItemsData as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $itemData['product']->id,
                    'name' => $itemData['product']->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $itemData['discount'],
                    'tax_rate' => 18.00,
                    'tax_amount' => $itemData['tax_amount'],
                    'total' => $itemData['total']
                ]);
            }
        }

        $this->command->info("✅ Initial Data Seeded for {$business->name}");
    }
}
