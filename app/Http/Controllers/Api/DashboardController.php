<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Announcement;
use App\Models\Doleance;
use App\Models\Event;
use App\Models\Intervention;
use App\Services\DiscordLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for dashboard statistics.
 */
class DashboardController extends Controller
{
    /**
     * Return dashboard statistics based on the user's role.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cityId = $user->city_id;

            if ($user->isAdministre()) {
                return response()->json([
                    'stats' => [
                        'mes_doleances' => Doleance::where('user_id', $user->id)->count(),
                        'mes_doleances_en_cours' => Doleance::where('user_id', $user->id)
                            ->where('status', 'en_cours')->count(),
                        'mes_doleances_resolues' => Doleance::where('user_id', $user->id)
                            ->where('status', 'resolue')->count(),
                        'evenements_a_venir' => Event::where('city_id', $cityId)
                            ->where('starts_at', '>=', now())
                            ->where('is_published', true)->count(),
                        'annonces_recentes' => Announcement::where('city_id', $cityId)
                            ->whereNotNull('published_at')->count(),
                        'alertes_actives' => Alert::where('city_id', $cityId)
                            ->where('is_active', true)->count(),
                    ],
                ]);
            }

            // Staff view - full city statistics
            return response()->json([
                'stats' => [
                    'doleances_total' => Doleance::where('city_id', $cityId)->count(),
                    'doleances_nouvelles' => Doleance::where('city_id', $cityId)
                        ->where('status', 'nouvelle')->count(),
                    'doleances_en_cours' => Doleance::where('city_id', $cityId)
                        ->where('status', 'en_cours')->count(),
                    'doleances_resolues' => Doleance::where('city_id', $cityId)
                        ->where('status', 'resolue')->count(),
                    'evenements_total' => Event::where('city_id', $cityId)->count(),
                    'evenements_a_venir' => Event::where('city_id', $cityId)
                        ->where('starts_at', '>=', now())->count(),
                    'annonces_total' => Announcement::where('city_id', $cityId)->count(),
                    'alertes_actives' => Alert::where('city_id', $cityId)
                        ->where('is_active', true)->count(),
                    'interventions_total' => Intervention::where('city_id', $cityId)->count(),
                    'interventions_planifiees' => Intervention::where('city_id', $cityId)
                        ->where('status', 'planifiee')->count(),
                    'interventions_en_cours' => Intervention::where('city_id', $cityId)
                        ->where('status', 'en_cours')->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Dashboard stats failed', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }
}
