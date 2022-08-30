<?php

namespace App\Models;

use Carbon\Carbon;

class SummaryTopic extends Summary
{
    protected $table = 'summary_topics';

    protected $fillable = [
        'user_id', 'topic_id', 'status_id', 'views', 'attempts', 'downloads', 'answers', 'restarts',
        'current_quiz_started_at', 'current_quiz_finishes_at', 'taking_quiz',
        'last_time_evaluated_at',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    protected $dates = [
        'current_quiz_started_at',
        'current_quiz_finishes_at',
        'last_time_evaluated_at',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    protected function setStartQuizData($topic)
    {
        $row = $this->getCurrentRow($topic);

        if (!$row->taking_quiz) {

            $data = [
                'current_quiz_started_at' => now(),
                'current_quiz_finishes_at' => now()->addHour(),
                'taking_quiz' => ACTIVE,
            ];

            $row->update($data);
        }

        return $row;
    }

    public function isOutOfTimeForQuiz()
    {
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

        $query = SummaryTopic::whereIn('topic_id', $topicsIds)
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

    public function hasNoAttemptsLeft($attempts_limit = null)
    {
        if (!$attempts_limit)
        {
            $config = auth()->user()->getSubworkspaceSetting('mod_evaluaciones');
            $attempts_limit = $config['nro_intentos'] ?? 5;
        }

        return $row->attempts >= $attempts_limit;
    }

    protected function calculateGrade($correct_answers, $failed_answers)
    {
        return ($correct_answers == 0) ? 0 : ((20 / ($correct_answers + $failed_answers)) * $correct_answers);
    }

    protected function hasPassed($new_grade, $passing_grade = NULL)
    {
        if (!$passing_grade)
            $passing_grade = auth()->user()->getSubworkspaceSetting('mod_evaluaciones', 'nota_aprobatoria');

        return $new_grade >= $passing_grade;
    }

    public function hasImprovedGrade($new_grade)
    {
        return $new_grade >= $this->grade;
    }
}
