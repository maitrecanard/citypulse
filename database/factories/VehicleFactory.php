<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' ' . fake()->randomNumber(3),
            'type' => 'voiture',
            'plate_number' => strtoupper(fake()->bothify('??-###-??')),
            'team' => fake()->optional()->word(),
            'status' => 'disponible',
            'city_id' => City::factory(),
        ];
    }
}
