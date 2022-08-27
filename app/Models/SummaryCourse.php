<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;

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

    public static function resetUserCoursesAttempts($userId, $coursesIds)
    {
        self::whereIn('course_id', $coursesIds)
            ->where('user_id', $userId)
            ->update([
                'attempts' => 0,
                //'fuente' => 'resetm'
            ]);
    }

    /**
     * Reset attemtps of failed courses (desaprobados)
     * @param $courseId
     * @param $attemptsLimit
     * @param $scheduleDate
     * @return void
     */
    public static function resetFailedCourseAttemptsAllUsers(
        $courseId, $attemptsLimit, $scheduleDate = null
    ): void
    {

       $query = self::where('course_id', $courseId)
                    ->where('passed', 0)
                    ->where('attempts', '>=', $attemptsLimit);

       if ($scheduleDate)
           $query->where('last_time_evaluated_at', '<=', $scheduleDate);

        $query->update([
                'attempts' => 0,
                'last_time_evaluated_at' => Carbon::now()
                //'fuente' => 'resetm'
            ]);
    }

    /**
     * Reset topics attempts
     *
     * @param $courseId
     * @param $userId
     * @return Collection
     */
    public static function getCourseTopicsIds($courseId, $userId = null): Collection
    {

        $query = SummaryTopic::query()
            ->join('topics', 'topics.id', 'summary_topics.topic_id')
            ->join('courses', 'courses.id', 'topics.course_id')
            ->where('courses.id', $courseId);

        if ($userId) {
            $query->where('summary_topics.user_id', $userId);
        }

        return $query->pluck('summary_topics.topic_id');
    }

    /**
     * Reset topics attempts for all users
     *
     * @param $courseId
     * @param $attemptsLimit
     * @param null $scheduleDate
     * @return void
     */
    public static function resetCourseTopicsAttemptsAllUsers(
        $courseId, $attemptsLimit, $scheduleDate = null
    ): void
    {

        $topicsIds = SummaryTopic::query()
            ->join('topics', 'topics.id', 'summary_topics.topic_id')
            ->join('courses', 'courses.id', 'topics.course_id')
            ->where('courses.id', $courseId)
            ->pluck('summary_topics.topic_id');

        SummaryTopic::resetFailedTopicsAttemptsAllUsers(
            $topicsIds, $attemptsLimit, $scheduleDate
        );
    }

    /**
     * Update courses restarts count
     *
     * @param $courseId
     * @param $adminId
     * @param $userId
     * @return void
     */
    public static function updateCourseRestartsCount(
        $courseId, $adminId = null, $userId = null
    ): void
    {

        $query = SummaryCourse::where('course_id', $courseId);
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $summaryCourse = $query->first();

        if (!$summaryCourse) return;

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
