<?php

namespace App\Policies;

use App\PendingQuestion;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PendingQuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Allow admin to perform any actions
     *
     * @param \App\User $user
     * @param string $ability
     * @return true|void
     */
    public function before($user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isLanguageExpert() || $user->isMasterExpert();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\PendingQuestion  $pendingQuestion
     * @return mixed
     */
    public function view(User $user, PendingQuestion $pendingQuestion)
    {
        return $user->isLanguageExpert() || $user->isMasterExpert();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isLanguageExpert() || $user->isMasterExpert();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\PendingQuestion  $pendingQuestion
     * @return mixed
     */
    public function update(User $user, PendingQuestion $pendingQuestion)
    {
        return $user->isLanguageExpert() || $user->isMasterExpert();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\PendingQuestion  $pendingQuestion
     * @return mixed
     */
    public function delete(User $user, PendingQuestion $pendingQuestion)
    {
        return $user->isLanguageExpert() || $user->isMasterExpert();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\PendingQuestion  $pendingQuestion
     * @return mixed
     */
    public function restore(User $user, PendingQuestion $pendingQuestion)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\PendingQuestion  $pendingQuestion
     * @return mixed
     */
    public function forceDelete(User $user, PendingQuestion $pendingQuestion)
    {
        return false;
    }
}
