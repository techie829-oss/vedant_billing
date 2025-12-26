<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Party;

class TenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_cannot_access_other_business_data()
    {
        // 1. Setup User A (Business A)
        [$userA, $businessA] = $this->createBusinessUser();

        $partyA = Party::factory()->create(['business_id' => $businessA->id]);
        $invoiceA = Invoice::create([
            'business_id' => $businessA->id,
            'party_id' => $partyA->id,
            'invoice_number' => 'INV-A-001',
            'date' => now(),
            'due_date' => now(),
            'status' => 'draft',
            'grand_total' => 100
        ]);

        // 2. Setup User B (Business B)
        // Since createBusinessUser logs us in as that user, calling it again switches context
        [$userB, $businessB] = $this->createBusinessUser();

        // 3. User B tries to access Invoice A
        $response = $this->withHeaders([
            'X-Business-ID' => $businessB->id
        ])->getJson("/api/invoices/{$invoiceA->id}");

        // 4. Assert 404 (Not Found) because Global Scope hides it
        $response->assertStatus(404);
    }
}
