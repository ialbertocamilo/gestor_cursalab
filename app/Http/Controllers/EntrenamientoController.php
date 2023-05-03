<?php

namespace App\Http\Controllers;

use App\Models\ChecklistRpta;
use App\Models\ChecklistRptaItem;
use App\Models\Curso;
use App\Models\Resumen_x_curso;
use App\Models\Usuario;
use App\Models\CheckList;
use App\Models\CheckListItem;
use App\Models\EntrenadorUsuario;

use App\Http\Controllers\ApiRest\HelperController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AsignarEntrenadorImport;
use App\Imports\ChecklistImport;
use App\Models\Course;
use App\Models\Segment;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Str;
use function foo\func;


class EntrenamientoController extends Controller
{

    /* Funciones Test */
    public function dataPruebaEntrenadores(Request $request)
    {
        $cant_entrenadores = $request->cant_entrenadores;
        $cant_usuarios_x_entrenador = $request->cant_usuarios_x_entrenador;

        $entrenadores = EntrenadorUsuario::truncate();
        $usuarios_update_rol = Usuario::where('created_at', '<>', null)->update(['rol_entrenamiento' => null]);

        $entrenadores_random = Usuario::inRandomOrder()->limit($cant_entrenadores)->get();
        $ids_en_uso = collect($entrenadores_random->pluck('id')->all());
        foreach ($entrenadores_random as $key => $entrenador) {
            $entrenador->rol_entrenamiento = 'entrenador';
            $entrenador->save();
            $usuarios_random = Usuario::whereNotIn('id', $ids_en_uso)
                ->where('config_id', $entrenador->config_id)
                ->inRandomOrder()
                ->limit($cant_usuarios_x_entrenador)->get();
            $dataTemp = [];
            foreach ($usuarios_random as $usuario) {
                $usuario->rol_entrenamiento = 'alumno';
                $usuario->save();
                $dataTemp[] = [
                    'entrenador_id' => $entrenador->id,
                    'usuario_id' => $usuario->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'estado' => 1,
                    'estado_notificacion' => 1
                ];
                $ids_en_uso[] = $usuario->id;
            }
            EntrenadorUsuario::insert($dataTemp);
        }

        $entrenadores = EntrenadorUsuario::all();
        return response()->json($entrenadores, 200);
    }

    public function dataPruebaChecklist(Request $request)
    {
        CheckList::truncate();
        CheckListItem::truncate();

        $cant_checklist = $request->cant_checklist;
        $cant_checklist_items = $request->cant_checklist_items;
        $cursos_x_checklist = $request->cursos_x_checklist;

        for ($i = 0; $i < $cant_checklist; $i++) {
            $checklist = new CheckList();
            $checklist->titulo = 'Titulo Checklist ' . ($i + 1);
            $checklist->descripcion = 'Descripcion Checklist ' . ($i + 1);
            $checklist->estado = rand(0, 1);
            $checklist->save();

            for ($j = 0; $j < $cant_checklist_items; $j++) {
                $checklist_item = new CheckListItem();
                $checklist_item->actividad = 'Actividad Checklist ' . ($j + 1);
                $checklist_item->tipo = $i == 0 ? 'user_trainer' : 'trainer_user';
                $checklist_item->estado = rand(0, 1);
                $checklist_item->checklist_id = $checklist->id;
                $checklist_item->save();
            }
            $id_cursos_random = Curso::inRandomOrder()->limit($cursos_x_checklist)->pluck('id');
            $checklist->cursos()->sync($id_cursos_random->all());
        }

        $checklist = CheckList::with([
            'checklist_actividades',
            'cursos' => function ($q) {
                $q->select('*');
            }
        ])->get();


        return response()->json($checklist, 200);
    }
    /* ============================================================================================================== */


    /* Servicios para el gestor */

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        // $request->mergeIfMissing(['workspace_id' => $workspace?->id]);
        $data = EntrenadorUsuario::gridEntrenadores($request->all());

        // $data = $apiResponse['options'];
        // $data['data'] = $apiResponse['data'];

        return $this->success([]);
    }

    //ENTRENADORES
    public function listarEntrenadores(Request $request)
    {
        $data = $request->all();
        $apiResponse = EntrenadorUsuario::gridEntrenadores($data);

        return response()->json($apiResponse, 200);
    }

    public function asignar(Request $request)
    {
        $data = $request->all();
        foreach ($data['alumnos'] as $key => $alumno) {
            $temp = [
                'trainer_id' => $data['entrenador_id'],
                'user_id' => $alumno['id'],
                'active' => 1
            ];
            EntrenadorUsuario::asignar($temp);
        }
        $alumnos = EntrenadorUsuario::getUsuariosByEntrenador($data);
        $apiResponse['alumnos'] = $alumnos['data'];

        return response()->json($apiResponse, 200);
    }

    public function asignarMasivo(Request $request)
    {
        $info = [];
        if ($request->hasFile("archivo")) {
            $import = new AsignarEntrenadorImport();
            Excel::import($import, $request->file('archivo'));
            $info = $import->get_data();
            return response()->json([
                'error' => false,
                'msg' => $info['msg'],
                'info' => $info
            ], 200);
        }
        return response()->json([
            'error' => true,
            'msg' => 'No se ha seleccionado ningún archivo',
            'info' => $info
        ], 200);
    }

    public function cambiarEstadoEntrenadorAlumno(Request $request)
    {
        $estado = $request->estado;
        $dni_entrenador = $request->entrenador;
        $dni_alumno = $request->alumno;
        $entrenador = User::where('document', $dni_entrenador)->first();
        $alumno = User::where('document', $dni_alumno)->first();
        if ($entrenador && $alumno) {
            $registro = EntrenadorUsuario::where('trainer_id', $entrenador->id)
                ->where('user_id', $alumno->id)
                ->first();
            if ($registro) {
                $registro->active = $estado ? 1 : 0;
                $registro->save();
                //TODO:
                //                if ()

                return response()->json(['error' => false, 'msg' => 'Relación entrenador-alumno actualizada.'], 200);
            }
            return response()->json(['error' => true, 'msg' => 'El entrenador o el alumno no existe.'], 200);
        }
        return response()->json(['error' => true, 'msg' => 'El entrenador o el alumno no existe.'], 200);
    }

    public function buscarAlumno(Request $request)
    {
        $workspace = get_current_workspace();
        $entrenador_id = $request->entrenador_id;
        $config_id = $request->config_id;
        $filtro = $request->filtro;
        $alumnos_existentes_ids = EntrenadorUsuario::where('trainer_id', $entrenador_id)->pluck('user_id');
        $alumnos = User::where(function ($query) use ($filtro) {
            $query->where('users.name', 'like', "%$filtro%");
            $query->orWhere('users.document', 'like', "%$filtro%");
        })
            ->leftJoin('workspaces as w', 'users.subworkspace_id', '=', 'w.id')
            ->where('w.parent_id', $workspace->id)
            ->whereNotIn('users.id', $alumnos_existentes_ids->all())
            ->select('users.id', 'users.document', 'users.name', DB::raw("CONCAT(users.document, ' - ', users.name, ' - ', w.name) as text"))
            ->get();

        return response()->json(['error' => false, 'alumnos' => $alumnos], 200);
    }

    public function eliminarRelacionEntrenadorAlumno(Request $request)
    {
        $entrenador = $request->entrenador;
        $alumno = $request->alumno;

        EntrenadorUsuario::where('trainer_id', $entrenador['id'])->where('user_id', $alumno['id'])->delete();
        return response()->json(['error' => false, 'msg' => 'Relación Entrenador-Alumno eliminada.'], 200);
    }

    // CHECKLIST
    public function searchChecklist(Request $request)
    {
        $data = CheckList::gridCheckList($request->all());

        return $this->success($data);
    }


    /**
     * Process request to toggle value of active status (1 or 0)
     *
     * @param CheckList $checklist
     * @param Request $request
     * @return JsonResponse
     */
    public function status(CheckList $checklist, Request $request)
    {
        $checklist->update(['active' => !$checklist->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function deleteChecklist($checklist_id){
        $message = 'Eliminado exitosamente.';
        $type_message = 'success';
        if(ChecklistRpta::where('checklist_id',$checklist_id)->first()){
            $message = 'Este checklist no se puede eliminar debido a que tiene respuestas relacionadas.';
            $type_message = 'warning';
        }else{
            $checklist = Checklist::where('id',$checklist_id)->first();
            $checklist->checklist_actividades()->delete();
            $checklist->delete();
        }
        return response()->json([
            'type'=>$type_message,
            'data'=>[
                'msg' => $message]
            ],
        200);
    }

    public function listarChecklist(Request $request)
    {
        $data = $request->all();
        $apiResponse = CheckList::gridCheckList($data);

        return response()->json($apiResponse, 200);
    }

    public function guardarChecklist(Request $request)
    {
        $workspace = get_current_workspace();
        $data = $request->all();

        if(isset($data['duplicado']) && $data['duplicado'])
            $data['id'] = null;

        $type_checklist = Taxonomy::where('group', 'checklist')
                        ->where('type', 'type_checklist')
                        ->where('code', $data['type_checklist'])
                        ->first();
        $starts_at = (isset($data['starts_at']) && $data['starts_at']) ? $data['starts_at'] : null;
        $finishes_at = (isset($data['finishes_at']) && $data['finishes_at']) ? $data['finishes_at'] : null;

        //checklist
        $checklist = CheckList::updateOrCreate(
            ['id' => $data['id']],
            [
                'title' => $data['title'],
                'description' => $data['description'],
                'active' => $data['active'],
                'workspace_id' => $workspace->id,
                'type_id' => !is_null($type_checklist) ? $type_checklist->id : null,
                'starts_at' => $starts_at,
                'finishes_at' => $finishes_at
            ]
        );

        //actividades
        foreach ($data['checklist_actividades'] as $key => $checklist_actividad) {
            $type = Taxonomy::where('group', 'checklist')
                ->where('type', 'type')
                ->where('code', $checklist_actividad['type_name'])
                ->first();
            CheckListItem::updateOrCreate(
                ['id' => $checklist_actividad['id']],
                [
                    'activity' => $checklist_actividad['activity'],
                    'type_id' => !is_null($type) ? $type->id : null,
                    'active' => $checklist_actividad['active'],
                    'checklist_id' => $checklist->id,
                    'position' => $key + 1,
                ]
            );
        }

        if($data['type_checklist'] == 'libre')
        {
            // Segmentación directa
            if($data['type_segment'] == 'direct-segmentation')
            {
                $data['list_segments']['model_id'] = $checklist->id;
                $list_segments = (object) $data['list_segments'];

                (new Segment)->storeDirectSegmentation($list_segments);
            }
            // Segmentación por documento
            else if($data['type_segment'] == 'segmentation-by-document')
            {
                $data['list_segments_document']['model_id'] = $checklist->id;
                $list_segments = $data['list_segments_document'];

                (new Segment)->storeSegmentationByDocument($list_segments);
            }
        }
        else if($data['type_checklist'] == 'curso')
        {
            // Curso
            $cursos = collect($data['courses']);
            $checklist->courses()->sync($cursos->pluck('id'));
        }


        return $this->success(['msg' => 'Checklist creado. Se ha creado el checklist '.$checklist->title, 'checklist'=>$checklist]);
    }

    public function guardarActividadByID(Request $request)
    {
        $data = $request->all();
        $response = CheckListItem::guardarActividadByID($data);

        return $this->success($response);
        // return response()->json($response, 200);
    }

    public function eliminarActividadByID(Request $request)
    {
        $data = $request->all();
        CheckListItem::find($data['id'])->delete();
        return response()->json(['error' => false, 'msg' => 'Actividad de checklist eliminado.'], 200);
    }

    public function importChecklist(Request $request)
    {
        $info = [];
        if ($request->hasFile("archivo")) {
            $import = new ChecklistImport();
            Excel::import($import, $request->file('archivo'));
            $info = $import->get_data();
            return response()->json([
                'error' => false,
                'msg' => $info['msg'],
                'info' => $info
            ], 200);
        }
        return response()->json([
            'error' => true,
            'msg' => 'No se ha seleccionado ningún archivo',
            'info' => $info
        ], 200);
    }

    public function buscarCurso(Request $request)
    {
        $workspace = get_current_workspace();

        $filtro = $request->filtro;
        $cursos_filtered = Course::whereRelation('workspaces', 'id', $workspace->id)->filtroName($filtro)->where('active', 1)->get();
        $cursos = collect();
        foreach ($cursos_filtered as $curso) {
            $tempCarreras = collect();
            $workspace = !is_null($curso->workspaces) && count($curso->workspaces) ? $curso->workspaces[0]->name : '';
            $school = !is_null($curso->schools) && count($curso->schools) ? $curso->schools[0]->name : '';
            $temp = [
                'id' => $curso->id,
                'modulo' => $workspace,
                'curso' => $curso->name,
                'escuela' => $school,
                'nombre' => $workspace . ' - ' . $school . ' - ' . $curso->name,
                'carreras' => ''
            ];
            $cursos->push($temp);
        }
        return response()->json(['error' => false, 'data' => $cursos], 200);
    }
    // CHECKLIST

    // ALUMNOS
    public function buscarAlumnoGestor($dni)
    {
        $usuario = User::where('document', $dni)->first();
        if (!$usuario) {
            return response()->json([
                'error' => true,
                'msg' => 'Alumno no encontrado.'
            ], 200);
        }
        $is_student = EntrenadorUsuario::alumno($usuario->id)->first();
        if (is_null($is_student)) {
            return response()->json([
                'error' => true,
                'msg' => 'El usuario no es un alumno.'
            ], 200);
        }

        // Entrenador activo
        $entrenadorActivo = EntrenadorUsuario::where('user_id', $usuario->id)->where('active', 1)->first();
        if ($is_student && $entrenadorActivo) {
            $entrenador = User::find($is_student->trainer_id);
            $usuario->entrenador = $entrenador->document . ' - ' . $entrenador->name;
        } else {
            $usuario->entrenador = 'El alumno no tiene un entrenador actual';
        }

        // Checklists del alumno
        $checklists = ChecklistRpta::with('checklistRelation', 'rpta_items')->where('student_id', $usuario->id)->get();
        $checklistTemp = collect();
        $checklists->each(static function ($checklist_rpta, $key) use ($checklistTemp) {
            //            dd($checklist_rpta);
            $checklistTemp->push([
                'titulo' => $checklist_rpta->checklistRelation->titulo,
                'avance' => $checklist_rpta->porcentaje
            ]);
        });
        $usuario->checklists = $checklistTemp;
        $usuario->cargo = '';
        $usuario->botica = '';
        $usuario->grupo_nombre = '';

        return response()->json([
            'error' => false,
            'alumno' => $usuario->only('document', 'name', 'cargo', 'botica', 'grupo_nombre', 'entrenador', 'checklists')
        ], 200);
    }
    // ALUMNOS

    /* ============================================================================================================== */


    /* APIs */

    public function alumnos(Request $request)
    {
        $user = auth('api')->user();
        $data = [
            'entrenador_dni' => $user->dni,
            'filtro' => $request->filtro
        ];
        $apiResponse = EntrenadorUsuario::alumnosApi($data);

        return response()->json($apiResponse, 200);
    }

    public function getChecklistsByAlumno(Usuario $alumno)
    {
        /*$user = auth('api')->user();*/
        //        $response = CheckList::getChecklistsByAlumno($user->id, $alumno->id);
        $response = CheckList::getChecklistsByAlumno($alumno->id);

        return response()->json($response, 200);
    }

    public function marcarActividad(Request $request)
    {
        $alumno_id = $request->alumno_id;
        $entrenador = EntrenadorUsuario::where('usuario_id', $alumno_id)->where('estado', 1)->first();
        $checklist_id = $request->checklist_id;

        $estado = $request->estado;
        $actividad_id = $request->actividad_id;

        $checklistRpta = ChecklistRpta::checklist($checklist_id)->alumno($alumno_id)->entrenador($entrenador->entrenador_id)->first();

        $checklistRptaItem = ChecklistRptaItem::where('checklist_rpta_id', $checklistRpta->id)->where('checklist_item_id', $actividad_id)->first();
        if (!$checklistRptaItem) {
            $checklistRptaItem = ChecklistRptaItem::create([
                'checklist_rpta_id' => $checklistRpta->id,
                'checklist_item_id' => $actividad_id,
                'calificacion' => $estado
            ]);
        }
        $checklistRptaItem->calificacion = $estado;
        $checklistRptaItem->save();

        ChecklistRpta::actualizarChecklistRpta($checklistRpta);

        return response()->json([
            'error' => false,
            'msg' => 'Actividad actualizada.'
            /*'checklist_rpta_item' => $checklistRptaItem*/
        ], 200);
    }


    /* ============================================================================================================== */
}
