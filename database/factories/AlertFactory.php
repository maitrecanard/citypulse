<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(2),
            'type' => fake()->randomElement(['securite', 'meteo', 'travaux', 'autre']),
            'severity' => 'info',
            'is_active' => true,
            'city_id' => City::factory(),
            'created_by' => User::factory(),
            'expires_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
        ];
    }
}
