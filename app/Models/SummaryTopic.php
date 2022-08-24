<?php

namespace App\Models;

class SummaryTopic extends BaseModel
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

    protected function incrementUserAttempts($topic, $user = null)
    {
        $user = $user ?? auth()->user;
        $config_quiz = $user->subworspace->mod_evaluaciones;

        $row = SummaryTopic::select('id', 'attempts')
                    ->where('topic_id', $topic->id)
                    ->where('user_id', $user->id)
                    ->first();

        if ( $row AND ($row->attempts < $config_quiz['nro_attempts']) ) 
            return $row->update(['attempts' => $row->attempts + 1]);

        return SummaryTopic::storeData($topic, $user);
    }

    protected function storeData($topic, $user = null)
    {
        $user = $user ?? auth()->user;

        $row = SummaryTopic::create([
            'user_id' => $user->id,
            'topic_id' => $topic->id,
            'attempts' => 1,
            // 'fuente' => $fuente
            // 'status_id' => 'desarrollo'
        ]);

        return $row;
    }
}
