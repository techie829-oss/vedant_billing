<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')->orderBy('price')->get();
        return view('internal.plans.index', compact('plans'));
    }

    public function create()
    {
        $features = Feature::all();
        return view('internal.plans.create', compact('features'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:plans,slug',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:monthly,yearly',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'features' => 'array',
            'features.*.id' => 'exists:features,id',
            'features.*.limit' => 'required|integer|min:-1',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $plan = Plan::create($validated);

        // Attach features
        if (!empty($validated['features'])) {
            foreach ($validated['features'] as $feature) {
                $plan->features()->attach($feature['id'], ['limit' => $feature['limit']]);
            }
        }

        return redirect()->route('internal.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(string $id)
    {
        $plan = Plan::with('features')->findOrFail($id);
        $features = Feature::all();
        return view('internal.plans.edit', compact('plan', 'features'));
    }

    public function update(Request $request, string $id)
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:plans,slug,' . $id,
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:monthly,yearly',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'features' => 'array',
            'features.*.id' => 'exists:features,id',
            'features.*.limit' => 'required|integer|min:-1',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $plan->update($validated);

        // Sync features
        $featureData = [];
        if (!empty($validated['features'])) {
            foreach ($validated['features'] as $feature) {
                $featureData[$feature['id']] = ['limit' => $feature['limit']];
            }
        }
        $plan->features()->sync($featureData);

        return redirect()->route('internal.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(string $id)
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return redirect()->route('internal.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
