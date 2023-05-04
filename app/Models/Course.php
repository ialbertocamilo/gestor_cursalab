<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Course extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'imagen', 'plantilla_diploma', 'external_code', 'slug',
        'assessable', 'freely_eligible', 'type_id',
        'scheduled_restarts', 'active',
        'duration', 'investment', 'mod_evaluaciones',
        'show_certification_date'
    ];

    protected $casts = [
        'mod_evaluaciones' => 'array',
        'scheduled_restarts' => 'array',
        'show_certification_date' => 'boolean',
    ];

    public function schools()
    {
        return $this->belongsToMany(School::class);
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class, 'course_id');
    }

    public function polls()
    {
        return $this->belongsToMany(Poll::class);
    }

    // public function requirement()
    // {
    //     return $this->belongsToMany(Course::class);
    // }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_relationships', 'course_id', 'checklist_id');
    }

    public function update_usuarios()
    {
        return $this->hasMany(Update_usuarios::class, 'curso_id');
    }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function models()
    {
        return $this->morphMany(Requirement::class, 'model');
    }

    public function requirements()
    {
        return $this->morphMany(Requirement::class, 'model');
    }

    public function summaries()
    {
        return $this->hasMany(SummaryCourse::class);
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function compatibilities_a()
    {
        return $this->belongsToMany(Course::class, 'compatibilities', 'course_a_id', 'course_b_id');
    }

    public function compatibilities_b()
    {
        return $this->belongsToMany(Course::class, 'compatibilities', 'course_b_id', 'course_a_id');
    }

    public function getCompatibilities()
    {
        // return $this->belongsToMany(Course::class, 'compatibilities', 'course_a_id', 'course_b_id');
        // info('this->compatibilities_a');
        // info($this->compatibilities_a);

        // info('this->compatibilities_b');
        // info($this->compatibilities_b);

        return $this->compatibilities_a->merge($this->compatibilities_b);
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1') ? 1 : 0;
    }

    public function scopeActive($q, $active)
    {
        return $q->where('active', $active);
    }

    public function scopeFiltroName($q, $filtro)
    {
        return $q->where('name', 'like', "%$filtro%");
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
        $workspace = get_current_workspace();

        $q = Course::query()
            ->with('schools.subworkspaces')
            // ->with('segments.values', function ($q) {
            //     $q
            //         ->withWhereHas('criterion_value', function ($q) {
            //             $q
            //                 ->select('id', 'value_text', 'criterion_id')
            //                 ->whereRelation('criterion', 'code', 'module');
            //         })
            //         ->select('id', 'segment_id', 'criterion_id', 'criterion_value_id')
            //         ->whereRelation('criterion', 'code', 'module');
            // })
            ->whereHas('workspaces', function ($t) use ($workspace) {
                $t->where('workspace_id', $workspace->id);
            });

        if ($request->school_id) {
            $q->whereHas('schools', function ($t) use ($request) {
                $t->where('school_id', $request->school_id);
            })->when($request->canChangePosition ?? null, function ($q) use ($request) {
                $q->join('course_school as cs','cs.course_id','courses.id')->where('cs.school_id',$request->school_id);
            });
        }

        $q->withCount(['topics', 'polls', 'segments', 'type', 'compatibilities_a', 'compatibilities_b']);

        if ($request->schools) {
            $q->whereHas('schools', function ($t) use ($request) {
                $t->whereIn('school_id', $request->schools);
            })->when($request->canChangePosition ?? null, function ($q) use ($request) {
                $q->join('course_school as cs','cs.course_id','courses.id')->whereIn('cs.school_id', $request->schools);
            });
        }

        if ($request->segmented_module) {

            $module_value = $request->segmented_module;

            $q->whereHas('schools.subworkspaces', function ($q) use ($module_value) {
                $q->where('id', $module_value);
            });

        }

        if ($request->q)
            $q->where('name', 'like', "%$request->q%");

        if ($request->type)
            $q->where('type_id', $request->type);

        if ($request->active == 1)
            $q->where('active', ACTIVE);

        if ($request->active == 2)
            $q->where('active', '<>', ACTIVE);

        if ($request->dates) {

            if (isset($request->dates[0]))
                $q->whereDate('created_at', '>=', $request->dates[0]);

            if (isset($request->dates[1]))
                $q->whereDate('created_at', '<=', $request->dates[1]);
        }

        // if (!is_null($request->sortBy)) {
        //     $field = $request->sortBy ?? 'position';
        //     $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        //     $q->orderBy($field, $sort);
        // } else {
        //     $q->orderBy('created_at', 'DESC');
        // }
        if(!$request->canChangePosition){
            // $field = $request->sortBy == 'orden' ? 'position' : $request->sortBy;
    
            $field = $field ?? 'created_at';
            $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
    
            $q->orderBy($field, $sort);
        }else{
            $q->addSelect('cs.position as course_position')->orderBy('course_position', 'ASC')->groupBy('courses.id');
        }

        return $q->paginate($request->paginate);
    }

    protected function storeRequest($data, $course = null)
    {
        try {
            $workspace = get_current_workspace();

            DB::beginTransaction();

            $data['scheduled_restarts'] = $data['reinicios_programado'];

            if ($course) :

                $course->update($data);

                // TODO: Compatibles: Si se cambia el estado del curso
                if ($course->wasChanged('active')):

                    $course->updateOnModifyingCompatibility();

                endif;

            else:

                $course = self::create($data);
                $course->workspaces()->sync([$workspace->id]);
                foreach ($data['escuelas'] as  $escuela) {
                    SortingModel::setLastPositionInPivotTable(CourseSchool::class,Course::class,[
                        'school_id' => $escuela,
                        'course_id'=>$course->id,
                    ],[
                        'school_id'=>$escuela,
                    ]);
                }
            endif;

            if ($data['requisito_id']) :
                Requirement::updateOrCreate(
                    ['model_type' => Course::class, 'model_id' => $course->id,],
                    ['requirement_type' => Course::class, 'requirement_id' => $data['requisito_id']]
                );

            else :

                $course->requirements()->delete();

            endif;


            $course->schools()->sync($data['escuelas']);

            // $course->compatibilities()->sync($data['compatibilities'] ?? []);

            // Generate code when is not defined

            if (!$course->code) {
                $course->code = 'C' . str_pad($course->id, 2, '0', STR_PAD_LEFT);
                $course->save();
            }

            // $course->save();

            DB::commit();
        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }

        cache_clear_model(School::class);
        cache_clear_model(Requirement::class);

        return $course;
    }

    protected function validateBeforeUpdate(array $data, School $school, Course $course)
    {
        $validations = collect();

        $is_required_course = $this->checkIfIsRequiredCourse($data, $school, $course);
        if ($is_required_course['ok']) $validations->push($is_required_course);

        $has_active_topics = $this->hasActiveTopics($data, $school, $course);
        if ($has_active_topics['ok']) $validations->push($has_active_topics);


        $show_confirm = !($validations->where('show_confirm', false)->count() > 0);

        return [
            'list' => $validations->toArray(),
            'title' => !$show_confirm ? 'Alerta' : 'Tener en cuenta',
            'show_confirm' => $show_confirm,
            'type' => 'validations-before-update'
        ];
    }

    protected function checkIfIsRequiredCourse(array $data, School $school, Course $course, $verb = 'inactivar')
    {
        $requirements_of = Requirement::whereHasMorph('requirement', [Course::class], function ($query) use ($course) {
            $query->where('id', $course->id);
        })->get();
        $is_required_course = $requirements_of->count() > 0;
        $will_be_deleted = $data['to_delete'] ?? false;
        $will_be_inactivated = $data['active'] === false;
        $temp['ok'] = (($will_be_inactivated || $will_be_deleted) && $is_required_course);

        if (!$temp['ok']) return $temp;

        $temp['title'] = "No se puede {$verb} el curso.";
        $temp['subtitle'] = "Para poder {$verb} el curso es necesario quitarlo como requisito de los siguientes cursos:";
        $temp['show_confirm'] = false;
        $temp['type'] = 'check_if_is_required_course';
        $temp['list'] = [];

        foreach ($requirements_of as $requirement) {
            $requisito = Course::find($requirement->model_id);
            $route = route('cursos.editCurso', [$school->id, $requirement->model_id]);
            $temp['list'][] = "<a href='{$route}'>" . $requisito->name . "</a>";
        }

        return $temp;
    }

    public function hasActiveTopics($data, School $school, Course $course, $verb = 'inactivar')
    {
        $will_be_inactivated = $data['active'] === false;
        $has_active_topics = $course->topics->where('active', ACTIVE)->count() > 0;
        $temp['ok'] = $will_be_inactivated && $has_active_topics;

        if (!$temp['ok']) return $temp;

        $temp['title'] = "Tener en cuenta que al {$verb} el curso.";
        $temp['subtitle'] = "Los siguientes temas también se {$verb}án:";
        $temp['show_confirm'] = true;
        $temp['type'] = 'has_active_topics';
        $temp['list'] = [];

        foreach ($course->topics as $topic) {
            $route = route('temas.editTema', [$school->id, $course->id, $topic->id]);
            $temp['list'][] = "<a href='{$route}' target='_blank'>" . $topic->name . "</a>";
        }

        return $temp;
    }

    protected function getMessagesAfterUpdate(Course $course, $title)
    {
        $messages = collect();

        $messages_on_update_status = $this->getMessagesOnUpdateStatus($course);

        if ($messages_on_update_status['ok']) $messages->push($messages_on_update_status);

        return [
            'list' => $messages->toArray(),
            'title' => $title,
            'type' => 'validations-after-update'
        ];
    }

    public function getMessagesOnUpdateStatus($course)
    {
//        dd($course->wasChanged('active'), $course->wasChanged('active') && $course->active === 1);
        $temp['ok'] = ($course->wasChanged('active') and $course->active === 1) and $course->topics->count() > 0;

        if (!$temp['ok']) return $temp;

        $temp['title'] = null;
        $temp['subtitle'] = "Esto puede producir un ajuste en el avance de los usuarios. Los cambios se mostrarán en el app y web en unos minutos.";
        $temp['show_confirm'] = true;
        $temp['type'] = 'update_message_on_update';

        return $temp;
    }

    protected function validateBeforeDelete($data, School $school, Course $course)
    {
        $validations = collect();

        $is_required_course = $this->checkIfIsRequiredCourse(['to_delete' => true, 'active' => false], $school, $course, verb: 'eliminar');
        if ($is_required_course['ok']) $validations->push($is_required_course);

        $has_active_topics = $this->hasActiveTopics($data, $school, $course, verb: 'eliminar');
        if ($has_active_topics['ok']) $validations->push($has_active_topics);

        $show_confirm = !($validations->where('show_confirm', false)->count() > 0);

        return [
            'list' => $validations->toArray(),
            'title' => !$show_confirm ? 'Alerta' : 'Tener en cuenta',
            'show_confirm' => $show_confirm,
            //            'show_confirm' => true,
            'type' => 'validations-before-update'
        ];
    }

    protected function getMessagesAfterDelete(Course $course)
    {
        $messages = collect();

        $show_confirm = false;

        return [
            'list' => $messages->toArray(),
            'title' => !$show_confirm ? 'Alerta' : 'Tener en cuenta',
            'show_confirm' => $show_confirm,
            'type' => 'validations-before-update'
        ];
    }

    public function avisoAllTemasInactive($escuela, $curso)
    {
        $temp = [
            'title' => "Tener en cuenta",
            'subtitle' => null,
            'show_confirm' => true,
            'type' => 'all_temas_inactive_notice'
        ];
        $list[] = "Todos los temas del curso: <strong> {$curso->nombre}</strong>, se encuentra inactivos.";
        $temp['list'] = $list;
        return $temp;
    }

    protected function getModEval($course, $value = '')
    {
        // $value could be nro_intentos,nota_aprobatoria
        //variable course can be course instance or course_id
        if ($course instanceof Course && !isset($course->mod_evaluaciones) && isset($course->id)) {
            $course = Course::select('mod_evaluaciones')->where('id', $course->id)->first();
        } else if (is_int($course)) {
            $course = Course::select('mod_evaluaciones')->where('id', $course)->first();
        }
        if ($value) {
            return isset($course->mod_evaluaciones[$value]) ? $course->mod_evaluaciones[$value] : null;
        }
        return isset($course->mod_evaluaciones) ? $course->mod_evaluaciones : null;
    }

    protected function getDataToCoursesViewAppByUser($user, $user_courses): array
    {
        // $workspace_id = auth()->user()->subworkspace->parent_id;
        $workspace_id = $user->subworkspace->parent_id;
        $schools = $user_courses->groupBy('schools.*.id');
        $summary_topics_user = SummaryTopic::whereHas('topic.course', function ($q) use ($user_courses) {
            $q->whereIn('id', $user_courses->pluck('id'))->where('active', ACTIVE)->orderBy('position');
        })->with('status:id,code')
            ->where('user_id', $user->id)
            ->get();

        $polls_questions_answers = PollQuestionAnswer::select(DB::raw("COUNT(course_id) as count"), 'course_id')
            ->whereIn('course_id', $user_courses->pluck('id'))
            ->where('user_id', $user->id)
            ->groupBy('course_id')
            ->get();

        $data = [];
        $positions_schools = SchoolSubworkspace::select('school_id','position')
                                ->where('subworkspace_id',$user->subworkspace_id)
                                ->whereIn('school_id',array_keys($schools->all()))
                                ->get();
       
        $positions_courses = CourseSchool::select('school_id','course_id','position')
                                ->whereIn('school_id',array_keys($schools->all()))
                                ->whereIn('course_id',$user_courses->pluck('id'))
                                ->get();
                                
        foreach ($schools as $school_id => $courses) {

            $school = $courses->first()->schools->where('id', $school_id)->first();
            $school_position = $positions_schools->where('school_id', $school_id)->first()?->position;
            $school_courses = [];
            $school_completed = 0;
            $school_assigned = 0;
            $school_percentage = 0;
            $last_course_reviewed = null;
            $last_school_courses = [];

            $courses = $courses->sortBy('position');

            foreach ($courses as $course) {
                $course_position = $positions_courses->where('school_id', $school_id)->where('course_id',$course->id)->first()?->position;

                $course->poll_question_answers_count = $polls_questions_answers->where('course_id', $course->id)->first()?->count;
                $school_assigned++;
                $last_topic = null;
                $course_status = self::getCourseStatusByUser($user, $course);
                if ($course_status['status'] == 'completado') $school_completed++;

                $topics = $course->topics->sortBy('position')->where('active', ACTIVE);
                $summary_topics = $summary_topics_user->whereIn('topic_id', $topics->pluck('id'));
                $before_topic=null;
                if ($summary_topics->count() > 0) {
                    foreach ($topics as $topic) {
                        $topics_view = $summary_topics->where('topic_id', $topic->id)->first();
                        $topic_requirement = $topic->requirements->first();
                        if($topic_requirement){
                            $requirement_summary = $summary_topics->where('topic_id',$topic_requirement?->requirement_id)->first();
                            $available_topic = $requirement_summary && in_array($requirement_summary?->status?->code, ['aprobado', 'realizado', 'revisado']);
                            if(!$available_topic){
                                $last_topic = ($before_topic?->id);
                                break;
                            }
                        }
                        $before_topic=$topic;
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
                // UC rule
                $course_name = $course->name;
                $tags = [];
                if ($workspace_id === 25) {
                    $course_name = removeUCModuleNameFromCourseName($course_name);
                    $tags = $this->getCourseTagsToUCByUser($course, $user);
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

                if (is_null($last_course_reviewed) && $course_status['status'] != 'completado') {
                    $last_course_reviewed = [
                        'id' => $course->id,
                        'nombre' => $course_name,
                        'imagen' => $course->imagen,
                        'porcentaje' => $course_status['progress_percentage'],
                        'ultimo_tema_visto' => $last_topic_reviewed,
                    ];
                }

                if ($course_status['status'] != 'completado' && $course_status['status'] != 'bloqueado') {
                    $last_school_courses[] = [
                        'id' => $course->id,
                        'nombre' => $course_name,
                        'imagen' => $course->imagen,
                        'porcentaje' => $course_status['progress_percentage'],
                        // 'ultimo_tema_visto' => $last_topic_reviewed,
                        'ultimo_tema_visto' => [
                            'id' => $last_topic_reviewed,
                            'last_media_access' => $last_media_access,
                            'last_media_status' => $last_media_status,
                            'last_media_duration' => $last_media_duration
                        ],
                    ];
                }

                if ($course->compatible) {

                    $school_courses[] = [
                        'id' => $course->id,
                        'nombre' => $course_name,
                        'descripcion' => $course->description,
                        'orden' => $course_position,
                        'imagen' => $course->imagen,
                        'requisito_id' => NULL,
                        'c_evaluable' => 0,
                        'disponible' => true,
                        'status' => 'aprobado',
                        'requirements' => null,

                        'encuesta' => false,
                        'encuesta_habilitada' => false,
                        'encuesta_resuelta' => false,
                        'encuesta_id' => null,

                        'temas_asignados' => $topics->count(),
                        'temas_completados' => $topics->count(),

                        'porcentaje' => '100.00',
                        'ultimo_tema_visto' => $last_topic_reviewed,
                        'compatible' => $course->compatible?->course->only('id', 'name') ?: null,
                    ];
                }
                else
                {
                    $school_courses[] = [
                        'id' => $course->id,
                        'nombre' => $course_name,
                        'descripcion' => $course->description,
                        'orden' => $course_position,
                        'imagen' => $course->imagen,
                        'c_evaluable' => $course->assessable,
                        'disponible' => $course_status['available'],
                        'status' => $course_status['status'],
                        'requirements' => $course_status['requirements'],
                        'encuesta' => $course_status['available_poll'],
                        'encuesta_habilitada' => $course_status['enabled_poll'],
                        'encuesta_resuelta' => $course_status['solved_poll'],
                        'encuesta_id' => $course_status['poll_id'],
                        'temas_asignados' => $course_status['exists_summary_course'] ?
                            $course_status['assigned_topics']
                            : $topics->count(),
                        'temas_completados' => $course_status['completed_topics'],
                        'porcentaje' => $course_status['progress_percentage'],
                        'tags' => $tags,
                        'ultimo_tema_visto' => $last_topic_reviewed,
                        'compatible' => $course->compatible?->course->only('id', 'name') ?: null,
                    ];
                }
            }

            if ($school_completed > 0) :
                $school_status = $school_completed >= $school_assigned ? 'Aprobado' : 'Desarrollo';
                $school_percentage = ($school_completed / $school_assigned) * 100;
            else :
                $school_status = 'Pendiente';
            endif;
            $school_percentage = round($school_percentage);

            // UC
            $school_name = $school->name;
            if ($workspace_id === 25) {
                $school_name = removeUCModuleNameFromCourseName($school_name);
            }
            $columns = array_column($school_courses, 'orden');
            array_multisort($columns, SORT_ASC, $school_courses);
            $data[] = [
                'categoria_id' => $school->id,
//                'categoria' => $school->name,
                'categoria' => $school_name,
                'completados' => $school_completed,
                'asignados' => $school_assigned,
                'porcentaje' => $school_percentage,
                'estado' => $school_status,
                'ultimo_curso' => $last_course_reviewed,
                'ultimos_cursos' => $last_school_courses,
                'orden' => $school_position,
                "cursos" => $school_courses,
            ];
        }

        $columns = array_column($data, 'orden');
        array_multisort($columns, SORT_ASC, $data);

        return $data;
    }

    protected function getCourseStatusByUser(User $user, Course $course): array
    {
//        if ($course->compatible)
//            dd($course->compatible);
        $workspace_id = $user->subworkspace->parent_id;

        $course_progress_percentage = 0.00;
        $status = 'por-iniciar';
        $available_course = true;
        $poll_id = null;
        $available_poll = false;
        $enabled_poll = false;
        $solved_poll = false;
        $assigned_topics = 0;
        $completed_topics = 0;
        $requirement_list = null;

        $statuses = Taxonomy::where('group', 'course')->where('type', 'user-status')->get();
        $status_approved = $statuses->where('code', 'aprobado')->first();
        $status_enc_pend = $statuses->where('code', 'enc_pend')->first();
        $status_desaprobado = $statuses->where('code', 'desaprobado')->first();

        $requirement_course = $course->requirements->first();

        if ($requirement_course) {

            $summary_requirement_course = $requirement_course->summaries_course->first();

            if (!$summary_requirement_course) {

                // TODO: Validar si existe algun curso compatible aprobado del curso requisito

                $req_course = $requirement_course->model_course;

                $req_course->load([
                    'summaries' => function ($q) use ($user) {
                        $q
                            ->with('status:id,name,code')
                            ->where('user_id', $user->id);
                    },
                    'compatibilities_a:id',
                    'compatibilities_b:id',
                ]);

                $compatible_course_req = $req_course->getCourseCompatibilityByUser($user);
                // $compatible_course_req = $requirement_course->model_course->getCourseCompatibilityByUser($user);

                if (!$compatible_course_req):
                    $available_course = false;
                    $status = 'bloqueado';
                    if($requirement_course?->requirement_id){
                        $req = Course::where('id',$requirement_course?->requirement_id)->first();

                        $available_course_req = true;
                        $requirement_course_req = $req->requirements->first();

                        if ($requirement_course_req) {

                            $summary_requirement_course_req = $requirement_course_req->summaries_course()->where('user_id',$user->id)->first();

                            if (!$summary_requirement_course_req) {

                                $req_course_req = $requirement_course_req->model_course;

                                $req_course_req->load([
                                    'summaries' => function ($q) use ($user) {
                                        $q
                                            ->with('status:id,name,code')
                                            ->where('user_id', $user->id);
                                    },
                                    'compatibilities_a:id',
                                    'compatibilities_b:id',
                                ]);

                                $compatible_course_req_req = $req_course_req->getCourseCompatibilityByUser($user);

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
                        if ($workspace_id === 25) {
                            $course_name_req = removeUCModuleNameFromCourseName($course_name_req);
                        }
                        $requirement_list = [
                            'id' => $requirement_course?->requirement_id,
                            'name' => $course_name_req,
                            'school_id' => $req_school?->id,
                            'disponible' => $available_course_req,
                            'ultimo_tema_visto' => ($req) ? self::ultimoTemaVisto($req, $user) : null
                        ];
                    }
                endif;

            }else{
                try {
                    if(!in_array($summary_requirement_course?->status?->code,['aprobado', 'realizado', 'revisado'])){
                        $available_course = false;
                        $status = 'bloqueado';
                        if($requirement_course?->requirement_id){
                            $req = Course::where('id',$requirement_course?->requirement_id)->first();

                            $requirement_course_req = $req->requirements->first();
                            $available_course_req = true;

                            if ($requirement_course_req) {

                                $summary_requirement_course_req = $requirement_course_req->summaries_course()->where('user_id',$user->id)->first();

                                if (!$summary_requirement_course_req) {

                                    $req_course_req = $requirement_course_req->model_course;

                                    $req_course_req->load([
                                        'summaries' => function ($q) use ($user) {
                                            $q
                                                ->with('status:id,name,code')
                                                ->where('user_id', $user->id);
                                        },
                                        'compatibilities_a:id',
                                        'compatibilities_b:id',
                                    ]);

                                    $compatible_course_req_req = $req_course_req->getCourseCompatibilityByUser($user);

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
                            if ($workspace_id === 25) {
                                $course_name_req = removeUCModuleNameFromCourseName($course_name_req);
                            }
                            $requirement_list = [
                                'id' => $requirement_course?->requirement_id,
                                'name' => $course_name_req,
                                'school_id' => $req_school?->id,
                                'disponible' => $available_course_req,
                                'ultimo_tema_visto' => ($req) ? self::ultimoTemaVisto($req, $user) : null
                            ];
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }
        // info($available_course);
        if ($available_course) {

            if ($course->compatible):

                return [
                    'status' => 'aprobado',
                    'requirements' => null,
                    'progress_percentage' => 100,
                    'available' => true,
                    'poll_id' => null,
                    'available_poll' => false,
                    'enabled_poll' => false,
                    'solved_poll' => false,
                    'exists_summary_course' => false,
                    'assigned_topics' => $course->topics->count(),
                    'completed_topics' => $course->topics->count(),
                ];

            endif;

            $poll = $course->polls->first();
            if ($poll) {
                $poll_id = $poll->id;
                $available_poll = true;

                if ($course->poll_question_answers_count)
                    $solved_poll = true;
            }

            $summary_course = $course->summaries->first();

            if ($summary_course) {
                $completed_topics = $summary_course->passed + $summary_course->taken + $summary_course->reviewed;
                $assigned_topics = $summary_course->assigned;
                $course_progress_percentage = $summary_course->advanced_percentage;
                if ($course_progress_percentage == 100 && $summary_course->status_id == $status_approved->id) :

//                if ($course_progress_percentage == 100 && $summary_course->status->code == 'aprobado') :
                    $status = 'completado';
                elseif ($course_progress_percentage == 100 && $summary_course->status_id == $status_enc_pend->id) :
//                elseif ($course_progress_percentage == 100 && $summary_course->status->code == 'enc_pend') :
                    $status = 'enc_pend';
//                elseif ($summary_course->status?->code == 'desaprobado') :
                elseif ($summary_course->status_id == $status_desaprobado->id) :
                    $status = 'desaprobado';
                    $enabled_poll = true;
                else :
                    $status = 'continuar';
                    $resolved_topics = $completed_topics + $summary_course->failed;
                    if ($summary_course->assigned <= $resolved_topics)
                        $enabled_poll = true;
                endif;

                if ($course_progress_percentage == 100)
                    $enabled_poll = true;
            }
        }

        return [
            'status' => $status,
            'requirements' => $requirement_list,
            'progress_percentage' => $course_progress_percentage,
            'available' => $available_course,
            'poll_id' => $poll_id,
            'available_poll' => $available_poll,
            'enabled_poll' => $enabled_poll,
            'solved_poll' => $solved_poll,
            'exists_summary_course' => $available_course && $summary_course,
            'assigned_topics' => $assigned_topics,
            'completed_topics' => $completed_topics,
        ];
    }

    protected function getCourseProgressByUser($user, Course $course)
    {
        $course_requirement = $course->requirements->first();
        if ($course_requirement) {
//            $requirement_summary_course = SummaryCourse::with('status:id,code')
//                ->where('course_id', $course_requirement->requirement_id)
//                ->where('user_id', $user->id)->first();
            $requirement_summary_course = $course_requirement->summaries_course->first();

            if (!$requirement_summary_course || ($requirement_summary_course && $requirement_summary_course->status->code != 'aprobado'))
                return ['average_grade' => 0, 'status' => 'bloqueado'];
        }

//        $summary_course = SummaryCourse::with('status:id,code')->where('course_id', $course->id)->where('user_id', $user->id)->first();
        $summary_course = $course->summaries->first();

        $grade_average = $summary_course ? floatval($summary_course->grade_average) : 0;
        $grade_average = $summary_course ?
            ($summary_course->passed > 0 || $grade_average > 0) ? $grade_average : null
            : null;

        return ['average_grade' => $grade_average, 'status' => $summary_course->status->code ?? 'por-iniciar'];
    }

    public function hasBeenSegmented()
    {
        return $this->segments->where('active', ACTIVE)->count();
    }

    public static function probar()
    {
        $course = Course::find('265');
        $fun_1 = $course->getUsersBySegmentation('count');
        print_r('Función 1: ');
        print_r($fun_1);
        $fun_2 = $course->usersSegmented($course->segments, $type = 'count');
        print_r('Función 2: ');
        print_r($fun_2);
    }

    public function usersSegmented($course_segments, $type = 'get_records')
    {
        $users_id_course = [];
        foreach ($course_segments as $key => $segment) {
            $query = User::select('id')->where('active', 1);
            $grouped = $segment->values->groupBy('criterion_id');
            foreach ($grouped as $idx => $values) {
                $segment_type = Criterion::find($idx);
                if ($segment_type->field_type->code == 'date') {
                    $select_date = CriterionValue::select('id')->where(function ($q) use ($values) {
                        foreach ($values as $value) {
                            $starts_at = carbonFromFormat($value->starts_at)->format('Y-m-d');
                            $finishes_at = carbonFromFormat($value->finishes_at)->format('Y-m-d');
                            $q->orWhereRaw('value_date between "' . $starts_at . '" and "' . $finishes_at . '"');
                        }
                    })->where('criterion_id', $idx)->get();
                    $ids = $select_date->pluck('id');
                } else {
                    $ids = $values->pluck('criterion_value_id');
                }
                $query->join("criterion_value_user as cvu{$idx}", function ($join) use ($ids, $idx) {
                    $join->on('users.id', '=', "cvu{$idx}" . '.user_id')
                        ->whereIn("cvu{$idx}" . '.criterion_value_id', $ids);
                });
            }
            // $counts[$key] = $query->count();
//            dd($query->toSql());
            $users_id_course = array_merge($users_id_course, $query->pluck('id')->toArray());
            // $users = DB::table('criterion_value_user')->join('criterion_values','criterion_values.id','=','criterion_value_user.criterion_value_id');
            // $criteria = $segment->values->groupBy('criterion_id');

            // foreach ($criteria as $criterion_values) {
            //     $criterion_values = $criterion_values->pluck('criterion_value_id');
            //     $users->orWhere(function ($q) use ($criterion_values) {
            //         $q->whereIn('criterion_value_id', $criterion_values);
            //     });
            // }
            // $users_id = $users->groupBy('user_id')->groupBy('criterion_values.criterion_id')->select('user_id', DB::raw('count(user_id) as count_group_user_id'))
            //     ->having('count_group_user_id', '=', count($criteria))->pluck('user_id')->toArray();
            // $users_id_course = array_merge($users_id_course, $users_id);
        }
        if ($type == 'users_id') {
            return $users_id_course;
        }
        if ($type == 'users_full') {
            return User::where('active', 1)
                ->whereIn('id', $users_id_course)
                ->get();
            // ->simplePaginate(5);
        }

        $users_have_course = User::where('active', 1)->whereIn('id', $users_id_course)->select('id');
        return ($type == 'get_records') ? $users_have_course->get() : $users_have_course->count();
    }

    public function getUsersBySegmentation($type = 'count')
    {
        $this->load('segments.values');

        if (!$this->hasBeenSegmented()) return [];

        // $users = collect();
        $users = [];

        $counts = [];

        foreach ($this->segments as $key => $segment) {

            $query = User::select('id')->where('active', 1);
            // $clause = $key == 0 ? 'where' : 'orWhere';

            $grouped = $segment->values->groupBy('criterion_id');

            foreach ($grouped as $idx => $values) {

                $query->join("criterion_value_user as cvu{$idx}", function ($join) use ($values, $idx) {

                    $ids = $values->pluck('criterion_value_id');

                    $join->on('users.id', '=', "cvu{$idx}" . '.user_id')
                        ->whereIn("cvu{$idx}" . '.criterion_value_id', $ids);
                });
            }


            // info($query->toSql());
            $counts[$key] = $query->count();

            // $result = $query->get()->pluck('id')->toArray();
            // $users[$key] = $result;
            // $counts[$key] = count($result);
        }

        // info($users);
        // info($counts);

        return $counts;
        // return $query->$type();
    }

    public function getUsersBySegmentations($type = 'count')
    {
        $this->load('segments.values');

        if (!$this->hasBeenSegmented()) return [];

        // $users = collect();
        $users = [];

        $counts = [];

        $query = User::select('id');

        foreach ($this->segments as $key => $segment) {

            $clause = $key == 0 ? 'where' : 'orWhere';
            $query->$clause(function ($q) use ($segment, $key) {

                $grouped = $segment->values->groupBy('criterion_id');

                foreach ($grouped as $i => $values) {


                    info($idx);

                    $q->join("criterion_value_user as cvu{$idx}", function ($join) use ($values, $idx) {

                        $ids = $values->pluck('criterion_value_id');

                        info($ids);

                        $join->on('users.id', '=', "cvu{$idx}" . '.user_id')
                            ->whereIn("cvu{$idx}" . '.criterion_value_id', $ids);
                    });
                }
            });
        }

        // info($counts);
        $a = $query->$type();

        // info($query->toSql());
        return $a;
    }

    public function getCourseTagsToUCByUser($course, $user)
    {
        $tags = [];

        $user_active_cycle = $user->getActiveCycle();

        if (!$user_active_cycle) return $tags;

        if ($user_active_cycle->value_text === 'Ciclo 0') {

            $tags = ['Ciclo 0'];

        } else {

            $temp_segment = null;
            $user_criteria = $user->criterion_values()->with('criterion.field_type')->get()->groupBy('criterion_id');

            foreach ($course->segments as $segment) {

                $course_segment_criteria = $segment->values->groupBy('criterion_id');

                $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $course_segment_criteria);

                if ($valid_segment) :
                    $temp_segment = $segment;
                    break;
                endif;

            }

//            $ciclos_values = $temp_segment->values()->whereRelation('criterion', 'code', 'cycle')->pluck('criterion_value_id');
//            $ciclos = CriterionValue::whereIn('id', $ciclos_values)->where('value_text', '<>', 'Ciclo 0')->get();

            $ciclo = null;
            if ($temp_segment)
                $ciclo = CriterionValue::whereIn('id', $temp_segment->values->pluck('criterion_value_id'))
                    ->whereRelation('criterion', 'code', 'cycle')
                    ->where('value_text', '<>', 'Ciclo 0')
                    ->orderBy('position')
                    ->first();

            if ($ciclo)
                $tags = [$ciclo->value_text];
        }

        return $tags;
    }

    public function updateOnModifyingCompatibility()
    {
        $course = $this;
        // $course->loadMissing('compatibilities.segments');

        $course->compatibilities = $course->getCompatibilities();

        if ($course->compatibilities->count() === 0) return;

        $course->loadMissing('segments');

        $courses_to_update[] = $course->id;

        $users_segmented = Course::usersSegmented($course->segments, type: 'users_id');

        foreach ($course->compatibilities as $compatibility_course) {

            $users_segmented = array_merge(
                $users_segmented,
                Course::usersSegmented($compatibility_course->segments, type: 'users_id'),
            );
            $courses_to_update[] = $compatibility_course->id;
        }

        // TODO: review how to reduce the number of users to update
        $users_segmented = array_unique($users_segmented);
//        $users_to_update = SummaryCourse::whereIn('user_id', $users_segmented)
//            ->whereIn('course_id', $courses_to_update)
//            ->whereNull('grade_average')
//            ->pluck('user_id');

        $chunk_users = array_chunk($users_segmented, 80);
        foreach ($chunk_users as $chunked_users) {
            SummaryUser::setSummaryUpdates($chunked_users, $courses_to_update);
        }
    }


    public function getCourseCompatibilityByUser($user)
    {
        $compatible_course = null;

        $course = $this;
        $course->compatibilities = $course->getCompatibilities();

        $summary_course = $course->summaries->first();
//        dd($course->compatibilities->pluck('id')->toArray());

        if ($summary_course) return null;

        if ($course->compatibilities->count() === 0) return null;

        $compatible_summary_course = SummaryCourse::with('course:id,name')
            ->whereRelation('course', 'active', ACTIVE)
            ->where('user_id', $user->id)
            ->whereIn('course_id', $course->compatibilities->pluck('id')->toArray())
            ->orderBy('grade_average', 'DESC')
            ->whereRelation('status', 'code', 'aprobado')
            ->first();


        if ($compatible_summary_course):

            $compatible_summary_course->course->compatible_of = $course;
            $compatible_course = $compatible_summary_course;

        endif;

        return $compatible_course;
    }

    protected function storeCompatibilityRequest($course, $data = [])
    {
        $course->compatibilities_b()->sync([]);
        $course->compatibilities_a()->sync($data['compatibilities'] ?? []);

        // TODO: Compatibles: Actualizar al modificar los cursos compatibles
        $course->updateOnModifyingCompatibility();

        return $course;
    }

    public function hasBeenValidated($user = null)
    {
        $user = $user ?? auth()->user();

        $workspace_id = $user->subworkspace->parent_id;

        if ($workspace_id != 25) return false;

        $this->load([
            'summaries' => function ($q) use ($user) {
                $q
                    ->with('status:id,name,code')
                    ->where('user_id', $user->id);
            },
            'compatibilities_a:id',
            'compatibilities_b:id',
        ]);

        $compatible = $this->getCourseCompatibilityByUser($user);

        return $compatible ? true : false;
    }

    public static function getModulesFromCourseSchools($courseId): array
    {
        $course = Course::find($courseId);
        $modules = [];
        if ($course) {
            $_schooldsIds = $course->schools()->pluck('id')->toArray();
            $schooldsIds = implode(',', $_schooldsIds);
            $modules = DB::select(DB::raw("
                        select w.name module_name,
                               w.criterion_value_id module_id,
                               s.name school_name,
                               s.id school_id
                        from school_subworkspace sw
                            inner join workspaces w on sw.subworkspace_id = w.id
                            inner join schools s on s.id = sw.school_id
                        where school_id in ($schooldsIds)
                    "));
        }

        return $modules;
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
    public function getScheduleRestartMinutes()
    {
        $config = $this->scheduled_restarts;
        
        $tiempo_en_minutos = $this->parseConfigInMinutes($config);
        if ($config AND $config['activado'] AND $tiempo_en_minutos > 0) {

            return $tiempo_en_minutos;
        }

        $minutes = $config = [];
        foreach ($this->schools as $key => $school) {

            $config = json_decode($school->scheduled_restarts, true);
            $tiempo_en_minutos = $this->parseConfigInMinutes($config);
            if ($config AND $config['activado'] AND $tiempo_en_minutos > 0) {

                $minutes[] = $tiempo_en_minutos;
            }
        }

        return count($minutes) ? min($minutes) : 0;
    }

    public function parseConfigInMinutes($config){
        $reinicio_dias = isset($config['reinicio_dias']) && $config['reinicio_dias'] != null  
        ? $config['reinicio_dias']*1440  : 0 ;

        $reinicio_horas = isset($config['reinicio_horas']) && $config['reinicio_horas'] != null  
                ? $config['reinicio_horas']*60  : 0 ;

        $reinicio_minutos = isset($config['reinicio_minutos']) && $config['reinicio_minutos'] != null  
                ? $config['reinicio_minutos']  : 0 ;

        return $reinicio_dias + $reinicio_horas + $reinicio_minutos;
    }
    public function getAttemptsLimit()
    {
        if (!$this->mod_evaluaciones) return null;
        
        return isset($this->mod_evaluaciones['nro_intentos']) ? $this->mod_evaluaciones['nro_intentos'] : null;
    }
}
