<?php

namespace App\Policies;

use App\Models\Intervention;
use App\Models\User;

/**
 * Policy for Intervention authorization.
 */
class InterventionPolicy
{
    /**
     * Determine whether the user can view any interventions.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view the intervention.
     */
    public function view(User $user, Intervention $intervention): bool
    {
        return $user->isStaff() && $user->city_id === $intervention->city_id;
    }

    /**
     * Determine whether the user can create interventions.
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the intervention.
     */
    public function update(User $user, Intervention $intervention): bool
    {
        return $user->isStaff() && $user->city_id === $intervention->city_id;
    }

    /**
     * Determine whether the user can delete the intervention.
     */
    public function delete(User $user, Intervention $intervention): bool
    {
        return $user->isStaff() && $user->city_id === $intervention->city_id;
    }
}
