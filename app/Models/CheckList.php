<?php

namespace App\Models;

use App\Http\Controllers\ApiRest\HelperController;

class CheckList extends BaseModel
{
    protected $table = 'checklists';

    protected $fillable = [
        'title',
        'description',
        'active',
        'workspace_id',
        'type_id',
        'starts_at',
        'finishes_at',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function checklist_actividades()
    {
        return $this->hasMany(CheckListItem::class, 'checklist_id', 'id');
    }

    public function actividades()
    {
        return $this->hasMany(CheckListItem::class, 'checklist_id', 'id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'checklist_relationships', 'checklist_id', 'course_id');
    }
    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }
    /*======================================================= SCOPES ==================================================================== */

    public function scopeActive($q, $estado)
    {
        return $q->where('active', $estado);
    }

    /*=================================================================================================================================== */

    protected function gridCheckList($data)
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $workspace = get_current_workspace();

        $queryChecklist = CheckList::with([
            'checklist_actividades' => function ($q) {
                $q->orderBy('active', 'desc')->orderBy('position');
            },
            'courses' => function ($q) {
                $q->select('courses.id', 'courses.name');
            }
        ])->where('workspace_id', $workspace->id);

        $field = 'created_at';
        $sort = 'DESC';

        $queryChecklist->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $queryChecklist->where(function ($query) use ($filtro) {
                $query->where('checklists.title', 'like', "%$filtro%");
                $query->orWhere('checklists.description', 'like', "%$filtro%");
            });
        }
        $checklists = $queryChecklist->paginate(request('paginate', 15));

        foreach ($checklists->items() as $checklist) {
            foreach ($checklist->checklist_actividades as $act) {
                $type_id = $act->type_id ?? null;
                $type_name = !is_null($type_id) ? Taxonomy::where('id', $type_id)->first() : null;
                $type_name = !is_null($type_name) ? $type_name->code : '';
                $act->type_name = $type_name;
            }
            foreach ($checklist->courses as $curso) {
                $workspace = !is_null($curso->workspaces) && count($curso->workspaces) ? $curso->workspaces[0]->name : '';
                $school = !is_null($curso->schools) && count($curso->schools) ? $curso->schools[0]->name : '';

                $curso->modulo = $workspace;
                $curso->curso = $curso->name;
                $curso->escuela = $school;
                $curso->nombre = $workspace . ' - ' . $school . ' - ' . $curso->name;
            }

            $criteria = Segment::getCriteriaByWorkspace(get_current_workspace());
            $segments = Segment::getSegmentsByModel($criteria, CheckList::class, $checklist->id);

            $checklist->segments = $segments;

            $checklist->active = $checklist->active;
        }

        $response['data'] = $checklists->items();
        $response['lastPage'] = $checklists->lastPage();

        $response['current_page'] = $checklists->currentPage();
        $response['first_page_url'] = $checklists->url(1);
        $response['from'] = $checklists->firstItem();
        $response['last_page'] = $checklists->lastPage();
        $response['last_page_url'] = $checklists->url($checklists->lastPage());
        $response['next_page_url'] = $checklists->nextPageUrl();
        $response['path'] = $checklists->getOptions()['path'];
        $response['per_page'] = $checklists->perPage();
        $response['prev_page_url'] = $checklists->previousPageUrl();
        $response['to'] = $checklists->lastItem();
        $response['total'] = $checklists->total();

        return $response;
    }

    protected function getChecklistsByAlumno($alumno_id): array
    {
        $entrenador = EntrenadorUsuario::where('user_id', $alumno_id)->where('active', 1)->first();
        $entrenador_id = !is_null($entrenador) ? $entrenador->trainer_id : null;

        $response['error'] = false;
        $checklistCompletados = 0;

        $user = User::where('id', $alumno_id)->first();
        $cursos_x_user = $user->getCurrentCourses();
        $cursos_ids = $cursos_x_user->pluck('id')->toArray();

        $cursos = Course::with('checklists', 'schools')->whereIn('id', $cursos_ids)->get();
        $checklists = collect();
        foreach ($cursos as $curso) {
            $curso->categoria = $curso->schools->first()->only('id', 'name');
            if ($curso->checklists->count() > 0) {
                foreach ($curso->checklists as $checklist) {
                    if ($checklist->active) {
                        $tax_trainer_user = Taxonomy::where('group', 'checklist')
                            ->where('type', 'type')
                            ->where('code', 'trainer_user')
                            ->first();
                        $tax_user_trainer = Taxonomy::where('group', 'checklist')
                            ->where('type', 'type')
                            ->where('code', 'user_trainer')
                            ->first();
                        $actividades_activas = $checklist->actividades->where('active', 1)->where('type_id', $tax_trainer_user->id)->sortBy('position');
                        $actividades_activasFeedback = $checklist->actividades->where('active', 1)->where('type_id', $tax_user_trainer->id)->sortBy('position');
                        if (!$checklists->where('id', $checklist->id)->first() && $actividades_activas->count() > 0 && $checklist->active) {
                            $r_x_c = SummaryCourse::where('user_id', $alumno_id)->where('course_id', $curso->id)->first();

                            $aprobado = Taxonomy::getFirstData('course', 'user-status', 'aprobado');

                            $disponible = $r_x_c && $r_x_c->status_id === $aprobado->id;
                            $checklistRpta = ChecklistRpta::checklist($checklist->id)->alumno($alumno_id)->entrenador($entrenador_id)->first();
                            if (!$checklistRpta) {
                                $checklistRpta = ChecklistRpta::create([
                                    'checklist_id' => $checklist->id,
                                    'student_id' => $alumno_id,
                                    'course_id' => $curso->id, //deprecated
                                    'school_id' => $curso->categoria['id'], //deprecated
                                    'coach_id' => $entrenador_id,
                                    'percent' => 0
                                ]);
                            }
                            $progresoActividad = $this->getProgresoActividades($checklist, $checklistRpta, $actividades_activas);
                            $progresoActividadFeedback = $this->getProgresoActividadesFeedback($checklistRpta, $actividades_activasFeedback);
                            $tempChecklist = [
                                'id' => $checklist->id,
                                'titulo' => $checklist->title,
                                'descripcion' => $checklist->description,
                                'disponible' => $disponible,
                                'curso' => $checklist->courses()->with([
                                    'schools' => function ($query) {
                                        $query->select('id', 'name');
                                    }
                                ])->select('id', 'name')->get(),
                                'porcentaje' => $progresoActividad['porcentaje'],
                                'actividades_totales' => $progresoActividad['actividades_totales'],
                                'actividades_completadas' => $progresoActividad['actividades_completadas'],
                                'actividades' => $progresoActividad['actividades'],
                                'actividades_feedback' => $progresoActividadFeedback['actividades_feedback'],
                                'feedback_disponible' => $progresoActividad['feedback_disponible']
                            ];
                            if ($tempChecklist['porcentaje'] === 100.00) $checklistCompletados++;
                            $checklists->push($tempChecklist);
                        }
                    }
                }
            }
        }
        $response['checklists_totales'] = $checklists->count();
        $response['checklists_completados'] = $checklistCompletados;
        $response['porcentaje'] = $checklists->count() > 0 ? (float)number_format((($checklistCompletados / $checklists->count()) * 100), 2) : 0;
        $response['checklists'] = $checklists->sortByDesc('disponible')->values()->all();
        $response['active'] = !is_null($entrenador);
        return $response;
    }

    protected function getChecklistsByTrainer($trainer_id): array
    {
        $checklists = CheckList::where('workspace_id',25)->where('active',1)->paginate(request('paginate', 5));
        $list_checklist = collect();

        if(count($checklists) > 0) {
            $i = 0;
            foreach ($checklists as $check) {
                $list_checklist->push((object)array(
                    'id' => $check->id,
                    'title'  => $check->title,
                    'description'  => $check->description,
                    'percentage'  => 16 * (rand(1,5)),
                    'status'  => $i % 2 == 0 ? 'realizado': 'pendiente',
                    'courses' => collect()
                ));
                $i++;
            }
        }

        $response['data'] = $list_checklist;
        $response['lastPage'] = $checklists->lastPage();

        $response['current_page'] = $checklists->currentPage();
        $response['first_page_url'] = $checklists->url(1);
        $response['from'] = $checklists->firstItem();
        $response['last_page'] = $checklists->lastPage();
        $response['last_page_url'] = $checklists->url($checklists->lastPage());
        $response['next_page_url'] = $checklists->nextPageUrl();
        $response['path'] = $checklists->getOptions()['path'];
        $response['per_page'] = $checklists->perPage();
        $response['prev_page_url'] = $checklists->previousPageUrl();
        $response['to'] = $checklists->lastItem();
        $response['total'] = $checklists->total();

        return $response;
    }

    protected function getStudentsByChecklist($checklist_id, $trainer_id): array
    {
        $alumnos_ids = EntrenadorUsuario::entrenador($trainer_id)->where('active', 1)->limit(20)->get();

        $list_students = User::leftJoin('workspaces as w', 'users.subworkspace_id', '=', 'w.id')
            ->whereIn('users.id', $alumnos_ids->pluck('user_id')->all())
            ->select('users.id', 'users.name', 'users.fullname as full_name', 'users.document', 'w.name as subworkspace')
            ->paginate(request('paginate', 5));

        if(count($list_students) > 0) {
            $i = 0;
            foreach ($list_students as $check) {
                $check->percentage = 16 * (rand(1,5));
                $check->makeHidden(['abilities', 'roles', 'age', 'fullname']);
                $i++;
            }
        }

        $response['data'] = $list_students->items();
        $response['lastPage'] = $list_students->lastPage();

        $response['current_page'] = $list_students->currentPage();
        $response['first_page_url'] = $list_students->url(1);
        $response['from'] = $list_students->firstItem();
        $response['last_page'] = $list_students->lastPage();
        $response['last_page_url'] = $list_students->url($list_students->lastPage());
        $response['next_page_url'] = $list_students->nextPageUrl();
        $response['path'] = $list_students->getOptions()['path'];
        $response['per_page'] = $list_students->perPage();
        $response['prev_page_url'] = $list_students->previousPageUrl();
        $response['to'] = $list_students->lastItem();
        $response['total'] = $list_students->total();

        return $response;
    }

    public function getProgresoActividadesFeedback(ChecklistRpta $checklistRpta, $actividades)
    {

        foreach ($actividades as $actividad) {
            $actividadProgreso = ChecklistRptaItem::where('checklist_item_id', $actividad->id)->where('checklist_answer_id', $checklistRpta->id)->first();
            $actividad->disponible = !is_null($actividadProgreso);
            if (!is_null($actividadProgreso)) {
                $actividad->estado = $actividadProgreso->qualification;
            } else {
                $actividad->estado = 'Pendiente';
            }
            $type_name = !is_null($actividad->type_id) ? Taxonomy::where('id', $actividad->type_id)->first() : null;
            $type_name = !is_null($type_name) ? $type_name->code : '';
            $actividad->tipo = $type_name;
        }
        return [
            'actividades_feedback' => $actividades->values()->all()
        ];
    }

    public function getProgresoActividades(Checklist $checkList, ChecklistRpta $checklistRpta, $actividades)
    {
        $completadas = 0;
        if ($checklistRpta->porcentaje !== 100) {
            foreach ($actividades as $actividad) {
                $checklistRptaItem = ChecklistRptaItem::where('checklist_answer_id', $checklistRpta->id)->where('checklist_item_id', $actividad->id)->first();
                if (!$checklistRptaItem) {
                    $checklistRptaItem = ChecklistRptaItem::create([
                        'checklist_answer_id' => $checklistRpta->id,
                        'checklist_item_id' => $actividad->id,
                        'qualification' => 'Pendiente'
                    ]);
                    ChecklistRpta::actualizarChecklistRpta($checklistRpta);
                }
                $actividadProgreso = ChecklistRptaItem::where('checklist_item_id', $actividad->id)->where('checklist_answer_id', $checklistRpta->id)->first();
                if ($actividadProgreso) {
                    $actividad->estado = $actividadProgreso->qualification;
                    if (in_array($actividad->estado, ['Cumple', 'No cumple'])) $completadas++;
                } else {
                    $actividad->estado = 'Pendiente';
                }
                $type_name = !is_null($actividad->type_id) ? Taxonomy::where('id', $actividad->type_id)->first() : null;
                $type_name = !is_null($type_name) ? $type_name->code : '';
                $actividad->tipo = $type_name;
            }
            $porcentaje = $completadas / count($actividades);
        } else {
            $actividadProgreso = ChecklistRptaItem::with('rpta_items')->where('checklist_answer_id', $checklistRpta->id)->get();
            $actividades = collect();
            foreach ($actividadProgreso->rpta_items as $rpta) {
                $actividades->push([
                    'id' => $rpta->actividad->id,
                    'checklist_id' => $rpta->actividad->checklist_id,
                    'posicion' => $rpta->actividad->position,
                    'tipo' => $rpta->actividad->tipo,
                    'estado' => $rpta->qualification
                ]);
            }
            $porcentaje = $checklistRpta->porcentaje;
        }
        $feedback_disponible = $actividades->count() === $completadas;
        return [
            'actividades_completadas' => $completadas,
            'actividades_totales' => $actividades->count(),
            'porcentaje' => (float)number_format($porcentaje * 100, 2),
            'actividades' => $actividades->values()->all(),
            'feedback_disponible' => $feedback_disponible
        ];
    }
}
