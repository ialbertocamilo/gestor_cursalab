<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Topic extends BaseModel
{
    protected $fillable = [
        'name', 'slug', 'description', 'content', 'imagen',
        'position', 'visits_count', 'assessable', 'evaluation_verified',
        'topic_requirement_id', 'type_evaluation_id', 'duplicate_id', 'course_id',
        'active'
    ];

    //    protected $casts = [
    //        'assessable' => 'string'
    //    ];

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function setAssessableAttribute($value)
    {
        $this->attributes['assessable'] = ($value === 'true' or $value === 'si' or $value === true or $value === 1 or $value === '1');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Topic::class, 'topic_requirement_id');
    }

    public function medias()
    {
        return $this->hasMany(MediaTema::class, 'topic_id');
    }

    public function models()
    {
        return $this->morphMany(Requirement::class, 'model');
    }

    public function requirements()
    {
        return $this->morphMany(Requirement::class, 'model');
    }

    public function evaluation_type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_evaluation_id');
    }

    public function summaries()
    {
        return $this->hasMany(SummaryTopic::class);
    }

    public function countQuestionsByTypeEvaluation($code)
    {
        return $this->questions()
            ->where('active', ACTIVE)
            ->whereRelation('type', 'code', $code)
            ->count();
    }

    public function summaryByUser($user_id, array $withRelations = null)
    {
        return $this->summaries()
            ->when($withRelations ?? null, function ($q) use ($withRelations) {
                $q->with($withRelations);
            })
            ->where('user_id', $user_id)->first();
    }

    protected static function search($request, $paginate = 15)
    {
        $q = self::with('evaluation_type:id,code,name', 'questions.type')
            //            ->withCount('questions')
            ->where('course_id', $request->course_id);
        // $q = self::withCount('preguntas')
        //     ->where('categoria_id', $request->categoria_id)
        //     ->where('course_id', $request->course_id);

        if ($request->q)
            $q->where('name', 'like', "%$request->q%");


        if (!is_null($request->sortBy)) {

            $field = $request->sortBy ?? 'position';
            if ($field == 'orden')
                $field = 'position';
            else if ($field == 'nombre')
                $field = 'name';
            $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

            $q->orderBy($field, $sort);
        } else {
            $q->orderBy('created_at', 'DESC');
        }

        return $q->paginate($request->paginate);
    }

    protected static function search_preguntas($request, $topic)
    {
        $question_type_code = $topic->evaluation_type->code === 'qualified'
            ? 'select-options'
            : 'written-answer';
        //        info($question_type_code);
        $q = Question::whereRelation('type', 'code', $question_type_code)
            ->where('topic_id', $topic->id);

        if ($request->q)
            $q->where('pregunta', 'like', "%$request->q%");

        return $q->paginate($request->paginate ?? 10);
    }

    protected function storeRequest($data, $tema = null)
    {
        try {
            DB::beginTransaction();

            if ($tema) :
                $tema->update($data);
            else :
                $tema = self::create($data);
            endif;

            $tema->medias()->delete();
            if (!empty($data['medias'])) :
                $medias = array();
                $now = date('Y-m-d H:i:s');
                foreach ($data['medias'] as $index => $media) {
                    $valor = isset($media['file']) ? Media::uploadFile($media['file']) : $media['valor'];
                    // if(!str_contains($string, 'http') && ($media['tipo']=='audio' || $media['tipo']=='video')){
                    //     $valor = env('DO_URL') . '/' .$valor;
                    // }
                    $medias[] = [
                        'position' => ($index + 1),
                        'topic_id' => $tema->id,
                        'value' => $valor,
                        'title' => $media['titulo'] ?? '',
                        'embed' => $media['embed'],
                        'downloadable' => $media['descarga'],
                        'type_id' => $media['tipo'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('media_topics')->insert($medias);
            endif;

            if (!empty($data['file_imagen'])) :
                $path = Media::uploadFile($data['file_imagen']);
                $tema->imagen = $path;
            endif;

            ////// Si el tema es evaluable, marcar CURSO también como evaluable
            $curso = Course::find($tema->course_id);
            $tema_evaluable = Topic::where('course_id', $tema->id)->where('assessable', 1)->first();
            $curso->assessable = $tema_evaluable ? 1 : 0;
            $curso->save();
            //////

            $tema->save();

            // Generate code when is not defined

            if (!$tema->code) {
                $tema->code = 'T' . str_pad($tema->id, 2, '0', STR_PAD_LEFT);
                $tema->save();
            }

            DB::commit();
            return $tema;
        } catch (\Exception $e) {
            DB::rollBack();
            info($e);
            return $e;
        }
    }

    protected function validateBeforeUpdate(School $school, Topic $topic, $data)
    {
        $validations = collect();

        // Validar que sea el último tema activo y que se va a desactivar,
        // eso haría que se desactive el curso, validar si ese curso es requisito de otro e impedir que se desactive el tema
        $last_topic_active_and_required_course = $this->checkIfIsLastActiveTopicAndRequiredCourse($school, $topic);
        if ($last_topic_active_and_required_course['ok']) $validations->push($last_topic_active_and_required_course);


        // Validar si es tema es requisito de otro tema
        $is_required_topic = $this->checkIfIsRequiredTopic($school, $topic, $data);
        if ($is_required_topic['ok']) $validations->push($is_required_topic);


        // Validar si se está cambiando entre calificada y abierta, y no tiene preguntas del nuevo tipo de calificacion
        $evaluation_type_will_be_changed = $this->checkIfEvaluationTypeWillBeChanged($topic, $data);
        if ($evaluation_type_will_be_changed['ok']) $validations->push($evaluation_type_will_be_changed);


        $show_confirm = !($validations->where('show_confirm', false)->count() > 0);

        return [
            'list' => $validations->toArray(),
            //            'list' => [123,123,123],
            'title' => !$show_confirm ? 'Alerta' : 'Tener en cuenta',
            'show_confirm' => $show_confirm,
            //            'show_confirm' => true,
            'type' => 'validations-before-update'
        ];
    }

    public function checkIfEvaluationTypeWillBeChanged(Topic $topic, $data)
    {
        $assessable = $data['assessable'] ?? $topic->assessable;

        if ($topic->assessable == 0 || $assessable == 0) return ['ok' => false];

        $is_or_will_be_assessable = $topic->assessable || ($assessable === 1);
        $evaluation_type = $topic->evaluation_type;
        $questions_by_evaluation_type = $topic->questions()
            ->whereRelation('type', 'code', $evaluation_type->code == 'qualified' ? 'select-options' : 'written-answer')->count();
        $have_questions_of_new_type = $questions_by_evaluation_type > 0;

        $temp['ok'] = $is_or_will_be_assessable && !$have_questions_of_new_type && ($data['active'] || $data['active'] == 'true');

        if (!$temp['ok']) return $temp;

        $temp['title'] = "No se puede activar el tema.";
        $temp['subtitle'] = "Para poder activar el tema es necesario agregarle una evaluación.";
        $temp['show_confirm'] = false;
        $temp['type'] = 'check_if_evaluation_type_will_change_and_has_active_questions';
        $temp['list'] = [];


        return $temp;
    }

    public function checkIfIsLastActiveTopicAndRequiredCourse(School $school, Topic $topic)
    {
        $course_requirements = Requirement::whereHasMorph('requirement', [Course::class], function ($query) use ($topic) {
            $query->where('id', $topic->course->id);
        })->get();

        //        $course_requirements = $topic->course->requirements()->get();

        $is_required_course = $course_requirements->count() > 0;

        $last_active_topic = Topic::where('course_id', $topic->course_id)->where('active', 1)->get();
        $is_last_active_topic = ($last_active_topic->count() === 1 && $topic->active);

        $temp['ok'] = ($is_last_active_topic && $is_required_course);

        if (!$temp['ok']) return $temp;

        $temp['title'] = "No se puede inactivar el tema.";
        $temp['subtitle'] = "Para poder inactivar el curso es necesario quitarlo como requisito de los siguientes cursos:";
        $temp['show_confirm'] = false;
        $temp['type'] = 'last_active_topic_and_required_course';
        $temp['list'] = [];

        foreach ($course_requirements as $requirement) {
            $requisito = Course::find($requirement->requirement_id);
            $route = route('cursos.editCurso', [$school->id, $requirement->requirement_id]);
            $temp['list'][] = "<a href='{$route}'>" . $requisito->name . "</a>";
        }

        return $temp;
    }

    public function checkIfIsRequiredTopic(School $school, Topic $topic, $data, $verb = 'inactivar')
    {
        $requirements = Requirement::whereHasMorph('requirement', [Topic::class], function ($query) use ($topic) {
            $query->where('id', $topic->id);
        })->get();

        //        $requirements = $topic->requirements()->get();

        $is_required_topic = $requirements->count() > 0;
        $will_be_deleted = $data['to_delete'] ?? false;
        $will_be_inactivated = $data['active'] === false;
        $temp['ok'] = (($will_be_inactivated || $will_be_deleted) && $is_required_topic);

        if (!$temp['ok']) return $temp;

        $temp['title'] = "No se puede {$verb} el tema.";
        $temp['subtitle'] = "Para poder {$verb} el tema es necesario quitarlo como requisito de los siguientes temas:";
        $temp['show_confirm'] = false;
        $temp['type'] = 'check_if_is_required_topic';
        $temp['list'] = [];

        foreach ($requirements as $requirement) {
            $requisito = Topic::find($requirement->requirement_id);
            $route = route('temas.editTema', [$school->id, $topic->course->id, $requirement->requirement_id]);
            $temp['list'][] = "<a href='{$route}'>" . $requisito->name . "</a>";
        }

        return $temp;
    }

    protected function getMessagesAfterUpdate(Topic $topic, $data, $title)
    {
        $messages = collect();

        $messages_on_delete = $this->getMessagesOnDelete($topic);
        if ($messages_on_delete['ok']) $messages->push($messages_on_delete);

        $messages_on_create = $this->getMessagesOnCreate($topic);
        if ($messages_on_create['ok']) $messages->push($messages_on_create);

        $messages_on_update_status = $this->getMessagesAfterUpdateStatus($topic, $data);
        if ($messages_on_update_status['ok']) $messages->push($messages_on_update_status);

        return [
            'list' => $messages->toArray(),
            'title' => $title,
            'type' => 'validations-after-update'
        ];
    }

    public function getMessagesOnDelete($topic)
    {
        $temp['ok'] = $topic->trashed();

        if (!$temp['ok']) return $temp;

        $temp['title'] = null;
        $temp['subtitle'] = "Esto puede producir un ajuste en el avance de los usuarios. Los cambios se mostrarán en el app y web en unos minutos.";
        $temp['show_confirm'] = true;
        $temp['type'] = 'update_message_on_update';

        return $temp;
    }

    public function getMessagesOnCreate($topic)
    {
        $temp['ok'] = $topic->wasRecentlyCreated;

        if (!$temp['ok']) return $temp;

        $temp['title'] = null;
        $temp['subtitle'] = "Esto puede producir un ajuste en el avance de los usuarios. Los cambios se mostrarán en el app y web en unos minutos.";
        $temp['show_confirm'] = true;
        $temp['type'] = 'update_message_on_update';

        return $temp;
    }

    public function getMessagesAfterUpdateStatus($topic, $data)
    {

        $temp['ok'] = $topic->wasChanged('active');

        if (!$temp['ok']) return $temp;

        $temp['title'] = null;
        $temp['subtitle'] = "Esto puede producir un ajuste en el avance de los usuarios. Los cambios se mostrarán en el app y web en unos minutos.";
        $temp['show_confirm'] = true;
        $temp['type'] = 'update_message_on_update';

        return $temp;
    }

    protected function validateBeforeDelete(School $school, Topic $topic, $data)
    {
        $validations = collect();

        // Validar que sea el último tema activo y que se va a desactivar,
        // eso haría que se desactive el curso, validar si ese curso es requisito de otro e impedir que se desactive el tema
        $last_topic_active_and_required_course = $this->checkIfIsLastActiveTopicAndRequiredCourse($school, $topic);
        if ($last_topic_active_and_required_course['ok']) $validations->push($last_topic_active_and_required_course);


        // Validar si es tema es requisito de otro tema
        $is_required_topic = $this->checkIfIsRequiredTopic($school, $topic, $data);
        if ($is_required_topic['ok']) $validations->push($is_required_topic);


        $show_confirm = !($validations->where('show_confirm', false)->count() > 0);

        return [
            'list' => $validations->toArray(),
            //            'list' => [123,123,123],
            'title' => !$show_confirm ? 'Alerta' : 'Tener en cuenta',
            'show_confirm' => $show_confirm,
            //            'show_confirm' => true,
            'type' => 'validations-before-update'
        ];
    }

    protected function getMessagesAfterDelete(Topic $topic, $title)
    {
        $messages = collect();

        $messages_on_delete = $this->getMessagesOnDelete($topic);
        if ($messages_on_delete['ok']) $messages->push($messages_on_delete);

        $messages_on_create = $this->getMessagesOnCreate($topic);
        if ($messages_on_create['ok']) $messages->push($messages_on_create);


        return [
            'list' => $messages->toArray(),
            'title' => $title,
            'type' => 'validations-after-update'
        ];
    }


    protected function getDataToTopicsViewAppByUser($user, $user_courses, $school_id)
    {
        if ($user_courses->count() === 0) return [];

        $schools = $user_courses->groupBy('schools.*.id');
        $courses = $schools[$school_id] ?? collect();
        $school = $courses->first()?->schools?->where('id', $school_id)->first();

        $sub_workspace = $user->subworkspace;
        $mod_eval = $sub_workspace->mod_evaluaciones;

        $max_attempts = (int)$mod_eval['nro_intentos'];

        $schools_courses = [];

        $topic_status_arr = config('topics.status');

        foreach ($courses as $course) {
            $course_status = Course::getCourseStatusByUser($user, $course);
            $topics_data = [];

            $topics = $course->topics->where('active', ACTIVE)->sortBy('position');

            foreach ($topics as $topic) {
                $topic_status = self::getTopicStatusByUser($topic, $user, $max_attempts);

                $media_topics = $topic->medias->sortBy('position')->values()->all();
                foreach ($media_topics as $media) {
                    unset($media->created_at, $media->updated_at, $media->deleted_at);
                    $media->full_path = !in_array($media->type_id, ['youtube', 'vimeo', 'scorm', 'link'])
                        ? route('media.download.media_topic', [$media->id]) : null;
                }

                $topics_data[] = [
                    'id' => $topic->id,
                    'nombre' => $topic->name,
                    'requisito_id' => $topic_status['topic_requirement'],
                    'imagen' => $topic->imagen,
                    'contenido' => $topic->content,
                    'media' => $media_topics,
                    'evaluable' => $topic->assessable ? 'si' : 'no',
                    'tipo_ev' => $topic->evaluation_type->code ?? NULL,
                    'nota' => $topic_status['grade'],
                    'disponible' => $topic_status['available'],
                    'intentos_restantes' => $topic_status['remaining_attempts'],
                    'estado_tema' => $topic_status['status'],
                    //                    'estado_tema_str' => $topic_status['status'],
                    'estado_tema_str' => $topic_status_arr[$topic_status['status']],
                ];
            }

            $schools_courses[] = [
                'id' => $course->id,
                'nombre' => $course->name,
                'descripcion' => $course->description,
                'imagen' => $course->imagen,
                'requisito_id' => $course->require,
                'c_evaluable' => $course->c_evaluable,
                'disponible' => $course_status['available'],
                'status' => $course_status['status'],
                'encuesta' => $course_status['available_poll'],
                'encuesta_habilitada' => $course_status['enabled_poll'],
                'encuesta_resuelta' => $course_status['solved_poll'],
                'encuesta_id' => $course_status['poll_id'],
                'temas_asignados' => $course_status['exists_summary_course'] ?
                    $course_status['assigned_topics'] :
                    $topics->count(),
                'temas_completados' => $course_status['completed_topics'],
                'porcentaje' => $course_status['progress_percentage'],
                'temas' => $topics_data
            ];
        }

        return [
            'id' => $school?->id,
            'nombre' => $school?->name,
            'cursos' => $schools_courses
        ];
    }

    public function getTopicStatusByUser(Topic $topic, User $user, $max_attempts)
    {
        $grade = 0;
        $available_topic = false;
        $remaining_attempts = $max_attempts;
        // $summary_topic = $topic->summaryByUser($user->id);
        $summary_topic = SummaryTopic::getCurrentRow($topic, $user);
        $last_topic_reviewed = null;
        // $topic_status = 'por-iniciar';
        $topic_status = $summary_topic->status->code ?? 'por-iniciar';

        if ($topic->assessable && $topic->evaluation_type->code === 'qualified') {
            if ($summary_topic) {
                // $topic_status = $summary_topic->passed ? 'aprobado' : 'desaprobado';
                $grade = $summary_topic->grade;
                $sub = $max_attempts - $summary_topic->attempts;
                $remaining_attempts = max($sub, 0);
            }
        }

        $topic_requirement = $topic->requirement()->first();

        if (!$topic_requirement) {
            $available_topic = true;
        } else {
            $summary_requirement_topic = SummaryTopic::with('status')
                ->where('user_id', $user->id)
                ->where('topic_id', $topic_requirement->id)
                ->first();

            $activity_requirement = in_array($summary_requirement_topic?->status->code, ['aprobado', 'realizado', 'revisado']);
            $test_requirement = $summary_requirement_topic?->result == 1;

            if ($activity_requirement || $test_requirement)
                $available_topic = true;
        }

        return [
            //            'topic_name' => $topic->name,
            'status' => $topic_status,
            'topic_requirement' => $topic_requirement?->id,
            'grade' => $grade,
            'available' => $available_topic,
            'remaining_attempts' => $remaining_attempts,
            'activity' => $summary_topic ?? null,
            'last_topic_reviewed' => $last_topic_reviewed
        ];
    }

    public function getNextOne()
    {
        return Topic::where('course_id', $this->course_id)
            ->whereNotIn('id', [$this->id])
            ->where('position', '>=', $this->position)
            ->orderBy('position', 'ASC')
            ->first();
    }

    protected function evaluateAnswers($respuestas, $topic)
    {
        $questions = Question::select('id', 'rpta_ok', 'score')->where('topic_id', $topic->id)->get();

        $correct_answers = $failed_answers = $correct_answers_score = 0;

        foreach ($respuestas as $key => $respuesta) {

            $question = $questions->where('id', $respuesta['preg_id'])->first();

            if ($question->rpta_ok == $respuesta['opc']) {

                $correct_answers++;

                $respuestas[$key]['score'] = $question->score;

                $correct_answers_score += $question->score;

                continue;
            }

            $failed_answers++;
        }

        return [$correct_answers, $failed_answers, $correct_answers_score];
    }

    protected function getTopicProgressByUser($user, Topic $topic)
    {
        $topic_grade = null;
        $available_topic = true;
        $topic_requirement = $topic->requirement;

        if ($topic_requirement) :
            $requirement_summary = SummaryTopic::with('status:id,code')
                ->where('topic_id', $topic_requirement->id)
                ->where('user_id', $user->id)->first();

            $available_topic = $requirement_summary && in_array($requirement_summary->status->code, ['aprobado', 'realizado', 'revisado']);
        endif;

        $summary_topic = SummaryTopic::with('status:id,code')->where('topic_id', $topic->id)->where('user_id', $user->id)->first();

        if ($topic->evaluation_type?->code === 'qualified' && $summary_topic)
            $topic_grade = $summary_topic->grade;

        $topic_status = $summary_topic?->status?->code ?? 'por-iniciar';


        return [
            'available' => $available_topic,
            'grade' => $topic_grade,
            'status' => $topic_status,
        ];
    }
}
