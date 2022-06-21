<?php

namespace App;

use App\States\Answer\AnswerState;
use App\States\Answer\InTranslation;
use App\States\Answer\Published;
use App\States\Answer\Translated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;

class Answer extends Model
{
    use HasFactory;
    use HasStates;
    use LogsActivity;

    protected static $logAttributes = ['*'];
    protected static $submitEmptyLogs = false;

    protected function registerStates(): void
    {
        $this
            ->addState('status', AnswerState::class)
            ->default(InTranslation::class)
            ->allowTransition(InTranslation::class, Translated::class)
            ->allowTransition(InTranslation::class, Published::class)
            ->allowTransition(Translated::class, Published::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany('App\Language')
            ->using('App\AnswerLanguage')
            ->withPivot('description')
            ->withTimestamps();
    }
}
