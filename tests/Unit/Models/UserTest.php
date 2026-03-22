<?php

namespace Tests\Unit\Models;

use App\Models\City;
use App\Models\Doleance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_uuid_is_auto_generated_on_creation(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $user->uuid
        );
    }

    public function test_uuid_is_not_overwritten_if_already_set(): void
    {
        $customUuid = '00000000-0000-0000-0000-000000000001';
        $user = User::factory()->create(['uuid' => $customUuid]);

        $this->assertEquals($customUuid, $user->uuid);
    }

    public function test_route_key_name_is_uuid(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('uuid', $user->getRouteKeyName());
    }

    public function test_is_administre_returns_true_for_administre_role(): void
    {
        $user = User::factory()->asAdministre()->create();

        $this->assertTrue($user->isAdministre());
        $this->assertFalse($user->isMaire());
        $this->assertFalse($user->isSecretaire());
        $this->assertFalse($user->isAgent());
        $this->assertFalse($user->isStaff());
    }

    public function test_is_maire_returns_true_for_maire_role(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->asMaire()->forCity($city)->create();

        $this->assertTrue($user->isMaire());
        $this->assertFalse($user->isAdministre());
        $this->assertTrue($user->isStaff());
    }

    public function test_is_secretaire_returns_true_for_secretaire_role(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->asSecretaire()->forCity($city)->create();

        $this->assertTrue($user->isSecretaire());
        $this->assertFalse($user->isAdministre());
        $this->assertTrue($user->isStaff());
    }

    public function test_is_agent_returns_true_for_agent_role(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->asAgent()->forCity($city)->create();

        $this->assertTrue($user->isAgent());
        $this->assertFalse($user->isAdministre());
        $this->assertTrue($user->isStaff());
    }

    public function test_is_staff_returns_true_for_all_staff_roles(): void
    {
        $city = City::factory()->create();

        $maire = User::factory()->asMaire()->forCity($city)->create();
        $secretaire = User::factory()->asSecretaire()->forCity($city)->create();
        $agent = User::factory()->asAgent()->forCity($city)->create();

        $this->assertTrue($maire->isStaff());
        $this->assertTrue($secretaire->isStaff());
        $this->assertTrue($agent->isStaff());
    }

    public function test_is_staff_returns_false_for_administre(): void
    {
        $user = User::factory()->asAdministre()->create();

        $this->assertFalse($user->isStaff());
    }

    public function test_user_belongs_to_city(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();

        $this->assertInstanceOf(City::class, $user->city);
        $this->assertEquals($city->id, $user->city->id);
    }

    public function test_user_has_many_doleances(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();

        Doleance::factory()->count(3)->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
        ]);

        $this->assertCount(3, $user->doleances);
    }

    public function test_sensitive_fields_are_hidden(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $user->password);
    }

    public function test_default_role_is_administre(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('administre', $user->role);
    }

    public function test_city_id_defaults_to_null(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->city_id);
    }
}
