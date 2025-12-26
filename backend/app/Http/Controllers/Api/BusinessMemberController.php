<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\User;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Enums\Role;

use Illuminate\Support\Facades\Gate;

class BusinessMemberController extends Controller
{
    protected $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * List all members of the business.
     */
    public function index(Request $request, Business $business)
    {
        Gate::authorize('view', $business);

        // Use pivot model directly to avoid relation scope issues and ensure soft-deletes are handled if needed (though we restored them)
        // Also simpler to debug.
        $pivotRecords = \App\Models\BusinessUser::where('business_id', $business->id)
            ->with('user')
            ->get();

        $members = $pivotRecords->map(function ($pivot) {
            if (!$pivot->user)
                return null; // Should not happen
            return [
                'id' => $pivot->user->id,
                'name' => $pivot->user->name,
                'email' => $pivot->user->email,
                'role' => $pivot->role,
                'status' => $pivot->status,
                'joined_at' => $pivot->joined_at,
            ];
        })->filter()->values();

        return response()->json($members);
    }

    /**
     * Invite/Add a new member.
     */
    public function store(Request $request, Business $business)
    {
        Gate::authorize('update', $business);

        // 1. Check Feature Limit (Multi-User)
        $canAddUser = $this->featureService->hasFeature($business, 'multi_user');
        if (!$canAddUser) {
            return response()->json(['message' => 'Your plan does not support multiple users. Please upgrade.'], 403);
        }

        // Check numeric limit if applicable (e.g. max 5 users)
        // For now 'multi_user' is boolean in seeder, but we might want a 'users_limit' feature too.
        // PlanSeeder has 'multi_user' as boolean (0/1). 
        // But some plans might have a count limit. Let's rely on 'multi_user' boolean for access, 
        // and maybe checking 'clients_limit' style for users if we had one.
        // For now, if they have 'multi_user', we allow it.

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', Rule::in([Role::ADMIN->value, Role::STAFF->value, Role::ACCOUNTANT->value])]
        ]);

        \Illuminate\Support\Facades\Log::info("Inviting member: {$validated['email']} to business {$business->id}");

        $user = User::withTrashed()->where('email', $validated['email'])->first();

        $generatedPassword = null;

        if (!$user) {
            // Auto-create user
            $generatedPassword = \Illuminate\Support\Str::random(10);
            try {
                $user = User::create([
                    'name' => explode('@', $validated['email'])[0], // Use email prefix as name
                    'email' => $validated['email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($generatedPassword),
                    'status' => 'active'
                ]);
                \Illuminate\Support\Facades\Log::info("Created new user: {$user->id}");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to create user: " . $e->getMessage());
                return response()->json(['message' => 'Failed to create user account.'], 500);
            }
        } elseif ($user->trashed()) {
            // Restore soft-deleted user
            $user->restore();
            $user->update(['status' => 'active']); // Ensure active
            \Illuminate\Support\Facades\Log::info("Restored soft-deleted user: {$user->id}");
        }

        // Check if pivot exists (including trashed)
        $existingMember = $business->users()->withTrashed()->where('user_id', $user->id)->first();

        if ($existingMember) {
            // Check if it's trashed (soft deleted)
            if ($existingMember->pivot->trashed()) {
                // Restore logic for pivot
                // Since it's a pivot model with SoftDeletes, we should be able to restore it.
                // However, accessing pivot directly as model is cleaner using BusinessUser query.
                \App\Models\BusinessUser::withTrashed()
                    ->where('business_id', $business->id)
                    ->where('user_id', $user->id)
                    ->restore();

                \Illuminate\Support\Facades\Log::info("Restored soft-deleted member pivot: {$user->id}");
                return response()->json(['message' => 'Member restored successfully.']);
            }

            return response()->json(['message' => 'User is already a member of this business.'], 422);
        }

        // Attach User
        $business->users()->attach($user->id, [
            'role' => $validated['role'],
            'status' => 'active', // Direct add for MVP
            'joined_at' => now()
        ]);

        $message = 'Member added successfully.';
        if ($generatedPassword) {
            $message .= " User created. Temporary password: {$generatedPassword}";
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Update member role.
     */
    public function update(Request $request, Business $business, User $user)
    {
        Gate::authorize('update', $business);

        // Cannot change Owner's role
        $currentRole = $business->users()->where('user_id', $user->id)->first()->pivot->role;
        if ($currentRole === Role::OWNER->value) {
            return response()->json(['message' => 'Cannot change role of the Owner.'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in([Role::ADMIN->value, Role::STAFF->value, Role::ACCOUNTANT->value])]
        ]);

        $business->users()->updateExistingPivot($user->id, [
            'role' => $validated['role']
        ]);

        return response()->json(['message' => 'Member role updated.']);
    }

    /**
     * Remove member.
     */
    public function destroy(Business $business, User $user)
    {
        Gate::authorize('update', $business);

        // Cannot remove Owner
        $currentRole = $business->users()->where('user_id', $user->id)->first()->pivot->role;
        if ($currentRole === Role::OWNER->value) {
            return response()->json(['message' => 'Cannot remove the Owner.'], 403);
        }

        // Prevent removing yourself (if admin)
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot remove yourself.'], 403);
        }

        $business->users()->detach($user->id);

        return response()->json(['message' => 'Member removed successfully.']);
    }
}
