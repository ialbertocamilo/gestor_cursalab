<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\MassiveUploadTopicGradesProgressEvent;

class MassiveUploadTopicGradesProgressListener
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
    public function handle(MassiveUploadTopicGradesProgressEvent $event)
    {
        //
    }
}
