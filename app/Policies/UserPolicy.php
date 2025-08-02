<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only admin users can access the user management.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     * Admin users can view any user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     * Only admin users can create new users.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     * Admin users can update any user.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     * Admin users can delete any user EXCEPT themselves.
     */
    public function delete(User $user, User $model): bool
    {
        // Admin role required
        if (!$user->hasRole('admin')) {
            return false;
        }

        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     * Only admin users can restore users.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Admin users can force delete any user EXCEPT themselves.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Admin role required
        if (!$user->hasRole('admin')) {
            return false;
        }

        // Cannot force delete self
        if ($user->id === $model->id) {
            return false;
        }

        return true;
    }
}
