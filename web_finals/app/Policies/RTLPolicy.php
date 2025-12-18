<?php

namespace App\Policies;

use App\Models\RTL;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RTLPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RTL $rtl): bool
    {
        // Admin, Dekan, GPM can view all
        if ($user->hasRole([User::ROLE_ADMIN, User::ROLE_DEKAN, User::ROLE_GPM])) {
            return true;
        }

        // GKM and Kaprodi can view their prodi's RTLs
        if ($user->hasRole([User::ROLE_GKM, User::ROLE_KAPRODI])) {
            return $user->prodi_id === $rtl->prodi_id;
        }

        return $user->id === $rtl->users_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // GKM creates RTLs
        return $user->hasRole([User::ROLE_GKM, User::ROLE_ADMIN]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RTL $rtl): bool
    {
        // Admin can always update
        if ($user->isAdmin()) {
            return true;
        }

        // GKM can update if it's their prodi and status allows
        if ($user->isGKM() && $user->prodi_id === $rtl->prodi_id) {
            return $rtl->canEdit();
        }

        return false;
    }

    /**
     * Determine whether the user can verify the model.
     */
    public function verify(User $user, RTL $rtl): bool
    {
        // GPM and Dekan can verify RTLs
        if ($user->hasRole([User::ROLE_GPM, User::ROLE_DEKAN])) {
            return true;
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can complete the model.
     */
    public function complete(User $user, RTL $rtl): bool
    {
        // GKM can mark as complete
        if ($user->isGKM() && $user->prodi_id === $rtl->prodi_id) {
            return $rtl->canComplete();
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RTL $rtl): bool
    {
        // Only admin can delete
        return $user->isAdmin();
    }
}
