<?php

namespace App\Models;

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

    public static function resetMasiveAttempts($topicsIds, $userId)
    {
        self::whereIn('topic_id', $topicsIds)
            ->where('user_id', $userId)
            ->update([
                'attempts' => 0,
                //'fuente' => 'resetm'
            ]);
    }

    public function hasFailed()
    {
        return ! $this->passed;
    }

    public function hasNoAttemptsLeft($attempts_limit = null)
    {
        $config_quiz = auth()->user()->subworspace->mod_evaluaciones;

        $attempts_limit = $config_quiz['nro_intentos'] ?? 5;

        $attempts_limit
    }
}
