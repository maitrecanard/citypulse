<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Services\DiscordLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing city alerts.
 */
class AlertController extends Controller
{
    use AuthorizesRequests;

    /**
     * List alerts for the user's city.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $alerts = Alert::with(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name'])
                ->where('city_id', $request->user()->city_id)
                ->latest()
                ->paginate(15);

            return response()->json($alerts);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to list alerts', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Create a new alert (staff only).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', Alert::class);

            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:5000'],
                'type' => ['required', 'in:securite,meteo,travaux,autre'],
                'severity' => ['required', 'in:info,warning,critical'],
                'is_active' => ['sometimes', 'boolean'],
                'expires_at' => ['nullable', 'date', 'after:now'],
            ]);

            $user = $request->user();

            $alert = Alert::create([
                ...$validated,
                'city_id' => $user->city_id,
                'created_by' => $user->id,
            ]);

            app(DiscordLogger::class)->info('New alert created', [
                'alert' => $alert->uuid,
                'severity' => $alert->severity,
                'title' => $alert->title,
            ]);

            return response()->json([
                'message' => 'Alerte creee avec succes.',
                'alert' => $alert->fresh()->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ], 201);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to create alert', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Show a single alert.
     */
    public function show(Request $request, Alert $alert): JsonResponse
    {
        try {
            $this->authorize('view', $alert);

            return response()->json([
                'alert' => $alert->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to show alert', [
                'error' => $e->getMessage(),
                'alert' => $alert->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Update an alert (staff only).
     */
    public function update(Request $request, Alert $alert): JsonResponse
    {
        try {
            $this->authorize('update', $alert);

            $validated = $request->validate([
                'title' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string', 'max:5000'],
                'type' => ['sometimes', 'in:securite,meteo,travaux,autre'],
                'severity' => ['sometimes', 'in:info,warning,critical'],
                'is_active' => ['sometimes', 'boolean'],
                'expires_at' => ['nullable', 'date'],
            ]);

            $alert->update($validated);

            return response()->json([
                'message' => 'Alerte mise a jour avec succes.',
                'alert' => $alert->fresh()->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to update alert', [
                'error' => $e->getMessage(),
                'alert' => $alert->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Delete an alert (staff only).
     */
    public function destroy(Request $request, Alert $alert): JsonResponse
    {
        try {
            $this->authorize('delete', $alert);

            $alert->delete();

            return response()->json([
                'message' => 'Alerte supprimee avec succes.',
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to delete alert', [
                'error' => $e->getMessage(),
                'alert' => $alert->uuid ?? null,
            ]);

            throw $e;
        }
    }
}
