<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Services\DiscordLogger;
use Illuminate\Http\JsonResponse;

/**
 * Controller for public city information.
 */
class CityPublicController extends Controller
{
    /**
     * Show public information about a city, including recent events, announcements, and alerts.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $city = City::where('uuid', $uuid)->firstOrFail();

            $events = $city->events()
                ->where('is_published', true)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(5)
                ->get(['uuid', 'title', 'description', 'location', 'starts_at', 'ends_at']);

            $announcements = $city->announcements()
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->take(5)
                ->get(['uuid', 'title', 'content', 'priority', 'published_at']);

            $alerts = $city->alerts()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->latest()
                ->take(5)
                ->get(['uuid', 'title', 'description', 'type', 'severity', 'expires_at']);

            $cityData = $city->only(['uuid', 'name', 'slug', 'description', 'address', 'postal_code', 'department', 'region', 'population']);

            return response()->json(array_merge(
                $cityData,
                [
                    'city' => $cityData,
                    'events' => $events,
                    'announcements' => $announcements,
                    'alerts' => $alerts,
                ]
            ));
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to show public city info', [
                'error' => $e->getMessage(),
                'uuid' => $uuid,
            ]);

            throw $e;
        }
    }
}
