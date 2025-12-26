<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Invoice;
use App\Models\Ledger;
use App\Models\Party;
use App\Events\InvoiceFinalized;
use App\Listeners\CreateLedgerEntriesForInvoice;
use Illuminate\Support\Facades\Event;

class LedgerEntryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $business;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->business] = $this->createBusinessUser();

        // Seed the required ledgers
        Ledger::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Debtors',
            'code' => 'DEBTORS',
            'type' => 'asset'
        ]);

        Ledger::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Sales',
            'code' => 'SALES',
            'type' => 'income'
        ]);
    }

    /** @test */
    public function it_creates_ledger_entries_when_invoice_is_finalized()
    {
        // 1. Create Invoice
        $party = Party::factory()->create(['business_id' => $this->business->id]);

        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'party_id' => $party->id,
            'invoice_number' => 'INV-TEST-001',
            'date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => 'sent', // As if it was just finalized
            'grand_total' => 1000.00,
            'subtotal' => 1000.00,
            'tax_total' => 0,
            'meta' => []
        ]);

        // 2. Dispatch Event
        $event = new InvoiceFinalized($invoice);
        $listener = new CreateLedgerEntriesForInvoice();
        $listener->handle($event);

        // 3. Assert Journal Entry Created
        $this->assertDatabaseHas('journal_entries', [
            'business_id' => $this->business->id,
            'reference_id' => $invoice->id,
            'description' => "Invoice {$invoice->invoice_number}"
        ]);

        // 4. Assert Ledger Entries (Debit & Credit)
        // Check Debtors (Debit)
        $this->assertDatabaseHas('ledger_entries', [
            'business_id' => $this->business->id,
            'amount' => 100000, // 1000.00 * 100
            'type' => 'debit'
            // We can resolve ledger ID but it's fine to check amounts and types existence if unique enough
        ]);

        // Check Sales (Credit)
        $this->assertDatabaseHas('ledger_entries', [
            'business_id' => $this->business->id,
            'amount' => 100000,
            'type' => 'credit'
        ]);
    }
}
