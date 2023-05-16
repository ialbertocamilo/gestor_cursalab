<?php

namespace App\Listeners;

use Altek\Accountant\Events\Recording;

class RecordingListener
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
     * @param  \App\Events\Altek\Accountant\Events\Recording  $event
     * @return void
     */
    public function handle(Recording $event)
    {
        // info(['Ledger-recording', $event]);
    }
}
