<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Spatie\Activitylog\Traits\LogsActivity;

final class UserRole extends MorphPivot
{
    use LogsActivity;

    protected static $logAttributes = ['*'];
    protected static $submitEmptyLogs = false;
}
