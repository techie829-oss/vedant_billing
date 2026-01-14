<?php
/**
 * Laravel Tinker Script to Seed Exact Invoice Data
 * 
 * Usage: php artisan tinker
 * Then paste this entire script
 */

// Step 1: Create User
$user = \App\Models\User::firstOrCreate(
    ['email' => 'chitra@example.com'],
    [
        'name' => 'R/S Chitra Enterprises',
        'password' => bcrypt('password123')
    ]
);
echo "✓ User created: {$user->email}\n";

// Step 2: Create Business (Tenant)
$business = \App\Models\Business::firstOrCreate(
    ['name' => 'R/S CHITRA ENTERPRISES'],
    [
        'user_id' => $user->id,
        'address' => "Kewalpurwa Nagar\nCity : Lakhimpur, Uttar Pradesh - 262728",
        'gstin' => '09CUVPM6712J1ZV',
        'pan' => 'CUVPM6712J',
        'phone' => '6386040903',
        'email' => 'chitra@example.com',
        'website' => '',
        'currency' => 'INR',
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
            'default_terms' => 'This is computer generated Invoice'
        ]
    ]
);
echo "✓ Business created: {$business->name}\n";

// Step 3: Create Customer (Party)
$customer = \App\Models\Party::firstOrCreate(
    [
        'business_id' => $business->id,
        'name' => 'Prabhat Electric Company'
    ],
    [
        'type' => 'customer',
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
        'meta' => [
            'state_code' => '09'
        ]
    ]
);
echo "✓ Customer created: {$customer->name}\n";

// Step 4: Create Products
$products = [
    [
        'name' => '1/2 Module Metal Box RRep',
        'hsn_code' => '85389000',
        'unit_price' => 25.24,
        'original_price' => 85.00,
        'discount_rate' => 70.30 // percentage
    ],
    [
        'name' => '3 Module Metal Box RRep',
        'hsn_code' => '85389000',
        'unit_price' => 28.51,
        'original_price' => 96.00,
        'discount_rate' => 70.30
    ],
    [
        'name' => '6/8 Module Metal Box RRep',
        'hsn_code' => '85389000',
        'unit_price' => 50.49,
        'original_price' => 170.00,
        'discount_rate' => 70.30
    ],
    [
        'name' => '8-Module Metal Box RRep (H)',
        'hsn_code' => '85389000',
        'unit_price' => 70.60,
        'original_price' => 227.00,
        'discount_rate' => 68.90
    ],
    [
        'name' => '12/16 Module Metal Box RRep',
        'hsn_code' => '85389000',
        'unit_price' => 84.35,
        'original_price' => 284.00,
        'discount_rate' => 70.30
    ],
    [
        'name' => '8 Module Metal -(V)',
        'hsn_code' => '85389000',
        'unit_price' => 70.69,
        'original_price' => 238.00,
        'discount_rate' => 70.30
    ]
];

$createdProducts = [];
foreach ($products as $productData) {
    $product = \App\Models\Product::firstOrCreate(
        [
            'business_id' => $business->id,
            'name' => $productData['name']
        ],
        [
            'hsn_code' => $productData['hsn_code'],
            'unit_price' => $productData['unit_price'],
            'tax_rate' => 18.00,
            'description' => '',
            'unit' => 'nos',
            'meta' => [
                'original_price' => $productData['original_price'],
                'discount_rate' => $productData['discount_rate']
            ]
        ]
    );
    $createdProducts[] = $product;
    echo "✓ Product created: {$product->name}\n";
}

// Step 5: Create Invoice
$invoice = \App\Models\Invoice::create([
    'business_id' => $business->id,
    'party_id' => $customer->id,
    'type' => 'invoice',
    'invoice_number' => 'Invoice 34',
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
        'payment_terms' => 'Mode/Terms of Payment',
        'eway_bill_number' => '',
        'vehicle_number' => '',
        'delivery_note' => '',
        'reference_no' => '',
        'other_references' => '',
        'buyers_order_no' => '',
        'dispatch_doc_no' => '',
        'dispatched_through' => '',
        'destination' => '',
        'terms_of_delivery' => ''
    ]
]);

echo "✓ Invoice created: {$invoice->invoice_number}\n";

// Step 6: Create Invoice Items
$invoiceItems = [
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

foreach ($invoiceItems as $itemData) {
    $item = \App\Models\InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $itemData['product']->id,
        'name' => $itemData['product']->name,
        'description' => '',
        'hsn_code' => $itemData['product']->hsn_code,
        'quantity' => $itemData['quantity'],
        'unit' => 'nos',
        'unit_price' => $itemData['unit_price'],
        'discount' => $itemData['discount'],
        'tax_rate' => $itemData['tax_rate'],
        'tax_amount' => $itemData['tax_amount'],
        'total' => $itemData['total']
    ]);
    echo "✓ Invoice item created: {$item->name} x {$item->quantity}\n";
}

echo "\n";
echo "========================================\n";
echo "✓✓✓ SEEDING COMPLETE! ✓✓✓\n";
echo "========================================\n";
echo "Business: {$business->name}\n";
echo "Customer: {$customer->name}\n";
echo "Invoice: {$invoice->invoice_number}\n";
echo "Total Amount: ₹{$invoice->grand_total}\n";
echo "========================================\n";
echo "\nLogin Credentials:\n";
echo "Email: {$user->email}\n";
echo "Password: password123\n";
echo "========================================\n";
