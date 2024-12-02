<?php

namespace App\Listeners;

use App\Events\NotifyReceiving;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReceivingNotified
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
     * @param  NotifyReceiving  $event
     * @return void
     */
    public function handle(NotifyReceiving $event)
    {
        try {
            $e = $event;
        } catch (\Exception $th) {
            //throw $th;
        }
    }
}
