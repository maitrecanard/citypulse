<?php

namespace Tests\Feature\Event;

use App\Models\City;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private City $city;
    private User $administre;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::factory()->create();
        $this->administre = User::factory()->asAdministre()->forCity($this->city)->create();
        $this->staff = User::factory()->asMaire()->forCity($this->city)->create();
    }

    public function test_any_authenticated_user_can_list_events(): void
    {
        Event::factory()->count(3)->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->administre)->getJson('/api/events');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_any_authenticated_user_can_view_event(): void
    {
        $event = Event::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson("/api/events/{$event->uuid}");

        $response->assertOk()
            ->assertJsonStructure([
                'event' => ['uuid', 'title', 'description', 'location', 'starts_at'],
            ]);
    }

    public function test_staff_can_create_event(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/events', [
            'title' => 'Fete de la musique',
            'description' => 'Concert gratuit sur la place.',
            'location' => 'Place de la Mairie',
            'starts_at' => now()->addDays(10)->toDateTimeString(),
            'ends_at' => now()->addDays(10)->addHours(4)->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'event' => ['uuid', 'title'],
            ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Fete de la musique',
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);
    }

    public function test_administre_cannot_create_event(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/events', [
            'title' => 'Should not work',
            'description' => 'This should fail.',
            'location' => 'Somewhere',
            'starts_at' => now()->addDays(10)->toDateTimeString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_update_event(): void
    {
        $event = Event::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->putJson("/api/events/{$event->uuid}", [
            'title' => 'Updated title',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated title',
        ]);
    }

    public function test_administre_cannot_update_event(): void
    {
        $event = Event::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->putJson("/api/events/{$event->uuid}", [
            'title' => 'Hack attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_delete_event(): void
    {
        $event = Event::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->deleteJson("/api/events/{$event->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted($event);
    }

    public function test_administre_cannot_delete_event(): void
    {
        $event = Event::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->deleteJson("/api/events/{$event->uuid}");

        $response->assertStatus(403);
    }

    public function test_staff_from_different_city_cannot_update_event(): void
    {
        $otherCity = City::factory()->create();
        $otherStaff = User::factory()->asMaire()->forCity($otherCity)->create();

        $event = Event::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($otherStaff)->putJson("/api/events/{$event->uuid}", [
            'title' => 'Cross-city hack',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_events(): void
    {
        $response = $this->getJson('/api/events');

        $response->assertStatus(401);
    }

    public function test_create_event_validates_required_fields(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/events', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'location', 'starts_at']);
    }

    public function test_agent_can_create_event(): void
    {
        $agent = User::factory()->asAgent()->forCity($this->city)->create();

        $response = $this->actingAs($agent)->postJson('/api/events', [
            'title' => 'Agent event',
            'description' => 'Event created by agent.',
            'location' => 'Place Centrale',
            'starts_at' => now()->addDays(5)->toDateTimeString(),
        ]);

        $response->assertStatus(201);
    }
}
