<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $firstName . ' ' . $lastName,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'role' => 'administre',
            'city_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set user role to administre.
     */
    public function asAdministre(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'administre',
        ]);
    }

    /**
     * Set user role to maire.
     */
    public function asMaire(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'maire',
        ]);
    }

    /**
     * Set user role to secretaire.
     */
    public function asSecretaire(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'secretaire',
        ]);
    }

    /**
     * Set user role to agent.
     */
    public function asAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'agent',
        ]);
    }

    /**
     * Associate user with a city.
     */
    public function forCity(City $city): static
    {
        return $this->state(fn (array $attributes) => [
            'city_id' => $city->id,
        ]);
    }
}
