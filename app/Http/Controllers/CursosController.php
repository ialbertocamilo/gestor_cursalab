<?php

namespace App\Http\Controllers;

use App\Http\Requests\Curso\CursoEncuestaStoreUpdate;
use App\Http\Requests\Curso\CursosStoreUpdateRequest;
use App\Http\Requests\Curso\MoverCursoRequest;
use App\Http\Requests\CursoStoreRequest;

use App\Http\Resources\Curso\CursoSearchResource;
use DB;

// use App\Perfil;
// use App\Curso_perfil;
// use App\Posteo_perfil;
use App\Models\Media;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Carrera;
use App\Models\Abconfig;
use App\Models\Poll;
use App\Models\Pregunta;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\Curso_encuesta;

use Illuminate\Http\Request;

use App\Http\Controllers\ApiRest\HelperController;
use App\Models\Course;
use App\Models\Requirement;
use App\Models\School;

class CursosController extends Controller
{

    public function search(School $escuela, Request $request)
    {
        $workspace = session('workspace');
        $workspace_id = (is_array($workspace)) ? $workspace['id'] : null;

        $request->workspace_id = $workspace_id;
        $request->school_id = ($escuela->exists) ? $escuela->id : null;

        $cursos = Course::search($request);
        CursoSearchResource::collection($cursos);

        return $this->success($cursos);
    }

    public function searchCurso(School $escuela, Course $curso)
    {
        $scheduled_restarts = json_decode($curso->scheduled_restarts);
        $curso->scheduled_restarts = $scheduled_restarts->activado ?? false;
        $curso->scheduled_restarts_dias = $scheduled_restarts->reinicio_dias ?? 0;
        $curso->scheduled_restarts_horas = $scheduled_restarts->reinicio_horas ?? 0;
        $curso->scheduled_restarts_minutos = $scheduled_restarts->reinicio_minutos ?? 0;
        $form_selects =  $this->getFormSelects($escuela, $curso, true);
        $curso->makeHidden('reinicios_programado');

        $req_curso = Requirement::whereHasMorph('model', [Course::class], function ($query) use ($curso) {
            $query->where('id', $curso->id);
        })->first();
        $curso->requisito_id = (!is_null($req_curso)) ? $req_curso->requirement_id : '';

        $escuelas = collect();
        foreach ($curso->schools as $req) {
            $escuelas->push((object)[
                'id' => $req->id,
                'nombre' => $req->name,
            ]);
        }

        $curso->lista_escuelas = $escuelas;
        return $this->success([
            'curso' => $curso,
            'requisitos' => $form_selects['requisitos'],
            'escuelas' => $form_selects['escuelas'],
        ]);
    }

    public function storeCurso(School $escuela, CursosStoreUpdateRequest $request)
    {
        $data = $request->validated();

        $workspace = session('workspace');
        $workspace_id = (is_array($workspace)) ? $workspace['id'] : null;

        $data['workspace_id'] = $workspace_id;
        $data['school_id'] = ($escuela->exists) ? $escuela->id : null;
        $data['escuelas'] = $request->lista_escuelas;

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $curso = Course::storeRequest($data);

        $msg = 'Curso creado correctamente.';
        return $this->success(compact('curso', 'msg'));
    }

    public function updateCurso(School $escuela, Course $curso, CursosStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $validate = Course::validateCursoRequisito($data, $escuela, $curso);
        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $workspace = session('workspace');
        $workspace_id = (is_array($workspace)) ? $workspace['id'] : null;

        $data['workspace_id'] = $workspace_id;
        $data['school_id'] = ($escuela->exists) ? $escuela->id : null;
        $data['escuelas'] = $request->lista_escuelas;
        $data['active'] = ($data['active'] === 'true' or $data['active'] === true) ? 1 : 0;

        if (!$validate['validate'])
            return $this->success(compact('validate'), 422);

        $response_curso = Course::storeRequest($data, $curso);
        $response = [
            'curso' => $response_curso,
            'msg' => 'Curso actualizado correctamente.',
        ];

        $response['messages'] = Course::getMessagesActions($curso);

        return $this->success($response);
    }

    public function getFormSelects(School $escuela, Course $curso = null, $compactResponse = false)
    {
        $req_cursos = Course::join('course_school', 'course_school.course_id', '=', 'courses.id');
        if (!is_null($escuela) && $escuela->exists) {
            $req_cursos->where('school_id', $escuela->id);
        }
        if (!is_null($curso) && $curso->exists) {
            $req_cursos->where('course_id', '!=', $curso->id);
        }
        $req_cursos->get();

        $lista_escuelas = School::all()->where('active', 1);

        $requisitos = collect();
        $escuelas = collect();
        foreach ($req_cursos as $req) {
            $requisitos->push((object)[
                'id' => $req->id,
                'nombre' => $req->name,
                'carreras' => ''
            ]);
        }
        foreach ($lista_escuelas as $req) {
            $escuelas->push((object)[
                'id' => $req->id,
                'nombre' => $req->name,
            ]);
        }

        $response = compact('escuelas', 'requisitos');
        return $compactResponse ? $response : $this->success($response);
    }

    public function temas(Categoria $categoria, Curso $curso, Request $request)
    {
        $posteos = $curso->temas()->orderBy('orden')->paginate();
        foreach ($posteos as $posteo) {
            if ($posteo->tipo_ev) {
                $tipo = ($posteo->tipo_ev == 'calificada') ? 'selecciona' : 'texto';
                $posteo->preguntas_count = Pregunta::where('tipo_pregunta', $tipo)->where('post_id', $posteo->id)->count();
            } else {
                $posteo->preguntas_count = 0;
            }
        }

        // if ($request->has('pid')) {
        //     // $temaspp = Posteo_perfil::select('posteo_id')->where('perfile_id', $request->input('pid'))->pluck('posteo_id');
        //     // $posteos = $curso->temas()->whereIn('id', $temaspp)->orderBy('orden')->paginate();

        // } else {
        //     // $posteos = $curso->temas()->withCount(['preguntas'])->orderBy('orden')->paginate();
        // }
        // $perfiles = DB::table('perfiles AS p')
        //                 ->select('p.id','p.nombre')
        //                 ->join('posteo_perfil AS cp','p.id','=','cp.perfile_id')
        //                 ->join('posteos AS c','c.id','=','cp.posteo_id')
        //                  ->where('c.curso_id', $curso->id)
        //                  ->where('p.estado', 1)
        //                  ->groupBy('p.id')
        //                  ->orderBy('p.id','DESC')
        //                  ->get();

        // return $posteos;
        return view('cursos.temas', compact('curso', 'posteos', 'categoria'));
    }

    // public function index(Request $request)
    // {
    //     if ($request->has('q')) {
    //         $question = $request->input('q');
    //         // return $question;
    //         $cursos = Curso::where('nombre', 'like', '%'.$question.'%')->paginate();
    //     }else{
    //         $cursos = Curso::paginate();
    //     }

    //     return view('cursos.index', compact('cursos'));
    // }


    public function create(Categoria $categoria)
    {
        $config_id = $categoria->config->id;

        $config_array = Abconfig::select('id', 'etapa')->pluck('etapa', 'id');
        $cates_array = Categoria::select('id', 'nombre')->where('config_id', $config_id)->orderBaay('orden')->pluck('nombre', 'id');
        $carrera_array = Carrera::select('id', 'nombre')->pluck('nombre', 'id');
        $cursos_array = Curso::select('id', 'nombre')->where('categoria_id', $categoria->id)->where('estado', 1)->orderBy('orden')->pluck('nombre', 'id');

        $requisitos = DB::table('cursos AS c')
            ->select(DB::raw('c.id, c.nombre, c.orden, u.carrera_id'))
            ->join('curricula AS u', 'c.id', '=', 'u.curso_id')
            ->where('c.categoria_id', $categoria->id)
            ->where('c.estado', 1)
            ->orderBy('c.orden')
            ->get();
        // $requisitos->prepend('NINGUNO', '');
        // return $requisitos;

        $carreras = Carrera::where('config_id', $config_id)->where('estado', 1)->orderBy('nombre')->get();
        $curricula = [];
        // return $carreras;

        return view('cursos.create', compact('config_id', 'config_array', 'cates_array', 'carrera_array', 'cursos_array', 'requisitos', 'categoria', 'carreras', 'curricula'));
    }

    public function store(Categoria $categoria, CursoStoreRequest $request)
    {
        // return $request->all();
        // return $request->ciclos;
        // Mover imagen a carpea public/images
        // $image= $request->file('imagen');
        // $new_name = rand() . '.' . $image->getClientOriginalExtension();
        // $image->move(public_path('images'), $new_name);

        //cambiar valor de name en el request
        $data = $request->all();
        $data['libre'] = ($categoria->modalidad == 'libre') ? 1 : 0;
        // $data['libre'] = isset($data['libre']) ? 1 : 0 ;
        // if ($request->filled('imagen')) {
        //     $data['imagen'] = 'images/' . $request->imagen;
        // }
        if ($request->filled('imagen')) {
            $data['imagen'] = $request->imagen;
        }

        $data['reinicios_programado'] = HelperController::getDataReinicioAutomatico($data);

        $curso = Curso::create($data);


        if ($request->estado == "1") {
            /**
             * REGISTRAR AUDITORIA
             */
            $descripcion = $curso->estado == "1" ? "Se creó el curso: $curso->id, con estado ACTIVO." : "Se creó el curso: $curso->id, con estado INACTIVO.";
            $auditoria = new AuditoriaController();
            $afecta = ['tablas_resumen'];
            $auditoria->registrarAuditoria('curso', $curso->id, $descripcion, $afecta, auth()->user()->id);
        }

        //guardar curricula
        // if ($request->has('cantidad_bloques')) {
        //     for ($i=0; $i < $request->cantidad_bloques; $i++) {
        //         $carrera_id = $data['carrera'.$i];
        //         $ciclo_ids = $data['ciclo'.$i];
        //         // Buscar multiples (tambien aplica para 1)
        //         $ciclo_arr = explode (",", $ciclo_ids);
        //         // Según cantidad de ciclo IDs encontrados hace las inserciones
        //         foreach ($ciclo_arr as $key => $value) {
        //             $curricula = new Curricula;
        //             $curricula->carrera_id = $carrera_id;
        //             $curricula->curso_id = $curso->id;
        //             $curricula->ciclo_id = $value;
        //             $curricula->estado = 1;
        //             $curricula->save();
        //         }

        //     }
        // }
        return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id])
            ->with('info', 'Registro guardado con éxito');
    }

    public function edit(Categoria $categoria, Curso $curso)
    {
        $config_id = $categoria->config->id;

        $config_array = Abconfig::select('id', 'etapa')->pluck('etapa', 'id');
        $cates_array = Categoria::select('id', 'nombre')->where('config_id', $config_id)->orderBy('orden')->pluck('nombre', 'id');
        $carrera_array = Carrera::select('id', 'nombre')->where('config_id', $config_id)->pluck('nombre', 'id');
        // $ciclos_array = Ciclo::select('id','nombre')->pluck('nombre','id' );
        $cursos_array = Curso::select('id', 'nombre')->where('categoria_id', $categoria->id)->where('estado', 1)->orderBy('orden')->pluck('nombre', 'id');
        $requisitos = DB::table('cursos AS c')
            ->select(DB::raw('c.id, c.nombre, c.orden, u.carrera_id'))
            ->join('curricula AS u', 'c.id', '=', 'u.curso_id')
            ->where('c.id', "!=", $curso->id)
            ->where('c.categoria_id', $categoria->id)
            ->where('c.estado', 1)
            ->where('c.libre', $curso->libre)
            ->orderBy('c.orden')
            ->get();
        $carreras = Carrera::where('config_id', $config_id)->where('estado', 1)->orderBy('nombre')->get();

        // $curricula = $this->get_curricula_agrupada($curso->id);

        if ($curso->imagen != "") {
            $curso->imagen = str_replace("images/", "", $curso->imagen);
        }

        return view('cursos.edit', compact('config_id', 'curso', 'config_array', 'cates_array', 'carrera_array', 'cursos_array', 'requisitos', 'categoria', 'carreras'));
    }

    public function get_requisitos(Request $request)
    {
        $requisitos = Curso::select('id', 'nombre')->where('categoria_id', $request->categoria_id)
            ->where('estado', 1)->where('libre', $request->libre)->where('id', "!=", $request->curso_id)->with([
                'curricula' => function ($q) {
                    $q->select('id', 'curso_id', 'carrera_id');
                },
                'curricula.carrera' => function ($q) {
                    $q->select('id', 'nombre');
                },
            ])->orderBy('orden')->get('id');
        return response()->json(compact('requisitos'));
    }

    public static function get_curricula_agrupada($curso_id)
    {
        $curricula_res = Curricula::select('carrera_id')->where('curso_id', $curso_id)->groupBy('carrera_id')->get();
        $curricula = [];
        foreach ($curricula_res as $curri) {
            $curricula_res2 = Curricula::select('ciclo_id')->where('curso_id', $curso_id)->where('carrera_id', $curri->carrera_id)->get();
            $ciclos = [];
            foreach ($curricula_res2 as $curri2) {
                $ciclos[] = $curri2->ciclo_id;
            }
            $curricula[] = [
                "carrera_id" => $curri->carrera_id,
                "ciclos" => $ciclos
            ];
        }
        // dd($curricula);
        return $curricula;
    }

    public function update(Categoria $categoria, CursoStoreRequest $request, Curso $curso)
    {
        $data = $request->all();


        // $data['libre'] = isset($data['libre']) ? 1 : 0 ;

        // if ($request->filled('imagen')) {
        //     $data['imagen'] = 'images/' . $request->imagen;
        // }
        if ($request->filled('imagen')) {
            $data['imagen'] = $request->imagen;
        }
        $estado_curso = false;
        //Cambiar el valor del modelo persistiendo la data en la BD
        foreach ($data as $key => $d) {
            if (isset($curso[$key])) {
                $curso[$key] = $d;
            }
        }
        $move_curso = ($data['categoria_id'] != $data['move_categoria_id']) ? true : false;
        $q_requisito = Curso::select('id', 'nombre', 'categoria_id')->where('requisito_id', $curso->id)->get();
        if ($curso->isDirty('estado') || ($move_curso)) {
            //TOMAR EN CUENTA LOS REQUISITOS
            if (count($q_requisito) > 0 && (($curso->estado == 0) || $move_curso)) {
                $li = "<ul>";
                foreach ($q_requisito as $req) {
                    $redirect = '/categorias/' . $req->categoria_id . '/cursos/' . $req->id . '/edit';
                    $li = $li . "<div class='d-flex align-items-center'><li class='ml-4 mt-2'>" . $req->nombre . "</li><a href='" . $redirect . "' target='_blank'><i class='fas fa-external-link-alt ml-2'></a></i></div>";
                }
                $li = $li . "</ul>";
                $act_text = ($data['categoria_id'] != $data['move_categoria_id']) ? 'mover' : 'desactivar';
                return redirect()->route('cursos.edit', [$categoria->id, $curso->id])
                    ->with('modal-info', '<strong>No se guadaron los cambios.</strong> <br>
                    Para poder ' . $act_text . ' este curso es necesario quitar el requisito en los siguientes cursos' . $li);
            }

            $afecta = [];
            $estado_curso = true;
            Posteo::where('curso_id', $curso->id)->update([
                'estado' => $curso->estado
            ]);
            /**
             * REGISTRAR AUDITORIA
             */
            $descripcion = $curso->estado == "1" ? "El curso: $curso->id, se actualizó con estado ACTIVO." : "El curso: $curso->id, se actualizó con estado INACTIVO.";
            if (count($curso->temas) > 0) {
                $afecta[] = 'tablas_resumen';
            }
            $auditoria = new AuditoriaController();
            $auditoria->registrarAuditoria('curso', $curso->id, $descripcion, $afecta, auth()->user()->id);
        }
        // if($curso->isDirty('libre') && count($q_requisito)>0){
        //     $li  = "<ul>";
        //     foreach ($q_requisito as $req) {
        //         $redirect = '/categorias/'.$req->categoria_id.'/cursos/'.$req->id.'/edit';
        //         $li = $li."<div class='d-flex align-items-center'><li class='ml-4 mt-2'>".$req->nombre."</li><a href='".$redirect."' target='_blank'><i class='fas fa-external-link-alt ml-2'></a></i></div>";
        //     }
        //     $li = $li."</ul>";
        //     return redirect()->route('cursos.edit', [$categoria->id, $curso->id])
        //         ->with('modal-info', '<strong>No se guadaron los cambios.</strong> <br>
        //         Para poder cambiar el estado de libre de este curso es necesario quitar el requisito en los siguientes cursos'.$li);
        // }
        // $curso->update($data);
        $help = new HelperController();
        $data['reinicios_programado'] = $help->getDataReinicioAutomatico($data);
        Curso::find($curso->id)->update($data);
        if ($move_curso) {
            DB::table('cursos')->where('id', $curso->id)->update(
                [
                    'categoria_id' => $data['move_categoria_id'],
                    'requisito_id' => null,
                ]
            );
            DB::table('posteos')->where('curso_id', $curso->id)->update(['categoria_id' => $data['move_categoria_id']]);
            DB::table('ev_abiertas')->where('curso_id', $curso->id)->update(['categoria_id' => $data['move_categoria_id']]);
            DB::table('pruebas')->where('curso_id', $curso->id)->update(['categoria_id' => $data['move_categoria_id']]);
            DB::table('resumen_x_curso')->where('curso_id', $curso->id)->update(['categoria_id' => $data['move_categoria_id']]);
            $categoria_nueva = Categoria::where('id', $data['move_categoria_id'])->select('modalidad')->first();
            $libre = ($categoria_nueva->modalidad == 'libre') ? 1 : 0;
            Curso::find($curso->id)->update(['libre' => $libre]);
            return redirect()->route('categorias.cursos', [$categoria->config->id, $data['move_categoria_id'], '#curso_' . $curso->id])
                ->with('modal-info', '<strong>Curso movido con éxito.</strong><br>');
        }
        //guardar curricula
        // $curso->curricula()->delete(); //eliminar antes de actualizar

        // if ($request->has('cantidad_bloques')) {
        //     for ($i=0; $i < $request->cantidad_bloques; $i++) {
        //         $carrera_id = $data['carrera'.$i];
        //         $ciclo_ids = $data['ciclo'.$i];
        //         // Buscar multiples (tambien aplica para 1)
        //         $ciclo_arr = explode (",", $ciclo_ids);
        //         // Según cantidad de ciclo IDs encontrados hace las inserciones
        //         foreach ($ciclo_arr as $key => $value) {
        //             $curricula = new Curricula;
        //             $curricula->carrera_id = $carrera_id;
        //             $curricula->curso_id = $curso->id;
        //             $curricula->ciclo_id = $value;
        //             $curricula->estado = 1;
        //             $curricula->save();
        //         }

        //     }
        // }

        // if ($request->has('ciclos')) {
        //     //eliminar anteriores datos de posteo perfil
        //     $curso->curricula()->delete();
        //     //guardar curricula
        //     $ciclos = $request->ciclos;
        //     foreach ($ciclos as $key => $value) {
        //         if($value > 0){
        //             $ciclo_obj = Ciclo::where('id', $value)->first();

        //             $curricula = new Curricula;
        //             $curricula->carrera_id = $ciclo_obj->carrera_id;
        //             $curricula->curso_id = $curso->id;
        //             $curricula->ciclo_id = $value;
        //             $curricula->estado = 1;
        //             $curricula->save();
        //         }
        //     }
        // }


        // return redirect()->route('cursos.edit', $curso->id)
        //         ->with('info', 'Cursoo actualizado con éxito');

        // if ($curso->wasChanged('estado')) {
        if ($estado_curso) {
            if (count($curso->temas) > 0) {
                return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id, '#curso_' . $curso->id])
                    ->with('modal-info', '<strong>Curso actualizado con éxito.</strong> <br>
                        Este cambio produce actualizaciones en el avance de los usuarios, que se ejecutarán dentro de 20 minutos.
                        Las actualizaciones se verán reflejadas en la app y en los reportes al finalizar este proceso.');
            } else {
                return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id, '#curso_' . $curso->id])
                    ->with('info', 'Registro actualizado con éxito');
            }
        } else {
            return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id, '#curso_' . $curso->id])
                ->with('info', 'Registro actualizado con éxito');
        }
        // return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id])
        //     ->with('info', 'Registro actualizado con éxito');
    }


    public function destroy(School $escuela, Course $curso)
    {
        // \File::delete(public_path().'/'.$curso->imagen);
        $q_requisito = Course::select('id', 'name')->where('requisito_id', $curso->id)->get();
        if (count($q_requisito) > 0) {
            $li = "<ul>";
            foreach ($q_requisito as $req) {
                $li = $li . "<li class='ml-4 mt-2'>" . $req->nombre . "</li>";
            }
            $li = $li . "</ul>";
            return redirect()->route('categorias.cursos', [$escuela->id, $escuela->id])
                ->with('modal-info', '<strong>No se puede eliminar este Curso.</strong> <br>
                Para poder eliminar este curso es necesario quitar el requisito en los siguientes cursos' . $li);
        }
        // SOLO ACTUALIZAR LAS TABLAS RESUMEN SI EL TEMA ESTABA ACTIVO
        $descripcion = "El curso $curso->id, se eliminó.";
        $afecta = [];
        if ($curso->estado == 1) {
            $afecta[] = 'tablas_resumen';
        }
        /**
         * REGISTRAR AUDITORIA
         */
        $auditoria = new AuditoriaController();
        $auditoria->registrarAuditoria('curso', $curso->id, $descripcion, $afecta, auth()->user()->id);

        $temas_count = count($curso->temas);
        $curso_estado = $curso->estado;

        $curso->delete();

        if ($curso_estado == 1) {

            if ($temas_count > 0) {
                return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id])
                    ->with('modal-info', '<strong>Curso eliminado correctamente.</strong> <br>
                        Este cambio produce actualizaciones en el avance de los usuarios, que se ejecutarán dentro de 20 minutos.
                        Las actualizaciones se verán reflejadas en la app y en los reportes al finalizar este proceso.');
            } else {
                return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id])
                    ->with('info', 'Eliminado correctamente.');
            }
        } else {
            return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id])
                ->with('info', 'Eliminado correctamente.');
        }

        // return redirect()->route('categorias.cursos', [$categoria->config->id, $categoria->id])->with('info', 'Eliminado Correctamente');
    }


    ////////////////////// CURSO ENCUESTA ////////////////////////////
    public function getEncuesta(School $escuela, Course $curso)
    {
        $encuestas = Poll::select('id', 'titulo')->select('titulo as nombre', 'id')->get();
        $encuestas->prepend(['nombre' => 'Ninguno', 'id' => "ninguno"]);
        $curso->encuesta_id = $curso->encuesta->encuesta_id ?? "ninguno";
        return $this->success(compact('encuestas', 'curso'));
    }

    public function storeUpdateEncuesta(School $escuela, Course $curso, CursoEncuestaStoreUpdate $request)
    {
        $data = $request->validated();
        if ($data['encuesta_id'] === "ninguno") {
            $curso->encuesta->delete();
            return $this->success(['msg' => 'Encuesta removida.']);
        }

        $curso->polls()->sync($data['encuesta_id']);

        return $this->success(['msg' => 'Encuesta actualizada.']);
    }

    public function create_CE(Curso $curso)
    {
        $encuestas_array = Poll::select('id', 'titulo')->pluck('titulo', 'id');
        $encuestas_array->prepend('NINGUNO', '');

        return view('cursos.create_encuesta', compact('encuestas_array', 'curso'));
    }

    public function store_CE(Curso $curso, Request $request)
    {

        $ce = new Curso_encuesta;
        $ce->curso_id = $request->curso_id;
        $ce->encuesta_id = $request->encuesta_id;

        if ($request->encuesta_id != "" || $request->encuesta_id != NULL) {
            $ce->save();
        }

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])
            ->with('info', 'Registro guardado con éxito');
    }

    public function edit_CE(Curso $curso, Curso_encuesta $ce)
    {

        $encuestas_array = Poll::select('id', 'titulo')->where('tipo', 'xcurso')->where('estado', 1)->pluck('titulo', 'id');
        $encuestas_array->prepend('NINGUNO', '');
        // return $encuestas_array;
        return view('cursos.edit_encuesta', compact('curso', 'ce', 'encuestas_array'));
    }

    public function update_CE(Curso $curso, Request $request, Curso_encuesta $ce)
    {

        $ce->curso_id = $request->curso_id;
        $ce->encuesta_id = $request->encuesta_id;
        if ($request->encuesta_id != "" || $request->encuesta_id != NULL) {
            $ce->save();
        } else {
            $ce->delete();
        }

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])
            ->with('info', 'Registro actualizado con éxito');
    }

    public function destroy_CE(Curso $curso, Curso_encuesta $ce)
    {
        $ce->delete();

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])->with('info', 'Eliminado Correctamente');
    }

    public function destroyCurso(School $escuela, Course $curso, Request $request)
    {
        if ($request->withValidations == 0) {

            $validate = Course::validateCursoEliminar($escuela, $curso);

            if (!$validate['validate'])
                return $this->success(compact('validate'), 422);
        }

        $curso->delete();

        $response = [
            'curso' => $curso,
            'msg' => 'Estado eliminado correctamente.',
        ];

        $response['messages'] = Curso::getMessagesActions($curso, 'Curso eliminado correctamente');

        return $this->success($response);
    }

    public function moverCurso(Abconfig $abconfig, Categoria $categoria, Curso $curso, MoverCursoRequest $request)
    {
        $data = $request->validated();
        $validate = Curso::validateMoverCurso($curso);
        //        dd($validate);
        if (!$validate['validate'])
            return $this->success(compact('validate'), 422);

        $curso = Curso::moverCurso($curso, $data['escuela_id']);

        return $this->success(['msg' => 'El curso se movió correctamente.']);
    }

    public function updateStatus(School $escuela, Course $curso, Request $request)
    {
        $active = ($curso->active == 1) ? 0 : 1;
        if ($request->withValidations == 0) {

            $validate = Course::validateUpdateStatus($escuela, $curso, $active);

            if (!$validate['validate'])
                return $this->success(compact('validate'), 422);
        }
        $curso->active = $active;
        $curso->save();

        $response = [
            'curso' => $curso,
            'msg' => 'Estado actualizado con éxito.',
        ];

        $response['messages'] = Course::getMessagesActions($curso);

        return $this->success($response);
    }
}
