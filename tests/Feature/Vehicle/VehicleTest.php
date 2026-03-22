<?php

namespace Tests\Feature\Vehicle;

use App\Models\City;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    private City $city;
    private User $administre;
    private User $maire;
    private User $secretaire;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::factory()->create();
        $this->administre = User::factory()->asAdministre()->forCity($this->city)->create();
        $this->maire = User::factory()->asMaire()->forCity($this->city)->create();
        $this->secretaire = User::factory()->asSecretaire()->forCity($this->city)->create();
        $this->agent = User::factory()->asAgent()->forCity($this->city)->create();
    }

    // --- List / View ---

    public function test_staff_can_list_vehicles(): void
    {
        Vehicle::factory()->count(3)->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->agent)->getJson('/api/vehicles');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_administre_cannot_list_vehicles(): void
    {
        $response = $this->actingAs($this->administre)->getJson('/api/vehicles');

        $response->assertStatus(403);
    }

    public function test_staff_can_view_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->agent)->getJson("/api/vehicles/{$vehicle->uuid}");

        $response->assertOk()
            ->assertJsonStructure([
                'vehicle' => ['uuid', 'name', 'type', 'plate_number', 'status'],
            ]);
    }

    public function test_administre_cannot_view_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->administre)->getJson("/api/vehicles/{$vehicle->uuid}");

        $response->assertStatus(403);
    }

    // --- Create ---

    public function test_maire_can_create_vehicle(): void
    {
        $response = $this->actingAs($this->maire)->postJson('/api/vehicles', [
            'name' => 'Camion Benne 01',
            'type' => 'camion',
            'plate_number' => 'AB-123-CD',
            'team' => 'voirie',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'vehicle' => ['uuid', 'name', 'type', 'plate_number'],
            ]);

        $this->assertDatabaseHas('vehicles', [
            'name' => 'Camion Benne 01',
            'city_id' => $this->city->id,
        ]);
    }

    public function test_secretaire_can_create_vehicle(): void
    {
        $response = $this->actingAs($this->secretaire)->postJson('/api/vehicles', [
            'name' => 'Utilitaire 02',
            'type' => 'utilitaire',
            'plate_number' => 'EF-456-GH',
        ]);

        $response->assertStatus(201);
    }

    public function test_agent_cannot_create_vehicle(): void
    {
        $response = $this->actingAs($this->agent)->postJson('/api/vehicles', [
            'name' => 'Should not work',
            'type' => 'voiture',
            'plate_number' => 'XX-000-XX',
        ]);

        $response->assertStatus(403);
    }

    public function test_administre_cannot_create_vehicle(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/vehicles', [
            'name' => 'Should not work',
            'type' => 'voiture',
            'plate_number' => 'XX-000-XX',
        ]);

        $response->assertStatus(403);
    }

    // --- Update ---

    public function test_maire_can_update_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->maire)->putJson("/api/vehicles/{$vehicle->uuid}", [
            'name' => 'Updated name',
            'status' => 'en_service',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'name' => 'Updated name',
            'status' => 'en_service',
        ]);
    }

    public function test_secretaire_can_update_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->secretaire)->putJson("/api/vehicles/{$vehicle->uuid}", [
            'name' => 'Secretaire update',
        ]);

        $response->assertOk();
    }

    public function test_agent_cannot_update_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->agent)->putJson("/api/vehicles/{$vehicle->uuid}", [
            'name' => 'Should not work',
        ]);

        $response->assertStatus(403);
    }

    // --- Delete ---

    public function test_maire_can_delete_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->maire)->deleteJson("/api/vehicles/{$vehicle->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted($vehicle);
    }

    public function test_secretaire_can_delete_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->secretaire)->deleteJson("/api/vehicles/{$vehicle->uuid}");

        $response->assertOk();
    }

    public function test_agent_cannot_delete_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->agent)->deleteJson("/api/vehicles/{$vehicle->uuid}");

        $response->assertStatus(403);
    }

    // --- Cross-city ---

    public function test_staff_from_different_city_cannot_access_vehicle(): void
    {
        $otherCity = City::factory()->create();
        $otherMaire = User::factory()->asMaire()->forCity($otherCity)->create();

        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($otherMaire)->getJson("/api/vehicles/{$vehicle->uuid}");

        $response->assertStatus(403);
    }

    // --- Maintenance ---

    public function test_staff_can_list_vehicle_maintenances(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);
        VehicleMaintenance::factory()->count(3)->create(['vehicle_id' => $vehicle->id]);

        $response = $this->actingAs($this->agent)->getJson("/api/vehicles/{$vehicle->uuid}/maintenances");

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_staff_can_add_maintenance(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->agent)->postJson("/api/vehicles/{$vehicle->uuid}/maintenances", [
            'description' => 'Revision annuelle',
            'type' => 'revision',
            'cost' => 350.00,
            'performed_at' => now()->toDateString(),
            'next_due_at' => now()->addYear()->toDateString(),
            'performed_by' => 'Garage Martin',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'maintenance' => ['uuid', 'description', 'type', 'cost'],
            ]);
    }

    public function test_administre_cannot_manage_maintenance(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->administre)->getJson("/api/vehicles/{$vehicle->uuid}/maintenances");

        $response->assertStatus(403);
    }

    public function test_adding_maintenance_with_next_due_updates_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['city_id' => $this->city->id]);
        $nextDue = now()->addMonths(6)->toDateString();

        $this->actingAs($this->agent)->postJson("/api/vehicles/{$vehicle->uuid}/maintenances", [
            'description' => 'Controle technique',
            'type' => 'controle',
            'performed_at' => now()->toDateString(),
            'next_due_at' => $nextDue,
        ]);

        $vehicle->refresh();
        $this->assertEquals($nextDue, $vehicle->next_maintenance_at->toDateString());
    }

    // --- Validation ---

    public function test_create_vehicle_validates_required_fields(): void
    {
        $response = $this->actingAs($this->maire)->postJson('/api/vehicles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'plate_number']);
    }

    public function test_create_vehicle_validates_type_enum(): void
    {
        $response = $this->actingAs($this->maire)->postJson('/api/vehicles', [
            'name' => 'Test',
            'type' => 'invalid_type',
            'plate_number' => 'XX-000-XX',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_unauthenticated_user_cannot_access_vehicles(): void
    {
        $response = $this->getJson('/api/vehicles');

        $response->assertStatus(401);
    }
}
