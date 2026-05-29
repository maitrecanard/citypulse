<?php

namespace Tests\Feature\Security;

use App\Models\City;
use App\Models\Doleance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks down a race between update() and delete() on consulted doleances.
 *
 * The README guarantees an administre may modify OR delete their doleance
 * only while it has not yet been consulted by the administration. Both
 * actions must therefore honour the *same* "is consulted" signal.
 */
class DoleanceConsultationLockTest extends TestCase
{
    use RefreshDatabase;

    private function makeFixtures(): array
    {
        $city = City::factory()->create();
        $staff = User::factory()->asMaire()->forCity($city)->create();
        $alice = User::factory()->asAdministre()->forCity($city)->create();

        $doleance = Doleance::create([
            'user_id' => $alice->id,
            'city_id' => $city->id,
            'title' => 'Test',
            'description' => 'x',
            'category' => 'voirie',
            'status' => 'nouvelle',
        ]);

        return compact('city', 'staff', 'alice', 'doleance');
    }

    public function test_owner_cannot_delete_after_status_bumped_even_without_consulted_at(): void
    {
        ['staff' => $staff, 'alice' => $alice, 'doleance' => $doleance] = $this->makeFixtures();

        // Staff bumps status without ever GETting the doleance first, so
        // consulted_at COULD legitimately stay null. The fix forces it.
        $this->actingAs($staff)
            ->putJson('/api/doleances/' . $doleance->uuid, ['status' => 'en_cours'])
            ->assertOk();

        $doleance->refresh();
        $this->assertTrue($doleance->isConsulted());
        $this->assertNotNull($doleance->consulted_at, 'controller must keep consulted_at in sync with status bumps');

        $this->actingAs($alice)
            ->deleteJson('/api/doleances/' . $doleance->uuid)
            ->assertForbidden();
    }

    public function test_owner_cannot_update_after_status_bumped(): void
    {
        ['staff' => $staff, 'alice' => $alice, 'doleance' => $doleance] = $this->makeFixtures();

        $this->actingAs($staff)
            ->putJson('/api/doleances/' . $doleance->uuid, ['status' => 'en_cours'])
            ->assertOk();

        $this->actingAs($alice)
            ->putJson('/api/doleances/' . $doleance->uuid, ['title' => 'too late'])
            ->assertForbidden();
    }

    public function test_owner_can_still_update_and_delete_before_staff_touches_it(): void
    {
        ['alice' => $alice, 'doleance' => $doleance] = $this->makeFixtures();

        $this->actingAs($alice)
            ->putJson('/api/doleances/' . $doleance->uuid, ['title' => 'edited'])
            ->assertOk();

        $this->actingAs($alice)
            ->deleteJson('/api/doleances/' . $doleance->uuid)
            ->assertOk();
    }

    public function test_isConsulted_reflects_both_signals(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->asAdministre()->forCity($city)->create();

        $fresh = Doleance::create([
            'user_id' => $user->id, 'city_id' => $city->id,
            'title' => 'a', 'description' => 'b', 'category' => 'voirie', 'status' => 'nouvelle',
        ]);
        $this->assertFalse($fresh->isConsulted());

        $viewedOnly = Doleance::create([
            'user_id' => $user->id, 'city_id' => $city->id,
            'title' => 'a', 'description' => 'b', 'category' => 'voirie',
            'status' => 'nouvelle', 'consulted_at' => now(),
        ]);
        $this->assertTrue($viewedOnly->isConsulted());

        $statusBumpedOnly = Doleance::create([
            'user_id' => $user->id, 'city_id' => $city->id,
            'title' => 'a', 'description' => 'b', 'category' => 'voirie',
            'status' => 'en_cours',
        ]);
        $this->assertTrue($statusBumpedOnly->isConsulted());
    }
}
