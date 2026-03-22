<?php

namespace Tests\Feature\Auth;

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Send a request with session support via Sanctum's stateful API.
     */
    private function withSessionHeaders(): static
    {
        return $this->withHeaders([
            'origin' => config('app.url'),
        ]);
    }

    public function test_user_can_register_successfully(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jean.dupont@example.com',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'name' => 'Jean Dupont',
            'role' => 'administre',
        ]);
    }

    public function test_user_can_register_with_city(): void
    {
        $city = City::factory()->create();

        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'city_uuid' => $city->uuid,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'marie.martin@example.com',
            'city_id' => $city->id,
        ]);
    }

    public function test_registration_auto_assigns_administre_role(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'jean@example.com')->first();
        $this->assertEquals('administre', $user->role);
    }

    public function test_registration_fails_without_first_name(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }

    public function test_registration_fails_without_last_name(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_name']);
    }

    public function test_registration_fails_without_email(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_without_password(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'existing@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_without_password_confirmation(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass456!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_with_optional_phone_and_address(): void
    {
        $response = $this->withSessionHeaders()->postJson('/api/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'phone' => '+33612345678',
            'address' => '1 rue de la Paix, Paris',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'jean@example.com',
            'phone' => '+33612345678',
            'address' => '1 rue de la Paix, Paris',
        ]);
    }
}
