<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Course;
use DB;

class SummaryTopic extends Summary
{
    protected $table = 'summary_topics';

    protected $fillable = [
        'user_id', 'topic_id', 'status_id', 'source_id', 'views', 'attempts', 'downloads', 'answers', 'correct_answers',
        'failed_answers', 'restarts', 'current_quiz_started_at', 'current_quiz_finishes_at', 'taking_quiz', 'grade',
        'old_admin_id', 'answers_old', 'restarter_id',
        'passed', 'last_time_evaluated_at',
        'last_media_access', 'last_media_duration', 'media_progress',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public $defaultRelationships = [
        'topic_id' => 'topic',
        'user_id' => 'user'
    ];

    protected $dates = [
        'current_quiz_started_at',
        'current_quiz_finishes_at',
        'last_time_evaluated_at',
    ];

    // protected $hidden = ['answers'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    public function mediatopic()
    {
        return $this->belongsTo(MediaTema::class, 'last_media_access');
    }

    protected function setStartQuizData($topic)
    {
        $row = $this->getCurrentRow($topic);

        if ($row && !$row->taking_quiz) {
            $duration = config('app.quizzes.duration');
            $current_quiz_finishes_at = now()->addHours($duration);
            if(isset($topic?->course?->mod_evaluaciones['duration_quizz'])){
                $duration_quizz = $topic?->course?->mod_evaluaciones['duration_quizz'];
                $current_quiz_finishes_at = now()->addMinutes($duration_quizz)->second(1);
            }
            $data = [
                'current_quiz_started_at' => now(),
                'current_quiz_finishes_at' => $current_quiz_finishes_at,
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
