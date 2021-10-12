<?php

namespace App\Policies;

use App\Question;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\States\Question\InTranslation;
use App\States\Question\Translated;

class QuestionPolicy
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
     * @param  \App\Question  $question
     * @return mixed
     */
    public function view(User $user, Question $question)
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
     * @param  \App\Question  $question
     * @return mixed
     */
    public function update(User $user, Question $question)
    {
        return $user->isLanguageExpert() || $user->isMasterExpert();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Question  $question
     * @return mixed
     */
    public function delete(User $user, Question $question)
    {
        if ($user->isMasterExpert()) {
            return true;
        } else if ($user->isLanguageExpert() && ($question->status->is(Translated::class) || $question->status->is(InTranslation::class))) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Question  $question
     * @return mixed
     */
    public function restore(User $user, Question $question)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Question  $question
     * @return mixed
     */
    public function forceDelete(User $user, Question $question)
    {
        return false;
    }
}
