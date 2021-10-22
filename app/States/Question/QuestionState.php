<?php

namespace App\States\Question;

use Spatie\ModelStates\State;

abstract class QuestionState extends State
{
    abstract public static function status(): string;
}
