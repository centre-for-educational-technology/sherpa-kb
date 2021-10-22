<?php

namespace App\Providers;

use App\Answer;
use App\PendingQuestion;
use App\Policies\AnswerPolicy;
use App\Policies\PendingQuestionPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\TopicPolicy;
use App\Question;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        PendingQuestion::class => PendingQuestionPolicy::class,
        Question::class => QuestionPolicy::class,
        Answer::class => AnswerPolicy::class,
        Topic::class => TopicPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
