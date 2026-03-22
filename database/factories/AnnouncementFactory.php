<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'priority' => 'normale',
            'city_id' => City::factory(),
            'created_by' => User::factory(),
            'published_at' => now(),
        ];
    }
}
