<?php

namespace Tests\Feature\Alert;

use App\Models\Alert;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertTest extends TestCase
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

    public function test_any_authenticated_user_can_list_alerts(): void
    {
        Alert::factory()->count(3)->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson('/api/alerts');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_any_authenticated_user_can_view_alert(): void
    {
        $alert = Alert::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson("/api/alerts/{$alert->uuid}");

        $response->assertOk()
            ->assertJsonStructure([
                'alert' => ['uuid', 'title', 'description', 'type', 'severity'],
            ]);
    }

    public function test_staff_can_create_alert(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/alerts', [
            'title' => 'Alerte meteo',
            'description' => 'Vigilance orange pluie-inondation.',
            'type' => 'meteo',
            'severity' => 'warning',
            'expires_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'alert' => ['uuid', 'title', 'type', 'severity'],
            ]);

        $this->assertDatabaseHas('alerts', [
            'title' => 'Alerte meteo',
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);
    }

    public function test_administre_cannot_create_alert(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/alerts', [
            'title' => 'Should not work',
            'description' => 'This should fail.',
            'type' => 'securite',
            'severity' => 'info',
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_update_alert(): void
    {
        $alert = Alert::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->putJson("/api/alerts/{$alert->uuid}", [
            'title' => 'Updated alert title',
            'severity' => 'critical',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'title' => 'Updated alert title',
            'severity' => 'critical',
        ]);
    }

    public function test_administre_cannot_update_alert(): void
    {
        $alert = Alert::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->putJson("/api/alerts/{$alert->uuid}", [
            'title' => 'Hack attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_delete_alert(): void
    {
        $alert = Alert::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->deleteJson("/api/alerts/{$alert->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted($alert);
    }

    public function test_administre_cannot_delete_alert(): void
    {
        $alert = Alert::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->deleteJson("/api/alerts/{$alert->uuid}");

        $response->assertStatus(403);
    }

    public function test_staff_from_different_city_cannot_update_alert(): void
    {
        $otherCity = City::factory()->create();
        $otherStaff = User::factory()->asMaire()->forCity($otherCity)->create();

        $alert = Alert::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($otherStaff)->putJson("/api/alerts/{$alert->uuid}", [
            'title' => 'Cross-city hack',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_alerts(): void
    {
        $response = $this->getJson('/api/alerts');

        $response->assertStatus(401);
    }

    public function test_create_alert_validates_required_fields(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/alerts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'type', 'severity']);
    }

    public function test_create_alert_validates_type_enum(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/alerts', [
            'title' => 'Test',
            'description' => 'Test',
            'type' => 'invalid_type',
            'severity' => 'info',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_create_alert_validates_severity_enum(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/alerts', [
            'title' => 'Test',
            'description' => 'Test',
            'type' => 'securite',
            'severity' => 'invalid_severity',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['severity']);
    }
}
