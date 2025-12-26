<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\BusinessUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BusinessUserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Any active member can view listing (e.g. for team page)
        // We assume the controller scopes to the current tenant
        // We need to know which tenant we are checking for.

        // In TenantContext middleware, we set 'business' attribute.
        // Policies might not have easy access to request attributes directly without passing them.
        // However, usually we check permissions on a specific Resource or "Business" context.

        // For general 'viewAny', we might rely on Middleware Role check.
        // But if we want policy:
        return true; // Use middleware for broad access
    }

    /**
     * Determine whether the user can create models (Invite users).
     */
    public function create(User $user): bool
    {
        // Get current business context ID from request or similar
        // Limitation: Standard Policy 'create' doesn't pass the Target Model instance.
        // We rely on the request header or argument passed toauthorize('create', [BusinessUser::class, $business])

        // For simplicity here, we assume checking against the CURRENT tenant context found in request
        $business = request()->attributes->get('business');
        if (!$business)
            return false;

        return $user->businesses()
            ->where('business_id', $business->id)
            ->wherePivotIn('role', [Role::OWNER->value, Role::ADMIN->value])
            ->exists();
    }

    /**
     * Determine whether the user can update the model (Change role).
     */
    public function update(User $user, BusinessUser $targetMembership): bool
    {
        // 1. You cannot update your own role (prevent admin from promoting self to owner)
        if ($user->id === $targetMembership->user_id) {
            return false;
        }

        $businessId = $targetMembership->business_id;

        // 2. Get actor's role
        $actorMembership = $user->businesses()
            ->where('business_id', $businessId)
            ->first();

        if (!$actorMembership)
            return false;

        $actorRole = $actorMembership->pivot->role;
        $targetRole = $targetMembership->role;

        // 3. Logic:
        // Owner can update anyone.
        if ($actorRole === Role::OWNER->value) {
            return true;
        }

        // Admin can update Staff/Accountant, but NOT Owner or other Admins
        if ($actorRole === Role::ADMIN->value) {
            return !in_array($targetRole, [Role::OWNER->value, Role::ADMIN->value]);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model (Remove user).
     */
    public function delete(User $user, BusinessUser $targetMembership): bool
    {
        // Same logic as update: Owner can remove anyone; Admin can remove lower roles.
        // Cannot remove self (use 'leave' logic instead)
        if ($user->id === $targetMembership->user_id) {
            return false;
        }

        $businessId = $targetMembership->business_id;

        $actorMembership = $user->businesses()
            ->where('business_id', $businessId)
            ->first();

        if (!$actorMembership)
            return false;

        $actorRole = $actorMembership->pivot->role;
        $targetRole = $targetMembership->role;

        if ($actorRole === Role::OWNER->value) {
            return true;
        }

        if ($actorRole === Role::ADMIN->value) {
            return !in_array($targetRole, [Role::OWNER->value, Role::ADMIN->value]);
        }

        return false;
    }
}
