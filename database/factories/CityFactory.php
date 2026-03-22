<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->optional()->paragraph(),
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'department' => fake()->state(),
            'region' => fake()->state(),
            'population' => fake()->numberBetween(500, 100000),
            'subscription_status' => 'active',
        ];
    }
}
