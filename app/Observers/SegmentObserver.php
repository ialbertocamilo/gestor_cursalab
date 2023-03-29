<?php

namespace App\Observers;

use App\Models\Segment;
use App\Models\Summary;

class SegmentObserver
{
    /**
     * Handle the Segment "created" event.
     *
     * @param  \App\Models\Segment  $segment
     * @return void
     */
    public function created(Segment $segment)
    {
        if($segment->model_type=='App\\Models\\Course' && $segment->model()->first()->active){
            Summary::updateUsersByCourse($segment->model()->first(),null,false);
        }
    }

    /**
     * Handle the Segment "updated" event.
     *
     * @param  \App\Models\Segment  $segment
     * @return void
     */
    public function updated(Segment $segment)
    {
        if($segment->model_type=='App\\Models\\Course' && $segment->wasChanged('updated_at') && $segment->model()->first()->active){
            Summary::updateUsersByCourse($segment->model()->first(),null,false);
        }
        if($segment?->type?->code == 'segmentation-by-document'){

        }
    }

    /**
     * Handle the Segment "deleted" event.
     *
     * @param  \App\Models\Segment  $segment
     * @return void
     */
    public function deleted(Segment $segment)
    {
        if($segment->model_type=='App\\Models\\Course' && $segment->model()->first()->active){
            Summary::updateUsersByCourse($segment->model()->first(),null,false);
        }
    }

    /**
     * Handle the Segment "restored" event.
     *
     * @param  \App\Models\Segment  $segment
     * @return void
     */
    public function restored(Segment $segment)
    {
        //
    }

    /**
     * Handle the Segment "force deleted" event.
     *
     * @param  \App\Models\Segment  $segment
     * @return void
     */
    public function forceDeleted(Segment $segment)
    {
        //
    }
}
