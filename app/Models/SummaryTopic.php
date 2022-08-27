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

    protected function setStartQuizData($topic)
    {
        $row = $this->getCurrentRow($topic);

        if (!$row->taking_quiz) {

            $data = [
                'current_quiz_started_at' => now(),
                'taking_quiz' => ACTIVE,
            ];

            $row->update($data);
        }


        return $row;
        // return SummaryTopic::create($data);
    }

    public function isOutOfTimeForQuiz()
    {
        return now() >= $row->current_quiz_started_at->addHour();
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
        if (!$attempts_limit)
        {
            $config_quiz = auth()->user()->subworspace->mod_evaluaciones;
            $attempts_limit = $config_quiz['nro_intentos'] ?? 5;
        }

        return $row->attempts >= $attempts_limit; 
    }
}
