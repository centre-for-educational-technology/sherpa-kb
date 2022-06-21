<?php

namespace App\States\Answer;

class InTranslation extends AnswerState
{
    public static $name = 'in_translation';

    public static function status(): string
    {
        return 'inTranslation';
    }
}
