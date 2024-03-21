<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SummaryCourse extends Summary
{
    protected $table = 'summary_courses';

    protected $fillable = [
        'last_time_evaluated_at', 'user_id', 'course_id', 'status_id', 'assigned', 'attempts',
        'views', 'advanced_percentage', 'grade_average', 'passed', 'taken', 'reviewed', 'failed',
        'old_admin_id',
        'completed', 'restarts', 'restarter_id', 'certification_issued_at', 'certification_accepted_at','dc3_path', 'registro_capacitacion_path'
    ];

    protected $casts = [
        'certification_issued_at' => 'datetime',
        'certification_accepted_at' => 'datetime',
    ];

    // Relationships
    public $defaultRelationships = [
        'user_id' => 'user',
        'course_id' => 'course',
        'status_id' => 'status',
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

        // Get "Desaprobado" status from taxonomies

        $desaprobado = Taxonomy::getFirstData('course', 'user-status', 'desaprobado');


        $query = self::where('course_id', $courseId)
            ->where('status_id', $desaprobado->id)
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
            ->groupBy('topic_id')
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
            ->groupBy('topic_id')
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

        if (!$summaryCourse) {
            info("CURSO {$courseId} de USER {$userId} SIN SUMMARYCOURSE");
            return;
        }

        // Calculate number of restars

        $restars = $summaryCourse->restarts
            ? $summaryCourse->restarts + 1
            : 1;

        // Update record

        $summaryCourse->restarts = $restars;
        $summaryCourse->restarter_id = $adminId;
        $summaryCourse->save();
    }


    protected function updateUserData($course, $user = null, $update_attempts = true,$update_certification_data=true,$notSaveData=false,$certification_issued_at=null)
    {
        $user = $user ?? auth()->user();
        $row_course = SummaryCourse::getCurrentRow($course, $user);
        if(!$row_course){
            return true;
        }
        $active_topics = $course->topics->where('active', ACTIVE);

        $topics_for_review = $active_topics->where('assessable', '<>', 1)->pluck('id');
        $topics_qualified = $active_topics->where('evaluation_type.code', 'qualified')->pluck('id');
        $topics_open = $active_topics->where('evaluation_type.code', 'open')->pluck('id');

        $passed = $taken = $reviewed = $failed = 0;

        // $max_attempts = $user->getSubworkspaceSetting('mod_evaluaciones', 'nro_intentos');
        $max_attempts = Course::getModEval($course->id,'nro_intentos');
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

        $grade_average = $rows->whereIn('topic_id', $topics_qualified)->average('grade');
        $grade_average = round($grade_average ?? 0, 2);
//        info($topics_qualified->toArray());
//        info("COUNT TOPICS QUALIFIED :: ". $topics_qualified->count());
//        info("GRADE AVERGAE UPDATED :: " . $grade_average);

        $course_data = compact('assigned', 'passed', 'taken', 'reviewed', 'failed', 'grade_average', 'advanced_percentage');

        $status = 'desarrollo';

        if ($q_completed >= $assigned) {
//            info("1");

            $poll = $course->polls()->first();

            if ($poll) {
//                info("2");
//                info("USER ID :: ". $user->id); info("CURSO ID :: ". $course->id);
                $poll_answers = PollQuestionAnswer::where('user_id', $user->id)->where('course_id', $course->id)->count();
//                info("COUNT POLL ANSWERS :: ".$poll_answers);
                $status = 'enc_pend';

                if ($poll_answers > 0) {
//                    info("3");
                    $status = 'aprobado';
                    if($update_certification_data){
                        $course_data['certification_issued_at'] = $certification_issued_at ? $certification_issued_at : now();

                        // If user confirmation is not necessary,
                        // accepts certificate automatically

                        if (!$course->user_confirms_certificate) {
                            $course_data['certification_accepted_at'] = $certification_issued_at ? $certification_issued_at : now();
                        }
                    }
                }

            } else {

                $status = 'aprobado';
                if($update_certification_data){
                    $course_data['certification_issued_at'] =$certification_issued_at ? $certification_issued_at : now();

                    // If user confirmation is not necessary,
                    // accepts certificate automatically

                    if (!$course->user_confirms_certificate) {
                        $course_data['certification_accepted_at'] = $certification_issued_at ? $certification_issued_at : now();
                    }
                }
            }

        } else {
            // if ($failed >= $assigned)
            if((count($topics_qualified)>0) && ($failed >= count($topics_qualified))){
                $status = 'desaprobado';
            }
        }

//        info("UPDATE TO ". $status);
        $course_data['status_id'] = Taxonomy::getFirstData('course', 'user-status', $status)->id;
        if ($update_attempts)
            $course_data['last_time_evaluated_at'] = now();
            $course_data['attempts'] = $row_course->attempts + 1;
        if($notSaveData){
            $course_data['id'] = $row_course->id;
            return $course_data;
        }
//        info($row_course->status_id);
        $row_course->update($course_data);
//        info($row_course->status_id);

        // info($row_course);

        return $row_course;
    }

    /**
     * Calculate totals from summary coourse statuses,
     * including only users from supervisor
     *
     * @param $supervisorId
     * @param $workspaceId
     * @return array
     */
    public static function calculateSupervisorCoursesTotals($supervisorId, $workspaceId): array
    {
        // Load users ids which matches criterion values

        $usersIds = Segment::loadSupervisorSegmentUsersIds($supervisorId, $workspaceId);

        if (count($usersIds) === 0) return [];

        // Courses statuses from database

        $aprobado = Taxonomy::getFirstData('course', 'user-status', 'aprobado');
        $desarrollo = Taxonomy::getFirstData('course', 'user-status', 'desarrollo');
        $desaprobado = Taxonomy::getFirstData('course', 'user-status', 'desaprobado');
        $encuestaPend = Taxonomy::getFirstData('course', 'user-status', 'enc_pend');

        // Calculate courses totals

        $courseTotals = [];
        if (count($usersIds)) {
            $_usersIds = implode(',', $usersIds);
            $courseTotals = DB::select(DB::raw("
                select
                    sum(if(sc.status_id = :aprobadoId, 1, 0)) aprobados,
                    sum(if(sc.status_id = :desarrolloId, 1, 0)) desarrollados,
                    sum(if(sc.status_id = :desaprobadoId, 1, 0)) desaprobados,
                    sum(if(sc.status_id = :encuestaPendId, 1, 0)) encuestaPend

                from
                    users u
                        inner join summary_courses sc on sc.user_id = u.id
                where
                    sc.deleted_at is null and
                    u.id in ($_usersIds)
            "), [
                    'aprobadoId' => $aprobado->id,
                    'desarrolloId' => $desarrollo->id,
                    'desaprobadoId' => $desaprobado->id,
                    'encuestaPendId' => $encuestaPend->id
                ]);
        }

        // Count active users

        $activeUsers = User::query()
            ->where('active', 1)
            ->whereIn('id', $usersIds)
            ->count();

        return [
            'courses' => $courseTotals,
            'users' => $activeUsers
        ];
    }

    protected function getUserAverageGradeByCourses($user, $courses_id)
    {
        $average = SummaryCourse::query()
            ->whereRelation('course.type', 'code', '<>', 'free')
            ->whereIn('course_id', $courses_id)
            ->where('user_id', $user->id)
            ->avg('grade_average');

        return $average;
    }

    protected function getUserTotalAttemptsByCourses($user, $courses_id)
    {
        $sum = SummaryCourse::query()
            // ->whereRelation('course', 'active', ACTIVE)
            ->whereRelation('course.type', 'code', '<>', 'free')
            ->where('user_id', $user->id)
            ->whereIn('course_id', $courses_id)
            ->sum('attempts');

        return $sum;
    }

    protected function getUserTotalCoursesByStatusCode($user, $courses_id, $status_code)
    {
        $count = SummaryCourse::query()
            ->where('user_id', $user->id)
            ->whereRelation('status', 'code', $status_code)
            ->whereRelation('course.type', 'code', '<>', 'free')
            ->whereIn('course_id', $courses_id)
            ->count();

        return $count;
    }
}
