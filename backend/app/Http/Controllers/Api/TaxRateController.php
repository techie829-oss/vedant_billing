<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TaxRate::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'in:percentage,fixed',
            'status' => 'in:active,inactive',
        ]);

        $taxRate = TaxRate::create($validated);

        return response()->json($taxRate, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TaxRate $taxRate)
    {
        return response()->json($taxRate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'rate' => 'sometimes|numeric|min:0|max:100',
            'type' => 'sometimes|in:percentage,fixed',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $taxRate->update($validated);

        return response()->json($taxRate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return response()->noContent();
    }
}
