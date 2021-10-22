<?php

namespace App\States\Answer;

class Published extends AnswerState
{
    public static $name = 'published';

    public static function status(): string
    {
        return 'Published';
    }
}
