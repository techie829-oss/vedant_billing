<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Ledger;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\InvoiceScan;
use App\Models\TempProduct;
use App\Models\QuickNote;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Party;

class TenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function all_models_enforce_tenancy_isolation()
    {
        // 1. Setup Business A
        [$userA, $businessA] = $this->createBusinessUser();
        
        $models = [
            Ledger::class => ['name' => 'Ledger A', 'code' => 'A', 'type' => 'asset'],
            Expense::class => ['category' => 'Travel', 'amount' => 100, 'date' => now()],
            JournalEntry::class => ['description' => 'Entry A', 'date' => now()],
            Payment::class => ['amount' => 50, 'date' => now(), 'method' => 'cash'],
            QuickNote::class => ['title' => 'Note A', 'user_id' => $userA->id, 'content' => []],
            Product::class => ['name' => 'Product A', 'type' => 'goods', 'sale_price' => 10],
            Party::class => ['name' => 'Party A', 'party_type' => 'customer'],
            Invoice::class => [
                'party_id' => Party::factory()->create(['business_id' => $businessA->id])->id,
                'invoice_number' => 'INV-A', 
                'date' => now(), 
                'due_date' => now(), 
                'grand_total' => 100
            ],
            InvoiceScan::class => ['image_path' => 'scans/a.jpg', 'status' => 'pending'],
        ];

        $createdIds = [];
        foreach ($models as $class => $data) {
            $instance = $class::create(array_merge($data, ['business_id' => $businessA->id]));
            $createdIds[$class] = $instance->id;
        }

        // 2. Setup Business B (and switch context)
        [$userB, $businessB] = $this->createBusinessUser();
        
        // 3. Verify Business B cannot see Business A's data
        foreach ($createdIds as $class => $id) {
            $found = $class::find($id);
            $this->assertNull($found, "User B should not see {$class} from Business A (ID: {$id})");
        }

        // 4. Verify Business B's creations belong to Business B automatically
        foreach ($models as $class => $data) {
            // Filter out specific fields that must belong to business B
            if ($class === Invoice::class) {
                $data['party_id'] = Party::factory()->create(['business_id' => $businessB->id])->id;
                $data['invoice_number'] = 'INV-B';
            }
            if ($class === QuickNote::class) {
                $data['user_id'] = $userB->id;
            }
            
            // Should auto-fill business_id via creating hook
            $instanceB = $class::create($data);
            $this->assertEquals($businessB->id, $instanceB->business_id, "{$class} should auto-assign business_id on creation");
        }
    }
}
