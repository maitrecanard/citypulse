<?php

namespace Tests\Feature\Dashboard;

use App\Models\Alert;
use App\Models\Announcement;
use App\Models\City;
use App\Models\Doleance;
use App\Models\Event;
use App\Models\Intervention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();
        $this->city = City::factory()->create();
    }

    public function test_administre_sees_own_stats(): void
    {
        $user = User::factory()->asAdministre()->forCity($this->city)->create();
        $staff = User::factory()->asMaire()->forCity($this->city)->create();

        // Create the user's doleances
        Doleance::factory()->count(2)->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id,
            'status' => 'nouvelle',
        ]);
        Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id,
            'status' => 'en_cours',
        ]);
        Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id,
            'status' => 'resolue',
            'resolved_at' => now(),
        ]);

        // Create other user's doleances (should not appear in personal stats)
        $otherUser = User::factory()->asAdministre()->forCity($this->city)->create();
        Doleance::factory()->count(3)->create([
            'user_id' => $otherUser->id,
            'city_id' => $this->city->id,
        ]);

        // Create city events and announcements
        Event::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $staff->id,
            'starts_at' => now()->addDays(5),
            'is_published' => true,
        ]);

        Alert::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $staff->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'stats' => [
                    'mes_doleances',
                    'mes_doleances_en_cours',
                    'mes_doleances_resolues',
                    'evenements_a_venir',
                    'annonces_recentes',
                    'alertes_actives',
                ],
            ]);

        $stats = $response->json('stats');
        $this->assertEquals(4, $stats['mes_doleances']);
        $this->assertEquals(1, $stats['mes_doleances_en_cours']);
        $this->assertEquals(1, $stats['mes_doleances_resolues']);
        $this->assertEquals(1, $stats['evenements_a_venir']);
        $this->assertEquals(1, $stats['alertes_actives']);
    }

    public function test_staff_sees_city_stats(): void
    {
        $staff = User::factory()->asMaire()->forCity($this->city)->create();
        $user = User::factory()->asAdministre()->forCity($this->city)->create();

        // Create city-wide data
        Doleance::factory()->count(2)->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id,
            'status' => 'nouvelle',
        ]);
        Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id,
            'status' => 'en_cours',
        ]);
        Doleance::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id,
            'status' => 'resolue',
            'resolved_at' => now(),
        ]);

        Event::factory()->count(2)->create([
            'city_id' => $this->city->id,
            'created_by' => $staff->id,
        ]);

        Announcement::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $staff->id,
        ]);

        Alert::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $staff->id,
            'is_active' => true,
        ]);

        Intervention::factory()->count(2)->create([
            'city_id' => $this->city->id,
            'created_by' => $staff->id,
            'status' => 'planifiee',
        ]);

        Intervention::factory()->create([
            'city_id' => $this->city->id,
            'created_by' => $staff->id,
            'status' => 'en_cours',
        ]);

        $response = $this->actingAs($staff)->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'stats' => [
                    'doleances_total',
                    'doleances_nouvelles',
                    'doleances_en_cours',
                    'doleances_resolues',
                    'evenements_total',
                    'evenements_a_venir',
                    'annonces_total',
                    'alertes_actives',
                    'interventions_total',
                    'interventions_planifiees',
                    'interventions_en_cours',
                ],
            ]);

        $stats = $response->json('stats');
        $this->assertEquals(4, $stats['doleances_total']);
        $this->assertEquals(2, $stats['doleances_nouvelles']);
        $this->assertEquals(1, $stats['doleances_en_cours']);
        $this->assertEquals(1, $stats['doleances_resolues']);
        $this->assertEquals(2, $stats['evenements_total']);
        $this->assertEquals(1, $stats['annonces_total']);
        $this->assertEquals(1, $stats['alertes_actives']);
        $this->assertEquals(3, $stats['interventions_total']);
        $this->assertEquals(2, $stats['interventions_planifiees']);
        $this->assertEquals(1, $stats['interventions_en_cours']);
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }

    public function test_staff_does_not_see_other_city_data(): void
    {
        $otherCity = City::factory()->create();
        $staff = User::factory()->asMaire()->forCity($this->city)->create();
        $otherUser = User::factory()->asAdministre()->forCity($otherCity)->create();
        $otherStaff = User::factory()->asMaire()->forCity($otherCity)->create();

        // Create data for another city
        Doleance::factory()->count(5)->create([
            'user_id' => $otherUser->id,
            'city_id' => $otherCity->id,
        ]);

        Event::factory()->count(3)->create([
            'city_id' => $otherCity->id,
            'created_by' => $otherStaff->id,
        ]);

        $response = $this->actingAs($staff)->getJson('/api/dashboard');

        $response->assertOk();
        $stats = $response->json('stats');
        $this->assertEquals(0, $stats['doleances_total']);
        $this->assertEquals(0, $stats['evenements_total']);
    }
}
