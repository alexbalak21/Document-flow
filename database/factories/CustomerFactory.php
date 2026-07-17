<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => fake()->name(),
            'company'    => fake()->company(),
            'department' => fake()->optional()->jobTitle(),
            'street'     => fake()->streetAddress(),
            'city'       => fake()->city(),
            'zip'        => fake()->postcode(),
            'country'    => fake()->country(),
            'phone'      => fake()->phoneNumber(),
            'email'      => fake()->unique()->safeEmail(),
            'vat_number' => 'VAT' . fake()->numerify('#########'),
        ];
    }
}
