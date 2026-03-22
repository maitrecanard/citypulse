<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 day', '+30 days');

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'location' => fake()->address(),
            'starts_at' => $startsAt,
            'ends_at' => fake()->dateTimeBetween($startsAt, '+60 days'),
            'city_id' => City::factory(),
            'created_by' => User::factory(),
            'is_published' => true,
        ];
    }

    /**
     * Set event as unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
