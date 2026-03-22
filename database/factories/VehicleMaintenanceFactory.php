<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleMaintenance>
 */
class VehicleMaintenanceFactory extends Factory
{
    protected $model = VehicleMaintenance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'description' => fake()->sentence(6),
            'type' => 'revision',
            'cost' => fake()->randomFloat(2, 50, 5000),
            'performed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'next_due_at' => fake()->dateTimeBetween('+30 days', '+365 days'),
            'performed_by' => fake()->optional()->name(),
        ];
    }
}
