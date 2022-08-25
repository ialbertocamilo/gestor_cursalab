<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Course extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'imagen', 'plantilla_diploma', 'external_code', 'slug',
        'assessable', 'freely_eligible',
        'position', 'scheduled_restarts', 'active'
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
        return $this->belongsToMany(Checklist::class, 'checklist_relationship', 'course_id', 'checklist_id');
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
        return $this->morphMany(Requirement::class, 'requirement');
    }

    public function summaries()
    {
        return $this->hasMany(SummaryCourse::class);
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function scopeActive($q, $active)
    {
        return $q->where('active', $active);
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

        $q = Course::whereHas('workspaces', function ($t) use ($workspace) {
            $t->where('workspace_id', $workspace->id);
        });

        info($request->all());

        if ($request->school_id) {
            $q->whereHas('schools', function ($t) use ($request) {
                $t->where('school_id', $request->school_id);
            });
        }

        $q->withCount(['topics', 'polls', 'segments']);

        if ($request->q)
            $q->where('name', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

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
            else :
                $course = self::create($data);
                $course->workspaces()->sync([$workspace->id]);
            endif;

            $req_curso = Requirement::whereHasMorph('model', [Course::class], function ($query) use ($course) {
                $query->where('id', $course->id);
            })->first();

            $course_requirements = [
                'model_type' => Course::class,
                'model_id' => $course->id,
                'requirement_type' => Course::class,
                'requirement_id' => $data['requisito_id']
            ];

            Requirement::storeRequest($course_requirements, $req_curso);


            $course->schools()->sync($data['escuelas']);

            // $course->save();

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            return $e;
        }

        cache_clear_model(School::class);

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
        $temp['ok'] = ($course->wasChanged('active') or $course->active === 1) and $course->topics->count() > 0;

        if (!$temp['ok']) return $temp;

        $temp['title'] = null;
        $temp['subtitle'] = "Este cambio produce actualizaciones en el avance de los usuarios, que se ejecutarán dentro de 20 minutos.
                        Las actualizaciones se verán reflejadas en la app y en los reportes al finalizar este proceso.";
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


    protected function getCourseStatusByUser(User $user, Course $course): array
    {
        $course_progress_percentage = 0;
        $status = 'por-iniciar';
        $available_course = true;
        $poll_id = null;
        $available_poll = false;
        $enabled_poll = false;
        $solved_poll = false;
        $assigned_topics = 0;
        $completed_topics = 0;

        $requirement_course = $course->requirements->first();
        if ($requirement_course) {
            $summary_requirement_course = SummaryCourse::with('course')
                ->where('user_id', $user->id)
                ->where('course_id', $requirement_course->id)
                ->whereRelation('status', 'code', '=', 'aprobado')
                ->first();

            if (!$summary_requirement_course) {
                $available_course = false;
                $status = 'bloqueado';
            }
        }

        if ($available_course) {
            $poll = $course->polls->first();
            if ($poll) {
                $poll_id = $poll->id;
                $available_poll = true;

                $poll_questions_answers = PollQuestionAnswer::whereIn('question_id', $poll->questions->pluck('id'))
                    ->where('user_id', $user->id)->first();

                if ($poll_questions_answers) $solved_poll = true;
            }

            $summary_course = $course->summaryByUser($user->id);

            if ($summary_course) {
                $completed_topics = $summary_course->passed + $summary_course->taken + $summary_course->reviewved;
                $course_progress_percentage = $summary_course->advanced_percentage;
                if ($course_progress_percentage == 100 && $summary_course->status->code == 'aprobado') :
                    $status = 'completado';
                elseif ($course_progress_percentage == 100 && $summary_course->status->code == 'enc_pend') :
                    $status = 'enc_pend';
                elseif ($summary_course->status->code == 'desaprobado') :
                    $status = 'desaprobado';
                    $enabled_poll = true;
                else :
                    $status = 'continuar';
                    $resolved_topics = $completed_topics + $summary_course->failed;
                    if ($summary_course->assigned <= $resolved_topics)
                        $available_poll = true;
                endif;
            }
        }

        return [
            'status' => $status,
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

    protected function getDataToCoursesViewAppByUser($user, $user_courses): array
    {
        $schools = $user_courses->groupBy('schools.*.id');
        $summary_topics_user = SummaryTopic::whereHas('topic.course', function ($q) use ($user_courses) {
            $q->whereIn('id', $user_courses->pluck('id'))->where('active', ACTIVE)->orderBy('position');
        })
            ->where('user_id', $user->id)
            ->get();

        $data = [];

        foreach ($schools as $school_id => $courses) {
            $school = $courses->first()->schools->where('id', $school_id)->first();
            $school_courses = [];
            $school_completed = 0;
            $school_assigned = 0;
            $school_percentage = 0;
            $last_course_reviewed = null;

            foreach ($courses as $course) {
                $school_assigned++;
                $last_topic = null;
                $course_status = self::getCourseStatusByUser($user, $course);
                if ($course_status['status'] == 'completado') $school_completed++;

                $topics = $course->topics->where('active', ACTIVE);
                $summary_topics = $summary_topics_user->whereIn('topic_id', $topics->pluck('id'));

                if ($summary_topics->count() > 0) {
                    foreach ($topics as $topic) {
                        $topics_view = $summary_topics->where('topic_id', $topic->id)->first();
                        $last_item = ($topic->id == $topics->last()->id);
                        if ($topics_view?->views) {
                            $passed_tests = $summary_topics->where('posteo_id', $topic->id)->where('passed', 1)->first();
                            if ($topic->evaluation_type->code == 'calificada' && $passed_tests && !$last_item) continue;
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

                if (is_null($last_course_reviewed) && $course_status['status'] != 'completado') {
                    $last_course_reviewed = [
                        'id' => $course->id,
                        'nombre' => $course->name,
                        'imagen' => $course->imagen,
                        'porcentaje' => $course_status['progress_percentage'],
                        'ultimo_tema_visto' => $last_topic_reviewed,
                    ];
                }


                $school_courses[] = [
                    'id' => $course->id,
                    'nombre' => $course->name,
                    'descripcion' => $course->description,
                    'imagen' => $course->imagen,
                    'c_evaluable' => $course->assessable,
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
                    'ultimo_tema_visto' => $last_topic_reviewed
                ];
            }

            if ($school_completed > 0) :
                $school_status = $school_completed >= $school_assigned ? 'Aprobado' : 'Desarrollo';
                $school_percentage = ($school_completed / $school_assigned) * 100;
            else :
                $school_status = 'Pendiente';
            endif;
            $school_percentage = round($school_percentage);

            $data[] = [
                'categoria_id' => $school->id,
                'categoria' => $school->name,
                'completados' => $school_completed,
                'asignados' => $school_assigned,
                'porcentaje' => $school_percentage,
                'estado' => $school_status,
                'ultimo_curso' => $last_course_reviewed,
                "cursos" => $school_courses
            ];
        }

        return $data;
    }
}
