<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Models\BusinessUser;

class PaymentPolicy
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
    public function view(User $user, Payment $payment): bool
    {
        return $user->currentBusinessId() === $payment->business_id;
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
    public function update(User $user, Payment $payment): bool
    {
        if ($user->currentBusinessId() !== $payment->business_id) {
            return false;
        }

        $role = $user->businesses()
            ->where('business_id', $user->currentBusinessId())
            ->value('business_users.role');

        return in_array($role, [BusinessUser::ROLE_OWNER, BusinessUser::ROLE_ADMIN]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): bool
    {
        if ($user->currentBusinessId() !== $payment->business_id) {
            return false;
        }

        $role = $user->businesses()
            ->where('business_id', $user->currentBusinessId())
            ->value('business_users.role');

        return in_array($role, [BusinessUser::ROLE_OWNER, BusinessUser::ROLE_ADMIN]);
    }
}
