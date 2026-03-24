<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Ledger;

class LedgerTenancyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_cannot_access_ledgers_of_other_businesses()
    {
        // 1. Setup Business A
        [$userA, $businessA] = $this->createBusinessUser();
        $ledgerA = Ledger::create([
            'business_id' => $businessA->id,
            'name' => 'Cash Account A',
            'code' => 'CASH-A',
            'type' => 'asset'
        ]);

        // 2. Setup Business B (and switch context)
        [$userB, $businessB] = $this->createBusinessUser();
        
        // 3. User B tries to find Ledger A
        $foundLedger = Ledger::find($ledgerA->id);

        // 4. Assert Ledger A is NOT found because of global scope
        $this->assertNull($foundLedger);
        
        // 5. Verify User B can see their own ledger if they create one
        $ledgerB = Ledger::create([
            'name' => 'Cash Account B',
            'code' => 'CASH-B',
            'type' => 'asset'
        ]);
        $this->assertNotNull(Ledger::find($ledgerB->id));
        $this->assertEquals($businessB->id, $ledgerB->business_id);
    }
}
