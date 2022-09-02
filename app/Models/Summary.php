<?php

namespace App\Models;

class Summary extends BaseModel
{
    protected function setUserLastTimeEvaluation($model, $user = NULL)
    {
        $user = $user ?? auth()->user();

        $query = self::where('user_id', $user->id);

        if ($model == 'topic')
            $query->where('topic_id', $model->id);

        if ($model == 'course')
            $query->where('course_id', $model->id);

        $query->update(['last_time_evaluated_at' => now()]);
    }

    protected function incrementUserAttempts($model, $setEvaluatedAt = true, $user = null)
    {
        $user = $user ?? auth()->user();

        $row = $this->getCurrentRow($model, $user);

        if ($row) {

            if ( $model instanceof Topic ) {

                $config_quiz = $user->subworspace->mod_evaluaciones;

                if ($row->attempts >= $config_quiz['nro_intentos'])
                    return false;
            }

            $data = ['attempts' => $row->attempts + 1];

            if ($setEvaluatedAt)
                $data['last_time_evaluated_at'] = now();

            return $row->update($data);
        }

        return $this->storeData($model, $user);
    }

    protected function incrementViews($model, $user = null)
    {
        $user = $user ?? auth()->user();

        $row = $this->getCurrentRow($model);

        if ($row) return $row->update(['views' => $row->views + 1]);


        return $this->storeData($model, $user);
    }

    protected function getCurrentRow($model, $user = null)
    {
        $user = $user ?? auth()->user();

        $query = self::where('user_id', $user->id);

        if ($model instanceof Topic)
            $query->where('topic_id', $model->id);

        if ($model instanceof Course)
            $query->where('course_id', $model->id);

        return $query->first();
    }

    protected function storeData($model, $user = null)
    {
        $user = $user ?? auth()->user();

        // $source = Taxonomy::getFirstData('topic', 'user-status', 'por-iniciar');

        $data = [
            'user_id' => $user->id,
            'attempts' => 0,
            'views' => 1,
            'advanced_percentage' => 0,
            // 'last_time_evaluated_at' => now(),
            // 'fuente' => $fuente
            // 'libre' => $curso->libre,
        ];

        if ($model instanceof Topic) {

            $status = Taxonomy::getFirstData('topic', 'user-status', 'por-iniciar');

            $data['topic_id'] = $model->id;
            $data['status_id'] = $status->id;
            $data['downloads'] = 0;
        }

        if ($model instanceof Course) {

            $status = Taxonomy::getFirstData('course', 'user-status', 'desarrollo');
            $assigneds = $model->topics->where('active', ACTIVE)->count();

            $data['course_id'] = $model->id;
            $data['status_id'] = $status->id;
            $data['assigned'] = $assigneds;
        }

        if ($model instanceof User) {

            $assigneds = $model->getCurrentCourses()->count();
            $data['courses_assigned'] = $assigneds;
        }

        return self::create($data);
    }

    protected function updateUsersDataByCourse($users, $course)
    {
        $course_rows =  SummaryCourse::where('course_id', $course->id)->get();
    }
}
