<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Course;
use DB;

class ProcessSummaryActivity extends BaseModel
{
    protected $table = 'process_summary_users_activities';

    protected $fillable = [
        'user_id', 'activity_id', 'status_id',
    ];

    // protected $casts = [
    //     'answers' => 'array',
    // ];

    public $defaultRelationships = [
        'activity_id' => 'activity',
        'user_id' => 'user'
    ];

    // protected $dates = [
    //     'current_quiz_started_at',
    //     'current_quiz_finishes_at',
    //     'last_time_evaluated_at',
    // ];

    // protected $hidden = ['answers'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }


    protected function setStartQuizData($topic)
    {
        $row = $this->getCurrentRow($topic);

        if ($row && !$row->taking_quiz) {

            $duration = config('app.quizzes.duration');

            $data = [
                'current_quiz_started_at' => now(),
                'current_quiz_finishes_at' => now()->addHours($duration),
                'taking_quiz' => ACTIVE,
            ];

            $row->update($data);
        }

        return $row;
    }

    public function isOutOfTimeForQuiz()
    {
        if (!$this->current_quiz_finishes_at) return false;

        return now() >= $this->current_quiz_finishes_at;
    }

    public static function resetUserTopicsAttempts($userId, $topicsIds)
    {
        self::whereIn('topic_id', $topicsIds)
            ->where('user_id', $userId)
            ->update([
                'attempts' => 0,
                //'fuente' => 'resetm'
            ]);
    }

    /**
     * Reset attemtps of failed topics (desaprobados)
     * @param $topicsIds
     * @param $attemptsLimit
     * @param null $scheduleDate
     * @return void
     */
    public static function resetFailedTopicsAttemptsAllUsers(
        $topicsIds, $attemptsLimit, $scheduleDate = null
    ): void
    {

        // Get "Desaprobado" status from taxonomies

        $desaprobado = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');
        //Some users use the quiz attempt but don't finish it, so the summary topic has the status to-start
        $por_iniciar = Taxonomy::getFirstData('topic', 'user-status', 'por-iniciar');

        $query = SummaryTopic::whereIn('topic_id', $topicsIds)
            ->whereIn('status_id', [$desaprobado->id,$por_iniciar->id])
            ->where('attempts', '>=', $attemptsLimit);
        if ($scheduleDate)
            $query->where('last_time_evaluated_at', '<=', $scheduleDate);

        $count = $query;
        if($count->first()){
            $query->increment('restarts', 1, ['attempts' => 0]);
        }
        // $query->update([
        //     'attempts' => 0,
        //     'restarts' => DB::raw('restarts+1')
        //     // 'last_time_evaluated_at' => Carbon::now()
        //     //'fuente' => 'resetm'
        // ]);
    }

    /**
     * Update topics restarts count
     *
     * @param $topicId
     * @param $userId
     * @param $adminId
     * @return void
     */
    public static function updateTopicRestartsCount(
        $topicId, $userId = null, $adminId = null
    ): void
    {

        $query = SummaryTopic::where('topic_id', $topicId);

        if ($userId)
            $query->where('user_id', $userId);

        $summaryTopic = $query->first();

        if (!$summaryTopic) return;

        // Calculate number of restars

        $restarts = $summaryTopic->restarts
            ? $summaryTopic->restarts + 1
            : 1;

        // Update record

        $summaryTopic->restarts =  $restarts;
        $summaryTopic->restarter_id = $adminId;
        $summaryTopic->save();
    }

    public function hasFailed()
    {
        return ! $this->passed;
    }

    public function hasNoAttemptsLeft($attempts_limit = null, $course = null)
    {
        if (!$attempts_limit)
        {
            // $user = $user ?? auth()->user();
            // $config = $user->getSubworkspaceSetting('mod_evaluaciones');
            $attempts_limit = Course::getModEval($course,'nro_intentos') ?? 5;
            // $attempts_limit = $mod_eval['nro_intentos'] ?? 5;
        }

        return $this->attempts >= $attempts_limit;
    }

    protected function calculateGrade($correct_answers, $failed_answers)
    {
        return ($correct_answers == 0) ? 0 : ((20 / ($correct_answers + $failed_answers)) * $correct_answers);
    }

    protected function hasPassed($new_grade, $passing_grade = NULL, $course = null)
    {
        if (!$passing_grade)
            // $passing_grade = auth()->user()->getSubworkspaceSetting('mod_evaluaciones', 'nota_aprobatoria');
            $passing_grade = Course::getModEval($course,'nota_aprobatoria');

        return $new_grade >= $passing_grade;
    }

    public function hasImprovedGrade($new_grade)
    {
        return $new_grade >= $this->grade;
    }

    public function hasAttemptsLeft($attempts_limit = null)
    {
        return ! $this->hasNoAttemptsLeft($attempts_limit);
    }

    protected function getUserAverageGradeByCourses($user, $courses_id, $rounded = true)
    {
        $res = SummaryTopic::query()
            ->select(DB::raw('AVG(IFNULL(grade, 0)) AS nota_avg'))
            ->whereHas('topic', fn($q) => $q->where('active', ACTIVE)->whereIn('course_id', $courses_id))
            ->whereRelation('topic', 'assessable', ACTIVE)
            ->whereRelation('topic.evaluation_type', 'code', 'qualified')
            ->where('user_id', $user->id)
            ->first();

        $avg = $res->nota_avg ?? 0;

        return $rounded ? round($avg, 2) : $avg;
    }
}
