<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\User;

class LogSuccessfulLogout
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
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        if ($event->user instanceof User) {
            activity('auth')
              ->performedOn($event->user)
              ->causedBy($event->user)
              ->withProperties([
                  'guard' => $event->guard,
              ])
              ->log('logout');
        }
    }
}
