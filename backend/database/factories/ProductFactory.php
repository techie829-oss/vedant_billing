<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'type' => 'goods',
            'sale_price' => $this->faker->randomFloat(2, 100, 5000),
            'purchase_price' => $this->faker->randomFloat(2, 50, 4000),
            'tax_rate' => 18.00,
            'hsn_code' => $this->faker->numberBetween(1000, 9999),
            'current_stock' => 100,
        ];
    }
}
