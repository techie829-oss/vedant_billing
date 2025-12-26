<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BusinessPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Business $business): bool
    {
        // Get membership from configured context (e.g., from middleware or pivot lookup)
        // Since Policy is usually called in controller where context is implicit or accessible
        // We can access it via user->businesses->pivot if loaded, or look it up.

        // Simpler approach: Check if user is owner or admin of THIS business
        $membership = $user->businesses()
            ->where('business_id', $business->id)
            ->wherePivotIn('role', [Role::OWNER->value, Role::ADMIN->value])
            ->exists();

        return $membership;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Business $business): bool
    {
        return $user->businesses()
            ->where('business_id', $business->id)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Business $business): bool
    {
        // Only Owner can delete business
        return $user->businesses()
            ->where('business_id', $business->id)
            ->wherePivot('role', Role::OWNER->value)
            ->exists();
    }
}
