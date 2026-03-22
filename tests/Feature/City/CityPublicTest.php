<?php

namespace Tests\Feature\City;

use App\Models\Alert;
use App\Models\Announcement;
use App\Models\City;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_public_city_page(): void
    {
        $city = City::factory()->create();
        $staff = User::factory()->asMaire()->forCity($city)->create();

        Event::factory()->count(2)->create([
            'city_id' => $city->id,
            'created_by' => $staff->id,
            'is_published' => true,
            'starts_at' => now()->addDays(5),
        ]);

        Announcement::factory()->count(2)->create([
            'city_id' => $city->id,
            'created_by' => $staff->id,
            'published_at' => now(),
        ]);

        Alert::factory()->count(2)->create([
            'city_id' => $city->id,
            'created_by' => $staff->id,
            'is_active' => true,
            'expires_at' => now()->addDays(5),
        ]);

        $response = $this->getJson("/api/cities/{$city->uuid}");

        $response->assertOk()
            ->assertJsonStructure([
                'city' => [
                    'uuid',
                    'name',
                    'slug',
                    'description',
                    'address',
                    'postal_code',
                    'department',
                    'region',
                    'population',
                ],
                'events',
                'announcements',
                'alerts',
            ]);

        $this->assertCount(2, $response->json('events'));
        $this->assertCount(2, $response->json('announcements'));
        $this->assertCount(2, $response->json('alerts'));
    }

    public function test_returns_404_for_nonexistent_city(): void
    {
        $response = $this->getJson('/api/cities/00000000-0000-0000-0000-000000000999');

        $response->assertStatus(404);
    }

    public function test_public_city_page_does_not_show_past_events(): void
    {
        $city = City::factory()->create();
        $staff = User::factory()->asMaire()->forCity($city)->create();

        // Past event
        Event::factory()->create([
            'city_id' => $city->id,
            'created_by' => $staff->id,
            'is_published' => true,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDays(4),
        ]);

        // Future event
        Event::factory()->create([
            'city_id' => $city->id,
            'created_by' => $staff->id,
            'is_published' => true,
            'starts_at' => now()->addDays(5),
        ]);

        $response = $this->getJson("/api/cities/{$city->uuid}");

        $response->assertOk();
        $this->assertCount(1, $response->json('events'));
    }

    public function test_public_city_page_does_not_show_unpublished_events(): void
    {
        $city = City::factory()->create();
        $staff = User::factory()->asMaire()->forCity($city)->create();

        Event::factory()->unpublished()->create([
            'city_id' => $city->id,
            'created_by' => $staff->id,
            'starts_at' => now()->addDays(5),
        ]);

        $response = $this->getJson("/api/cities/{$city->uuid}");

        $response->assertOk();
        $this->assertCount(0, $response->json('events'));
    }

    public function test_public_city_page_does_not_show_inactive_alerts(): void
    {
        $city = City::factory()->create();
        $staff = User::factory()->asMaire()->forCity($city)->create();

        Alert::factory()->create([
            'city_id' => $city->id,
            'created_by' => $staff->id,
            'is_active' => false,
        ]);

        $response = $this->getJson("/api/cities/{$city->uuid}");

        $response->assertOk();
        $this->assertCount(0, $response->json('alerts'));
    }

    public function test_public_city_page_does_not_show_expired_alerts(): void
    {
        $city = City::factory()->create();
        $staff = User::factory()->asMaire()->forCity($city)->create();

        Alert::factory()->create([
            'city_id' => $city->id,
            'created_by' => $staff->id,
            'is_active' => true,
            'expires_at' => now()->subDays(1),
        ]);

        $response = $this->getJson("/api/cities/{$city->uuid}");

        $response->assertOk();
        $this->assertCount(0, $response->json('alerts'));
    }

    public function test_public_city_page_does_not_require_authentication(): void
    {
        $city = City::factory()->create();

        $response = $this->getJson("/api/cities/{$city->uuid}");

        $response->assertOk();
    }

    public function test_city_data_does_not_expose_sensitive_fields(): void
    {
        $city = City::factory()->create();

        $response = $this->getJson("/api/cities/{$city->uuid}");

        $response->assertOk();
        $cityData = $response->json('city');

        $this->assertArrayNotHasKey('id', $cityData);
        $this->assertArrayNotHasKey('stripe_subscription_id', $cityData);
        $this->assertArrayNotHasKey('mayor_id', $cityData);
    }
}
