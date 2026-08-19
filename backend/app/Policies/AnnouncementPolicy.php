<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor', 'Kontributor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Announcement $announcement): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Tenant Isolation
        return $user->organization_id === $announcement->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Admin Organisasi', 'Editor', 'Kontributor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->organization_id !== $announcement->organization_id) {
            return false;
        }

        if ($user->hasRole(['Admin Organisasi', 'Editor'])) {
            return true;
        }

        // Kontributor hanya bisa edit miliknya sendiri, dan tidak bisa edit jika sudah published.
        if ($user->hasRole('Kontributor')) {
            return $user->id === $announcement->user_id && $announcement->status !== 'published';
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->organization_id !== $announcement->organization_id) {
            return false;
        }

        if ($user->hasRole(['Admin Organisasi'])) {
            return true;
        }

        if ($user->hasRole('Kontributor')) {
            return $user->id === $announcement->user_id && $announcement->status === 'draft';
        }

        return false;
    }
}
