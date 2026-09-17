<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Redirect;

class RedirectSessionExpired
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
        return Redirect::to('/login');
    }
}
