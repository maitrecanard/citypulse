<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Intervention;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Intervention>
 */
class InterventionFactory extends Factory
{
    protected $model = Intervention::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'status' => 'planifiee',
            'priority' => 'normale',
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'city_id' => City::factory(),
            'created_by' => User::factory(),
        ];
    }
}
