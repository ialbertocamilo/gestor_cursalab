<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\Usuario\UsuarioSearchResource;
use App\Models\Abconfig;
use App\Models\Botica;
use App\Models\Cargo;
use App\Models\Carrera;
use App\Models\Categoria;
use App\Models\Ciclo;
use App\Models\Criterio;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Ingreso;
use App\Models\Matricula;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Reinicio;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\User;
use App\Models\Usuario;
use App\Models\Workspace;
use App\Services\FileService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

// use App\Perfil;

class UsuarioController extends Controller
{

    /**
     * Process request to load authenticated user and its workspace
     */
    public function session(Request $request)
    {

        if (Auth::check()) {

            $user = Auth::user();
            $session = $request->session()->all();
            $workspace = $session['workspace'];
            $workspace['logo'] = FileService::generateUrl($workspace['logo'] ?? '');

            return [
                'user' => [
                    'username' => $user->username,
                    'fullname' => $user->fullname
                ],
                'session' => [
                    'workspace' => $workspace
                ]
            ];
        }
    }

    /**
     * Process request to update workspace in session
     *
     * @param Request $request
     * @param Workspace $workspace
     */
    public function updateWorkspaceInSession(Request $request, Workspace $workspace)
    {

        // update workspace value in Session

        Session::put('workspace', $workspace);
    }

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $sub_workspaces_id = $workspace?->subworkspaces?->pluck('id');
        info($sub_workspaces_id);
        $request->merge(['sub_workspaces_id' => $sub_workspaces_id]);

        $users = User::search($request);

        UsuarioSearchResource::collection($users);

        return $this->success($users);
    }

    public function getListSelects()
    {
        $workspace = get_current_workspace();

        $sub_workspaces = Workspace::where('parent_id', $workspace?->id)->get();

        return $this->success(['sub_workspaces' => $sub_workspaces]);
    }

    public function edit(User $user)
    {
        $current_workspace_criterion_list = $this->getFormSelects(true);
        $user_criteria = [];

        foreach ($current_workspace_criterion_list as $criterion) {
            $value = $user->criterion_values->where('criterion_id', $criterion->id);
            $user_criterion_value = $criterion->multiple ?
                $value->pluck('id') : $value?->first()?->id;

            $user_criteria[$criterion->code] = $user_criterion_value;
        }


//        $criterion_grouped = $user->criterion_values()->with('criterion')->get()
//            ->groupBy('criterion.code')->toArray();
//
//        $criterion_list = [];
//        foreach ($criterion_grouped as $code => $criterion_values) {
//            if (count($criterion_values) == 1 || count($criterion_values) == 0) {
//                $criterion_list[$code] = $criterion_values[0]['id'];
//            } else if(count($criterion_values) > 1){
//                foreach ($criterion_values as $criterion_value) {
//                    $criterion_list[$code][] = [
//                        'id' => $criterion_value['id'],
//                        'value_text' => $criterion_value['value_text']
//                    ];
//                }
//            }
//        }
        $user->criterion_list = $user_criteria;
//        $user->criterion_list = $criterion_grouped;
        return $this->success([
            'usuario' => $user,
            'criteria' => $this->getFormSelects(true)
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        $current_workspace = get_current_workspace();
        $criteria = Criterion::query()
            ->with([
                'values' => function ($q) use($current_workspace) {
                    $q->with('parents:id,criterion_id,value_text')
                        ->whereHas('workspaces', function ($q2) use($current_workspace){
                            $q2->where('id', $current_workspace->id);
                        })
                        ->select('id', 'criterion_id', 'exclusive_criterion_id', 'value_text', 'parent_id');
                }
            ])
            ->whereRelation('workspaces', 'id', $current_workspace?->id)
            ->select('id', 'name', 'code', 'parent_id', 'multiple', 'required')
            ->orderBy('position')
            ->get();

        $response = compact('criteria');

        return $compactResponse ? $criteria : $this->success($response);
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();

        User::storeRequest($data);

        return $this->success(['msg' => 'Usuario creado correctamente.']);
    }

    public function update(UserStoreRequest $request, User $user)
    {
        $data = $request->validated();

        User::storeRequest($data, $user);

        return $this->success(['msg' => 'Usuario actualizado correctamente.']);
    }

    public function destroy(Usuario $usuario)
    {
        // \File::delete(public_path().'/'.$usuario->imagen);

        // $usuario->matricula()->delete();
        // $usuario->delete();

        // return back()->with('info', 'Eliminado Correctamente');

        \File::delete(public_path() . '/' . $usuario->imagen);
        $matriculas = Matricula::where('usuario_id', $usuario->id)->select(['id']);
        foreach ($matriculas as $matricula) {
            \Log::info($matricula->id);

            Matricula_criterio::where('matricula_id', $matricula->id)->destroy();
            Matricula::destroy($matricula->id);
        }
        // $usuario->matricula()->delete();
        $usuario->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }

    // ==========================================================================================


    // RESETEO DE USUARIOS
    public function reset(Usuario $usuario)
    {

        $config = $usuario->config;
        $mod_eval = json_decode($config->mod_evaluaciones, true);
        // lista temas evaluados
        $temas = \DB::select(\DB::raw("SELECT t.id, t.nombre FROM pruebas p
                                                INNER JOIN posteos t ON t.id = p.posteo_id
                                                WHERE p.usuario_id = " . $usuario->id . "
                                                AND p.resultado = 0
                                                AND p.intentos >= " . $mod_eval['nro_intentos'] . "
                                                ORDER BY p.id DESC"));

        // lista cursos evaluados
        $cursos = \DB::select(\DB::raw("SELECT c.id, c.nombre FROM pruebas p
                                                INNER JOIN posteos t ON t.id = p.posteo_id
                                                INNER JOIN cursos c ON c.id = t.curso_id
                                                WHERE p.usuario_id = " . $usuario->id . "
                                                AND p.resultado = 0
                                                AND p.intentos >= " . $mod_eval['nro_intentos'] . "
                                                GROUP BY t.curso_id
                                                ORDER BY p.id DESC"));

        return $this->success(compact('temas', 'cursos', 'usuario'));

        // return view('usuarios.reset', compact('usuario', 'temas', 'cursos'));
    }

    // Reiniciar
    public function reset_x_tema(Usuario $usuario, Request $request)
    {
        if ($request->has('p')) {
            $posteo_id = $request->input('p');
            // $prueba_reinicio = $usuario->rpta_pruebas()->where('posteo_id', $posteo_id)->delete();
            $prueba_reinicio = Prueba::where('usuario_id', $usuario->id)->where('posteo_id', $posteo_id)
                ->update([
                    'intentos' => 0,
                    // 'rptas_ok' => NULL,
                    // 'rptas_fail' => NULL,
                    // 'nota' => NULL,
                    // 'resultado' => 0,
                    // 'usu_rptas' => NULL,
                    'fuente' => 'reset']);
            //PONER ESTADO EN DESARROLLO
            $posteo = Posteo::where('id', $posteo_id)->select('curso_id')->first();
            $posteos = Posteo::where('curso_id', $posteo->curso_id)->select('id')->get();
            if (count($posteos) == 1) {
                $res = Resumen_x_curso::where('usuario_id', $usuario->id)->where('curso_id', $posteo->curso_id)->update([
                    'estado' => 'desarrollo',
                ]);
            }
            // Registrar reinicios
            $user = \Auth::user();
            $res = Reinicio::where('usuario_id', $usuario->id)->where('posteo_id', $posteo_id)->first();
            if (!$res) {

                $dip = new Reinicio;
                $dip->tipo = 'por_tema';
                $dip->usuario_id = $usuario->id;
                $dip->posteo_id = $posteo_id;
                $dip->curso_id = $posteo->curso_id;
                $dip->admin_id = $user->id;
                $dip->acumulado = 1;
                $dip->save();

            } else {
                $res->acumulado = $res->acumulado + 1;
                $res->save();
            }
            // Fin registro reinicios
            return $this->success(['msg' => 'Reinicio por tema exitoso']);
        }

        return $this->error('No se pudo completar el reinicio');
    }

    public function reset_x_curso(Usuario $usuario, Request $request)
    {
        if ($request->has('c')) {
            $curso_id = $request->input('c');

            $pruebas = \DB::select(\DB::raw("SELECT p.id FROM pruebas p
                                                    INNER JOIN posteos t ON t.id = p.posteo_id
                                                    INNER JOIN cursos c ON c.id = t.curso_id
                                                    WHERE p.usuario_id = " . $usuario->id . "
                                                    AND c.id = " . $curso_id . "
                                                    "));
            //AND p.resultado = 0

            // $rptas = $usuario->rpta_pruebas()->whereIn('id', collect($prueba_reinicio)->pluck('id'))->delete();

            $prueba_reinicio = Prueba::whereIn('id', collect($pruebas)->pluck('id'))
                ->update([
                    'intentos' => 0,
                    // 'rptas_ok' => NULL,
                    // 'rptas_fail' => NULL,
                    // 'nota' => NULL,
                    //  'resultado' => 0,
                    //  'usu_rptas' => NULL,
                    'fuente' => 'reset']);

            // Registrar reinicios
            $user = \Auth::user();
            $res = Reinicio::where('usuario_id', $usuario->id)->where('curso_id', $curso_id)->where('tipo', 'por_curso')->first();
            if (!$res) {
                $dip = new Reinicio;
                $dip->tipo = 'por_curso';
                $dip->usuario_id = $usuario->id;
                $dip->curso_id = $curso_id;
                $dip->admin_id = $user->id;
                $dip->acumulado = 1;
                $dip->save();
            } else {
                $res->acumulado = $res->acumulado + 1;
                $res->save();
            }
            // Fin registro reinicios

            return $this->success(['msg' => 'Reinicio por curso exitoso']);
        }

        return $this->error('No se pudo completar el reinicio');
    }

    public function reset_total(Usuario $usuario)
    {
        // $rptas = $usuario->rpta_pruebas()->get();
        // $rptas = $usuario->rpta_encuestas()->delete();

        // $rptas = $usuario->rpta_pruebas()->delete();

        $rptas = $usuario->rpta_pruebas()
            ->update([
                'intentos' => 0,
                // 'rptas_ok' => NULL,
                // 'rptas_fail' => NULL,
                // 'nota' => NULL,
                //  'resultado' => NULL,
                //  'usu_rptas' => NULL,
                'fuente' => 'reset'
            ]);

        // Registrar reinicios
        $user = \Auth::user();
        $res = Reinicio::where('usuario_id', $usuario->id)->where('tipo', 'total')->first();
        if (!$res) {
            $dip = new Reinicio;
            $dip->tipo = 'total';
            $dip->usuario_id = $usuario->id;
            $dip->admin_id = $user->id;
            $dip->acumulado = 1;
            $dip->save();
        } else {
            $res->acumulado = $res->acumulado + 1;
            $res->save();
        }
        // Fin registro reinicios
        return $this->success(['msg' => 'Reinicio total exitoso']);
    }

    public function crear(Request $request)
    {
        // return $request->all();
//        info($request->all());
        if ($request->usuario['id'] == 0) {

            $already_exist = Usuario::where('dni', trim($request->usuario['dni']))->first();

            if ($already_exist)
                return response()->json(['error' => true, 'msg' => 'El DNI ya ha sido registrado.'], 422);

            $modulo = Abconfig::findOrFail($request->usuario['modulo']['id']);

            $codigo = $modulo->codigo_matricula . '-' . date('dmy');
            $grupo_sistema = Grupo::firstOrCreate(['nombre' => $codigo, 'estado' => 1]);

            // CREAR
            $nuevo_usuario = new Usuario();
            $nuevo_usuario->config_id = $request->usuario['modulo']['id'];
            $nuevo_usuario->nombre = $request->usuario['nombre'];
            $nuevo_usuario->grupo_id = $grupo_sistema->id;
            // $nuevo_usuario->grupo_id = $request->usuario['grupo_sistema'];
//            $nuevo_usuario->botica_id =  $request->usuario['botica_id'];
            $nuevo_usuario->botica_id = $request->usuario['botica']['id'];
//            $botica = Botica::with('criterio')->where('id', $request->usuario['botica_id'])->first();
            $botica = Botica::with('criterio')->where('id', $request->usuario['botica']['id'])->first();
            $nuevo_usuario->botica = $botica->nombre;
            $nuevo_usuario->grupo = $botica->criterio_id;
            $nuevo_usuario->grupo_nombre = $botica->criterio->valor;
            $nuevo_usuario->dni = $request->usuario['dni'];
            $nuevo_usuario->password = Hash::make($request->usuario['password']);
            $nuevo_usuario->sexo = $request->usuario['sexo'] == 'M' ? 'MASCULINO' : 'FEMENINO';
            $nuevo_usuario->cargo = $request->usuario['cargo'];

            $nuevo_usuario->estado = $request->usuario['estado'];
            $nuevo_usuario->save();

            // //Consultar dias de configuracion
            // $dias = $nuevo_usuario->config->duracion_dias;
            // date_default_timezone_set('America/Lima');
            // //insertar vigencia
            // $vigencia = new Usuario_vigencia;
            // $vigencia->usuario_id = $nuevo_usuario->id;
            // $vigencia->fecha_inicio = date('Y-m-d');
            // $vigencia->fecha_fin = date('Y-m-d', strtotime($vigencia->fecha_inicio. ' + '.$dias.' days'));
            // $vigencia->save();

            // ENCONTRAR EL ULTIMO CICLO ACTIVO
            $ciclos = $request->ciclos;
            $_ciclos = collect($request->ciclos);
            $ciclos_activos = $_ciclos->where('estado', true);
            $ultimo_ciclo_activo = $ciclos_activos->last();

            foreach ($ciclos as $ciclo) {
                $matricula = new Matricula;
                $matricula->usuario_id = $nuevo_usuario->id;
                $matricula->carrera_id = $request->carrera_id;
                $matricula->ciclo_id = $ciclo['id'];
                $matricula->secuencia_ciclo = $ciclo['secuencia'];
                $matricula->presente = ($ultimo_ciclo_activo['id'] == $ciclo['id']) ? 1 : 0;
                if (count($ciclos_activos) == 1 && $ciclo['estado'] == true) {
                    $matricula->estado = 1;
                } else if ($ciclo['secuencia'] == 0) {
                    $matricula->estado = 0;
                } else {
                    $matricula->estado = $ciclo['estado'] ? 1 : 0;
                }
                $matricula->save();
                DB::table('matricula_criterio')->insert([
                    'matricula_id' => $matricula->id,
                    'criterio_id' => $request->grupo
                ]);
            }
            $hel = new HelperController();
            Resumen_general::insert([
                'usuario_id' => $nuevo_usuario->id,
                'cur_asignados' => count($hel->help_cursos_x_matricula($nuevo_usuario->id)),
            ]);
            return $this->success(['msg' => 'Usuario creado con éxito.']);
//            return response()->json(['msg'=> 'Usuario creado con éxito.'], 200);
        } else {
            // return $request->all();
            // EDITAR
            $usuario_editar = Usuario::find($request->usuario['id']);
//            $usuario_editar->config_id = $request->usuario['modulo'];
            $usuario_editar->nombre = $request->usuario['nombre'];
            // $usuario_editar->grupo_id = $request->usuario['grupo_sistema'];
            if ($usuario_editar->dni !== trim($request->usuario['dni'])) {
                $already_exist = Usuario::where('dni', trim($request->usuario['dni']))->first();

                if ($already_exist)
                    return response()->json(['msg' => 'El DNI ya ha sido registrado.'], 422);

                $usuario_editar->dni = trim($request->usuario['dni']);
            }

            if (isset($request->usuario['password']) and $request->usuario['password'] != "") {
                $usuario_editar->password = Hash::make($request->usuario['password']);
            }

            $usuario_editar->sexo = $request->usuario['sexo'] == 'M' ? 'MASCULINO' : 'FEMENINO';
            $usuario_editar->cargo = $request->usuario['cargo'];
            $usuario_editar->estado = $request->usuario['estado'];
//            $usuario_editar->botica_id =  $request->usuario['botica_id'];
            $usuario_editar->botica_id = $request->usuario['botica']['id'];
//            $botica = Botica::with('criterio')->where('id', $request->usuario['botica_id'])->first();
            $botica = Botica::with('criterio')->where('id', $request->usuario['botica']['id'])->first();
            $usuario_editar->botica = $botica->nombre;
            $usuario_editar->grupo = $botica->criterio_id;
            $usuario_editar->grupo_nombre = $botica->criterio->valor;
            $usuario_editar->save();
            // //Consultar dias de configuracion
            // $dias = $usuario_editar->config->duracion_dias;
            // date_default_timezone_set('America/Lima');
            // //insertar vigencia
            // $vigencia = $usuario_editar->vigencia;
            // $vigencia->usuario_id = $usuario_editar->id;
            // $vigencia->fecha_inicio = date('Y-m-d');
            // $vigencia->fecha_fin = date('Y-m-d', strtotime($vigencia->fecha_inicio. ' + '.$dias.' days'));
            // $vigencia->save();

            // ENCONTRAR EL ULTIMO CICLO ACTIVO
            $ciclos = $request->ciclos;
            $_ciclos = collect($request->ciclos);
            $ciclos_activos = $_ciclos->where('estado', true);
            $ultimo_ciclo_activo = $ciclos_activos->last();

            foreach ($ciclos as $ciclo) {
                $matricula = Matricula::find($ciclo['matricula_id']);
                $matricula->presente = ($ultimo_ciclo_activo['id'] == $ciclo['id']) ? 1 : 0;
                if (count($ciclos_activos) == 1 && $ciclo['estado'] == true) {
                    $matricula->estado = 1;
                } else if ($ciclo['secuencia'] == 0) {
                    $matricula->estado = 0;
                } else {
                    $matricula->estado = $ciclo['estado'] ? 1 : 0;
                }
                $matricula->save();
            }
            $helper = new HelperController();
            $curso_ids_matricula = $helper->help_cursos_x_matricula($usuario_editar->id);
            //LIMPIAR TABLAS RESUMENES
            Resumen_x_curso::where('usuario_id', $usuario_editar->id)->whereNotIn('curso_id', $curso_ids_matricula)->update(['estado_rxc' => 0]);
            // Resumen_general::where('usuario_id',$usuario_editar->id)->delete();
            //ACTUALIZAR TABLAS RESUMENES
            $rest_avance = new RestAvanceController();
            $ab_conf = Abconfig::select('mod_evaluaciones')->where('id', $request->usuario['modulo'])->first(['mod_evaluaciones']);
            $mod_eval = json_decode($ab_conf->mod_evaluaciones, true);
            foreach ($curso_ids_matricula as $cur_id) {
                // ACTUALIZAR RESUMENES
                $rest_avance->actualizar_resumen_x_curso($usuario_editar->id, $cur_id, $mod_eval['nro_intentos']);
            }
            $rest_avance->actualizar_resumen_general($usuario_editar->id);
            return $this->success(['msg' => 'Usuario actualizado con éxito.']);
//            return response()->json(['msg'=> 'Usuario actualizado con éxito.'], 200);

        }
    }

    public function getCoursesByUser(User $user)
    {
        $user->setCurrentCourses();

        return $this->success(compact('user'));
    }

    public function status($usuario_id)
    {
        $user = Usuario::find($usuario_id);
        $estado = ($user->estado == 1) ? 0 : 1;
        $user->estado = $estado;
        $user->save();
        return back()->with(['info' => 'Estado actualizado con éxito.']);
    }

    public function updateStatus(User $user)
    {
        info(!$user->active);
        $user->update(['active' => !$user->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    //**************************************************** REINICIO MASIVOS DE NOTAS POR CURSO ********************************************************/
    public function index_reinicios()
    {
        return view('masivo.index_reinicios');
    }

    public function reinicios_data()
    {
        $modulos = Abconfig::where('estado', 1)->get();
        $categorias = Categoria::where('estado', 1)->get();
        return response()->json(compact('modulos', 'categorias'), 200);
    }

    public function buscarCursosxEscuela($categoria_id)
    {
        $cursos = Curso::where('categoria_id', $categoria_id)->where('estado', 1)->get();
        return response()->json(compact('cursos'), 200);
    }

    public function buscarTemasxCurso($curso_id)
    {
        $posteos = Posteo::where('curso_id', $curso_id)->where('estado', 1)->get();
        return response()->json(compact('posteos'), 200);
    }

    public function validarReinicioIntentos(Request $request)
    {
        $tipo = $request->tipo; // TODOS o SOLO DESAPROBADOS
        $admin = $request->admin;
        $config = Abconfig::select('mod_evaluaciones')->where('id', $request->modulo)->first();
        $mod_eval = json_decode($config->mod_evaluaciones, true);
        $data = $this->validarDetallesReinicioIntentosMasivo($request->curso, $request->tema, $request->modulo, $tipo, $mod_eval, $admin['id']);
        return response()->json([
            'data' => $data,
            'mod_eval' => $mod_eval
        ], 200);
    }

    public function validarDetallesReinicioIntentosMasivo($curso_id, $posteo_id, $modulo_id, $tipo, $mod_eval, $admin_id)
    {
        $query_usuarios = Prueba::with('usuario')
            ->where('pruebas.fuente');

        if ($posteo_id == null) $query_usuarios->where('pruebas.curso_id', $curso_id); // Por Curso
        else $query_usuarios->where('pruebas.posteo_id', $posteo_id); // Por Tema

        if ($tipo['id'] == 1) { // Solo desaprobados
            $query_usuarios->where('pruebas.resultado', 0)
                ->where('pruebas.intentos', '>=', $mod_eval['nro_intentos']);
        }

        $usuarios = $query_usuarios->orderBy('pruebas.nota')->get();

        foreach ($usuarios as $key => $usuario) {
            $usuario->selected = false;
        }
        return [
            'pruebas' => $usuarios,
            'count_usuarios' => $usuarios->count()
        ];
    }

    public function reiniciarIntentosMasivos(Request $request)
    {
        $admin = $request->admin;
        $config = Abconfig::select('mod_evaluaciones')->where('id', $request->modulo)->first();
        $mod_eval = json_decode($config->mod_evaluaciones, true);
        $usuarios = $request->usuarios;
        $count_usuarios = count($usuarios);
        if ($request->tema == null) {
            $curso = Curso::where('id', $request->curso)->first();
            $data = $this->reinicioMasivoxCurso($request->curso, $request->modulo, $mod_eval, $admin['id'], $usuarios);
            $msg = "Se reiniciaron los intentos de " . $count_usuarios . " usuario(s) para el curso $curso->nombre.";
        } else {
            $tema = Posteo::where('id', $request->tema)->first();
            $data = $this->reinicioMasivoxTema($request->tema, $request->curso, $request->modulo, $mod_eval, $admin['id'], $usuarios);
            $msg = "Se reiniciaron los intentos de " . $count_usuarios . " usuario(s) para el tema $tema->nombre.";
        }
        return response()->json([
            'data' => $data,
            'mod_eval' => $mod_eval,
            'msg' => $msg
        ], 200);
    }

    public function reinicioMasivoxCurso($curso_id, $modulo_id, $mod_eval, $admin_id, $usuarios)
    {
        // $rest = new RestAvanceController;
        foreach ($usuarios as $key => $usuario) {
            $usuario_id = $usuario['usuario']['id'];
            $posteosIds = Prueba::join('posteos', 'posteos.id', 'pruebas.posteo_id')
                ->join('cursos', 'cursos.id', 'posteos.curso_id')
                ->where('pruebas.usuario_id', $usuario_id)
                ->where('cursos.id', $curso_id)
                ->pluck('pruebas.posteo_id');
            $prueba_reinicio = Prueba::reinicioIntentosMasivos($posteosIds, $usuario_id);
            // $visita          = Visita::where('usuario_id', $usuario_id)
            //                         ->whereIn('post_id', $posteosIds)
            //                         ->update([
            //                                 'tipo_tema' => "",
            //                                 'estado_tema' => ""
            //                             ]);

            // $rest->actualizar_resumen_x_curso($usuario_id, $curso_id, (int) $mod_eval['nro_intentos']);
            // $rest->actualizar_resumen_general($usuario_id);
            $reinicio = $this->actualizarReiniciosxReinicioIntentosMasivo('por_curso', $curso_id, $usuario_id, $admin_id);
        }
    }

    public function reinicioMasivoxTema($posteo_id, $curso_id, $modulo_id, $mod_eval, $admin_id, $usuarios)
    {
        // $rest = new RestAvanceController;
        foreach ($usuarios as $key => $usuario) {
            $usuario_id = $usuario['usuario']['id'];
            $prueba_reinicio = Prueba::reinicioIntentosMasivos([$posteo_id], $usuario_id);
            // $visita          = Visita::where('usuario_id', $usuario_id)
            //                         ->where('post_id', $posteo_id)
            //                         ->update([
            //                                 'tipo_tema' => "",
            //                                 'estado_tema' => ""
            //                             ]);

            // $rest->actualizar_resumen_x_curso($usuario_id, $curso_id, (int) $mod_eval['nro_intentos']);
            // $rest->actualizar_resumen_general($usuario_id);
            $reinicio = $this->actualizarReiniciosxReinicioIntentosMasivo('por_tema', $posteo_id, $usuario_id, $admin_id);
        }
    }

    public function actualizarReiniciosxReinicioIntentosMasivo($tipo, $recurso_id, $usuario_id, $admin_id)
    {
        switch ($tipo) {
            case 'por_tema':
                $reinicio = Reinicio::where('usuario_id', $usuario_id)->where('posteo_id', $recurso_id)->first();
                break;
            case 'por_curso':
                $reinicio = Reinicio::where('usuario_id', $usuario_id)->where('curso_id', $recurso_id)->first();
                break;
        }
        if (!$reinicio) {
            $reinicio = new Reinicio;
            $reinicio->tipo = $tipo;
            $reinicio->usuario_id = $usuario_id;
            if ($tipo == 'por_tema') $reinicio->posteo_id = $recurso_id;
            else if ($tipo == 'por_curso') $reinicio->curso_id = $recurso_id;
            $reinicio->admin_id = $admin_id;
            $reinicio->acumulado = 1;
        } else {
            $reinicio->acumulado = $reinicio->acumulado + 1;
        }
        $reinicio->save();
    }
    //**************************************************** REINICIO MASIVOS DE NOTAS POR CURSO ********************************************************/
}
