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
        if($topic->estado){
            Summary::updateUsersByCourse($topic->course);
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
        if ($topic->isDirty('active') || $topic->isDirty('type_evaluation_id')) {
            Summary::updateUsersByCourse($topic->course);
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
        Summary::updateUsersByCourse($topic->course);
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
