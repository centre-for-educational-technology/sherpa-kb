<?php

namespace App\States\Question;

class Translated extends QuestionState
{
    public static $name = 'translated';

    public static function status(): string
    {
        return 'Translated';
    }
}
