<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Invoice;
use App\Models\Party;

class UsageService
{
    /**
     * Get usage statistics for a business.
     *
     * @param Business $business
     * @return array
     */
    public function getUsage(Business $business)
    {
        return [
            'monthly_invoices' => $this->getMonthlyInvoicesUsage($business),
            'clients_limit' => $this->getClientsUsage($business),
            'multi_user' => $this->getUsersUsage($business),
        ];
    }

    protected function getMonthlyInvoicesUsage(Business $business)
    {
        // Count invoices created in the current month
        return Invoice::where('business_id', $business->id)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->count();
    }

    protected function getClientsUsage(Business $business)
    {
        // Count active customers
        return Party::where('business_id', $business->id)
            ->where('party_type', 'customer')
            ->count();
    }

    protected function getUsersUsage(Business $business)
    {
        // Count users attached to the business
        return BusinessUser::where('business_id', $business->id)->count();
    }
}
