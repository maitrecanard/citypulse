<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Services\DiscordLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing vehicles and their maintenance records.
 */
class VehicleController extends Controller
{
    use AuthorizesRequests;

    /**
     * List vehicles for the user's city.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Vehicle::class);

            $vehicles = Vehicle::with(['city:id,uuid,name'])
                ->where('city_id', $request->user()->city_id)
                ->latest()
                ->paginate(15);

            return response()->json($vehicles);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to list vehicles', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Create a new vehicle (maire/secretaire only).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', Vehicle::class);

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'in:voiture,camion,utilitaire,engin,autre'],
                'plate_number' => ['required', 'string', 'max:20'],
                'team' => ['nullable', 'string', 'max:255'],
                'status' => ['sometimes', 'in:disponible,en_service,maintenance,hors_service'],
                'next_maintenance_at' => ['nullable', 'date'],
            ]);

            $vehicle = Vehicle::create([
                ...$validated,
                'city_id' => $request->user()->city_id,
            ]);

            app(DiscordLogger::class)->info('New vehicle created', [
                'vehicle' => $vehicle->uuid,
                'plate' => $vehicle->plate_number,
            ]);

            return response()->json([
                'message' => 'Vehicule cree avec succes.',
                'vehicle' => $vehicle->fresh()->load(['city:id,uuid,name']),
            ], 201);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to create vehicle', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Show a single vehicle.
     */
    public function show(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $this->authorize('view', $vehicle);

            return response()->json([
                'vehicle' => $vehicle->load(['city:id,uuid,name', 'maintenances']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to show vehicle', [
                'error' => $e->getMessage(),
                'vehicle' => $vehicle->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Update a vehicle (maire/secretaire only).
     */
    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $this->authorize('update', $vehicle);

            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'type' => ['sometimes', 'in:voiture,camion,utilitaire,engin,autre'],
                'plate_number' => ['sometimes', 'string', 'max:20'],
                'team' => ['nullable', 'string', 'max:255'],
                'status' => ['sometimes', 'in:disponible,en_service,maintenance,hors_service'],
                'next_maintenance_at' => ['nullable', 'date'],
            ]);

            $vehicle->update($validated);

            return response()->json([
                'message' => 'Vehicule mis a jour avec succes.',
                'vehicle' => $vehicle->fresh()->load(['city:id,uuid,name']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to update vehicle', [
                'error' => $e->getMessage(),
                'vehicle' => $vehicle->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Delete a vehicle (maire/secretaire only).
     */
    public function destroy(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $this->authorize('delete', $vehicle);

            $vehicle->delete();

            return response()->json([
                'message' => 'Vehicule supprime avec succes.',
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to delete vehicle', [
                'error' => $e->getMessage(),
                'vehicle' => $vehicle->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * List maintenance records for a vehicle.
     */
    public function maintenances(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $this->authorize('manageMaintenance', $vehicle);

            $maintenances = $vehicle->maintenances()
                ->latest('performed_at')
                ->paginate(15);

            return response()->json($maintenances);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to list vehicle maintenances', [
                'error' => $e->getMessage(),
                'vehicle' => $vehicle->uuid ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Add a maintenance record to a vehicle.
     */
    public function addMaintenance(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $this->authorize('manageMaintenance', $vehicle);

            $validated = $request->validate([
                'description' => ['required', 'string', 'max:5000'],
                'type' => ['required', 'in:revision,reparation,controle,autre'],
                'cost' => ['nullable', 'numeric', 'min:0'],
                'performed_at' => ['required', 'date'],
                'next_due_at' => ['nullable', 'date', 'after:performed_at'],
                'performed_by' => ['nullable', 'string', 'max:255'],
            ]);

            $maintenance = $vehicle->maintenances()->create($validated);

            // Update vehicle's next maintenance date if provided
            if (isset($validated['next_due_at'])) {
                $vehicle->update(['next_maintenance_at' => $validated['next_due_at']]);
            }

            app(DiscordLogger::class)->info('Vehicle maintenance recorded', [
                'vehicle' => $vehicle->uuid,
                'maintenance' => $maintenance->uuid,
                'type' => $maintenance->type,
            ]);

            return response()->json([
                'message' => 'Maintenance enregistree avec succes.',
                'maintenance' => $maintenance,
            ], 201);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to add vehicle maintenance', [
                'error' => $e->getMessage(),
                'vehicle' => $vehicle->uuid ?? null,
            ]);

            throw $e;
        }
    }
}
