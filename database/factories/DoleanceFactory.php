<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Doleance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doleance>
 */
class DoleanceFactory extends Factory
{
    protected $model = Doleance::class;

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
            'category' => fake()->randomElement(['voirie', 'eclairage', 'proprete', 'bruit', 'securite', 'autre']),
            'priority' => 'normale',
            'status' => 'nouvelle',
            'user_id' => User::factory(),
            'city_id' => City::factory(),
        ];
    }

    /**
     * Set status to en_cours.
     */
    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'en_cours',
        ]);
    }

    /**
     * Set status to resolue.
     */
    public function resolue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolue',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark as consulted.
     */
    public function consulted(): static
    {
        return $this->state(fn (array $attributes) => [
            'consulted_at' => now(),
        ]);
    }
}
