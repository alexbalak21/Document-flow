<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference'    => strtoupper('PRD-' . fake()->unique()->numerify('####')),
            'name'         => fake()->words(3, true),
            'description'  => fake()->optional()->sentence(),
            'product_unit' => fake()->randomElement(['unit', 'hour', 'day', 'license']),
            'unit_price'   => fake()->randomFloat(2, 5, 2000),
            'page_url'     => fake()->optional()->url(),
        ];
    }
}
