<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasRoles
    {
        roles as private traitRoles;
    }
    use LogsActivity;

    const ROLE_LANGUAGE_EXPERT = 'expert';
    const ROLE_MASTER_EXPERT = 'master';
    const ROLE_ADMINISTRATOR = 'administrator';

    protected static $logAttributes = ['*'];
    protected static $submitEmptyLogs = false;

    protected static $logAttributesToIgnore = [
        'password', 'remember_token',
    ];
    protected static $ignoreChangedAttributes = [
        'remember_token',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    // ActivityLog: Role removal is not captured as those are just being removed from the database instead
    public function roles(): BelongsToMany
    {
        return $this->traitRoles()->using(UserRole::class);
    }

    /**
     * Determines if user is a language expert.
     *
     * @return bool
     */
    public function isLanguageExpert(): bool
    {
        return $this->hasRole(self::ROLE_LANGUAGE_EXPERT);
    }

    /**
     * Determines if user is a master expert.
     *
     * @return bool
     */
    public function isMasterExpert(): bool
    {
        return $this->hasRole(self::ROLE_MASTER_EXPERT);
    }

    /**
     * Determines if user is an administrator.
     *
     * @return bool
     */
    public function isAdministrator(): bool
    {
        return $this->hasRole(self::ROLE_ADMINISTRATOR);
    }
}
