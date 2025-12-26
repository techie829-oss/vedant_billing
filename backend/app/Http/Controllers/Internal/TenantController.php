<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businesses = Business::with(['subscriptions.plan'])
            ->latest()
            ->paginate(10);

        return view('internal.tenants.index', compact('businesses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('internal.tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // Admin user for the tenant
            'subdomain' => 'nullable|string|unique:businesses,subdomain',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gstin' => 'nullable|string|max:20',
            'pan' => 'nullable|string|max:15',
            'website' => 'nullable|url|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
        ]);

        // 1. Create Business
        $business = Business::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . uniqid(),
            // 'subdomain' => $validated['subdomain'], // Phase 6 (Domains)
            'status' => 'active',
            'mobile' => $validated['mobile'] ?? null,
            'address' => $validated['address'] ?? null,
            'gstin' => $validated['gstin'] ?? null,
            'pan' => $validated['pan'] ?? null,
            'website' => $validated['website'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'ifsc_code' => $validated['ifsc_code'] ?? null,
        ]);

        // 2. Create Admin User for Tenant
        $user = \App\Models\User::create([
            'name' => $validated['name'] . ' Admin',
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make('password'), // Default password
        ]);

        // 3. Link User to Business (Owner/Admin role)
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // 4. Assign "Free" Plan by default
        $freePlan = \App\Models\Plan::where('slug', 'free')->first();
        if ($freePlan) {
            $business->subscriptions()->create([
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'current_cycle_start' => now(),
                'current_cycle_end' => now()->addMonth(),
            ]);
        }

        return redirect()->route('internal.tenants.index')
            ->with('success', 'Tenant created successfully. Default password: password');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $business = Business::with(['subscriptions.plan', 'users', 'featureOverrides.feature'])
            ->findOrFail($id);

        $features = \App\Models\Feature::where('is_active', true)->get();

        return view('internal.tenants.show', compact('business', 'features'));
    }

    /**
     * Update the specified resource status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $business = Business::findOrFail($id);

        $request->validate([
            'status' => 'required|in:active,suspended'
        ]);

        $business->update(['status' => $request->status]);

        return back()->with('success', 'Business status updated.');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $business = Business::findOrFail($id);
        return view('internal.tenants.edit', compact('business'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $business = Business::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'email' => 'required|email|unique:users,email', // Email update logic is separate usually
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gstin' => 'nullable|string|max:20',
            'pan' => 'nullable|string|max:15',
            'website' => 'nullable|url|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
        ]);

        $business->update($validated);

        return redirect()->route('internal.tenants.show', $business->id)
            ->with('success', 'Tenant details updated successfully.');
    }

    /**
     * Show the form for changing a user's password.
     */
    public function editUserPassword(string $businessId, string $userId)
    {
        $business = Business::findOrFail($businessId);
        $user = \App\Models\User::findOrFail($userId);

        // Security check: Ensure user belongs to this business (optional strictly speaking, but good practice)
        // For now, assuming internal admin has global access, but validating relation is safer.
        if (!$business->users->contains($user->id)) {
            abort(404, 'User not found in this business.');
        }

        return view('internal.tenants.users.password', compact('business', 'user'));
    }

    /**
     * Update a user's password.
     */
    public function updateUserPassword(Request $request, string $businessId, string $userId)
    {
        $business = Business::findOrFail($businessId);
        $user = \App\Models\User::findOrFail($userId);

        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return redirect()->route('internal.tenants.show', $businessId)
            ->with('success', "Password for {$user->name} updated successfully.");
    }

}
