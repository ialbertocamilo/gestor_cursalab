<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class SummaryCourse extends Summary
{
    protected $table = 'summary_courses';

    protected $fillable = [
        'last_time_evaluated_at', 'user_id', 'course_id', 'status_id', 'assigned', 'attempts',
        'views', 'advanced_percentage', 'grade_average', 'passed', 'taken', 'reviewed', 'failed',
        'completed', 'restarts', 'restarter_id', 'certification_issued_at',
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

        $summaryCourse->restarts = $restars;
        $summaryCourse->restarter_id = $adminId;
        $summaryCourse->save();
    }


    protected function updateUserData($course, $user = null)
    {
        $user = $user ?? auth()->user();
        $row_course = SummaryCourse::getCurrentRow($course, $user);

        $active_topics = $course->topics->where('active', ACTIVE);

        $topics_for_review = $active_topics->where('assessable', '<>', 1)->pluck('id');
        $topics_qualified = $active_topics->where('evaluation_type.code', 'qualified')->pluck('id');
        $topics_open = $active_topics->where('evaluation_type.code', 'open')->pluck('id');

        $passed = $taken = $reviewed = $failed = 0;

        $max_attempts = $user->getSubworkspaceSetting('mod_evaluaciones', 'nro_intentos');

        $rows = SummaryTopic::with('status')
            ->whereIn('topic_id', $active_topics->pluck('id'))
            ->where('user_id', $user->id)
            ->get();

        if (count($topics_qualified) > 0) {

            $passed = $rows->where('passed', 1)
                ->whereIn('topic_id', $topics_qualified)
                ->count();

            $failed = $rows->where('passed', '<>', 1)
                ->whereIn('topic_id', $topics_qualified)
                ->where('attempts', '>=', $max_attempts)
                ->count();
        }

        if (count($topics_open) > 0)
            $taken = $rows->whereIn('topic_id', $topics_open)->count();

        if (count($topics_for_review) > 0)
            $reviewed = $rows->whereIn('topic_id', $topics_for_review)->count();
        // ->where('visitas.estado_tema', "revisado")

        $q_completed = $passed + $taken + $reviewed;

        // Porcentaje avance por curso
        $assigned = count($active_topics);
//        info($course->name);
//        info("PASSED :: ". $passed);
//        info("TAKEN :: ". $taken);
//        info("REVISADOS :: ". $reviewed);
//        info("ASSIGNED ". $assigned);
//        info("q_completed ". $q_completed);
        $advanced_percentage = ($assigned > 0) ? (($q_completed / $assigned) * 100) : 0;
        $advanced_percentage = ($advanced_percentage > 100) ? 100 : $advanced_percentage; // Maximo porcentaje = 100

        $grade_average = $rows->whereIn('topic_id', $topics_qualified->pluck('id'))->average('grade');
        $grade_average = round($grade_average ?? 0, 2);

        $course_data = compact('assigned', 'passed', 'taken', 'reviewed', 'failed', 'grade_average', 'advanced_percentage');
        $course_data['last_time_evaluated_at'] = now();

        $status = 'desarrollo';

        if ($q_completed >= $assigned) {
            info("1");

            $poll = $course->polls()->first();

            if ($poll) {
                info("2");
                info("USER ID :: ". $user->id); info("CURSO ID :: ". $course->id);
                $poll_answers = PollQuestionAnswer::where('user_id', $user->id)->where('course_id', $course->id)->first();
                info($poll_answers);
                $status = 'enc_pend';

                if ($poll_answers) {
                    info("3");


                    $status = 'aprobado';
                    $course_data['certification_issued_at'] = now();
                }

            } else {

                $status = 'aprobado';
                $course_data['certification_issued_at'] = now();
            }

        } else {

            if ($failed >= $assigned)
                $status = 'desaprobado';
        }

        $course_data['status_id'] = Taxonomy::getFirstData('course', 'user-status', $status)->id;
        $course_data['attempts'] = $row_course->attempts + 1;

        $row_course->update($course_data);

        info($row_course);

        return $row_course;
    }
}
