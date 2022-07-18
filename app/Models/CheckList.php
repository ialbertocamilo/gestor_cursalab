<?php

namespace App\Models;

use App\Http\Controllers\ApiRest\HelperController;

class CheckList extends BaseModel
{
    protected $table = 'checklist';

    protected $fillable = [
        'titulo',
        'descripcion',
        'curso_id',
        'carrera_id',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
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

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'relaciones_checklist', 'checklist_id', 'curso_id');
    }

    /*======================================================= SCOPES ==================================================================== */

    public function scopeEstado($q, $estado)
    {
        return $q->where('estado', $estado);
    }

    /*=================================================================================================================================== */

    protected function gridCheckList($data)
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $queryChecklist = CheckList::with([
            'checklist_actividades' => function ($q) {
                $q->orderBy('estado', 'desc')->orderBy('posicion');
            },
            'cursos' => function ($q) {
                $q->select('cursos.id', 'cursos.nombre', 'cursos.config_id', 'cursos.categoria_id');
            }
        ]);

        $field = request()->sortBy ?? 'created_at';
        $sort = request()->sortDesc == 'true' ? 'DESC' : 'ASC';

        $queryChecklist->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $queryChecklist->where(function ($query) use ($filtro) {
                $query->where('titulo', 'like', "%$filtro%");
                $query->orWhere('descripcion', 'like', "%$filtro%");
            });
        }
        $checklists = $queryChecklist->paginate(request('paginate', 15));

        foreach ($checklists->items() as $checklist) {
            foreach ($checklist->cursos as $curso) {
                $curso->modulo = $curso->config->codigo_matricula;
                $curso->curso = $curso->nombre;
                $curso->escuela = $curso->categoria->nombre;
                $curso->nombre = $curso->config->codigo_matricula . ' - ' . $curso->categoria->nombre . ' - ' . $curso->nombre;
            }

            $checklist->active = $checklist->estado;
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
//    protected function getChecklistsByAlumno($entrenador_id, $alumno_id): array
    {
        $entrenador = EntrenadorUsuario::where('usuario_id', $alumno_id)->where('estado', 1)->first();
        $entrenador_id = $entrenador->entrenador_id;

        $response['error'] = false;
        $checklistCompletados = 0;

        $helper = new HelperController();
        $cursos_ids = $helper->help_cursos_x_matricula_con_cursos_libre($alumno_id);
        $cursos = Curso::with('checklists', 'categoria')->whereIn('id', $cursos_ids)->get();
        $checklists = collect();
        foreach ($cursos as $curso) {
            $curso->categoria = $curso->categoria->only('id', 'nombre');
            if ($curso->checklists->count() > 0) {
                foreach ($curso->checklists as $checklist) {
                    if ($checklist->estado) {
//                        info('CHECKLIST ID :: '.$checklist->id);
//                        info('CURSO ID :: '.$curso->id);
                        $actividades_activas = $checklist->actividades->where('estado', 1)->where('tipo', 'entrenador_usuario')->sortBy('posicion');
                        $actividades_activasFeedback = $checklist->actividades->where('estado', 1)->where('tipo', 'usuario_entrenador')->sortBy('posicion');
                        if (!$checklists->where('id', $checklist->id)->first() && $actividades_activas->count() >0 && $checklist->estado) {
                            $r_x_c = Resumen_x_curso::where('usuario_id', $alumno_id)->where('curso_id', $curso->id)->first();
                            $disponible = $r_x_c && $r_x_c->estado === 'aprobado';
                            $checklistRpta = ChecklistRpta::checklist($checklist->id)->alumno($alumno_id)->entrenador($entrenador_id)->first();
                            if (!$checklistRpta) {
                                $checklistRpta = ChecklistRpta::create([
                                    'checklist_id' => $checklist->id,
                                    'alumno_id' => $alumno_id,
                                    'curso_id' => $curso->id,
                                    'categoria_id' => $curso->categoria_id,
                                    'entrenador_id' => $entrenador_id,
                                    'porcentaje' => 0
                                ]);
                            }
                            $progresoActividad = $this->getProgresoActividades($checklist, $checklistRpta, $actividades_activas);
                            $progresoActividadFeedback = $this->getProgresoActividadesFeedback($checklistRpta, $actividades_activasFeedback);
                            $tempChecklist = [
                                'id' => $checklist->id,
                                'titulo' => $checklist->titulo,
                                'descripcion' => $checklist->descripcion,
                                'disponible' => $disponible,
                                'curso' => $curso->only('id', 'nombre', 'categoria'),
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
        return $response;
    }

    public function getProgresoActividadesFeedback(ChecklistRpta $checklistRpta, $actividades)
    {

        foreach ($actividades as $actividad) {
            $actividadProgreso = ChecklistRptaItem::where('checklist_item_id', $actividad->id)->where('checklist_rpta_id', $checklistRpta->id)->first();
            $actividad->disponible = !(bool)$actividadProgreso;
            if ($actividadProgreso) {
                $actividad->estado = $actividadProgreso->calificacion;
            } else {
                $actividad->estado = 'Pendiente';
            }
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
                $checklistRptaItem = ChecklistRptaItem::where('checklist_rpta_id', $checklistRpta->id)->where('checklist_item_id', $actividad->id)->first();
                if ($actividad->is_default && !$checklistRptaItem) {
                    $checklistRptaItem = ChecklistRptaItem::create([
                        'checklist_rpta_id' => $checklistRpta->id,
                        'checklist_item_id' => $actividad->id,
                        'calificacion' => 'Cumple'
                    ]);
                    ChecklistRpta::actualizarChecklistRpta($checklistRpta);
                }
                $actividadProgreso = ChecklistRptaItem::where('checklist_item_id', $actividad->id)->where('checklist_rpta_id', $checklistRpta->id)->first();
                if ($actividadProgreso) {
                    $actividad->estado = $actividadProgreso->calificacion;
                    if (in_array($actividad->estado, ['Cumple', 'No cumple'])) $completadas++;
                } else {
                    $actividad->estado = 'Pendiente';
                }
            }
            $porcentaje = $completadas / count($actividades);
        } else {
            $actividadProgreso = ChecklistRptaItem::with('rpta_items')->where('checklist_rpta_id', $checklistRpta->id)->get();
            $actividades = collect();
            foreach ($actividadProgreso->rpta_items as $rpta) {
                $actividades->push([
                    'id' => $rpta->actividad->id,
                    'checklist_id' => $rpta->actividad->checklist_id,
                    'posicion' => $rpta->actividad->posicion,
                    'tipo' => $rpta->actividad->tipo,
                    'estado' => $rpta->calificacion
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
