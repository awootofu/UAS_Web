<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can verify the submission.
     */
    public function verify(User $user, Submission $submission): bool
    {
        // Dekan and GPM can verify submissions
        return $user->hasRole([User::ROLE_DEKAN, User::ROLE_GPM]);
    }
}