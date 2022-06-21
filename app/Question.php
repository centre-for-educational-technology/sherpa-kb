<?php

namespace App;

use App\States\Question\InTranslation;
use App\States\Question\Published;
use App\States\Question\QuestionState;
use App\States\Question\Translated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;

class Question extends Model
{
    use HasFactory;
    use HasStates;
    use LogsActivity;

    protected static $logAttributes = ['*'];
    protected static $submitEmptyLogs = false;

    protected function registerStates(): void
    {
        $this
            ->addState('status', QuestionState::class)
            ->default(InTranslation::class)
            ->allowTransition(InTranslation::class, Translated::class)
            ->allowTransition(InTranslation::class, Published::class)
            ->allowTransition(Translated::class, Published::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany('App\Language')
            ->using('App\QuestionLanguage')
            ->withPivot('description')
            ->withTimestamps();
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Topic');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo('App\Answer');
    }

    public function pendingQuestion(): BelongsTo
    {
        return $this->belongsTo('App\PendingQuestion');
    }
}
