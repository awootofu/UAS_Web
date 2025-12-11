<?php

namespace App\Policies;

use App\Models\Renstra;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RenstraPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view renstra list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Renstra $renstra): bool
    {
        // All authenticated users can view renstra
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only BPAP and Admin can create renstra items
        return $user->hasRole([User::ROLE_BPAP, User::ROLE_ADMIN]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Renstra $renstra): bool
    {
        // Only BPAP, Admin, or the creator can update
        if ($user->hasRole([User::ROLE_BPAP, User::ROLE_ADMIN])) {
            return true;
        }
        
        return $user->id === $renstra->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Renstra $renstra): bool
    {
        // Only BPAP and Admin can delete
        return $user->hasRole([User::ROLE_BPAP, User::ROLE_ADMIN]);
    }
}
