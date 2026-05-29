<?php

namespace App\Policies;

use App\Models\Doleance;
use App\Models\User;

/**
 * Policy for Doleance authorization.
 */
class DoleancePolicy
{
    /**
     * Determine whether the user can view any doleances.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the doleance.
     */
    public function view(User $user, Doleance $doleance): bool
    {
        // Owner can view their own doleance
        if ($user->id === $doleance->user_id) {
            return true;
        }

        // Staff can view doleances from their city
        return $user->isStaff() && $user->city_id === $doleance->city_id;
    }

    /**
     * Determine whether the user can create doleances.
     */
    public function create(User $user): bool
    {
        return $user->isAdministre();
    }

    /**
     * Determine whether the user can update the doleance.
     */
    public function update(User $user, Doleance $doleance): bool
    {
        // Owner can update only while the doleance has not been consulted.
        if ($user->id === $doleance->user_id) {
            return !$doleance->isConsulted();
        }

        // Staff can update doleances from their city (status, response, etc.)
        return $user->isStaff() && $user->city_id === $doleance->city_id;
    }

    /**
     * Determine whether the user can delete the doleance.
     */
    public function delete(User $user, Doleance $doleance): bool
    {
        // Only the owner, and only while not yet consulted by the administration.
        return $user->id === $doleance->user_id && !$doleance->isConsulted();
    }
}
