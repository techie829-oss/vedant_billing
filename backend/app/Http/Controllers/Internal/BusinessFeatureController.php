<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessFeatureOverride;
use Illuminate\Http\Request;

class BusinessFeatureController extends Controller
{
    /**
     * Store (or update) a feature override for a business.
     */
    public function store(Request $request, Business $business)
    {
        $validated = $request->validate([
            'feature_id' => 'required|exists:features,id',
            'limit' => 'required|integer|min:-1',
        ]);

        BusinessFeatureOverride::updateOrCreate(
            [
                'business_id' => $business->id,
                'feature_id' => $validated['feature_id'],
            ],
            [
                'limit' => $validated['limit'],
                // 'expires_at' => $request->date('expires_at'), // Optional future enhancement
            ]
        );

        return back()->with('success', 'Feature override updated successfully.');
    }

    /**
     * Remove an override (reverting to plan defaults).
     */
    public function destroy(Business $business, $featureId)
    {
        BusinessFeatureOverride::where('business_id', $business->id)
            ->where('feature_id', $featureId)
            ->delete();

        return back()->with('success', 'Feature override removed. Reverted to plan defaults.');
    }
}
