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

    public function workspace()
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

    public function requirement()
    {
        return $this->belongsToMany(Course::class);
    }

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

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function scopeActive($q, $active)
    {
        return $q->where('active', $active);
    }

    protected static function search($request, $paginate = 15)
    {
        $q = Course::whereHas('workspace', function ($t) use ($request) {
            $t->where('workspace_id', $request->workspace_id);
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
            $curso->schools()->sync($data['school_id']);

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
        return [
            'percentage' => 0,
            'status' => null,
            'available' => true,
            'survey_id' => null,
            'survey_available' => true,
            'survey_enabled' => true,
            'solved_survey' => true,
            'exists_summary_course' => true,
            'assigned_topics' => 0,
            'completed_topics' => 0,
        ];
    }

    protected function getDataToCoursesViewAppByUser($user, $courses_id): array
    {
        $schools = School::withWhereHas('courses', function ($q) use ($courses_id) {
            $q->whereIn('id', $courses_id)
                ->where('active', ACTIVE)
                ->select('id', 'name', 'description', 'assessable');
        })
            ->select('id', 'name')
            ->get();
        $workspace = $user->workspace;
        $mod_eval = json_decode($workspace->mod_evaluaciones, true);
        $summary_topics = SummaryTopic::whereHas('courses', function ($q) use ($courses_id) {
            $q->whereIn('id', $courses_id)->where('active', ACTIVE)->sortBy('position');
        })
            ->where('user_id', $user->id)
            ->get();

        $data = [];

        foreach ($schools as $school) {
            $courses = [];
            $school_completed = 0;
            $school_assigned = 0;
            $school_percentage = 0;
            $school_status = '';
            $last_course = null;

            foreach ($school->courses as $course) {
                $topics = [];
                $school_assigned++;

                $course_status = self::getCourseStatusByUser($user, $course);


                $courses[] = [
                    'id' => $course->id,
                    'nombre' => $course->name,
                    'descripcion' => $course->description,
                    'imagen' => $course->imagen,
                    //                    'requisito_id' => $course->requisito_id,
                    'c_evaluable' => $course->assessable,
                    'porcentaje' => $course_status['percentage'],
                    'disponible' => $course_status['available'],
                    //                    'status' => $arr_estados_cursos[$course_status['status']],
                    'encuesta' => $course_status['survey_available'],
                    'encuesta_habilitada' => $course_status['survey_enabled'],
                    'encuesta_resuelta' => $course_status['solved_survey'],
                    'encuesta_id' => $course_status['survey_id'],
                    'temas_asignados' => $course_status['exists_summary_course'] ?
                        $course_status['assigned_topics'] :
                        $course->topics->where('active', ACTIVE)->count(),
                    'temas_completados' => $course_status['completed_topics'],
                    'temas' => $topics
                ];
            }

            $data[] = [
                'categoria_id' => $school->id,
                'categoria' => $school->name,
                'completados' => $school_completed,
                'asignados' => $school_assigned,
                'porcentaje' => $school_percentage,
                'estado' => $school_status,
                'ultimo_curso' => $last_course,
                "cursos" => $courses
            ];
        }

        return $data;
    }
}
