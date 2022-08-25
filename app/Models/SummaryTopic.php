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

    // protected function setUserLastTimeEvaluation($topic, $user = NULL)
    // {
    //     $user = $user ?? auth()->user;

    //     return SummaryTopic::where('user_id', $user->id)
    //             ->where('topic_id', $topic->id)
    //             ->update(['last_time_evaluated_at' => now()]);
    // }

    // protected function incrementUserAttempts($topic, $setLastTimeEvaluation = true, $user = null)
    // {
    //     $user = $user ?? auth()->user;
    //     $config_quiz = $user->subworspace->mod_evaluaciones;

    //     $row = SummaryTopic::select('id', 'attempts')
    //                 ->where('topic_id', $topic->id)
    //                 ->where('user_id', $user->id)
    //                 ->first();

    //     $data = ['attempts' => $row->attempts + 1];

    //     if ($setLastTimeEvaluation)
    //         $data['last_time_evaluated_at'] = now();

    //     if ( $row AND ($row->attempts < $config_quiz['nro_attempts']) ) 
    //         return $row->update($data);

    //     return SummaryTopic::storeData($topic, $user);
    // }

    // protected function storeData($topic, $user = null)
    // {
    //     $user = $user ?? auth()->user;

    //     $row = SummaryTopic::create([
    //         'user_id' => $user->id,
    //         'topic_id' => $topic->id,
    //         'attempts' => 1,
    //         'last_time_evaluated_at' => now(),
    //         // 'fuente' => $fuente
    //         // 'status_id' => 'desarrollo'
    //     ]);

    //     return $row;
    // }
}
