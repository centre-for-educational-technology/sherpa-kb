<?php

namespace App\States\Question;

class InTranslation extends QuestionState
{
    public static $name = 'in_translation';

    public static function status(): string
    {
        return 'inTranslation';
    }
}
