<?php

namespace Tests\Unit\Models;

use App\Models\Alert;
use App\Models\Announcement;
use App\Models\City;
use App\Models\Doleance;
use App\Models\Event;
use App\Models\Intervention;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityTest extends TestCase
{
    use RefreshDatabase;

    public function test_uuid_is_auto_generated_on_creation(): void
    {
        $city = City::factory()->create();

        $this->assertNotNull($city->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $city->uuid
        );
    }

    public function test_route_key_name_is_uuid(): void
    {
        $city = City::factory()->create();

        $this->assertEquals('uuid', $city->getRouteKeyName());
    }

    public function test_city_has_many_users(): void
    {
        $city = City::factory()->create();
        User::factory()->count(3)->forCity($city)->create();

        $this->assertCount(3, $city->users);
    }

    public function test_city_has_many_doleances(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->forCity($city)->create();

        Doleance::factory()->count(2)->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
        ]);

        $this->assertCount(2, $city->doleances);
    }

    public function test_city_has_many_events(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->asMaire()->forCity($city)->create();

        Event::factory()->count(2)->create([
            'city_id' => $city->id,
            'created_by' => $user->id,
        ]);

        $this->assertCount(2, $city->events);
    }

    public function test_city_has_many_announcements(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->asMaire()->forCity($city)->create();

        Announcement::factory()->count(2)->create([
            'city_id' => $city->id,
            'created_by' => $user->id,
        ]);

        $this->assertCount(2, $city->announcements);
    }

    public function test_city_has_many_alerts(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->asMaire()->forCity($city)->create();

        Alert::factory()->count(2)->create([
            'city_id' => $city->id,
            'created_by' => $user->id,
        ]);

        $this->assertCount(2, $city->alerts);
    }

    public function test_city_has_many_interventions(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->asMaire()->forCity($city)->create();

        Intervention::factory()->count(2)->create([
            'city_id' => $city->id,
            'created_by' => $user->id,
        ]);

        $this->assertCount(2, $city->interventions);
    }

    public function test_city_has_many_vehicles(): void
    {
        $city = City::factory()->create();

        Vehicle::factory()->count(2)->create([
            'city_id' => $city->id,
        ]);

        $this->assertCount(2, $city->vehicles);
    }

    public function test_has_active_subscription(): void
    {
        $activeCity = City::factory()->create(['subscription_status' => 'active']);
        $trialCity = City::factory()->create(['subscription_status' => 'trial']);
        $inactiveCity = City::factory()->create(['subscription_status' => 'inactive']);

        $this->assertTrue($activeCity->hasActiveSubscription());
        $this->assertTrue($trialCity->hasActiveSubscription());
        $this->assertFalse($inactiveCity->hasActiveSubscription());
    }

    public function test_sensitive_fields_are_hidden(): void
    {
        $city = City::factory()->create();
        $array = $city->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('stripe_subscription_id', $array);
    }

    public function test_city_uses_soft_deletes(): void
    {
        $city = City::factory()->create();
        $city->delete();

        $this->assertSoftDeleted($city);
        $this->assertNotNull(City::withTrashed()->find($city->id));
    }
}
