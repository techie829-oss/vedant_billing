<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Party;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'sent', 'paid']);
        $date = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'business_id' => Business::factory(),
            'party_id' => Party::factory(),
            'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
            'date' => $date,
            'due_date' => $this->faker->dateTimeBetween($date, '+30 days'),
            'status' => $status,
            'subtotal' => 0, // Will be calculated after adding items
            'tax_total' => 0,
            'discount_total' => 0,
            'grand_total' => 0,
            'paid_amount' => 0,
            'notes' => $this->faker->sentence(),
            'terms' => $this->faker->sentence(),
            'meta' => [
                'billing_address' => [],
                'shipping_address' => [],
            ],
        ];
    }
}
