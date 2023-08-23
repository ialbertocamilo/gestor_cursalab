<?php

namespace App\Listeners;

use Altek\Accountant\Events\Recorded;

class RecordedListener
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
     * @param  \App\Events\Altek\Accountant\Events\Recorded  $event
     * @return void
     */
    public function handle(Recorded $event)
    {
        // info(['Ledger-recorded', $event]);
    }
}
