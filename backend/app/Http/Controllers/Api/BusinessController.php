<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    /**
     * Display a listing of the authenticated user's businesses.
     */
    public function index(Request $request)
    {
        $businesses = $request->user()->businesses()
            ->withPivot(['role', 'status', 'joined_at'])
            ->with([
                'subscriptions' => function ($q) {
                    $q->with('plan.features')->latest()->limit(1);
                }
            ])
            ->get();

        return response()->json($businesses);
    }

    /**
     * Store a newly created business in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3', // e.g., INR
            // Add other fields as necessary from migration
        ]);

        // Create the business
        $business = Business::create($validated);

        // Attach the user as Owner (Assuming role logic exists or pivot table has defaults)
        // Adjust based on your BusinessUser pivot or relation logic
        $request->user()->businesses()->attach($business->id, ['role' => 'owner']); // Ensure role column exists in pivot

        return response()->json($business, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Business $business)
    {
        // Authorization: Ensure user belongs to this business
        if (!$business->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($business);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business $business)
    {
        // Check authorization (e.g. valid user and maybe 'owner' role)
        if (!$business->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'currency' => 'sometimes|string|size:3',
            'address' => 'sometimes|nullable|string',
            'gstin' => 'sometimes|nullable|string',
            'pan' => 'sometimes|nullable|string',
            'website' => 'sometimes|nullable|url',
            'bank_name' => 'sometimes|nullable|string',
            'account_number' => 'sometimes|nullable|string',
            'ifsc_code' => 'sometimes|nullable|string',
            'meta' => 'sometimes|array', // Validate meta as array
        ]);

        // If meta is present, we merge it with existing meta instead of overwriting completely
        if (isset($validated['meta'])) {
            $currentMeta = $business->meta ?? [];
            $validated['meta'] = array_merge($currentMeta, $validated['meta']);
        }

        $business->update($validated);

        return response()->json($business);
    }

    /**
     * Update invoice display preferences
     */
    public function updateInvoicePreferences(Request $request, Business $business)
    {
        // Check authorization
        if (!$business->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'show_hsn_sac' => 'boolean',
            'show_gst_breakdown' => 'boolean',
            'show_bank_qr' => 'boolean',
            'show_notes' => 'boolean',
            'show_shipping_address' => 'boolean',
            'show_discount_column' => 'boolean',
            'show_transport_details' => 'boolean',
        ]);

        $currentMeta = $business->meta ?? [];
        $currentMeta['invoice_display_preferences'] = $validated;

        $business->update(['meta' => $currentMeta]);

        return response()->json([
            'message' => 'Preferences updated successfully',
            'preferences' => $validated
        ]);
    }
}
