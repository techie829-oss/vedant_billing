<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Party;

class PartyFactory extends Factory
{
    protected $model = Party::class;

    public function definition()
    {
        return [
            'business_id' => \App\Models\Business::factory(),
            'name' => $this->faker->name,
            'party_type' => 'customer',
            'email' => $this->faker->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'billing_address' => [
                'street' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => 'Maharashtra', // Default state
                'zip' => $this->faker->postcode,
            ],
            'shipping_address' => [
                'street' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => 'Maharashtra',
                'zip' => $this->faker->postcode,
            ],
        ];
    }
}
