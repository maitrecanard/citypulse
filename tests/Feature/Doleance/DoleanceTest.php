<?php

namespace Tests\Feature\Doleance;

use App\Models\City;
use App\Models\Doleance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoleanceTest extends TestCase
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

    // --- Administre: List ---

    public function test_administre_can_list_own_doleances(): void
    {
        Doleance::factory()->count(3)->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
        ]);

        // Another user's doleances should not appear
        $otherUser = User::factory()->asAdministre()->forCity($this->city)->create();
        Doleance::factory()->count(2)->create([
            'user_id' => $otherUser->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson('/api/doleances');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    // --- Administre: Create ---

    public function test_administre_can_create_doleance(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/doleances', [
            'title' => 'Nid de poule rue de la Paix',
            'description' => 'Un gros nid de poule est apparu devant le numero 12.',
            'category' => 'voirie',
            'priority' => 'haute',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'doleance' => ['uuid', 'title', 'description', 'category', 'priority', 'status'],
            ]);

        $this->assertDatabaseHas('doleances', [
            'title' => 'Nid de poule rue de la Paix',
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
            'status' => 'nouvelle',
        ]);
    }

    public function test_administre_can_create_doleance_with_default_priority(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/doleances', [
            'title' => 'Eclairage en panne',
            'description' => 'Le lampadaire de la place est en panne.',
            'category' => 'eclairage',
        ]);

        $response->assertStatus(201);
    }

    // --- Administre: View ---

    public function test_administre_can_view_own_doleance(): void
    {
        $doleance = Doleance::factory()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson("/api/doleances/{$doleance->uuid}");

        $response->assertOk()
            ->assertJsonStructure([
                'doleance' => ['uuid', 'title', 'description'],
            ]);
    }

    // --- Administre: Update ---

    public function test_administre_can_update_own_doleance_if_status_is_nouvelle(): void
    {
        $doleance = Doleance::factory()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
            'status' => 'nouvelle',
        ]);

        $response = $this->actingAs($this->administre)->putJson("/api/doleances/{$doleance->uuid}", [
            'title' => 'Updated title',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('doleances', [
            'id' => $doleance->id,
            'title' => 'Updated title',
        ]);
    }

    public function test_administre_cannot_update_doleance_if_status_is_not_nouvelle(): void
    {
        $doleance = Doleance::factory()->enCours()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($this->administre)->putJson("/api/doleances/{$doleance->uuid}", [
            'title' => 'Should not update',
        ]);

        $response->assertStatus(403);
    }

    // --- Administre: Delete ---

    public function test_administre_can_delete_own_doleance_if_not_consulted(): void
    {
        $doleance = Doleance::factory()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
            'consulted_at' => null,
        ]);

        $response = $this->actingAs($this->administre)->deleteJson("/api/doleances/{$doleance->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted($doleance);
    }

    public function test_administre_cannot_delete_consulted_doleance(): void
    {
        $doleance = Doleance::factory()->consulted()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($this->administre)->deleteJson("/api/doleances/{$doleance->uuid}");

        $response->assertStatus(403);
    }

    // --- Staff: List ---

    public function test_staff_can_list_city_doleances(): void
    {
        Doleance::factory()->count(3)->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
        ]);

        $otherAdmin = User::factory()->asAdministre()->forCity($this->city)->create();
        Doleance::factory()->count(2)->create([
            'user_id' => $otherAdmin->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($this->staff)->getJson('/api/doleances');

        $response->assertOk();
        // Staff sees all city doleances
        $this->assertCount(5, $response->json('data'));
    }

    // --- Staff: Update status/response ---

    public function test_staff_can_update_doleance_status_and_response(): void
    {
        $doleance = Doleance::factory()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
            'status' => 'nouvelle',
        ]);

        $response = $this->actingAs($this->staff)->putJson("/api/doleances/{$doleance->uuid}", [
            'status' => 'en_cours',
            'admin_response' => 'Nous allons traiter votre demande.',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('doleances', [
            'id' => $doleance->id,
            'status' => 'en_cours',
            'admin_response' => 'Nous allons traiter votre demande.',
        ]);
    }

    public function test_staff_can_resolve_doleance(): void
    {
        $doleance = Doleance::factory()->enCours()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($this->staff)->putJson("/api/doleances/{$doleance->uuid}", [
            'status' => 'resolue',
        ]);

        $response->assertOk();

        $doleance->refresh();
        $this->assertEquals('resolue', $doleance->status);
        $this->assertNotNull($doleance->resolved_at);
    }

    // --- Staff: Cannot create ---

    public function test_staff_cannot_create_doleance(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/doleances', [
            'title' => 'Staff should not create',
            'description' => 'This should fail.',
            'category' => 'voirie',
        ]);

        $response->assertStatus(403);
    }

    // --- Cross-user / Unauthorized access ---

    public function test_administre_cannot_view_other_users_doleance(): void
    {
        $otherUser = User::factory()->asAdministre()->forCity($this->city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $otherUser->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson("/api/doleances/{$doleance->uuid}");

        $response->assertStatus(403);
    }

    public function test_administre_cannot_update_other_users_doleance(): void
    {
        $otherUser = User::factory()->asAdministre()->forCity($this->city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $otherUser->id,
            'city_id' => $this->city->id,
            'status' => 'nouvelle',
        ]);

        $response = $this->actingAs($this->administre)->putJson("/api/doleances/{$doleance->uuid}", [
            'title' => 'Hack attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_administre_cannot_delete_other_users_doleance(): void
    {
        $otherUser = User::factory()->asAdministre()->forCity($this->city)->create();
        $doleance = Doleance::factory()->create([
            'user_id' => $otherUser->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($this->administre)->deleteJson("/api/doleances/{$doleance->uuid}");

        $response->assertStatus(403);
    }

    public function test_staff_from_different_city_cannot_view_doleance(): void
    {
        $otherCity = City::factory()->create();
        $otherStaff = User::factory()->asMaire()->forCity($otherCity)->create();

        $doleance = Doleance::factory()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($otherStaff)->getJson("/api/doleances/{$doleance->uuid}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_doleances(): void
    {
        $response = $this->getJson('/api/doleances');

        $response->assertStatus(401);
    }

    public function test_create_doleance_validates_category(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/doleances', [
            'title' => 'Test',
            'description' => 'Test description',
            'category' => 'invalid_category',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_create_doleance_requires_title(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/doleances', [
            'description' => 'Test description',
            'category' => 'voirie',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_staff_can_view_doleance_and_mark_consulted(): void
    {
        $doleance = Doleance::factory()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
            'consulted_at' => null,
        ]);

        $response = $this->actingAs($this->staff)->getJson("/api/doleances/{$doleance->uuid}");

        $response->assertOk();

        $doleance->refresh();
        $this->assertNotNull($doleance->consulted_at);
    }

    public function test_staff_cannot_delete_doleance(): void
    {
        $doleance = Doleance::factory()->create([
            'user_id' => $this->administre->id,
            'city_id' => $this->city->id,
            'consulted_at' => null,
        ]);

        $response = $this->actingAs($this->staff)->deleteJson("/api/doleances/{$doleance->uuid}");

        // Staff is not the owner, so they cannot delete
        $response->assertStatus(403);
    }
}
