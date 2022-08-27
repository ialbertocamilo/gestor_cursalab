<?php

namespace App\Models;

use Carbon\Carbon;

class SummaryTopic extends Summary
{
    protected $table = 'summary_topics';

    protected $fillable = [
        'last_time_evaluated_at', 'user_id', 'topic_id', 'views', 'attempts', 'downloads'
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    protected function setInitialData($topic)
    {
        $data = [
            'user_id' => $user->id,
            'topic_id' => $topic->id,
            'current_quiz_started_at' => now(),
            'attempts' => 1,
        ];

        return SummaryTopic::create($data);
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
        $topicId, $userId, $adminId
    ): void
    {

        $summaryTopic = SummaryTopic::where('topic_id', $topicId)
            ->where('user_id', $userId)
            ->first();

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
            $config_quiz = auth()->user()->subworspace->mod_evaluaciones;
            $attempts_limit = $config_quiz['nro_intentos'] ?? 5;
        }

        return $row->attempts >= $attempts_limit;
    }
}
