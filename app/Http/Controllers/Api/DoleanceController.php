<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doleance;
use App\Services\DiscordLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing doleances (grievances).
 */
class DoleanceController extends Controller
{
    use AuthorizesRequests;

    /**
     * List doleances.
     * Staff sees all city doleances, administre sees only their own.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = Doleance::with(['user:id,uuid,first_name,last_name', 'city:id,uuid,name']);

            if ($user->isAdministre()) {
                $query->where('user_id', $user->id);
            } else {
                $query->where('city_id', $user->city_id);
            }

            $doleances = $query->latest()->paginate(15);

            return response()->json($doleances);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to list doleances', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Create a new doleance (administre only).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', Doleance::class);

            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:5000'],
                'category' => ['required', 'in:voirie,eclairage,proprete,bruit,securite,autre'],
                'priority' => ['sometimes', 'in:basse,normale,haute,urgente'],
            ]);

            $user = $request->user();

            $doleance = Doleance::create([
                ...$validated,
                'user_id' => $user->id,
                'city_id' => $user->city_id,
                'status' => 'nouvelle',
            ]);

            app(DiscordLogger::class)->info('New doleance created', [
                'doleance' => $doleance->uuid,
                'user' => $user->uuid,
                'category' => $doleance->category,
            ]);

            return response()->json([
                'message' => 'Doleance creee avec succes.',
                'doleance' => $doleance->fresh()->load(['user:id,uuid,first_name,last_name', 'city:id,uuid,name']),
            ], 201);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to create doleance', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Show a single doleance.
     */
    public function show(Request $request, Doleance $doleance): JsonResponse
    {
        try {
            $this->authorize('view', $doleance);

            // Mark as consulted if staff is viewing for the first time
            if ($request->user()->isStaff() && is_null($doleance->consulted_at)) {
                $doleance->update(['consulted_at' => now()]);
            }

            return response()->json([
                'doleance' => $doleance->load([
                    'user:id,uuid,first_name,last_name',
                    'city:id,uuid,name',
                    'intervention',
                ]),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to show doleance', [
                'error' => $e->getMessage(),
                'doleance' => $doleance->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Update a doleance.
     * Owner can update if status is nouvelle.
     * Staff can update status, response, priority.
     */
    public function update(Request $request, Doleance $doleance): JsonResponse
    {
        try {
            $this->authorize('update', $doleance);

            $user = $request->user();

            if ($user->isStaff()) {
                $validated = $request->validate([
                    'status' => ['sometimes', 'in:nouvelle,en_cours,resolue,rejetee'],
                    'admin_response' => ['sometimes', 'nullable', 'string', 'max:5000'],
                    'priority' => ['sometimes', 'in:basse,normale,haute,urgente'],
                ]);

                if (isset($validated['status']) && $validated['status'] === 'resolue') {
                    $validated['resolved_at'] = now();
                }
            } else {
                $validated = $request->validate([
                    'title' => ['sometimes', 'string', 'max:255'],
                    'description' => ['sometimes', 'string', 'max:5000'],
                    'category' => ['sometimes', 'in:voirie,eclairage,proprete,bruit,securite,autre'],
                    'priority' => ['sometimes', 'in:basse,normale,haute,urgente'],
                ]);
            }

            $doleance->update($validated);

            return response()->json([
                'message' => 'Doleance mise a jour avec succes.',
                'doleance' => $doleance->fresh()->load([
                    'user:id,uuid,first_name,last_name',
                    'city:id,uuid,name',
                ]),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to update doleance', [
                'error' => $e->getMessage(),
                'doleance' => $doleance->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Delete a doleance (owner only, if not consulted).
     */
    public function destroy(Request $request, Doleance $doleance): JsonResponse
    {
        try {
            $this->authorize('delete', $doleance);

            $doleance->delete();

            return response()->json([
                'message' => 'Doleance supprimee avec succes.',
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to delete doleance', [
                'error' => $e->getMessage(),
                'doleance' => $doleance->uuid ?? null,
            ]);

            throw $e;
        }
    }
}
