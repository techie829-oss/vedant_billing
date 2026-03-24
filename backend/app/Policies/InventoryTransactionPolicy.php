<?php

namespace App\Policies;

use App\Models\InventoryTransaction;
use App\Models\User;
use App\Models\BusinessUser;

class InventoryTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->currentBusinessId();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $role = $user->businesses()
            ->where('business_id', $user->currentBusinessId())
            ->value('business_users.role');

        return in_array($role, [BusinessUser::ROLE_OWNER, BusinessUser::ROLE_ADMIN]);
    }
}
