<?php

namespace App\States\PendingQuestion;

class Completed extends PendingQuestionState
{
    public static $name = 'completed';

    public static function status(): string
    {
        return 'Completed';
    }
}
