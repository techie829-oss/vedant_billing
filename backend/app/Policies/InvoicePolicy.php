<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Models\BusinessUser; // For Role constants

class InvoicePolicy
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
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->currentBusinessId() === $invoice->party->business_id;
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
    public function update(User $user, Invoice $invoice): bool
    {
        // Allow all members to edit (drafts etc)
        // We could restrict finalized invoices here, but that's usually status logic.
        return $user->currentBusinessId() === $invoice->party->business_id;
    }

    /**
     * Determine whether the user can delete the model.
     * RESTRICTED: Owner & Admin ONLY.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        if ($user->currentBusinessId() !== $invoice->party->business_id) {
            return false;
        }

        // Check Role
        $role = $user->businesses()
            ->where('business_id', $user->currentBusinessId())
            ->value('business_users.role'); // Use table name to be safe or just 'role' if ambiguous config allowed

        return in_array($role, [BusinessUser::ROLE_OWNER, BusinessUser::ROLE_ADMIN]);
    }

    /**
     * Determine if user can finalize invoice (optional, strictly speaking update covers it, but good to separate)
     */
    public function finalize(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }
}
