<?php

namespace Tests\Feature\Profile;

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_own_profile(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonStructure([
                'user' => [
                    'uuid',
                    'first_name',
                    'last_name',
                    'email',
                    'role',
                    'city',
                ],
            ]);
    }

    public function test_can_update_own_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'first_name' => 'Pierre',
            'last_name' => 'Martin',
            'phone' => '+33612345678',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Profil mis a jour avec succes.',
            ]);

        $user->refresh();
        $this->assertEquals('Pierre', $user->first_name);
        $this->assertEquals('Martin', $user->last_name);
        $this->assertEquals('Pierre Martin', $user->name);
        $this->assertEquals('+33612345678', $user->phone);
    }

    public function test_can_update_email(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'email' => 'new@example.com',
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertEquals('new@example.com', $user->email);
    }

    public function test_cannot_update_email_to_existing_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Mot de passe mis a jour avec succes.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword456!', $user->password));
    }

    public function test_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'WrongPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_password_change_requires_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/profile', [
            'first_name' => 'Hacker',
        ]);

        $response->assertStatus(401);
    }

    public function test_can_update_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'address' => '42 rue de la Liberte, 75001 Paris',
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertEquals('42 rue de la Liberte, 75001 Paris', $user->address);
    }
}
