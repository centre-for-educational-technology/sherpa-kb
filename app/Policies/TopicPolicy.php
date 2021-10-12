<?php

namespace App\Policies;

use App\Topic;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TopicPolicy
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
     * @param  \App\Topic $topic
     * @return mixed
     */
    public function view(User $user, Topic $topic)
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
        return $user->isMasterExpert();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Topic $topic
     * @return mixed
     */
    public function update(User $user, Topic $topic)
    {
        return $user->isMasterExpert();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Topic $topic
     * @return mixed
     */
    public function delete(User $user, Topic $topic)
    {
        return $user->isMasterExpert();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Topic $topic
     * @return mixed
     */
    public function restore(User $user, Topic $topic)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Topic $topic
     * @return mixed
     */
    public function forceDelete(User $user, Topic $topic)
    {
        return false;
    }
}
