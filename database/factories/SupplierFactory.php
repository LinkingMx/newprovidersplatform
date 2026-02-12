<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'status' => 'created',
            'password_hash' => null,
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
            'address_street' => null,
            'address_number' => null,
            'address_neighborhood' => null,
            'address_city' => null,
            'address_country' => null,
            'address_zip' => null,
            'clabe_interbancaria' => null,
        ];
    }

    public function registered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'registered',
            'password_hash' => bcrypt('password'),
        ]);
    }

    public function invited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'invited',
        ]);
    }

    public function profileCompleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'profile_completed',
            'password_hash' => bcrypt('password'),
            'address_street' => fake()->streetAddress(),
            'address_number' => fake()->buildingNumber(),
            'address_neighborhood' => fake()->city(),
            'address_city' => fake()->city(),
            'address_country' => 'Mexico',
            'address_zip' => fake()->postcode(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'password_hash' => bcrypt('password'),
            'address_street' => fake()->streetAddress(),
            'address_number' => fake()->buildingNumber(),
            'address_neighborhood' => fake()->city(),
            'address_city' => fake()->city(),
            'address_country' => 'Mexico',
            'address_zip' => fake()->postcode(),
            'clabe_interbancaria' => fake()->numerify('####################'),
        ]);
    }
}
