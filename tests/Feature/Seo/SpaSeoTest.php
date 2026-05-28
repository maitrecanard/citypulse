<?php

namespace Tests\Feature\Seo;

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_page_renders_default_meta(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<title>CityPulse - Gestion Communale</title>', false);
        $response->assertSee('og:site_name', false);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_city_page_renders_per_city_meta_tags(): void
    {
        $city = City::factory()->create([
            'name' => 'Saint-Exemple',
            'department' => 'Loire',
            'region' => 'Auvergne-Rhone-Alpes',
            'description' => 'Une commune de demonstration.',
        ]);

        $response = $this->get('/ville/' . $city->uuid);

        $response->assertOk();
        $response->assertSee('<title>Saint-Exemple - CityPulse</title>', false);
        $response->assertSee('Une commune de demonstration.', false);
        $response->assertSee('Loire', false);
        $response->assertSee('Auvergne-Rhone-Alpes', false);
        $response->assertSee('og:url', false);
        $response->assertSee('/ville/' . $city->uuid, false);
    }

    public function test_unknown_city_uuid_falls_back_to_spa_shell(): void
    {
        $response = $this->get('/ville/' . str_repeat('0', 8) . '-0000-0000-0000-' . str_repeat('0', 12));

        $response->assertOk();
        $response->assertSee('CityPulse', false);
    }

    public function test_non_uuid_segment_routes_to_default_spa(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('<div id="app">', false);
    }
}
