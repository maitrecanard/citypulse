<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

/**
 * Policy for Alert authorization.
 */
class AlertPolicy
{
    /**
     * Determine whether the user can view any alerts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the alert.
     */
    public function view(User $user, Alert $alert): bool
    {
        return $user->city_id === $alert->city_id;
    }

    /**
     * Determine whether the user can create alerts.
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the alert.
     */
    public function update(User $user, Alert $alert): bool
    {
        return $user->isStaff() && $user->city_id === $alert->city_id;
    }

    /**
     * Determine whether the user can delete the alert.
     */
    public function delete(User $user, Alert $alert): bool
    {
        return $user->isStaff() && $user->city_id === $alert->city_id;
    }
}
