<?php

namespace App\States\PendingQuestion;

class Pending extends PendingQuestionState
{
    public static $name = 'pending';

    public static function status(): string
    {
        return 'Pending';
    }
}
