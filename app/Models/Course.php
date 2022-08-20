<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    protected $fillable = [
        'name', 'description', 'imagen', 'plantilla_diploma', 'external_code', 'slug',
        'assessable', 'freely_eligible',
        'position', 'scheduled_restarts', 'active'
    ];

    /*

        Relationships

    --------------------------------------------------------------------------*/


    public function schools()
    {
        return $this->belongsToMany(
            School::class,
            'course_school'
        );
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

        if (!is_null($request->school_id)) {
            $q->whereHas('schools', function ($t) use ($request) {
                $t->where('school_id', $request->school_id);
            });
        }

        $q->withCount(['topics', 'polls', 'segments']);

        if ($request->q)
            $q->where('courses.name', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'courses.position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);
    }

    protected static function storeRequest($data, $curso = null)
    {
        try {
            DB::beginTransaction();

            $data['scheduled_restarts'] = $data['reinicios_programado'];

            if ($curso) :
                $curso->update($data);
            else :
                $curso = self::create($data);
            endif;

            $req_curso = Requirement::whereHasMorph('model', [Course::class], function ($query) use ($curso) {
                $query->where('id', $curso->id);
            })->first();
            $course_requirements = [
                'model_type' => Course::class,
                'model_id' => $curso->id,
                'requirement_type' => Course::class,
                'requirement_id' => $data['requisito_id']
            ];
            Requirement::storeRequest($course_requirements, $req_curso);

            $curso->workspace()->sync($data['workspace_id']);
            $curso->schools()->sync($data['escuelas']);

            $curso->save();
            DB::commit();
            return $curso;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    protected function validateCursoRequisito($data, $escuela, $curso)
    {
        $req_curso = Requirement::whereHasMorph('requirement', [Course::class], function ($query) use ($curso) {
            $query->where('id', $curso->id);
        })->get();

        if (in_array($data['active'], ['false', false, 0], true) && $req_curso->count() > 0) :
            $validate = [];
            foreach ($req_curso as $req) {
                $requisito = Course::where('id', $req->model_id)->first();
                $route = route('cursos.editCurso', [$escuela->id, $requisito->id]);
                $validate[] = "<a href='{$route}'>" . $requisito->name . "</a>";
            }
            return [
                'validate' => false,
                'data' => $validate,
                'type' => 'validate_curso_requisito',
                'title' => 'Ocurrió un problema'
            ];
        endif;
        return ['validate' => true];
    }

    protected function getMessagesActions($curso, $title = 'Curso actualizado con éxito')
    {
        $messages = [];
        if (($curso->wasChanged('active') || $curso->active === 1) && $curso->topics->count() > 0) :
            $messages[] = [
                'title' => $title,
                'subtitle' => "Este cambio produce actualizaciones en el avance de los usuarios, que se ejecutarán dentro de 20 minutos.
                        Las actualizaciones se verán reflejadas en la app y en los reportes al finalizar este proceso.",
                'type' => 'update_message'
            ];
        endif;

        return [
            'title' => 'Aviso',
            'data' => $messages
        ];
    }

    protected function validateUpdateStatus($escuela, $curso, $estado)
    {
        $req_curso = Requirement::whereHasMorph('requirement', [Course::class], function ($query) use ($curso) {
            $query->where('id', $curso->id);
        })->get();
        $cursos_requisitos = [];
        foreach ($req_curso as $req) {
            $cursos_requisitos[] = Course::where('id', $req->model_id)->first();
        }
        $topics = $curso->topics;
        $hasActiveTema = $topics->where('active', 1)->count() > 0;

        if (count($cursos_requisitos) > 0 || $hasActiveTema) :

            $validate = collect();

            if (!$hasActiveTema) {

                $validacion = $this->avisoAllTemasInactive($escuela, $curso);
                $validate->push($validacion);
            }

            if (count($cursos_requisitos) > 0) :

                $validacion = $this->validateReqCursos($escuela, $cursos_requisitos, $curso);
                $validate->push($validacion);

            endif;

            if ($hasActiveTema) :

                $validacion = $this->validateHasActiveTema($escuela, $curso, $topics);
                $validate->push($validacion);

            endif;

            if ($validate->count() === 0) return ['validate' => true];
            // Si existe algún tema que impida el envío de formulario (show_confirm = false)
            // no mostrar el botón de "Confirmar"
            $count = $validate->where('show_confirm', false)->count();

            return [
                'validate' => false,
                'data' => $validate->toArray(),
                'title' => 'Alerta',
                'show_confirm' => !($count > 0)
            ];

        endif;

        return ['validate' => true];
    }


    protected function validateCursoEliminar($escuela, $curso)
    {
        $req_curso = Requirement::whereHasMorph('requirement', [Course::class], function ($query) use ($curso) {
            $query->where('id', $curso->id);
        })->get();
        $cursos_requisitos = [];
        foreach ($req_curso as $req) {
            $cursos_requisitos[] = Course::where('id', $req->model_id)->first();
        }
        $topics = $curso->topics;
        $hasActiveTema = $topics->where('active', 1)->count() > 0;

        if (count($cursos_requisitos) > 0 || $hasActiveTema) :

            $validate = collect();

            if (!$hasActiveTema) {

                $validacion = $this->avisoAllTemasInactive($escuela, $curso);
                $validate->push($validacion);
            }

            if (count($cursos_requisitos) > 0) :

                $validacion = $this->validateReqCursos($escuela, $cursos_requisitos, $curso, 'eliminar');
                $validate->push($validacion);

            endif;

            if ($validate->count() === 0) return ['validate' => true];
            // Si existe algún tema que impida el envío de formulario (show_confirm = false)
            // no mostrar el botón de "Confirmar"
            $count = $validate->where('show_confirm', false)->count();

            return [
                'validate' => false,
                'data' => $validate->toArray(),
                'title' => 'Alerta',
                'show_confirm' => !($count > 0)
            ];

        endif;

        return ['validate' => true];
    }

    public function validateReqCursos($escuela, $req_cursos, $curso, $verb = 'desactivar')
    {
        $temp = [
            'title' => "No se puede {$verb} este curso.",
            'subtitle' => "Para poder {$verb} este curso es necesario quitar o cambiar el requisito en los siguientes cursos:",
            'show_confirm' => false,
            'type' => 'req_curso_validate'
        ];
        $list1 = [];

        foreach ($req_cursos as $req) {
            $route = route('cursos.editCurso', [$escuela->id, $req->id]);
            $list1[] = "<a href='{$route}' target='_blank'>" . $req->name . "</a>";
        }

        $temp['list'] = $list1;
        return $temp;
    }

    public function validateHasActiveTema($escuela, $curso, $temas, $verb = 'desactivar')
    {
        $temp = [
            'title' => "Tener en cuenta al desactivar el curso.",
            'subtitle' => "Los siguientes temas también se inactivarán:",
            'show_confirm' => true,
            'type' => 'has_active_tema'
        ];
        $list1 = [];
        foreach ($temas as $tema) {
            $route = route('temas.editTema', [$escuela->id, $curso->id, $tema->id]);
            $list1[] = "<a href='{$route}' target='_blank'>" . $tema->name . "</a>";
        }
        $temp['list'] = $list1;
        return $temp;
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
