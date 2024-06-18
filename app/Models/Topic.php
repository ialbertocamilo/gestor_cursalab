<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Topic extends BaseModel
{
    protected $fillable = [
        'name', 'slug', 'description', 'content', 'imagen', 'external_id',
        'position', 'visits_count', 'assessable', 'evaluation_verified', 'qualification_type_id',
        'topic_requirement_id', 'type_evaluation_id', 'duplicate_id', 'course_id','path_qr',
        'active', 'active_results', 'position','review_all_duration_media','modality_in_person_properties', 'open_evaluation_button',
        'type_requirement',
    ];

       protected $casts = [
           'modality_in_person_properties' => 'object'
       ];

    public $defaultRelationships = [
        'type_evaluation_id' => 'evaluation_type',
        'course_id' => 'course'
    ];

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1') ? 1 : 0;
    }

    public function setActiveResultsAttribute($value)
    {
        $this->attributes['active_results'] = ($value === 'true' or $value === true or $value === 1 or $value === '1') ? 1 : 0;
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
    // public function poll()
    // {
    //     return $this->belongsTo(Poll::class,'poll_id');
    // }
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
    public function tags()
    {
        return $this->morphMany(Tag::class, 'model');
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

    public function qualification_type()
    {
        return $this->belongsTo(Taxonomy::class, 'qualification_type_id');
    }

    public function getModalityInPersonPropertiesAttribute($value){
        if(is_null($value) || $value=='undefined'){
            $data = [];
            $data['reference'] = '';
            $data['geometry'] = '';
            $data['formatted_address'] = '';
            $data['url'] = '';
            $data['ubicacion'] = '';
            $data['host_id'] = null;
            $data['cohost_id'] = null;
            $data['start_date'] = null;
            $data['start_time'] = null;
            $data['finish_time'] = null;
            $data['show_medias_since_start_course'] = 0;
            return $data;
        }
        return json_decode($value);
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
        $q = self::with('evaluation_type:id,code,name', 'questions.type', 'requirements.model_topic')
            //            ->withCount('questions')
            ->where('course_id', $request->course_id);
        // $q = self::withCount('preguntas')
        //     ->where('categoria_id', $request->categoria_id)
        //     ->where('course_id', $request->course_id);

        if ($request->q) {
            $q->where('name', 'like', "%$request->q%")
                ->orWhere('id', $request->q);
        }

        $field = $request->sortBy ?? 'position';
        if ($field == 'orden')
            $field = 'position';
        else if ($field == 'nombre')
            $field = 'name';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);
    }

    protected static function search_preguntas($request, $topic)
    {
        $question_type_code = $topic?->evaluation_type?->code === 'qualified'
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
        // try {
            DB::beginTransaction();

            $isNew = false;
            if ($tema) {
                $tema->update($data);
            } else {
                $tema = self::create($data);
                $isNew = true;
            }

            if ($data['topic_requirement_id']) {
                Requirement::updateOrCreate(
                    ['model_type' => Topic::class, 'model_id' => $tema->id,],
                    ['requirement_type' => Topic::class, 'requirement_id' => $data['topic_requirement_id']]
                );
            } else {
                $tema->requirements()->delete();
            }
            //Create tags relations
            if(isset($data['tags']) && count($data['tags'])){
                foreach ($data['tags'] as $tag_id) {
                    $tag =  ['model_type' => Topic::class, 'model_id' => $tema->id,'tag_id'=> (int)$tag_id];
                    Tag::firstOrCreate($tag,$tag);
                }
                Tag::where('model_type',Topic::class)->where('model_id',$tema->id)->whereNotIn('tag_id',$data['tags'])->delete();
            }else{
                Tag::where('model_type',Topic::class)->where('model_id',$tema->id)->delete();
            }
            //
            $_medias = collect($tema->medias()->get());
            $tema->medias()->delete();
            if (!empty($data['medias'])) :
                $medias = array();
                $now = date('Y-m-d H:i:s');
                foreach ($data['medias'] as $index => $media) {
                    $valor = isset($media['file']) ? Media::uploadFile($media['file']) : $media['valor'];
                    // if(!str_contains($string, 'http') && ($media['tipo']=='audio' || $media['tipo']=='video')){
                    //     $valor = env('DO_URL') . '/' .$valor;
                    // }
                    $path_convert = $_medias->where('value',$valor)->first()?->path_convert;
                    if ($media['tipo'] == 'youtube') {
                        $valor = extractYoutubeVideoCode($valor);
                    }

                    if ($media['tipo'] == 'vimeo') {
                        $valor = extractVimeoVideoCode($valor);
                    }

                    $medias[] = [
                        'position' => ($index + 1),
                        'topic_id' => $tema->id,
                        'value' => $valor,
                        'title' => $media['titulo'] ?? '',
                        'embed' => $media['embed'],
                        'downloadable' => $media['descarga'],
                        'ia_convert' => $media['ia_convert'] ?? 0,
                        'path_convert' => $path_convert,
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

            // Fix topics position on updates

            if (!$isNew) {
                self::fixTopicsPosition($tema->course_id, $tema->id);
            }

            DB::commit();
            return $tema;
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     info($e);
        //     return $e;
        // }
    }

    /**
     * Fix position number of topics from the same course
     */
    public static function fixTopicsPosition($courseId, $topicIdToIgnore) {

        $topics = Topic::query()
            ->where('course_id', $courseId)
            ->where('id', '!=', $topicIdToIgnore)
            ->orderBy('position')
            ->get();

        $positionToIgnore = null;
        $topic = Topic::find($topicIdToIgnore);
        if ($topic) {
            $positionToIgnore = $topic->position;
        }

        $lastPosition = 1;
        foreach ($topics as $topic) {

            if ($lastPosition === $positionToIgnore) {
                $lastPosition++;
            }

            if ($topic->position != $lastPosition) {
                $topic->position = $lastPosition;
                $topic->save();
            }

            $lastPosition++;
        }
    }

    protected function validateBeforeUpdate(School $school, Topic $topic, $data)
    {
        $validations = collect();

        // Validar que sea el último tema activo y que se va a desactivar,
        // eso haría que se desactive el curso, validar si ese curso es requisito de otro e impedir que se desactive el tema
        $last_topic_active_and_required_course = $this->checkIfIsLastActiveTopicAndRequiredCourse($school, $topic, $data);
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

    public function checkIfIsLastActiveTopicAndRequiredCourse(School $school, Topic $topic, $data)
    {
        $course_requirements = Requirement::whereHasMorph('requirement', [Course::class], function ($query) use ($topic) {
            $query->where('id', $topic->course->id);
        })->get();

        //        $course_requirements = $topic->course->requirements()->get();

        $is_required_course = $course_requirements->count() > 0;

        $last_active_topic = Topic::where('course_id', $topic->course_id)->where('active', 1)->get();
        $is_last_active_topic = ($last_active_topic->count() === 1 && !$data['active']);

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
            $requisito = Topic::find($requirement->model_id);
            $route = route('temas.editTema', [$school->id, $topic->course->id, $requirement->requirement_id]);
            $temp['list'][] = "" . $requisito->name . " (ID: $requisito->id)";
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
        $last_topic_active_and_required_course = $this->checkIfIsLastActiveTopicAndRequiredCourse($school, $topic, $data);
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

        $workspace_id = auth()->user()->subworkspace->parent_id;

        $schools = $user_courses->groupBy('schools.*.id');
        $courses = $schools[$school_id] ?? collect();
        // if(count($courses)==0){
        //     //Solucion temporal: Si la escuela enviada por api no se encuetra dentro del curso, seleccionar la primera escuela
        //     //El bug de busqueda de cursos en escuela por el lado de aplicación.
        //     $school_id = $user_courses->first()->schools?->first()?->id;
        //     $courses = $schools[$school_id] ?? collect();
        // }
        $school = $courses->first()?->schools?->where('id', $school_id)->first();
        $courses_id = $user_courses->pluck('id');
        $positions_courses = CourseSchool::select('school_id','course_id','position')
                    ->where('school_id',$school_id)
                    ->whereIn('course_id',$courses_id)
                    ->get();
        $summaryCourses = SummaryCourse::query()
            ->whereIn('course_id', $courses_id)
            ->where('user_id', $user->id)
            ->get();

        $projects = Project::whereIn('course_id',$user_courses->pluck('id'))->where('active',1)->select('id','course_id')->get();
        $status_projects = collect();
        if(count($projects)>0){
            $status_projects   =  ProjectUser::whereIn('project_id',$projects->pluck('id'))->where('user_id',$user->id)->with('status:id,name,code')->select('id','project_id','user_id','status_id','msg_to_user')->get();
        }
        // UC
        $school_name = $school?->name;
        // if ($workspace_id === 25 && $school_name) {
        //     $school_name = removeUCModuleNameFromCourseName($school_name);
        // }
        $sub_workspace = $user->subworkspace;
        $mod_eval = $sub_workspace->mod_evaluaciones;

        // $max_attempts = isset($mod_eval['nro_intentos']) ? (int)$mod_eval['nro_intentos'] : 5;

        // $schools_courses = [];
        $schools_courses = collect();

        $topic_status_arr = config('topics.status');
        // $courses = $courses->sortBy('position');
        $polls_questions_answers = PollQuestionAnswer::select(DB::raw("COUNT(course_id) as count"), 'course_id')
            ->whereIn('course_id', $courses_id)
            ->where('user_id', $user->id)
            ->groupBy('course_id')
            ->get();
        $summary_courses_compatibles = SummaryCourse::with('course:id,name')
            ->whereRelation('course', 'active', ACTIVE)
            ->where('user_id', $user->id)
            // ->whereIn('course_id', $course->compatibilities->pluck('id')->toArray())
            ->orderBy('grade_average', 'DESC')
            ->whereRelation('status', 'code', 'aprobado')
            ->get();
        $user_status = Taxonomy::where('type', 'user-status')->get();
        // $statuses_courses = Taxonomy::where('group', 'course')->where('type', 'user-status')->get();
        $statuses_courses = $user_status->where('group', 'course')->where('type', 'user-status');
        // $statuses_topic = Taxonomy::where('group', 'topic')->where('type', 'user-status')
        //     ->whereIn('code', ['aprobado', 'realizado', 'revisado'])
        //     ->pluck('id')->toArray();

        $statuses_topic = $user_status->where('group', 'topic')->where('type', 'user-status')
        ->whereIn('code', ['aprobado', 'realizado', 'revisado'])
            ->pluck('id')->toArray();
        $medias = MediaTema::whereHas('topic', function($q) use ($courses) {
                $q->whereIn('course_id', $courses->pluck('id')->toArray());
            })
         ->get();
        foreach ($courses as $course) {
            $course_position = $positions_courses->where('course_id',$course->id)->first()?->position;
            // $compatible = $course->getCourseCompatibilityByUser($user);

            // UC rule
            $course_name = $course->name;
            // if ($workspace_id === 25 && $course_name) {
            //     $course_name = removeUCModuleNameFromCourseName($course_name);
            // }
            $max_attempts = $course->mod_evaluaciones['nro_intentos'];
            $course->poll_question_answers_count = $polls_questions_answers->where('course_id', $course->id)->first()?->count;
            $media_temas = $medias->whereIn('topic_id',$course->topics->pluck('id')) ?? [];
            $course_status = $course->compatible ? [] : Course::getCourseStatusByUser($user, $course,$summary_courses_compatibles,$media_temas,$statuses_courses);
            // $topics_data = [];
            $topics_data = collect();


            $topics = $course->topics->where('active', ACTIVE)->sortBy('position');

            $topics_count = count($topics);
            $summary_topics_user = $course->topics->pluck('summaries') ?? [];
            foreach ($topics as $topic) {

                // $media_topics = $topic->medias->sortBy('position')->values()->all();
                $media_topics = $media_temas->where('topic_id',$topic->id)->sortBy('position')->values()->all();
                $summary_topic =  $topic->summaries->first();
                // $summary_topic = $summary_topics_by_user->where('topic_id', $topic->id)
                // // ->where('user_id', $user->id)
                // ->first();

                $media_progress = !is_null($summary_topic?->media_progress) ? json_decode($summary_topic?->media_progress) : null;
                foreach ($media_topics as $media) {
                    unset($media->created_at, $media->updated_at, $media->deleted_at);
                    $media->full_path = !in_array($media->type_id, ['youtube', 'vimeo', 'scorm', 'link'])
                        ? route('media.download.media_topic', [$media->id]) : null;

                    $media->status_progress = 'por-iniciar';
                    $media->last_media_duration = null;

                    if(!is_null($media_progress)){
                        foreach($media_progress as $medp){
                            if($medp->media_topic_id == $media->id){
                                $media->status_progress = $medp->status;
                                $media->last_media_duration = $medp->last_media_duration;
                                break;
                            }
                        }
                    }
                }

                $media_embed = array();
                $media_not_embed = array();
                foreach ($media_topics as $med) {
                    if($med->embed)
                        array_push($media_embed, $med);
                    else
                        array_push($media_not_embed, $med);
                }


                $last_media_access = $summary_topic?->last_media_access;
                $last_media_duration = $summary_topic?->last_media_duration;

                $media_topic_progress = array('media_progress' => $media_progress,
                                            'last_media_access' => $last_media_access,
                                            'last_media_duration' => $last_media_duration);

                // if (true) {
                if ($course->compatible) {

                    $topics_data->push([
                        'id' => $topic->id,
                        'nombre' => $topic->name,
                        'requisito_id' => NULL,
                        'requirements' => NULL,
                        'imagen' => $topic->imagen,
                        'contenido' => $topic->content,
                        'media' => $media_embed,
                        'media_not_embed' => $media_not_embed,
                        'media_topic_progress'=>$media_topic_progress,
                        'evaluable' => 'no',
                        'tipo_ev' => NULL,
                        'nota' => '-',
                        'disponible' => true,
                        'intentos_restantes' => '-',
                        'estado_tema' => 'aprobado',
                        'estado_tema_str' => 'Convalidado',
                        'compatible' => true,
                        'mod_evaluaciones' => $course->getModEvaluacionesConverted($topic),
                    ]);

                    continue;
                }

                $topic_status = self::getTopicStatusByUser($topic, $user, $max_attempts,$statuses_topic);

                $topics_data->push([
                    'id' => $topic->id,
                    'nombre' => $topic->name,
                    'requisito_id' => $topic_status['topic_requirement'],
                    'requirements' => $topic_status['requirements'],
                    'imagen' => $topic->imagen,
                    'contenido' => $topic->content,
                    'media' => $media_embed,
                    'media_not_embed' => $media_not_embed,
                    'media_topic_progress'=>$media_topic_progress,
                    'evaluable' => $topic->assessable ? 'si' : 'no',
                    'tipo_ev' => $topic->evaluation_type->code ?? NULL,
                    'nota' => $topic_status['grade'],
                    'nota_sistema_nombre' => $topic_status['system_grade_name'] ?? NULL,
                    'nota_sistema_valor' => $topic_status['system_grade_value'] ?? NULL,
                    'disponible' => $topic_status['available'],
                    'intentos_restantes' => $topic_status['remaining_attempts'],
                    'estado_tema' => $topic_status['status'],
                    //                    'estado_tema_str' => $topic_status['status'],
                    'estado_tema_str' => $topic_status_arr[$topic_status['status']],
                    'mod_evaluaciones' => $course->getModEvaluacionesConverted($topic),
                    'tags' => $topic->tags->map( fn($t) => $t->taxonomy),
                    'review_all_duration_media' => boolval($topic->review_all_duration_media),
                    'open_evaluation_button' => $topic->open_evaluation_button
                        ?: 'Dar evaluación'
                ]);
            }

            $project = $projects->where('course_id',$course->id)->first();
            if($project){
                $status_project = $status_projects->where('project_id',$project->id)->where('user_id',$user->id)->first();
                $project->status = $status_project?->status?->name ?? 'Pendiente';
                $project->code = $status_project?->status?->code ?? 'pending';
                $project->available = $course_status['available'];
                $project->show_message = false;
                if(in_array($project->code,['observed','disapproved','passed'])){
                    $project->show_message = boolval($status_project->where('project_id',$project->id)->first()?->msg_to_user);
                }
                unset($project->course_id);
            }
            $requirement_list = null;
            $requirement_course = $course->requirements->first();

            if ($requirement_course) {

                $summary_requirement_course = $requirement_course->summaries_course->first();

                if (!$summary_requirement_course) {

                    $req_course = $requirement_course->model_course;

                    $req_course->loadMissing([
                        'summaries' => function ($q) use ($user) {
                            $q
                                ->with('status:id,name,code')
                                ->where('user_id', $user->id);
                        },
                        'compatibilities_a:id',
                        'compatibilities_b:id',
                    ]);

                    $compatible_course_req = $req_course->getCourseCompatibilityByUser($user,$summary_courses_compatibles);

                    if (!$compatible_course_req):
                        if($requirement_course?->requirement_id){
                            $req = $req_course;
                            // $req = Course::where('id',$requirement_course?->requirement_id)->first();

                            $available_course_req = true;

                            // if ($requirement_course_req) {

                            //     $summary_requirement_course_req = $requirement_course_req->summaries_course->where('user_id',$user->id)->first();

                            //     if (!$summary_requirement_course_req) {

                            //         $req_course_req = $requirement_course_req->model_course;

                            //         $req_course_req->loadMissing([
                            //             'summaries' => function ($q) use ($user) {
                            //                 $q
                            //                     ->with('status:id,name,code')
                            //                     ->where('user_id', $user->id);
                            //             },
                            //             'compatibilities_a:id',
                            //             'compatibilities_b:id',
                            //         ]);

                            //         $compatible_course_req_req = $req_course_req->getCourseCompatibilityByUser($user,$summary_courses_compatibles);

                            //         if (!$compatible_course_req_req):
                            //             $available_course_req = false;
                            //         endif;
                            //     }else{
                            //         try {
                                        // if(!in_array($summary_requirement_course_req?->status?->code,['aprobado', 'realizado', 'revisado'])){
                                        //     $available_course_req = false;
                                        // }
                            //         } catch (\Throwable $th) {
                            //             //throw $th;
                            //         }
                            //     }
                            // }

                            $req_school = $req->schools->first();
                            $course_name_req = $req->name;
                            // if ($workspace_id === 25 && $course_name_req) {
                            //     $course_name_req = removeUCModuleNameFromCourseName($course_name_req);
                            // }
                            $requirement_list = [
                                'id' => $requirement_course?->requirement_id,
                                'name' => $course_name_req,
                                'school_id' => $req_school?->id,
                                'disponible' => $available_course_req,
                                // 'ultimo_tema_visto' => ($req) ? self::ultimoTemaVisto($req, $user) : null,
                                'ultimo_tema_visto' => ($req) ? Course::ultimoTemaVisto($req, $user, $media_temas, $summary_topics_user) : null
                            ];
                        }
                    endif;

                }else{
                    try {
                        if(!in_array($summary_requirement_course?->status?->code,['aprobado', 'realizado', 'revisado'])){
                            if($requirement_course?->requirement_id){
                                $req = Course::where('id',$requirement_course?->requirement_id)->first();

                                $requirement_course_req = $req->requirements->first();
                                $available_course_req = true;

                                if ($requirement_course_req) {

                                    $summary_requirement_course_req = $requirement_course_req->summaries_course->where('user_id',$user->id)->first();

                                    if (!$summary_requirement_course_req) {

                                        $req_course_req = $requirement_course_req->model_course;

                                        $req_course_req->loadMissing([
                                            'summaries' => function ($q) use ($user) {
                                                $q
                                                    ->with('status:id,name,code')
                                                    ->where('user_id', $user->id);
                                            },
                                            'compatibilities_a:id',
                                            'compatibilities_b:id',
                                        ]);

                                        $compatible_course_req_req = $req_course_req->getCourseCompatibilityByUser($user,$summary_courses_compatibles);

                                        if (!$compatible_course_req_req):
                                            $available_course_req = false;
                                        endif;
                                    }else{
                                        try {
                                            if(!in_array($summary_requirement_course_req?->status?->code,['aprobado', 'realizado', 'revisado'])){
                                                $available_course_req = false;
                                            }
                                        } catch (\Throwable $th) {
                                            //throw $th;
                                        }
                                    }
                                }

                                $req_school = $req->schools->first();
                                $course_name_req = $req->name;
                                // if ($workspace_id === 25 && $course_name_req) {
                                //     $course_name_req = removeUCModuleNameFromCourseName($course_name_req);
                                // }
                                $requirement_list = [
                                    'id' => $requirement_course?->requirement_id,
                                    'name' => $course_name_req,
                                    'school_id' => $req_school?->id,
                                    'disponible' => $available_course_req,
                                    // 'ultimo_tema_visto' => ($req) ? self::ultimoTemaVisto($req, $user) : null
                                    'ultimo_tema_visto' => ($req) ?  Course::ultimoTemaVisto($req, $user, $media_temas, $summary_topics_user) : null
                                ];
                            }
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }

            // if (true) {
            if ($course->compatible) {

                $schools_courses->push([
                    'id' => $course->id,
                    'orden' => $course_position,
                    'nombre' => $course_name,
                    'descripcion' => $course->description,
                    'imagen' => $course->imagen,
                    'requisito_id' => NULL,
                    'c_evaluable' => 'no',
                    'disponible' => true,
                    'status' => 'aprobado',

                    'encuesta' => false,
                    'encuesta_habilitada' => false,
                    'encuesta_resuelta' => false,
                    'encuesta_id' => null,

                    'temas_asignados' => $topics_count,
                    'temas_completados' => $topics_count,

                    'porcentaje' => '100.00',
                    'temas' => $topics_data,
                    'mod_evaluaciones' => NULL,
                    'compatible' => [
                        'id' => $course->compatible->course->id ?? 'X',
                        'name' => $course->compatible->course->name ?? 'TEST DEFAULT COMPATIBLE',
                    ],
                    'tarea' => $project,
                ]);

                continue;
            }

            // Check whether 'registro capacitacion' is enabled for course

            $registroCapacitacionIsActive = $course->registroCapacitacionIsActive();

            // Get 'registro capacitacion' file

            $registroCapacitacionPath = null;
            $registroCapacitacionUrl = null;
            $summary = $summaryCourses->where('course_id', $course->id)->first();
            if ($summary) {
                $registroCapacitacionPath = $summary->registro_capacitacion_path;
                $registroCapacitacionUrl = $registroCapacitacionPath
                    ? Course::generateRegistroCapacitacionURL($registroCapacitacionPath)
                    : null;
            }


            $schools_courses->push([
                'id' => $course->id,
                'orden' => $course_position,
//                'nombre' => $course->name,
                'nombre' => $course_name,
                'descripcion' => $course->description,
                'imagen' => $course->imagen,
                'requisito_id' => $course->require,
                'requirements' => $requirement_list,
                'c_evaluable' => $course->c_evaluable,
                'disponible' => $course_status['available'],
                'status' => $course_status['status'],
                'encuesta' => $course_status['available_poll'],
                'encuesta_habilitada' => $course_status['enabled_poll'],
                'registro_capacitacion_is_active' => $registroCapacitacionIsActive,
                'registro_capacitacion_path' => $registroCapacitacionPath,
                'registro_capacitacion_url' => $registroCapacitacionUrl,
                'encuesta_resuelta' => $course_status['solved_poll'],
                'encuesta_id' => $course_status['poll_id'],
                'temas_asignados' => $course_status['exists_summary_course'] ?
                    $course_status['assigned_topics'] :
                    $topics_count,
                'temas_completados' => $course_status['completed_topics'],
                'porcentaje' => $course_status['progress_percentage'],
                'temas' => $topics_data,
                'mod_evaluaciones' => $course->getModEvaluacionesConverted(),
                'tarea' => $project,
                'scheduled_activation' => [
                    'message' => $course->deactivate_at ?
                                    'Disponible hasta el ' . Carbon::parse($course->deactivate_at)->format('d-m-Y')
                                    : null,
                ],
                // 'mod_evaluaciones' => $course->mod_evaluaciones
            ]);
        }
        $schools_courses = $schools_courses->toArray();
        $columns = array_column($schools_courses, 'orden');
        array_multisort($columns, SORT_ASC, $schools_courses);
        return [
            'id' => $school?->id,
//            'nombre' => $school?->name,
            'nombre' => $school_name,
            'cursos' => $schools_courses
        ];
    }

    public function getTopicStatusByUser(Topic $topic, User $user, $max_attempts,$statuses)
    {
        $grade = 0;
        $available_topic = false;
        $remaining_attempts = $max_attempts;
        // $summary_topic = $topic->summaryByUser($user->id);
//        $summary_topic = SummaryTopic::getCurrentRow($topic, $user);
        $summary_topic = $topic->summaries->first();
        $last_topic_reviewed = null;
        // $topic_status = 'por-iniciar';
        $topic_status = $summary_topic->status->code ?? 'por-iniciar';

        // $statuses = Taxonomy::where('group', 'topic')->where('type', 'user-status')
        //     ->whereIn('code', ['aprobado', 'realizado', 'revisado'])
        //     ->pluck('id')->toArray();

        if ($topic->assessable && $topic->evaluation_type->code === 'qualified') {
            if ($summary_topic) {
                // $topic_status = $summary_topic->passed ? 'aprobado' : 'desaprobado';
                $grade = $summary_topic->grade;
                $sub = $max_attempts - $summary_topic->attempts;
                $remaining_attempts = max($sub, 0);
            }
        }

//        $topic_requirement = $topic->requirements()->first();
        $topic_requirement = $topic->requirements->first();

        $available_topic_req = false;
        if (!$topic_requirement) {
            $available_topic = true;
        } else {
            // $summary_requirement_topic = SummaryTopic::with('status:id,name,code')
            //     ->where('user_id', $user->id)
            //     ->where('topic_id', $topic_requirement->requirement_id)
            //     ->first();
            $summary_requirement_topic = $topic_requirement->summaries_topics->first();
//            $activity_requirement = in_array($summary_requirement_topic?->status?->code, ['aprobado', 'realizado', 'revisado']);
            $activity_requirement = in_array($summary_requirement_topic?->status_id, $statuses);
            $test_requirement = $summary_requirement_topic?->result == 1;
            if($topic->type_requirement == 'requirement-inverse' && ($summary_requirement_topic?->result == 1 || $summary_requirement_topic?->result == 0)){
                $available_topic = true;
            }
            if ($activity_requirement || $test_requirement){
                $available_topic = true;
            }
        }

        $topic_req_name = null;
        if($topic_requirement?->requirement_id){
            // $topic_req = Topic::where('id', $topic_requirement?->requirement_id)->first();
            // dd($topic_req,$topic->requirements->first());
            $topic_req_name = $topic_requirement?->model_topic?->name;
            // $topic_req_status = self::getTopicProgressByUser($user,$topic_req);
            $topic_req_status = $topic_requirement->summaries?->status->code ?? 'por-iniciar';
            // $available_topic_req = !($topic_req_status['status'] == 'bloqueado');
            $available_topic_req = !($topic_req_status == 'bloqueado');
        }

        return [
            //            'topic_name' => $topic->name,
            'requirements' => ($topic_requirement) ? [
                'id' => $topic_requirement?->requirement_id,
                'name' => $topic_req_name,
                'disponible' => $available_topic_req
                ] : null,
            'status' => $topic_status,
            'topic_requirement' => $topic_requirement?->id,
            'grade' => calculateValueForQualification($grade, $topic->qualification_type?->position),
            'system_grade_name' => $topic->qualification_type?->name,
            'system_grade_value' => $topic->qualification_type?->position,
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

    protected function evaluateAnswers2($respuestas, $topic) {
        $questions = Question::select('id', 'rptas_json', 'pregunta','rpta_ok', 'score')->where('topic_id', $topic->id)->get();

        $question_results = [];

        foreach ($respuestas as $key => $respuesta) {

            $question = $questions->where('id', $respuesta['preg_id'])->first();
            $esCorrecto = ($question->rpta_ok == $respuesta['opc']);

            if ($question) {
                $question_results[] = [
                    'pregunta' => $question->pregunta,
                    'esCorrecto' => $esCorrecto,
                    'opcion_usuario' => ($respuesta['opc']) ?
                                        $question->rptas_json[$respuesta['opc']] : ''
                ];
                /*
                info([
                    'pregunta' => $question->pregunta,
                    'esCorrecto' => $esCorrecto,
                    'opcion_usuario' => $question->rptas_json
                ]);*/
            }
        }
        return $question_results;
    }

    protected function getTopicProgressByUser($user, Topic $topic)
    {
        $topic_grade = null;
        $available_topic = true;
        $topic_requirement = $topic->requirements->first();

        if ($topic_requirement) :

            $requirement_summary = $topic_requirement->summaries_topics->where('user_id',$user->id)->first();

            $available_topic = $requirement_summary && in_array($requirement_summary?->status?->code, ['aprobado', 'realizado', 'revisado']);
        endif;

        $summary_topic = $topic->summaries->where('user_id',$user->id)->first();

        if ($topic->evaluation_type?->code === 'qualified' && $summary_topic)
            $topic_grade = $summary_topic->grade;

        $topic_status = $summary_topic?->status?->code ?? 'por-iniciar';

        if (!$available_topic) {
            $topic_status = 'bloqueado';
        }

        return [
            'available' => $available_topic,
            'views' => $summary_topic->views ?? null,
            'last_time_evaluated_at' => ($summary_topic && $summary_topic->last_time_evaluated_at) ? $summary_topic->last_time_evaluated_at->format('d/m/Y H:i') : null,
            'total_attempts' => $summary_topic->total_attempts ?? $summary_topic->attempts ?? null,
            'answers' => $summary_topic->answers ?? [],
            'grade' => calculateValueForQualification($topic_grade, $topic->qualification_type?->position),
            'status' => $topic_status,
            // 'grade' => $topic_grade,
        ];
    }

    protected function getCounter($topic)
    {
        $topic->load('course.schools');

        $user = auth()->user()->load('subworkspace');

        $row = SummaryTopic::getCurrentRow($topic, $user);

        $counter = false;

        if ($row and $row->hasFailed() and $row->hasNoAttemptsLeft(null, $topic->course)) {

            $times = [];

            if ($topic->course->scheduled_restarts)
                $times[] = $topic->course->scheduled_restarts;

            // if ($topic->course->reinicios_programado)
            //     $times[] = $topic->course->reinicios_programado;

            // if ($user->subworkspace->reinicios_programado)
            //     $times[] = $user->subworkspace->reinicios_programado;

            if (count($times) > 0) {

                $scheduled = false;
                $minutes = 0;

                foreach ($times as $time) {

                    if ($time['activado']) {

                        $scheduled = true;
                        $minutes = $time['tiempo_en_minutos'];

                        break;
                    }
                }

                if ($scheduled and $row->last_time_evaluated_at) {

                    $finishes_at = $row->last_time_evaluated_at->addMinutes($minutes);
                    $counter = $finishes_at->diff(now())->format('%y/%m/%d %H:%i:%s');
                }
            }
        }

        return $counter;
    }

    protected function ultimoTemaVisto( Course $curso, User $user ): array
    {
        $topics_user = $curso->topics->pluck('id')->toArray();

        $topics = $curso->topics->sortBy('position')->where('active', ACTIVE);
        $summary_topics = SummaryTopic::whereIn('topic_id',$topics_user)->where('user_id',$user->id)->get();

        $last_topic = null;
        if ($summary_topics->count() > 0) {
            foreach ($topics as $topic) {
                $topics_view = $summary_topics->where('topic_id', $topic->id)->first();
                $last_item = ($topic->id == $topics->last()->id);
                if ($topics_view?->views) {
                    $passed_tests = $summary_topics->where('topic_id', $topic->id)->where('passed', 1)->first();
                    if ($topic->evaluation_type?->code == 'qualified' && $passed_tests && !$last_item) continue;
                    $last_topic = ($topic->id);
                    break;
                }
                if (is_null($last_topic) && $last_item) {
                    $last_topic = $topic->id;
                    break;
                }
            }
        }

        $last_topic_reviewed = $last_topic ?? $topics->first()->id ?? null;

        $media_topics = MediaTema::where('topic_id',$last_topic_reviewed)->orderBy('position')->get();

        $summary_topic = SummaryTopic::select('id','media_progress','last_media_access','last_media_duration')
        ->where('topic_id', $last_topic_reviewed)
        ->where('user_id', $user->id)
        ->first();

        $media_progress = !is_null($summary_topic?->media_progress) ? json_decode($summary_topic?->media_progress) : null;

        foreach ($media_topics as $media) {
            unset($media->created_at, $media->updated_at, $media->deleted_at);
            $media->full_path = !in_array($media->type_id, ['youtube', 'vimeo', 'scorm', 'link'])
                ? route('media.download.media_topic', [$media->id]) : null;

            $media->status_progress = 'por-iniciar';
            $media->last_media_duration = null;

            if(!is_null($media_progress)){
                foreach($media_progress as $medp){
                    if($medp->media_topic_id == $media->id){
                        $media->status_progress = $medp->status;
                        $media->last_media_duration = $medp->last_media_duration;
                        break;
                    }
                }
            }
        }

        $last_media_access = null;
        $last_media_duration = null;
        $last_media_status = null;

        if($media_topics){
            foreach($media_topics as $medt){
                if($medt->status_progress == 'iniciado'){
                    $last_media_access = $medt->id;
                    $last_media_status = $medt->status_progress;
                    $last_media_duration = $medt->last_media_duration;
                    break;
                }
                else if($medt->status_progress == 'por-iniciar'){
                    $last_media_access = $medt->id;
                    $last_media_status = $medt->status_progress;
                    $last_media_duration = $medt->last_media_duration;
                    break;
                }
            }
        }

        return [
            'id' => $last_topic_reviewed,
            'last_media_access' => $last_media_access,
            'last_media_status' => $last_media_status,
            'last_media_duration' => $last_media_duration
        ];
    }
    public function generateMediaUrl($type, $value) {
        switch ($type) {
            case 'youtube':
                return "https://www.youtube.com/embed/{$value}?rel=0&amp;modestbranding=1&amp;showinfo=0";
            case 'vimeo':
                return "https://player.vimeo.com/video/{$value}";
            case 'video':
            case 'audio':
            case 'h5p':
            case 'pdf':
            case 'office':
                return get_media_url($value, 's3');
            case 'image':
                return get_media_url($value, 's3');
            default:
                return $value;
        }
    }

    public function isAccessiblePoll(){
        $topic = $this;
        $is_accessible = false;
        if($topic && isset($topic->modality_in_person_properties->poll_started)){
            $is_accessible = $topic->modality_in_person_properties->poll_started;
        }
        return $is_accessible;
    }

    public function isAccessibleEvaluation(){
        $topic = $this;
        $is_accessible = false;
        if(isset($topic->modality_in_person_properties->evaluation->status)){
            $evaluationStatus = $topic->modality_in_person_properties->evaluation->status;
            $evaluationFinishDate = $topic->modality_in_person_properties->evaluation->date_finish;
            $is_accessible = $evaluationStatus == 'started';
            if(Carbon::now()->format('Y-m-d H:i:s') >= $evaluationFinishDate){
                $is_accessible = false;
            }
            if( $evaluationStatus == 'extra-time'){
                $is_accessible = true;
            }
        }
        return $is_accessible;
    }
    protected function validateAvaiableAccount($course,$data,$topic=null,$validate_before_create=false){
        $is_avaiable = true;
        if($course->modality->code == 'virtual'){

            $type = Taxonomy::select('id')->where('group','meeting')->where('type','type')->where('code','room')->first();
            $modality_in_person_properties = $data['modality_in_person_properties'];
            $start_datetime = Carbon::parse($modality_in_person_properties['start_date'].' '.$modality_in_person_properties['start_time']);
            $finish_datetime = Carbon::parse($modality_in_person_properties['start_date'].' '.$modality_in_person_properties['finish_time']);
            $starts_at = $start_datetime->format('Y-m-d H:i:s');
            $finishes_at = $finish_datetime->format('Y-m-d H:i:s');
            $meeting = null;
            // if($validate_before_create){
            //     Topic::where('starts_at', '<=', $dates['finishes_at'])->where('finishes_at', '>=', $dates['starts_at']);
            // }
            if($topic){
                $meeting = Meeting::where('model_type','App\\Models\\Topic')->where('model_id',$topic->id)->first();
                if($meeting and !$meeting->datesHaveChanged(compact('starts_at','finishes_at'))){
                   return true;
                }
            }
            $account = Account::getOneAvailableForMeeting($type, compact('starts_at','finishes_at'),$meeting);
            $is_avaiable = boolval($account);
        }
        return $is_avaiable;
    }
    public function isAccessibleMultimedia(){
        $topic = $this;
        $avaiable_to_show_resources = $topic->modality_in_person_properties->show_medias_since_start_course;
        if(!$avaiable_to_show_resources){
            $current_time = Carbon::now();
            $datetime = Carbon::parse($topic->modality_in_person_properties->start_date.' '.$topic->modality_in_person_properties->finish_time);
            $avaiable_to_show_resources = $current_time>=$datetime;
        }
        return $avaiable_to_show_resources;
    }
}
