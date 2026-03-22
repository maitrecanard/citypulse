<?php

namespace Tests\Feature\Intervention;

use App\Models\City;
use App\Models\Intervention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterventionTest extends TestCase
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

    public function test_staff_can_list_interventions(): void
    {
        Intervention::factory()->count(3)->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->getJson('/api/interventions');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_administre_cannot_list_interventions(): void
    {
        $response = $this->actingAs($this->administre)->getJson('/api/interventions');

        $response->assertStatus(403);
    }

    public function test_staff_can_create_intervention(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/interventions', [
            'title' => 'Reparation route principale',
            'description' => 'Rebouchage des nids de poule.',
            'priority' => 'haute',
            'scheduled_at' => now()->addDays(3)->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'intervention' => ['uuid', 'title', 'status', 'priority'],
            ]);

        $this->assertDatabaseHas('interventions', [
            'title' => 'Reparation route principale',
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);
    }

    public function test_administre_cannot_create_intervention(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/interventions', [
            'title' => 'Should not work',
            'description' => 'This should fail.',
            'scheduled_at' => now()->addDays(3)->toDateTimeString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_view_intervention(): void
    {
        $intervention = Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->getJson("/api/interventions/{$intervention->uuid}");

        $response->assertOk()
            ->assertJsonStructure([
                'intervention' => ['uuid', 'title', 'description', 'status', 'priority'],
            ]);
    }

    public function test_administre_cannot_view_intervention(): void
    {
        $intervention = Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson("/api/interventions/{$intervention->uuid}");

        $response->assertStatus(403);
    }

    public function test_staff_can_update_intervention(): void
    {
        $intervention = Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->putJson("/api/interventions/{$intervention->uuid}", [
            'status' => 'en_cours',
            'title' => 'Updated intervention',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('interventions', [
            'id' => $intervention->id,
            'status' => 'en_cours',
            'title' => 'Updated intervention',
        ]);
    }

    public function test_completing_intervention_sets_completed_at(): void
    {
        $intervention = Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
            'status' => 'en_cours',
        ]);

        $response = $this->actingAs($this->staff)->putJson("/api/interventions/{$intervention->uuid}", [
            'status' => 'terminee',
        ]);

        $response->assertOk();

        $intervention->refresh();
        $this->assertEquals('terminee', $intervention->status);
        $this->assertNotNull($intervention->completed_at);
    }

    public function test_administre_cannot_update_intervention(): void
    {
        $intervention = Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->putJson("/api/interventions/{$intervention->uuid}", [
            'title' => 'Hack attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_delete_intervention(): void
    {
        $intervention = Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->deleteJson("/api/interventions/{$intervention->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted($intervention);
    }

    public function test_administre_cannot_delete_intervention(): void
    {
        $intervention = Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->deleteJson("/api/interventions/{$intervention->uuid}");

        $response->assertStatus(403);
    }

    public function test_staff_from_different_city_cannot_access_intervention(): void
    {
        $otherCity = City::factory()->create();
        $otherStaff = User::factory()->asMaire()->forCity($otherCity)->create();

        $intervention = Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($otherStaff)->getJson("/api/interventions/{$intervention->uuid}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_interventions(): void
    {
        $response = $this->getJson('/api/interventions');

        $response->assertStatus(401);
    }

    public function test_create_intervention_validates_required_fields(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/interventions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'scheduled_at']);
    }

    public function test_agent_can_create_intervention(): void
    {
        $agent = User::factory()->asAgent()->forCity($this->city)->create();

        $response = $this->actingAs($agent)->postJson('/api/interventions', [
            'title' => 'Agent intervention',
            'description' => 'Created by agent.',
            'scheduled_at' => now()->addDays(5)->toDateTimeString(),
        ]);

        $response->assertStatus(201);
    }

    public function test_secretaire_can_create_intervention(): void
    {
        $secretaire = User::factory()->asSecretaire()->forCity($this->city)->create();

        $response = $this->actingAs($secretaire)->postJson('/api/interventions', [
            'title' => 'Secretaire intervention',
            'description' => 'Created by secretaire.',
            'scheduled_at' => now()->addDays(5)->toDateTimeString(),
        ]);

        $response->assertStatus(201);
    }
}
