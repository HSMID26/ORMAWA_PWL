<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor', 'Kontributor']);
    }

    public function view(User $user, Activity $activity): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->organization_id === $activity->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['Admin Organisasi', 'Editor', 'Kontributor']);
    }

    public function update(User $user, Activity $activity): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->organization_id !== $activity->organization_id) {
            return false;
        }

        if ($user->hasRole(['Admin Organisasi', 'Editor'])) {
            return true;
        }

        if ($user->hasRole('Kontributor')) {
            return $user->id === $activity->user_id && $activity->status !== 'published';
        }

        return false;
    }

    public function delete(User $user, Activity $activity): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->organization_id !== $activity->organization_id) {
            return false;
        }

        if ($user->hasRole(['Admin Organisasi'])) {
            return true;
        }

        if ($user->hasRole('Kontributor')) {
            return $user->id === $activity->user_id && $activity->status === 'draft';
        }

        return false;
    }
}
