<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

/**
 * Policy for Vehicle authorization.
 */
class VehiclePolicy
{
    /**
     * Determine whether the user can view any vehicles.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view the vehicle.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->isStaff() && $user->city_id === $vehicle->city_id;
    }

    /**
     * Determine whether the user can create vehicles.
     */
    public function create(User $user): bool
    {
        return $user->isMaire() || $user->isSecretaire();
    }

    /**
     * Determine whether the user can update the vehicle.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        return ($user->isMaire() || $user->isSecretaire()) && $user->city_id === $vehicle->city_id;
    }

    /**
     * Determine whether the user can delete the vehicle.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        return ($user->isMaire() || $user->isSecretaire()) && $user->city_id === $vehicle->city_id;
    }

    /**
     * Determine whether the user can manage maintenance records.
     */
    public function manageMaintenance(User $user, Vehicle $vehicle): bool
    {
        return $user->isStaff() && $user->city_id === $vehicle->city_id;
    }
}
