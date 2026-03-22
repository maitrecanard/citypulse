<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Services\DiscordLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing interventions (staff only).
 */
class InterventionController extends Controller
{
    use AuthorizesRequests;

    /**
     * List interventions for the user's city.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Intervention::class);

            $interventions = Intervention::with([
                    'assignee:id,uuid,first_name,last_name',
                    'vehicle:id,uuid,name,plate_number',
                    'creator:id,uuid,first_name,last_name',
                    'doleance:id,uuid,title',
                    'city:id,uuid,name',
                ])
                ->where('city_id', $request->user()->city_id)
                ->latest('scheduled_at')
                ->paginate(15);

            return response()->json($interventions);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to list interventions', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Create a new intervention (staff only).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', Intervention::class);

            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:5000'],
                'status' => ['sometimes', 'in:planifiee,en_cours,terminee,annulee'],
                'priority' => ['sometimes', 'in:basse,normale,haute,urgente'],
                'scheduled_at' => ['required', 'date'],
                'assigned_to' => ['nullable', 'exists:users,id'],
                'vehicle_id' => ['nullable', 'exists:vehicles,id'],
                'doleance_id' => ['nullable', 'exists:doleances,id'],
            ]);

            $user = $request->user();

            $intervention = Intervention::create([
                ...$validated,
                'city_id' => $user->city_id,
                'created_by' => $user->id,
            ]);

            app(DiscordLogger::class)->info('New intervention created', [
                'intervention' => $intervention->uuid,
                'title' => $intervention->title,
            ]);

            return response()->json([
                'message' => 'Intervention creee avec succes.',
                'intervention' => $intervention->fresh()->load([
                    'assignee:id,uuid,first_name,last_name',
                    'vehicle:id,uuid,name,plate_number',
                    'creator:id,uuid,first_name,last_name',
                    'city:id,uuid,name',
                ]),
            ], 201);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to create intervention', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Show a single intervention.
     */
    public function show(Request $request, Intervention $intervention): JsonResponse
    {
        try {
            $this->authorize('view', $intervention);

            return response()->json([
                'intervention' => $intervention->load([
                    'assignee:id,uuid,first_name,last_name',
                    'vehicle:id,uuid,name,plate_number',
                    'creator:id,uuid,first_name,last_name',
                    'doleance:id,uuid,title',
                    'city:id,uuid,name',
                ]),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to show intervention', [
                'error' => $e->getMessage(),
                'intervention' => $intervention->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Update an intervention (staff only).
     */
    public function update(Request $request, Intervention $intervention): JsonResponse
    {
        try {
            $this->authorize('update', $intervention);

            $validated = $request->validate([
                'title' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string', 'max:5000'],
                'status' => ['sometimes', 'in:planifiee,en_cours,terminee,annulee'],
                'priority' => ['sometimes', 'in:basse,normale,haute,urgente'],
                'scheduled_at' => ['sometimes', 'date'],
                'completed_at' => ['nullable', 'date'],
                'assigned_to' => ['nullable', 'exists:users,id'],
                'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            ]);

            if (isset($validated['status']) && $validated['status'] === 'terminee' && !isset($validated['completed_at'])) {
                $validated['completed_at'] = now();
            }

            $intervention->update($validated);

            return response()->json([
                'message' => 'Intervention mise a jour avec succes.',
                'intervention' => $intervention->fresh()->load([
                    'assignee:id,uuid,first_name,last_name',
                    'vehicle:id,uuid,name,plate_number',
                    'creator:id,uuid,first_name,last_name',
                    'city:id,uuid,name',
                ]),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to update intervention', [
                'error' => $e->getMessage(),
                'intervention' => $intervention->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Delete an intervention (staff only).
     */
    public function destroy(Request $request, Intervention $intervention): JsonResponse
    {
        try {
            $this->authorize('delete', $intervention);

            $intervention->delete();

            return response()->json([
                'message' => 'Intervention supprimee avec succes.',
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to delete intervention', [
                'error' => $e->getMessage(),
                'intervention' => $intervention->uuid ?? null,
            ]);

            throw $e;
        }
    }
}
