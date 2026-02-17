<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GstState;
use Illuminate\Http\Request;

class GstController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request)
    {
        $states = GstState::orderBy('name')->get();
        return response()->json($states);
    }
    /**
     * Lookup GSTIN details.
     */
    /**
     * Lookup GSTIN details (Cashfree).
     */
    public function lookup(Request $request, $gstin)
    {
        // 1. Check local DB (Cache)
        $cached = \App\Models\GstMaster::where('gstin', $gstin)->first();
        if ($cached) {
            $data = $this->formatResponse($cached->gstin, $cached->legal_name, $cached->trade_name, $cached->address, $cached->status, $cached->meta);
            return response()->json($data);
        }

        // Basic validation
        if (!preg_match("/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/", $gstin)) {
            return response()->json(['message' => 'Invalid GSTIN format'], 400);
        }

        try {
            $service = new \App\Services\CashfreeGstService();
            $result = $service->verify($gstin);

            if ($result['success'] && isset($result['data']['valid']) && $result['data']['valid'] === true) {
                $meta = $result['data'];

                // Map Cashfree response to our schema
                $legalName = $meta['legal_name_of_business'] ?? '';
                $tradeName = $meta['trade_name_of_business'] ?? $legalName;
                $addressString = $meta['principal_place_address'] ?? '';

                // Store in DB
                $gstMaster = \App\Models\GstMaster::create([
                    'gstin' => $gstin,
                    'legal_name' => $legalName,
                    'trade_name' => $tradeName,
                    'address' => $addressString,
                    'status' => $meta['gst_in_status'] ?? 'Active',
                    'meta' => $meta
                ]);

                return response()->json($this->formatResponse($gstin, $legalName, $tradeName, $addressString, $meta['gst_in_status'], $meta));
            }

            return response()->json([
                'message' => 'GSTIN not found or invalid',
                'error' => $result['error'] ?? 'Unknown error'
            ], 404);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error connecting to GST Service', 'error' => $e->getMessage()], 500);
        }
    }

    private function formatResponse($gstin, $legalName, $tradeName, $address, $status, $meta)
    {
        $data = [
            'gstin' => $gstin,
            'legal_name' => $legalName,
            'trade_name' => $tradeName,
            'address' => $address,
            'status' => $status,
            'source' => 'db',
            'raw' => $meta,
            // Extra normalized fields
            'city' => null,
            'state' => null,
            'pincode' => null,
            'full_address_details' => []
        ];

        // --- CASHFREE MAPPING START ---
        // If it's a Cashfree response, we can find 'principal_place_split_address'
        if (isset($meta['principal_place_split_address'])) {
            $split = $meta['principal_place_split_address'];
            $data['city'] = $split['city'] ?? $split['district'] ?? null;
            $data['state'] = $split['state'] ?? null;
            $data['pincode'] = $split['pincode'] ?? null;

            $data['full_address_details'] = [
                'building_no' => $split['building_number'] ?? '',
                'building_name' => $split['building_name'] ?? '',
                'floor_no' => $split['flat_number'] ?? '',
                'street' => $split['street'] ?? '',
                'location' => $split['location'] ?? '',
                'district' => $split['district'] ?? '',
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'latitude' => $split['latitude'] ?? '',
                'longitude' => $split['longitude'] ?? '',
            ];

            return $data; // Return early for Cashfree
        }
        // --- CASHFREE MAPPING END ---

        // State from GSTIN Code Fallback (Last Resort)
        if (empty($data['state'])) {
            $code = substr($gstin, 0, 2);
            $state = \App\Models\GstState::where('code', $code)->first();
            if ($state)
                $data['state'] = $state->name;
        }

        return $data;
    }
}
