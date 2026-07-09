<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertySubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertySubmission>
 */
class PropertySubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'owner_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'listing_price' => fake()->numberBetween(120000, 2500000),
            'status' => fake()->randomElement(['draft', 'pending', 'published', 'rejected']),
            'description' => fake()->paragraph(2),
            'notes' => fake()->sentence(),
            'publish_ready' => fake()->boolean(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'publish_ready' => false]);
    }
}
