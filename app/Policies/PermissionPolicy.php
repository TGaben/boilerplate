<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only admin users can access permission management.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     * Admin users can view any permission.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     * Initially disabled for security - permissions should be managed via code/seeders.
     */
    public function create(User $user): bool
    {
        return false; // Disabled for security - permissions managed via seeders
    }

    /**
     * Determine whether the user can update the model.
     * Initially disabled for security - permissions should be managed via code/seeders.
     */
    public function update(User $user, Permission $permission): bool
    {
        return false; // Disabled for security - permissions managed via seeders
    }

    /**
     * Determine whether the user can delete the model.
     * Initially disabled for security - permissions should be managed via code/seeders.
     */
    public function delete(User $user, Permission $permission): bool
    {
        return false; // Disabled for security - permissions managed via seeders
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permission $permission): bool
    {
        return false; // Not applicable for permissions
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return false; // Not applicable for permissions
    }
}
