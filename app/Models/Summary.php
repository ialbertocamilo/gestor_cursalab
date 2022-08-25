<?php

namespace App\Models;

class Summary extends BaseModel
{
    protected function setUserLastTimeEvaluation($model, $user = NULL)
    {
        $user = $user ?? auth()->user;

        $query = self::where('user_id', $user->id);

        if ($model == 'topic')
            $query->where('topic_id', $model->id);

        if ($model == 'course')
            $query->where('course_id', $model->id);

        $query->update(['last_time_evaluated_at' => now()]);
    }

    protected function incrementUserAttempts($model, $setEvaluatedAt = true, $user = null)
    {
        $user = $user ?? auth()->user;

        $row = $this->getCurrentRow($model, $user);

        if ($row) {

            if ( $model == 'topic' ) {
                
                $config_quiz = $user->subworspace->mod_evaluaciones;

                if ($row->attempts >= $config_quiz['nro_attempts'])
                    return false;
            }

            $data = ['attempts' => $row->attempts + 1];

            if ($setEvaluatedAt)
                $data['last_time_evaluated_at'] = now();
            
            return $row->update($data);
        }

        return $this->storeData($model, $user);
    }

    protected function getCurrentRow($model, $user)
    {
        $query = self::select('id', 'attempts')
                    ->where('user_id', $user->id);
        
        if ($model == 'topic')
            $query->where('topic_id', $model->id);

        if ($model == 'course')
            $query->where('course_id', $model->id);
        
        return $query->first();
    }

    protected function storeData($model, $user = null)
    {
        $user = $user ?? auth()->user;

        $status = Taxonomy::getFirstData('', '', 'desarrollo');

        $row = self::create([
            'user_id' => $user->id,
            'attempts' => 1,
            'last_time_evaluated_at' => now(),
            // 'fuente' => $fuente
            // 'libre' => $curso->libre,
            'status_id' => $status->id,
        ]);

        if ($model == 'topic')
            $row['topic_id'] = $model->id;

        if ($model == 'course') {

            $assigneds = $model->topics->where('active', ACTIVE)->count();
            $row['course_id'] = $model->id;
            $row['assigneds'] = $assigneds;
        }

        return $row;
    }
}
