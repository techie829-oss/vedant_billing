<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Party;
use App\Models\Product;
use Carbon\Carbon;

class InvoiceCalculationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $business;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->business] = $this->createBusinessUser();

        // Ensure GST States exist (Seeded usually, but we can mock or rely on seeder if RefreshDatabase calls it? 
        // RefreshDatabase does migrate, but doesn't seed by default. We should seed strictly if needed, or manually create.)
        // For calculation test, we might not need strict state DB relation if we don't validate it strictly yet.
        // But InvoiceController doesn't check state table, just party/business meta.
    }

    /** @test */
    public function it_calculates_simple_total_correctly()
    {
        $party = Party::factory()->create([
            'business_id' => $this->business->id,
            'party_type' => 'customer'
        ]);

        $product = Product::factory()->create([
            'business_id' => $this->business->id,
            'sale_price' => 100,
            'tax_rate' => 0 // Tax free for simple test
        ]);

        $payload = [
            'party_id' => $party->id,
            'date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => 'Test Item',
                    'quantity' => 2,
                    'unit_price' => 100,
                    'tax_rate' => 0
                ]
            ]
        ];

        $response = $this->withHeaders([
            'X-Business-ID' => $this->business->id
        ])->postJson('/api/invoices', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('subtotal', "200.00")
            ->assertJsonPath('tax_total', "0.00")
            ->assertJsonPath('grand_total', "200.00");
    }

    /** @test */
    public function it_calculates_tax_correctly()
    {
        $party = Party::factory()->create(['business_id' => $this->business->id]);

        $payload = [
            'party_id' => $party->id,
            'date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'items' => [
                [
                    'description' => 'Taxable Item',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'tax_rate' => 18 // 18% Tax
                ]
            ]
        ];

        $response = $this->withHeaders([
            'X-Business-ID' => $this->business->id
        ])->postJson('/api/invoices', $payload);

        // 1000 * 18% = 180 Tax. Total = 1180.
        $response->assertStatus(201)
            ->assertJsonPath('subtotal', "1000.00")
            ->assertJsonPath('tax_total', "180.00")
            ->assertJsonPath('grand_total', "1180.00");
    }

    /** @test */
    public function it_applies_discount_before_tax()
    {
        $party = Party::factory()->create(['business_id' => $this->business->id]);

        $payload = [
            'party_id' => $party->id,
            'date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'items' => [
                [
                    'description' => 'Discounted Item',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'discount' => 100, // 100rs off
                    'tax_rate' => 18
                ]
            ]
        ];

        // Taxable = 900. Tax = 900 * 0.18 = 162. Total = 1062.
        $response = $this->withHeaders([
            'X-Business-ID' => $this->business->id
        ])->postJson('/api/invoices', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('subtotal', "900.00")
            ->assertJsonPath('tax_total', "162.00")
            ->assertJsonPath('grand_total', "1062.00");
    }
}
