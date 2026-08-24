<?php

namespace App\Policies;

use App\Models\OrganizationPeriod;
use App\Models\User;

class OrganizationPeriodPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin Organisasi') || $user->hasRole('Editor') || $user->hasRole('Kontributor');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrganizationPeriod $organizationPeriod): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->organization_id === $organizationPeriod->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Admin Organisasi');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrganizationPeriod $organizationPeriod): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrganizationPeriod $organizationPeriod): bool
    {
        return $user->hasRole('Super Admin');
    }
}
