<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

/**
 * Mobile / NativePHP-remote auth flow.
 *
 * The default web SPA keeps using the httponly Sanctum session; mobile
 * clients pass `device_name` at login and receive a Bearer token they can
 * send via Authorization: Bearer ... on every subsequent request.
 */
class MobileTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_issues_token_when_device_name_provided(): void
    {
        User::factory()->create([
            'email' => 'admin@citypulse.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@citypulse.test',
            'password' => 'password123',
            'device_name' => 'iPhone de Mathieu',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'user', 'token']);

        $token = $response->json('token');
        $this->assertIsString($token);
        $this->assertNotSame('', $token);
        $this->assertSame(1, PersonalAccessToken::count());
        $this->assertSame('iPhone de Mathieu', PersonalAccessToken::first()->name);
    }

    public function test_login_does_not_issue_token_for_web_spa_flow(): void
    {
        User::factory()->create([
            'email' => 'admin@citypulse.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@citypulse.test',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonMissingPath('token');
        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_bearer_token_authenticates_protected_endpoint(): void
    {
        $user = User::factory()->create();
        $plain = $user->createToken('Android-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $plain)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('user.uuid', $user->uuid);
    }

    public function test_logout_revokes_the_current_bearer_token(): void
    {
        $user = User::factory()->create();
        $plain = $user->createToken('Android-test')->plainTextToken;

        $this->assertSame(1, PersonalAccessToken::count());

        $response = $this->withHeader('Authorization', 'Bearer ' . $plain)
            ->postJson('/api/logout');

        $response->assertOk();
        $this->assertSame(0, PersonalAccessToken::count());
    }
}
