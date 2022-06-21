<?php

namespace App\Listeners;

use App\User;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        if ($event->user instanceof User) {
            activity('auth')
              ->performedOn($event->user)
              ->causedBy($event->user)
              ->withProperties([
                  'guard' => $event->guard,
                  'remember' => $event->remember,
              ])
              ->log('login');
        }
    }
}
