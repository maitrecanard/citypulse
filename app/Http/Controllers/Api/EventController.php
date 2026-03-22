<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\DiscordLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing city events.
 */
class EventController extends Controller
{
    use AuthorizesRequests;

    /**
     * List events for the user's city.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $events = Event::with(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name'])
                ->where('city_id', $request->user()->city_id)
                ->where('is_published', true)
                ->latest('starts_at')
                ->paginate(15);

            return response()->json($events);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to list events', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Create a new event (staff only).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', Event::class);

            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:5000'],
                'location' => ['required', 'string', 'max:255'],
                'starts_at' => ['required', 'date', 'after:now'],
                'ends_at' => ['nullable', 'date', 'after:starts_at'],
                'is_published' => ['sometimes', 'boolean'],
            ]);

            $user = $request->user();

            $event = Event::create([
                ...$validated,
                'city_id' => $user->city_id,
                'created_by' => $user->id,
            ]);

            app(DiscordLogger::class)->info('New event created', [
                'event' => $event->uuid,
                'title' => $event->title,
            ]);

            return response()->json([
                'message' => 'Evenement cree avec succes.',
                'event' => $event->fresh()->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ], 201);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to create event', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Show a single event.
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            return response()->json([
                'event' => $event->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to show event', [
                'error' => $e->getMessage(),
                'event' => $event->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Update an event (staff only).
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        try {
            $this->authorize('update', $event);

            $validated = $request->validate([
                'title' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string', 'max:5000'],
                'location' => ['sometimes', 'string', 'max:255'],
                'starts_at' => ['sometimes', 'date'],
                'ends_at' => ['nullable', 'date', 'after:starts_at'],
                'is_published' => ['sometimes', 'boolean'],
            ]);

            $event->update($validated);

            return response()->json([
                'message' => 'Evenement mis a jour avec succes.',
                'event' => $event->fresh()->load(['creator:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to update event', [
                'error' => $e->getMessage(),
                'event' => $event->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Delete an event (staff only).
     */
    public function destroy(Request $request, Event $event): JsonResponse
    {
        try {
            $this->authorize('delete', $event);

            $event->delete();

            return response()->json([
                'message' => 'Evenement supprime avec succes.',
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to delete event', [
                'error' => $e->getMessage(),
                'event' => $event->uuid ?? null,
            ]);

            throw $e;
        }
    }
}
