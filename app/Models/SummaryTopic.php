<?php

namespace App\Models;

class SummaryTopic extends Summary
{
    protected $table = 'summary_topics';

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

}
