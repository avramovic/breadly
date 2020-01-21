<?php

namespace App\Listeners;

use App\Notifications\SendActivationEmail;
use App\Notifications\SendWelcomeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class BreadUserRegisteredListener implements ShouldQueue
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
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (!$event->user->is_verified) {
            $event->user->notify(new SendActivationEmail($event->user));
        } else {
            $event->user->notify(new SendWelcomeEmail($event->user));
        }
    }
}
