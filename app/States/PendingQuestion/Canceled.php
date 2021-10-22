<?php

namespace App\States\PendingQuestion;

class Canceled extends PendingQuestionState
{
    public static $name = 'canceled';

    public static function status(): string
    {
        return 'Canceled';
    }
}
