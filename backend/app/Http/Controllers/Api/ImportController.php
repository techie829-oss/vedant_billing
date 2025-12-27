<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Party;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    /**
     * Import Tally Masters (XML)
     * Handles Ledgers (Customers/Vendors) and Stock Items
     */
    public function importTallyMasters(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xml,txt', // Tally exports often as .xml or .txt
        ]);

        $businessId = $request->header('X-Business-Id');
        if (!$businessId) {
            return response()->json(['message' => 'Business ID required'], 400);
        }

        try {
            $file = $request->file('file');
            $xmlContent = file_get_contents($file->getRealPath());

            // Clean XML content if needed (Tally sometimes adds non-standard chars)
            $xmlContent = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $xmlContent);

            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                return response()->json(['message' => 'Invalid XML file'], 422);
            }

            $stats = [
                'ledgers_processed' => 0,
                'ledgers_created' => 0,
                'ledgers_updated' => 0,
                'stock_items_processed' => 0,
                'stock_items_created' => 0,
                'stock_items_updated' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            // 1. Process LEDGERS (Parties)
            // Tally Hierachy: BODY -> IMPORTDATA -> REQUESTDATA -> TALLYMESSAGE -> LEDGER
            // We'll search for all LEDGER nodes recursively or iterating TALLYMESSAGE

            // Using xpath to find all LEDGER nodes regardless of exact structure
            $ledgerNodes = $xml->xpath('//LEDGER');

            foreach ($ledgerNodes as $node) {
                try {
                    $stats['ledgers_processed']++;

                    $name = (string) $node['NAME'];
                    if (empty($name))
                        continue;

                    // Extract fields
                    $gstin = (string) $node->{'PARTYGSTIN'} ?? null;
                    $mailingName = (string) $node->{'MAILINGNAME.LIST'}->{'MAILINGNAME'} ?? $name;
                    $address = (string) $node->{'ADDRESS.LIST'}->{'ADDRESS'} ?? null;
                    $state = (string) $node->{'STATENAME'} ?? null;
                    $email = (string) $node->{'EMAIL'} ?? null;
                    $mobile = (string) $node->{'IINCOMETAXPAN'} ?? null; // Sometimes PAN is here, checking regex later
                    // Better to check specific tags

                    // Simple heuristic for type based on PARENT
                    $parentGroup = (string) $node->{'PARENT'};
                    $type = 'customer'; // Default
                    if (stripos($parentGroup, 'Creditor') !== false) {
                        $type = 'vendor';
                    }

                    // Tally Opening Balance
                    // OPENINGBALANCE: Negative is Debit (Receivable from customer), Positive is Credit (Payable to vendor) for Assets?
                    // Actually Tally logic: Debit is +, Credit is -. 
                    // Let's rely on standard: Dr > 0 (We owe/Asset?), Cr < 0 (Liability)?
                    // Simplification: We just store it.
                    $openingBalance = (float) $node->{'OPENINGBALANCE'};

                    $party = Party::where('business_id', $businessId)
                        ->where('name', $name)
                        ->first();

                    if (!$party) {
                        // Check match by GSTIN if available to avoid dupes
                        if ($gstin) {
                            $party = Party::where('business_id', $businessId)->where('gstin', $gstin)->first();
                        }
                    }

                    $data = [
                        'business_id' => $businessId,
                        'name' => $name,
                        'type' => $type,
                        'gstin' => $gstin,
                        'email' => $email,
                        'billing_address' => [
                            'street' => $address,
                            'city' => '', // Tally doesn't strictly separate city
                            'state' => $state,
                            'zip' => '',
                            'country' => 'India' // Assume India for Tally
                        ],
                        'shipping_address' => [ // Copy to shipping
                            'street' => $address,
                            'city' => '',
                            'state' => $state,
                            'zip' => '',
                            'country' => 'India'
                        ],
                        'is_active' => true
                    ];

                    if ($party) {
                        $party->update($data);
                        $stats['ledgers_updated']++;
                    } else {
                        Party::create($data);
                        $stats['ledgers_created']++;
                    }

                } catch (\Exception $e) {
                    $stats['errors'][] = "Ledger Error ({$name}): " . $e->getMessage();
                }
            }

            // 2. Process STOCK ITEMS (Products)
            $stockNodes = $xml->xpath('//STOCKITEM');

            foreach ($stockNodes as $node) {
                try {
                    $stats['stock_items_processed']++;

                    $name = (string) $node['NAME'];
                    if (empty($name))
                        continue;

                    $partNo = (string) $node->{'MAILINGNAME.LIST'}->{'MAILINGNAME'} ?? null; // Sometimes used
                    $baseUnits = (string) $node->{'BASEUNITS'} ?? 'Unit';

                    // GST Details (Often nested deep in Tally XML, simple extraction for now)
                    // Looking for generic IGSTRATE or similar
                    $igstRate = (float) ($node->{'IGSTRATE'} ?? 18); // Default/Fallback?
                    if ($igstRate == 0)
                        $igstRate = 18; // Safe default if missing

                    $hsn = (string) $node->{'HSNCODE'} ?? '';

                    // Opening Balance
                    $openingQty = (float) ($node->{'OPENINGBALANCE'} ?? 0);
                    // Parse "10 Nos" -> 10
                    $openingQty = (float) filter_var($node->{'OPENINGBALANCE'}, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                    $openingValue = (float) ($node->{'OPENINGVALUE'} ?? 0);
                    // Calculate Purchase Price estimate
                    $purchasePrice = $openingQty > 0 ? abs($openingValue / $openingQty) : 0;

                    // Standard Rate (Selling Price)
                    // Tally stores price lists separately usually, but we check for STANDARDPRICE
                    $salePrice = (float) ($node->{'STANDARDPRICE'} ?? 0);
                    if ($salePrice == 0)
                        $salePrice = $purchasePrice * 1.2; // 20% markup fallback

                    $product = Product::where('business_id', $businessId)
                        ->where('name', $name)
                        ->first();

                    $data = [
                        'business_id' => $businessId,
                        'name' => $name,
                        'description' => 'Imported from Tally',
                        'type' => 'goods', // Tally Stock Items are goods
                        'unit' => $baseUnits,
                        'hsn_code' => $hsn,
                        'sale_price' => $salePrice,
                        'purchase_price' => $purchasePrice,
                        'tax_rate' => $igstRate,
                        //'current_stock' => $openingQty, // We might increment this or set it? 
                        // For import, usually we want to set Opening Stock.
                        // But our system tracks current_stock directly. 
                        // If product exists, we might skip overwriting stock to avoid double counting if run twice.
                        // Let's set it only on create for safety.
                    ];

                    if ($product) {
                        // Updates
                        $product->update([
                            'hsn_code' => $hsn,
                            'sale_price' => $salePrice > 0 ? $salePrice : $product->sale_price,
                        ]);
                        $stats['stock_items_updated']++;
                    } else {
                        // Create
                        $data['current_stock'] = abs($openingQty); // Tally uses negative for debit? value is usually negative
                        Product::create($data);
                        $stats['stock_items_created']++;
                    }

                } catch (\Exception $e) {
                    $stats['errors'][] = "Stock Item Error ({$name}): " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Import processed successfully',
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tally Import Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
}
