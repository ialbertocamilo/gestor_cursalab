<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Mongo\AuditSummaryUpdate;

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

            if ($model instanceof Topic) {

                // $config_quiz = $user->subworspace->mod_evaluaciones;
                $config_quiz = Course::getModEval($model->course_id);

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

    protected function getCurrentRowOrCreate($model, $user = null)
    {
        $row = self::getCurrentRow($model, $user);

        if (!$row) $row = $this->storeData($model, $user);

        return $row;
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

            $assigneds = count($model->getCurrentCourses(withFreeCourses: false, withRelations: 'soft', only_ids: true));
            $data['courses_assigned'] = $assigneds;
        }

        return self::create($data);
    }

    protected function updateUsersDataByCourse($users, $course, $action)
    {
        $course_rows = SummaryCourse::where('course_id', $course->id)->get();

        foreach ($course_rows as $row) {
            // SummaryUser::

        }
    }

    protected function updateUsersByCourse($course,$users_id = null,$summary_course_update=true,$only_users_has_sc=false,$event='default',$send_notification=false   ){
        $users_id_segmented = [];
        // $course->loadMissing('segments');
        if($only_users_has_sc){
            $users_id_segmented  = ($users_id) ? $users_id : SummaryCourse::where('course_id',$course?->id)->pluck('user_id')->toArray();
        }else{
            $users_id_segmented = ($users_id) ? $users_id : $course->usersSegmented($course->segments,'users_id');
        }
        $chunk_users = array_chunk($users_id_segmented,80);
        foreach ($chunk_users as $users) {
            self::setSummaryUpdates($users,[$course?->id],$summary_course_update,$event);
        }

        // Create notifications for users assigned to course

        if ( count($users_id_segmented) && $send_notification) {

            $school = $course->schools->first();
            if ($school && get_current_workspace()) {
                UserNotification::createNotifications(
                    get_current_workspace()->id,
                    $users_id_segmented,
                    UserNotification::NEW_COURSE,
                    [
                        'courseName' => $course->name
                    ],
                    'escuela/'.$school->id.'/cursos/' . $course?->id . '/tema'
                );
            }
        }
    }

    protected function setSummaryUpdates($user_ids, $course_ids = null, $summary_course_update = null, $event = null)
    {
        $data = [
            'summary_user_update' => true,
            'required_update_at' => now(),
            'is_updating'=>0
        ];
        AuditSummaryUpdate::create([
            'user_ids' => $user_ids,
            'course_ids'=> $course_ids,
            'summary_course_update' =>$summary_course_update,
            'type' => $event,
        ]);

        if ($course_ids && $summary_course_update) {
            $course_ids = implode(',',$course_ids);
            $data['summary_course_update'] = true;
            User::whereIn('id', $user_ids)->whereNull('summary_course_data')->update(['summary_course_data'=>'0']);
            $data['summary_course_data'] = DB::raw("CONCAT(summary_course_data,',','{$course_ids}')");
        }
        User::whereIn('id', $user_ids)->update($data);
    }

}
