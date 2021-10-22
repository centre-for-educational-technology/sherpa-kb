<?php

namespace App\States\Answer;

use Spatie\ModelStates\State;

abstract class AnswerState extends State
{
    abstract public static function status(): string;
}
