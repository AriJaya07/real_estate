<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->streetName().' Residence',
            'location' => fake()->city().', '.fake()->country(),
            'price' => fake()->numberBetween(120000, 2500000),
            'type' => fake()->randomElement(['House', 'Apartment', 'Villa', 'Land', 'Office']),
            'image' => 'https://picsum.photos/seed/'.fake()->uuid().'/800/600',
            'description' => fake()->paragraph(3),
        ];
    }
}
