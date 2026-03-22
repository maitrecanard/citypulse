<?php

namespace Tests\Feature\Announcement;

use App\Models\Announcement;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
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

    public function test_any_authenticated_user_can_list_announcements(): void
    {
        Announcement::factory()->count(3)->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson('/api/announcements');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_any_authenticated_user_can_view_announcement(): void
    {
        $announcement = Announcement::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->getJson("/api/announcements/{$announcement->uuid}");

        $response->assertOk()
            ->assertJsonStructure([
                'announcement' => ['uuid', 'title', 'content', 'priority'],
            ]);
    }

    public function test_staff_can_create_announcement(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/announcements', [
            'title' => 'Coupure d\'eau prevue',
            'content' => 'Une coupure d\'eau est prevue le 15 mars de 8h a 12h.',
            'priority' => 'importante',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'announcement' => ['uuid', 'title'],
            ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'Coupure d\'eau prevue',
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);
    }

    public function test_administre_cannot_create_announcement(): void
    {
        $response = $this->actingAs($this->administre)->postJson('/api/announcements', [
            'title' => 'Should not work',
            'content' => 'This should fail.',
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_update_announcement(): void
    {
        $announcement = Announcement::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->putJson("/api/announcements/{$announcement->uuid}", [
            'title' => 'Updated announcement title',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Updated announcement title',
        ]);
    }

    public function test_administre_cannot_update_announcement(): void
    {
        $announcement = Announcement::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->putJson("/api/announcements/{$announcement->uuid}", [
            'title' => 'Hack attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_delete_announcement(): void
    {
        $announcement = Announcement::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->deleteJson("/api/announcements/{$announcement->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted($announcement);
    }

    public function test_administre_cannot_delete_announcement(): void
    {
        $announcement = Announcement::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->administre)->deleteJson("/api/announcements/{$announcement->uuid}");

        $response->assertStatus(403);
    }

    public function test_staff_from_different_city_cannot_update_announcement(): void
    {
        $otherCity = City::factory()->create();
        $otherStaff = User::factory()->asMaire()->forCity($otherCity)->create();

        $announcement = Announcement::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($otherStaff)->putJson("/api/announcements/{$announcement->uuid}", [
            'title' => 'Cross-city hack',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_announcements(): void
    {
        $response = $this->getJson('/api/announcements');

        $response->assertStatus(401);
    }

    public function test_create_announcement_validates_required_fields(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/api/announcements', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }
}
