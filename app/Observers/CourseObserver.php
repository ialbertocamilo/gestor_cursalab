<?php

namespace App\Observers;

use App\Models\Course;

class CourseObserver
{
    /**
     * Handle the Course "created" event.
     *
     * @param  \App\Models\Course $course
     * @return void
     */
    public function created(Course $course)
    {
        //
    }

    /**
     * Handle the Course "updated" event.
     *
     * @param  \App\Models\Course $course
     * @return void
     */
    public function updated(Course $course)
    {
        if ( $course->isDirty('active') ) {

            $action = $course->active ? 'actived' : 'inactived';

            if ($course->hasBeenSegmented()) {

                // Usuarios impactados por segmentación del curso
                $users = $course->getUsersBySegmentation();

                Summary::updateUsersDataByCourse($users, $course, $action);
                    // Actualizar resumen de usuarios (cantidades y avance)
                        // - filtrar por usuarios impactados en resumenes
                        // calcular y actualizar datos
            }



            // Ocultar si ya no le pertenece
                // SummaryCourse::where('course_id', $course_id)->update(['hidden' => !$course->active]);

        }
    }

    /**
     * Handle the Course "deleted" event.
     *
     * @param  \App\Models\Course $course
     * @return void
     */
    public function deleted(Course $course)
    {
        if ($course->hasBeenSegmented()) {

            $users = $course->getUsersBySegmentation();

            Summary::updateUsersDataByCourse($users, $course, 'deleted');
        }

        // Usuarios impactados por segmentación del curso

            // Actualizar resumen de usuarios (cantidades y avance)
                // - filtrar por usuarios impactados en resumenes
                // calcular y actualizar datos
    }

    /**
     * Handle the Course "restored" event.
     *
     * @param  \App\Models\Course $course
     * @return void
     */
    public function restored(Course $course)
    {
        //
    }

    /**
     * Handle the Course "force deleted" event.
     *
     * @param  \App\Models\Course $course
     * @return void
     */
    public function forceDeleted(Course $course)
    {
        //
    }
}
