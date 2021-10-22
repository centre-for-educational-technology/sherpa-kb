<?php

namespace App\States\Answer;

class Translated extends AnswerState
{
    public static $name = 'translated';

    public static function status(): string
    {
        return 'Translated';
    }
}
