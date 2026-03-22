<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

/**
 * Policy for Announcement authorization.
 */
class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any announcements.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the announcement.
     */
    public function view(User $user, Announcement $announcement): bool
    {
        return $user->city_id === $announcement->city_id;
    }

    /**
     * Determine whether the user can create announcements.
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the announcement.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        return $user->isStaff() && $user->city_id === $announcement->city_id;
    }

    /**
     * Determine whether the user can delete the announcement.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->isStaff() && $user->city_id === $announcement->city_id;
    }
}
