<?php

namespace Tests\Unit\Models;

use App\Models\City;
use App\Models\Doleance;
use App\Models\Intervention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoleanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_uuid_is_auto_generated_on_creation(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
        ]);

        $this->assertNotNull($doleance->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $doleance->uuid
        );
    }

    public function test_fillable_attributes(): void
    {
        $doleance = new Doleance();
        $expected = [
            'title',
            'description',
            'category',
            'priority',
            'status',
            'admin_response',
            'user_id',
            'city_id',
            'consulted_at',
            'resolved_at',
        ];

        $this->assertEquals($expected, $doleance->getFillable());
    }

    public function test_hidden_fields(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
        ]);

        $array = $doleance->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('user_id', $array);
        $this->assertArrayNotHasKey('city_id', $array);
    }

    public function test_casts_consulted_at_as_datetime(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
            'consulted_at' => '2025-06-15 10:30:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $doleance->consulted_at);
    }

    public function test_casts_resolved_at_as_datetime(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
            'resolved_at' => '2025-06-15 10:30:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $doleance->resolved_at);
    }

    public function test_belongs_to_user(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
        ]);

        $this->assertInstanceOf(User::class, $doleance->user);
        $this->assertEquals($user->id, $doleance->user->id);
    }

    public function test_belongs_to_city(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
        ]);

        $this->assertInstanceOf(City::class, $doleance->city);
        $this->assertEquals($city->id, $doleance->city->id);
    }

    public function test_has_one_intervention(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();
        $staff = User::factory()->asMaire()->forCity($city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
        ]);

        $intervention = Intervention::factory()->create([
            'doleance_id' => $doleance->id,
            'city_id' => $city->id,
            'created_by' => $staff->id,
        ]);

        $this->assertInstanceOf(Intervention::class, $doleance->intervention);
        $this->assertEquals($intervention->id, $doleance->intervention->id);
    }

    public function test_uses_soft_deletes(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
        ]);

        $doleance->delete();

        $this->assertSoftDeleted($doleance);
    }

    public function test_route_key_name_is_uuid(): void
    {
        $doleance = new Doleance();

        $this->assertEquals('uuid', $doleance->getRouteKeyName());
    }
}
