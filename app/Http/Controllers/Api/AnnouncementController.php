<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\DiscordLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing city announcements.
 */
class AnnouncementController extends Controller
{
    use AuthorizesRequests;

    /**
     * List announcements for the user's city.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $announcements = Announcement::with(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name'])
                ->where('city_id', $request->user()->city_id)
                ->latest()
                ->paginate(15);

            return response()->json($announcements);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to list announcements', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Create a new announcement (staff only).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', Announcement::class);

            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string', 'max:10000'],
                'priority' => ['sometimes', 'in:normale,importante,urgente'],
                'published_at' => ['nullable', 'date'],
            ]);

            $user = $request->user();

            $announcement = Announcement::create([
                ...$validated,
                'city_id' => $user->city_id,
                'created_by' => $user->id,
                'published_at' => $validated['published_at'] ?? now(),
            ]);

            app(DiscordLogger::class)->info('New announcement created', [
                'announcement' => $announcement->uuid,
                'title' => $announcement->title,
            ]);

            return response()->json([
                'message' => 'Annonce creee avec succes.',
                'announcement' => $announcement->fresh()->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ], 201);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to create announcement', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Show a single announcement.
     */
    public function show(Request $request, Announcement $announcement): JsonResponse
    {
        try {
            $this->authorize('view', $announcement);

            return response()->json([
                'announcement' => $announcement->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to show announcement', [
                'error' => $e->getMessage(),
                'announcement' => $announcement->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Update an announcement (staff only).
     */
    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        try {
            $this->authorize('update', $announcement);

            $validated = $request->validate([
                'title' => ['sometimes', 'string', 'max:255'],
                'content' => ['sometimes', 'string', 'max:10000'],
                'priority' => ['sometimes', 'in:normale,importante,urgente'],
                'published_at' => ['nullable', 'date'],
            ]);

            $announcement->update($validated);

            return response()->json([
                'message' => 'Annonce mise a jour avec succes.',
                'announcement' => $announcement->fresh()->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to update announcement', [
                'error' => $e->getMessage(),
                'announcement' => $announcement->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Delete an announcement (staff only).
     */
    public function destroy(Request $request, Announcement $announcement): JsonResponse
    {
        try {
            $this->authorize('delete', $announcement);

            $announcement->delete();

            return response()->json([
                'message' => 'Annonce supprimee avec succes.',
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to delete announcement', [
                'error' => $e->getMessage(),
                'announcement' => $announcement->uuid ?? null,
            ]);

            throw $e;
        }
    }
}
