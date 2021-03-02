<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

final class PendingQuestionLanguage extends Pivot
{
    use LogsActivity;

    protected static $logAttributes = ['*'];
    protected static $submitEmptyLogs = false;
}