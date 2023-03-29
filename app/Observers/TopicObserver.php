<?php

namespace App\Observers;

use App\Models\Topic;
use App\Models\Summary;

class TopicObserver
{
    /**
     * Handle the Topic "created" event.
     *
     * @param  \App\Models\Topic  $topic
     * @return void
     */
    public function created(Topic $topic)
    {
        if($topic->active){
            Summary::updateUsersByCourse($topic->course,null,true,true);
        }
    }

    /**
     * Handle the Topic "updated" event.
     *x
     * @param  \App\Models\Topic  $topic
     * @return void
     */
    public function updated(Topic $topic)
    {
        if ($topic->wasChanged('active') || $topic->wasChanged('type_evaluation_id')) {
            Summary::updateUsersByCourse($topic->course,null,true,true);
        }
    }

    /**
     * Handle the Topic "deleted" event.
     *
     * @param  \App\Models\Topic  $topic
     * @return void
     */
    public function deleted(Topic $topic)
    {
        Summary::updateUsersByCourse($topic->course,null,true,true);
    }

    /**
     * Handle the Topic "restored" event.
     *
     * @param  \App\Models\Topic  $topic
     * @return void
     */
    public function restored(Topic $topic)
    {
        //
    }

    /**
     * Handle the Topic "force deleted" event.
     *
     * @param  \App\Models\Topic  $topic
     * @return void
     */
    public function forceDeleted(Topic $topic)
    {
        //
    }
}
