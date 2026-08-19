<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin Organisasi', 'Editor', 'Kontributor']);
    }

    public function view(User $user, Post $post): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->organization_id === $post->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['Admin Organisasi', 'Editor', 'Kontributor']);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->organization_id !== $post->organization_id) {
            return false;
        }

        if ($user->hasRole(['Admin Organisasi', 'Editor'])) {
            return true;
        }

        if ($user->hasRole('Kontributor')) {
            return $user->id === $post->user_id && $post->status !== 'published';
        }

        return false;
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->organization_id !== $post->organization_id) {
            return false;
        }

        if ($user->hasRole(['Admin Organisasi'])) {
            return true;
        }

        if ($user->hasRole('Kontributor')) {
            return $user->id === $post->user_id && $post->status === 'draft';
        }

        return false;
    }
}
