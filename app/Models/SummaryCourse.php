<?php

namespace App\Models;

class SummaryCourse extends Summary
{
    protected $table = 'summary_courses';

    protected $fillable = [
        'last_time_evaluated_at', 'user_id', 'course_id', 'assigneds', 'attempts', 'views'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    public static function resetMasiveAttempts($coursesIds, $userId)
    {
        self::whereIn('course_id', $coursesIds)
            ->where('user_id', $userId)
            ->update([
                'attempts' => 0,
                //'fuente' => 'resetm'
            ]);
    }

    /**
     * Update courses restarts count
     *
     * @param $courseId
     * @param $userId
     * @param $adminId
     * @return void
     */
    public static function updateCourseRestartsCount(
        $courseId, $userId, $adminId
    ): void
    {

        $summaryCourse = SummaryCourse::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();

        // Calculate number of restars

        $restars = $summaryCourse->restarts
            ? $summaryCourse->restarts + 1
            : 1;

        // Update record

        $summaryCourse->restarts =  $restars;
        $summaryCourse->restarter_id = $adminId;
        $summaryCourse->save();
    }

}
