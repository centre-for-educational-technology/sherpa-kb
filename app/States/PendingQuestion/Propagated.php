<?php

namespace App\States\PendingQuestion;

class Propagated extends PendingQuestionState
{
    public static $name = 'propagated';

    public static function status(): string
    {
        return 'Propagated';
    }
}
