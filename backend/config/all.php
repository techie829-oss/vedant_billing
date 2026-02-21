<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GST Rates
    |--------------------------------------------------------------------------
    | Complete list of applicable Indian GST rates (including decimals).
    | Kept as strings to avoid floating-point array key casting issues.
    */
    'gst_rates' => [
        '0' => '0%',
        '0.1' => '0.1%',
        '0.25' => '0.25%',
        '1.5' => '1.5%',
        '3' => '3%',
        '5' => '5%',
        '12' => '12%',
        '18' => '18%',
        '28' => '28%',
        '40' => '40%',
    ],

    /*
    |--------------------------------------------------------------------------
    | Unit Types (UOM)
    |--------------------------------------------------------------------------
    | Flat associative array of Units of Measurement, grouped visually 
    | so validation rules like `in:array_keys(...)` continue to work smoothly.
    */
    'unit_types' => [
        // Quantity & Pieces
        'nos' => 'Numbers (Nos)',
        'pcs' => 'Pieces (Pcs)',
        'pair' => 'Pair',
        'set' => 'Set',
        'unit' => 'Unit',

        // Weight & Mass
        'gram' => 'Gram (g)',
        'kg' => 'Kilogram (Kg)',
        'quintal' => 'Quintal',
        'ton' => 'Metric Ton',

        // Volume & Liquid
        'ml' => 'Milliliter (Ml)',
        'ltr' => 'Liter (Ltr)',

        // Length & Distance
        'mm' => 'Millimeter (Mm)',
        'cm' => 'Centimeter (Cm)',
        'mtr' => 'Meter (Mtr)',
        'inch    ' => 'Inch',
        'foot' => 'Foot',
        'yard' => 'Yard',

        // Packaging & Containers
        'bag' => 'Bag',
        'bottle' => 'Bottle',
        'box' => 'Box',
        'bundle' => 'Bundle',
        'can' => 'Can',
        'carton' => 'Carton',
        'case' => 'Case',
        'coil' => 'Coil',
        'crate' => 'Crate',
        'drum' => 'Drum',
        'jar' => 'Jar',
        'package' => 'Package',
        'packet' => 'Packet',
        'pallet' => 'Pallet',
        'reel' => 'Reel',
        'roll' => 'Roll',
        'sack' => 'Sack',
        'sheet' => 'Sheet',
        'strip' => 'Strip',
        'tube' => 'Tube',

        // Production
        'batch' => 'Batch',
        'lot' => 'Lot',

        // Other
        'other' => 'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Types
    |--------------------------------------------------------------------------
    | Distinguishes between tangible items and services.
    */
    'product_types' => [
        'goods' => 'Goods',
        'service' => 'Service',
    ],
];