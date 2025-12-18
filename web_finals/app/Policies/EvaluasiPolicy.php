<?php

namespace App\Policies;

use App\Models\Evaluasi;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvaluasiPolicy
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
    public function view(User $user, Evaluasi $evaluasi): bool
    {
        // Admin and Dekan can view all
        if ($user->hasRole([User::ROLE_ADMIN, User::ROLE_DEKAN])) {
            return true;
        }

        // GPM can view all within their scope
        if ($user->isGPM()) {
            return true;
        }

        // GKM and Kaprodi can view their prodi's evaluations
        if ($user->hasRole([User::ROLE_GKM, User::ROLE_KAPRODI])) {
            return $user->prodi_id === $evaluasi->prodi_id;
        }

        // Creator can always view
        return $user->id === $evaluasi->created_by;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Kaprodi creates evaluations
        return $user->hasRole([User::ROLE_KAPRODI, User::ROLE_ADMIN]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Evaluasi $evaluasi): bool
    {
        // Admin can always update
        if ($user->isAdmin()) {
            return true;
        }

        // Kaprodi can update if it's their prodi and status allows
        if ($user->isKaprodi() && $user->prodi_id === $evaluasi->prodi_id) {
            return $evaluasi->canEdit();
        }

        return false;
    }

    /**
     * Determine whether the user can verify the model.
     */
    public function verify(User $user): bool
    {
        // Cek role user yang diperbolehkan
        // Role di database harus sesuai (huruf kecil/besar)
        return in_array($user->role, ['admin', 'dekan', 'GPM']);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Evaluasi $evaluasi): bool
    {
        // Dekan can approve verified evaluations
        if ($user->isDekan() && $evaluasi->canApprove()) {
            return true;
        }

        // Admin can also approve
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Evaluasi $evaluasi): bool
    {
        // Only admin can delete
        return $user->isAdmin();
    }
}
