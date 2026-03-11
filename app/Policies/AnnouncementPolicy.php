<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isEmployer();
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->isEmployer() && ($announcement->author_id === $user->id || $user->isAdmin());
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->isEmployer() && ($announcement->author_id === $user->id || $user->isAdmin());
    }

    public function restore(User $user, Announcement $announcement): bool
    {
        return false;
    }

    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return false;
    }
}
