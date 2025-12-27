<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Models\BusinessUser;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->currentBusinessId();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->currentBusinessId() === $product->business_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return (bool) $user->currentBusinessId();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->currentBusinessId() === $product->business_id;
    }

    /**
     * Determine whether the user can delete the model.
     * RESTRICTED: Owner & Admin ONLY.
     */
    public function delete(User $user, Product $product): bool
    {
        if ($user->currentBusinessId() !== $product->business_id) {
            return false;
        }

        // Check Role
        $role = $user->businesses()
            ->where('business_id', $user->currentBusinessId())
            ->value('business_users.role');

        return in_array($role, [BusinessUser::ROLE_OWNER, BusinessUser::ROLE_ADMIN]);
    }
}
