<?php

namespace App\Http\Controllers;

use App\Http\Resources\Usuario\UsuarioSearchResource;
use Auth;

use App\Models\Cargo;
use App\Models\Ciclo;
use App\Models\Curso;

// use App\Perfil;
use App\Models\Grupo;
use App\Models\Botica;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Carrera;
use App\Models\Ingreso;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Reinicio;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\Matricula;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Usuario_vigencia;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UsuarioStoreRequest;
use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class UsuarioController extends Controller
{

    public function search(Request $request)
    {
        $users = Usuario::search($request);

        UsuarioSearchResource::collection($users);

        return $this->success($users);
    }

    public function getListSelects()
    {
        $modules = Abconfig::select('id', 'etapa as nombre')->get();
//        $carreras = Carrera::select('id', 'nombre')->get();

        return $this->success([
            'modules' => $modules,
//            'carreras' => $carreras,
        ]);
    }

    public function searchUser(Usuario $usuario)
    {
        $matriculas_pasadas = Matricula::join('carreras', 'carreras.id', 'matricula.carrera_id')
            ->join('ciclos', 'ciclos.id', 'matricula.ciclo_id')
            ->join('matricula_criterio', 'matricula_criterio.matricula_id', 'matricula.id')
            ->join('criterios', 'criterios.id', 'matricula_criterio.criterio_id')
            ->where('matricula.usuario_id', $usuario->id)
            ->select('criterios.valor as grupo', 'carreras.nombre as carrera', 'ciclos.nombre as ciclo',
                'matricula.estado as estado', 'matricula.secuencia_ciclo as secuencia', 'matricula.id as matricula_id',
                'ciclos.id as id')
            ->orderBy('matricula.secuencia_ciclo')
            ->get();
        $usuario->load('modulo');
        $usuario->load('botica.criterio');
        $usuario->load('matricula_presente.carrera');
        $usuario->grupo_sistema_nombre = $usuario->grupo_sistema->nombre ?? "GS no defnido";
        $usuario->matriculas_pasadas = $matriculas_pasadas;
        $usuario->password = null;

        $formSelects = $this->getFormSelects(true);

        return $this->success([
            'usuario' => $usuario,
            'modules' => $formSelects['modules'],
            'cargos' => $formSelects['cargos']
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        $modules = Abconfig::select('id', 'etapa as nombre')->get();
        $cargos = Cargo::select('id', 'nombre')->pluck('nombre');

        $response = compact('modules', 'cargos');

        return $compactResponse ? $response : $this->success($response);
    }


    public function index(Request $request)
    {
        // return $request->all();
        $query = Usuario::orderBy('usuarios.nombre');
        $question = $request->input('q');


        if ($request->has('q')) {
            $question = $request->input('q');
            $usuarios = Usuario::whereRaw('nombre like ? or dni like ?', ["%$question%", "%$question%"])->paginate(100);
        } else {
            $usuarios = Usuario::paginate(100);
        }


        // if ($request->has('ciclo_usuario') && $request->ciclo_usuario > 0) {
        //     $query->join('matricula', 'matricula.usuario_id', 'usuarios.id');
        //     $query->where('matricula.carrera_id', $request->carrera_usuario);
        //     $query->where('matricula.ciclo_id', $request->ciclo_usuario);
        //     $query->where('matricula.estado', 1);
        // } else {
        //     if ($request->has('carrera_usuario') && $request->carrera_usuario > 0) {
        //         $query->join('matricula', 'matricula.usuario_id', 'usuarios.id');
        //         $query->where('matricula.carrera_id', $request->carrera_usuario);
        //         $query->where('matricula.estado', 1);
        //     } else {
        //         if ($request->has('modulo_usuario') && $request->modulo_usuario > 0) {
        //             $query->where('usuarios.modulo_usuario', $request->modulo_usuario);
        //         }
        //     }
        // }

        // if ($request->has('q') && $question!= "") {
        //     $query->where(function($query) use($question) {
        //         $query->whereRaw('nombre like ? or dni like ?', ["%$question%","%$question%"]);
        //     });
        // }
        // if ($request->has('grupo_usuario') && $request->input('grupo_usuario') > 0 ) {
        //     $query->join('criterios','criterios.id', 'usuarios.grupo');
        //     $query->where('usuarios.grupo', $request->input('grupo_usuario'));
        // }

        // $query->groupBy('usuarios.id');

        // $usuarios = $query->paginate(100);
        // dd($usuarios);

        $config_arr = Abconfig::select('id', 'etapa')->pluck('etapa', 'id');
        $grupos = Criterio::where('tipo_criterio', 'GRUPO')->pluck('valor', 'id');

        // $usuarios_master = UsuarioMaster::get();
        // return $usuarios_master;
        return view('usuarios.index', compact('usuarios', 'config_arr', 'grupos'));
    }


    public function create()
    {
        // $grupos = Criterio::pluck('valor', 'id');
        // $grupos->put('0','Seleccione un valor');
        // $grupos = $grupos->sortKeys();

        $grupo_array = Grupo::select('id', 'nombre')->pluck('nombre', 'id');
        $config_query = Abconfig::select('id', 'etapa')->orderBy('id');
        $config_array = $config_query->pluck('etapa', 'id');

        // $first_config = $config_query->first();
        // if($first_config){
        //     $carreras = Carrera::where('estado', 1)->where('config_id', $first_config->id)->orderBy('nombre')->get();
        // }else{
        //     $carreras = Carrera::where('estado', 1)->orderBy('nombre')->get();
        // }

        return view('usuarios.create', compact('config_array', 'grupo_array'));
    }


    public function store(UsuarioStoreRequest $request)
    {
        if ($request->errores > 0) {
            // return response()->json(['msg' => 'Rellene todos los campos requeridos.'], 200, $headers);
            $request->curr_grupo_id = 0;
            return redirect()->back()->withInput($request->all())->withErrors(['msg' => 'Debe asignar una matricula para registrar al usuario.']);
        }

        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        $data['rol'] = 'default';
        $data['grupo'] = $request->curr_grupo_id;

        $usuario = Usuario::create($data);

        //Consultar dias de configuracion
        $dias = $usuario->config->duracion_dias;
        date_default_timezone_set('America/Lima');
        //insertar vigencia
        $vigencia = new Usuario_vigencia;
        $vigencia->usuario_id = $usuario->id;
        $vigencia->fecha_inicio = date('Y-m-d');
        $vigencia->fecha_fin = date('Y-m-d', strtotime($vigencia->fecha_inicio . ' + ' . $dias . ' days'));
        $vigencia->save();

        if (isset($request->curr_carrera) && isset($request->curr_ciclos) && $request->curr_carrera != "0" && $request->curr_ciclos != "0") {
            // SABER EL NUMERO DE MATRICULAS DEL USUARIO
            $matricula = Matricula::select('id')->where('usuario_id', $usuario->id)->get();
            $ciclo = Ciclo::select('id', 'carrera_id', 'secuencia')->where('id', $request->curr_ciclos)->first();
            /**
             * 1 O MAS MATRICULAS
             * CAMBIO LA PRESENTE A 0
             * SI LA SECUENCIA DE CICLO DE LA PRESENTE ES 0, LA MATRICULA CAMBIA A ESTADO 0
             */
            if (count($matricula) > 0) {
                $matricula_presente = Matricula::select('id', 'ciclo_id')->where('usuario_id', $usuario->id)->where('presente', 1)->first();
                $matricula_presente->presente = 0;
                $ciclo_presente = Ciclo::select('id', 'carrera_id', 'secuencia')->where('id', $matricula_presente->ciclo_id)->first();
                if ($ciclo_presente->secuencia == 0) {
                    $matricula_presente->estado = 0;
                }
                $matricula_presente->save();
            }
            if ($ciclo->secuencia > 0) {
                $ciclos_crear = Ciclo::where('carrera_id', $request->curr_carrera)
                    ->where('secuencia', '>', 0)
                    ->where('secuencia', '<=', $ciclo->secuencia)
                    ->orderBy('secuencia')
                    ->get(['id', 'nombre', 'secuencia']);
                $i = 0;
                $len = count($ciclos_crear);
                foreach ($ciclos_crear as $ciclo_crear) {
                    // CREO MATRICULA
                    $matricula = new Matricula;
                    $matricula->usuario_id = $usuario->id;
                    $matricula->carrera_id = $request->curr_carrera;
                    $matricula->ciclo_id = $ciclo_crear->id;
                    $matricula->secuencia_ciclo = $ciclo_crear->secuencia;
                    $matricula->presente = ($i == $len - 1) ? 1 : 0;
                    $matricula->estado = 1;
                    $matricula->save();
                    DB::table('matricula_criterio')->insert([
                        'matricula_id' => $matricula->id,
                        'criterio_id' => $request->curr_grupo_id
                    ]);
                    $i++;
                }
            } else {
                $matricula = new Matricula;
                $matricula->usuario_id = $usuario->id;
                $matricula->carrera_id = $request->curr_carrera;
                $matricula->ciclo_id = $ciclo->id;
                $matricula->secuencia_ciclo = $ciclo->secuencia;
                $matricula->presente = 1;
                $matricula->estado = 1;
                $matricula->save();
                DB::table('matricula_criterio')->insert([
                    'matricula_id' => $matricula->id,
                    'criterio_id' => $request->curr_grupo_id
                ]);
            }
        }

        return redirect()->route('usuarios.index')
            ->with('info', 'Usuario guardado con éxito');
    }

    public function edit(Usuario $usuario)
    {

        // $usuario->load('grupo_sistema');

        $grupos = Criterio::pluck('valor', 'id');
        $grupos->put('0', 'Seleccione un grupo');
        $grupos = $grupos->sortKeys();

        $matricula = Matricula::where('usuario_id', $usuario->id)->first();

        $matriculas_pasadas = Matricula::join('carreras', 'carreras.id', 'matricula.carrera_id')
            ->join('ciclos', 'ciclos.id', 'matricula.ciclo_id')
            ->join('matricula_criterio', 'matricula_criterio.matricula_id', 'matricula.id')
            ->join('criterios', 'criterios.id', 'matricula_criterio.criterio_id')
            ->where('matricula.usuario_id', $usuario->id)
            ->select('criterios.valor as grupo', 'carreras.nombre as carrera', 'ciclos.nombre as ciclo',
                'matricula.estado as estado', 'matricula.secuencia_ciclo as secuencia')
            ->orderBy('matricula.secuencia_ciclo')
            ->get();

        $config_array = Abconfig::select('id', 'etapa')->pluck('etapa', 'id');
        $grupo_array = Grupo::select('id', 'nombre')->pluck('nombre', 'id');
        $config_query = Abconfig::select('id', 'etapa')->orderBy('id');
        // $carreras = Carrera::where('estado', 1)->where('config_id', $usuario->config_id)->orderBy('nombre')->get();

        $config_array = $config_query->pluck('etapa', 'id');

        $usuario->grupo_sistema_nombre = $usuario->grupo_sistema->nombre ?? "GS no definido";

        return view('usuarios.edit', compact('usuario', 'config_array', 'grupo_array', 'grupos', 'matriculas_pasadas'));
    }

    public function getInitialData($usuario_id)
    {
        $usuario = Usuario::where('id', $usuario_id)->first();
        $matriculas_pasadas = [];
        $carrera_usuario = 0;
        $grupo_usuario = 0;
        $grupos_x_carrera = [];
        $query_boticas = Botica::with([
            'criterio' => function ($query) {
                $query->select('id', 'valor', 'tipo_criterio', 'tipo_criterio_id');
            }
        ]);
        $query_cargos = Cargo::select('id as cargo_id', 'nombre as cargo_nombre');
        if ($usuario_id != 0) {
            $matriculas_pasadas = Matricula::join('carreras', 'carreras.id', 'matricula.carrera_id')
                ->join('ciclos', 'ciclos.id', 'matricula.ciclo_id')
                ->join('matricula_criterio', 'matricula_criterio.matricula_id', 'matricula.id')
                ->join('criterios', 'criterios.id', 'matricula_criterio.criterio_id')
                ->where('matricula.usuario_id', $usuario_id)
                ->select('criterios.valor as grupo', 'carreras.nombre as carrera', 'ciclos.nombre as ciclo',
                    'matricula.estado as estado', 'matricula.secuencia_ciclo as secuencia', 'matricula.id as matricula_id', 'ciclos.id as id', 'carreras.id as carrera_id')
                ->orderBy('matricula.secuencia_ciclo')
                ->get();
            $grupo = Usuario::join('criterios', 'criterios.id', 'usuarios.grupo')->where('usuarios.id', $usuario_id)->first(['criterios.valor']);
            $grupo_usuario = $grupo->valor;
            $carrera_usuario = $matriculas_pasadas[0]->carrera ?? NULL;
            $grupos_x_carrera = DB::table('curricula_criterio')
                ->join('criterios', 'criterios.id', 'curricula_criterio.criterio_id')
                ->join('curricula', 'curricula.id', 'curricula_criterio.curricula_id')
                ->where('curricula.carrera_id', $matriculas_pasadas[0]->carrera_id ?? NULL)
                ->select('criterios.valor', 'criterios.id')
                ->groupBy('criterios.valor')
                ->get();
            $query_boticas->where('config_id', $usuario->config_id);
        }

        $modulos = Abconfig::all(['id', 'etapa']);
        $grupos = Criterio::where('tipo_criterio_id' ,1)->get(['id', 'valor']);
        $grupo_sistema = Grupo::select('id','nombre')->get('nombre','id' );
        $boticas =  $query_boticas->select('id as botica_id', 'criterio_id', DB::raw(" CONCAT('[', codigo_local, ']', ' - ',nombre) as botica_nombre"), 'config_id')->get();
        $cargos = $query_cargos->get();
        $grupos_x_carr = 0;

        return response()->json(compact('grupos', 'modulos', 'grupo_sistema', 'matriculas_pasadas', 'carrera_usuario', 'grupo_usuario','grupos_x_carrera', 'boticas', 'cargos'), 200);
    }
    public function getCarrerasxGrupo($grupo_id, $config_id)
    {
        // $grupo = Criterio::where('id', $grupo_id)->first();
        // $carreras = DB::table('curricula_criterio')
        //             ->join('criterios', 'criterios.id', 'curricula_criterio.criterio_id')
        //             ->join('curricula', 'curricula.id', 'curricula_criterio.curricula_id')
        //             ->join('carreras', 'carreras.id', 'curricula.carrera_id')
        //             ->where('criterios.valor', $grupo->valor)
        //             ->where('carreras.config_id', $config_id)
        //             ->select('carreras.nombre', 'carreras.id')
        //             ->groupBy('carreras.nombre')
        //             ->get();
        $carreras = Carrera::where('config_id', $config_id)->select('nombre', 'id')->get();
        return response()->json(compact('carreras'), 200);
    }

    public function getCarrerasxModulo($config_id)
    {

        $carreras = Carrera::where('config_id', $config_id)->get(['nombre', 'id']);
        return response()->json(compact('carreras'), 200);
    }

    public function getCarrerasxModuloxBotica($config_id, $grupo_id)
    {
        $carreras = Carrera::where('config_id', $config_id)->get(['nombre', 'id']);
        return response()->json(compact('carreras'), 200);
    }

    public function getCiclosxCarreraFilter($carrera_id)
    {
        $ciclos = Ciclo::where('carrera_id', $carrera_id)->get(['nombre', 'id']);
        return response()->json(compact('ciclos'), 200);
    }

    public function getCiclosxCarrera($carrera_id, $botica)
    {
        // $ciclosxCarrera = Ciclo::where('carrera_id', $carrera_id)->get(['id']);
        $ciclos = Ciclo::where('carrera_id', $carrera_id)->get();
        $botica = Botica::find($botica);
//         dd($botica);
        $carrera = Carrera::find($carrera_id, ['nombre']);
        // $_ciclos = collect();
        // foreach ($ciclosxCarrera as $ciclo) {
        //     $_ciclos->push($ciclo->id);
        // }
        // $ciclos = Curricula::join('curricula_criterio', 'curricula_criterio.curricula_id', 'curricula.id')
        //                     ->join('ciclos', 'ciclos.id', 'curricula.ciclo_id')
        //                     ->where('curricula.carrera_id', $carrera_id)
        //                     ->where('curricula_criterio.criterio_id', $botica_id)
        //                     ->whereIn('curricula.ciclo_id', $_ciclos)
        //                     ->orderBy('ciclos.nombre')
        //                     ->groupBy('curricula.ciclo_id')
        //                     ->get(['ciclos.id', 'ciclos.nombre as ciclo', 'ciclos.secuencia', 'ciclos.estado']);
        $ciclo_final = collect();
        $only_one_ciclo = count($ciclos)===1;
        foreach ($ciclos as $ciclo) {
            $ciclo_final->push([
                'matricula_id' => 0,
                'id' => $ciclo->id,
                'ciclo' => $ciclo->nombre,
                'secuencia' => $ciclo->secuencia,
                'grupo' => $botica->criterio->valor ?? 'Seleccione una botica',
                'carrera' => $carrera->nombre,
                'estado' => !$only_one_ciclo && ($ciclo->estado == 0 ||  strtoupper($ciclo->nombre)=='CICLO 0') ? false : true
            ]);
        }
        return $this->success(compact('ciclo_final'));
//        return response()->json(compact('ciclo_final'), 200);
    }

    public function getDataCiclo($ciclo_id, $carrera_id, $grupo_id)
    {
        $error = 0;
        if ($ciclo_id == "0" || $carrera_id ==  "0" || $grupo_id == "0") {
            $msg = 'Debe seleccionar los campos : GRUPO, CARRERA y CICLO.';
            $error++;
            return response()->json(['msg' => $msg, 'error' => $error], 200);
        }
        $ciclo_seleccionado = Ciclo::find($ciclo_id, ['secuencia']);
        if ($ciclo_seleccionado->secuencia == 0) {
            $ciclos = Ciclo::where('carrera_id', $carrera_id)
                            ->where('secuencia', '<=', $ciclo_seleccionado->secuencia)
                            ->orderBy('secuencia')
                            ->get(['id', 'nombre', 'secuencia']);
        } else {
            $ciclos = Ciclo::where('carrera_id', $carrera_id)
                            ->where('secuencia', '>', 0)
                            ->where('secuencia', '<=', $ciclo_seleccionado->secuencia)
                            ->orderBy('secuencia')
                            ->get(['id', 'nombre', 'secuencia']);
        }
        $carrera = Carrera::find($carrera_id, ['nombre']);
        $grupo = Criterio::find($grupo_id, ['valor']);

        return response()->json(compact('ciclos', 'carrera', 'grupo'), 200);
    }

    public function getCiclo($ciclo_id, $carrera_id, $grupo_id)
    {
        $ciclo = Ciclo::find($ciclo_id, ['id', 'nombre', 'secuencia']);
        $carrera = Carrera::find($carrera_id, ['id', 'nombre']);
        $grupo = Criterio::find($grupo_id, ['valor']);
        $ciclo_agregar = [
            'secuencia' => $ciclo->secuencia,
            'ciclo_nombre' => $ciclo->nombre,
            'grupo' => $grupo->valor,
            'carrera' => $carrera->nombre,
            'estado' => "ACTIVO"
        ];
        return response()->json(compact('ciclo_agregar'), 200);
    }

    public function update(UsuarioStoreRequest $request, Usuario $usuario)
    {
        if ($request->errores > 0) {
            $request->curr_grupo_id = 0;
            return redirect()->back()->withInput($request->all())->withErrors(['msg' => 'Debe asignar una matricula para registrar al usuario.']);
        }

        $data = $request->all();
        // GUARDAR DATOS PREVIOS
        $dni_previo = $usuario->dni;
        $email_previo = $usuario->email;

        if (!is_null($request->password)) {
            $data['password'] = Hash::make($request->password);
        }else{
            $data['password'] = $usuario->password;
        }
        $data['grupo'] = $request->curr_grupo_id;

        $usuario->update($data);

        //Consultar dias de configuracion
         $dias = $usuario->config->duracion_dias;
         date_default_timezone_set('America/Lima');
        //insertar vigencia
        $vigencia = $usuario->vigencia;
        $vigencia->usuario_id = $usuario->id;
        $vigencia->fecha_inicio = date('Y-m-d');
        $vigencia->fecha_fin = date('Y-m-d', strtotime($vigencia->fecha_inicio. ' + '.$dias.' days'));
        $vigencia->save();


        if (isset($request->curr_carrera) && isset($request->curr_ciclos)  && $request->curr_carrera != "0" && $request->curr_ciclos != "0") {
            // SABER EL NUMERO DE MATRICULAS DEL USUARIO
            $matricula = Matricula::select('id')->where('usuario_id', $usuario->id)->get();
            $ciclo = Ciclo::select('id', 'carrera_id', 'nombre', 'secuencia')->where('id', $request->curr_ciclos)->first();
            // dd($ciclo);
            /**
             * 1 O MAS MATRICULAS
             * CAMBIO LA PRESENTE A 0
             * SI LA SECUENCIA DE CICLO DE LA PRESENTE ES 0, LA MATRICULA CAMBIA A ESTADO 0
             */
            if (count($matricula)>0) {
                $matricula_presente = Matricula::select('id', 'ciclo_id')->where('usuario_id', $usuario->id)->where('presente', 1)->first();
                // dd($matricula_presente);
                $matricula_presente->presente = 0;
                $ciclo_presente = Ciclo::select('id', 'carrera_id', 'secuencia')->where('id',$matricula_presente->ciclo_id)->first();
                if($ciclo_presente->secuencia == 0){
                    $matricula_presente->estado = 0;
                }
                $matricula_presente->save();
            }

            // SELECCIONAR TODOS LOS CICLOS DE LA CARRERA
            $ciclosxCarrera = Ciclo::where('carrera_id', $request->curr_carrera)
                                ->where('secuencia' , '>',0)
                                ->where('secuencia' , '<=', $ciclo->secuencia)
                                ->get(['secuencia', 'id']);
            $_ciclosxCarrera= collect();
            foreach ($ciclosxCarrera as $c) {
                $_ciclosxCarrera->push($c->id);
            }
            // RESTAR A TODOS EN LOS QUE YA SE ENCUENTRA MATRICULADOS Y QUE SEAN MAYOR QUE 0
            $ciclos_matriculados = Matricula::where('usuario_id', $usuario->id)->get(['secuencia_ciclo', 'ciclo_id']);
            $_ciclos_matriculados= collect();
            foreach ($ciclos_matriculados as $cm) {
                $cm->presente = 0;
                $cm->save();
                $_ciclos_matriculados->push($cm->ciclo_id);
            }
            $resta = $_ciclosxCarrera->diff($_ciclos_matriculados);
            $ciclos_restantes = $resta->all();
            $ciclos_restantes2 = Ciclo::whereIn('id', $ciclos_restantes)->get(['id', 'nombre', 'secuencia']);

            $i = 0;
            $len = count($ciclos_restantes2);
            // CREAR MATRICULA Y MATRICULA CRITERIO DE LOS RESTANTES
            foreach ($ciclos_restantes2 as $ciclo_restante) {
                $matricula = new Matricula;
                $matricula->usuario_id = $usuario->id;
                $matricula->carrera_id = $request->curr_carrera;
                $matricula->ciclo_id = $ciclo_restante->id;
                $matricula->secuencia_ciclo = $ciclo_restante->secuencia;
                $matricula->presente = ($i == $len - 1) ? 1 : 0;;
                $matricula->estado = 1;
                $matricula->save();
                // GUARDAR EN TABLA MATRICULA CRITERIO LA NUEVA MATRICULA
                DB::table('matricula_criterio')->insert([
                    'matricula_id' => $matricula->id,
                    'criterio_id' => $request->curr_grupo_id
                ]);
                $i++;
            }
        }

        return redirect()->route('usuarios.index')
            ->with('info', 'Usuario actualizado con éxito');
    }


    public function destroy(Usuario $usuario)
    {
        // \File::delete(public_path().'/'.$usuario->imagen);

        // $usuario->matricula()->delete();
        // $usuario->delete();

        // return back()->with('info', 'Eliminado Correctamente');

        \File::delete(public_path().'/'.$usuario->imagen);
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


    // RESETEO DE USUARIOS
    public function reset(Usuario $usuario){

        $config  = $usuario->config;
        $mod_eval = json_decode($config->mod_evaluaciones, true);
        // lista temas evaluados
        $temas = \DB::select( \DB::raw("SELECT t.id, t.nombre FROM pruebas p
                                                INNER JOIN posteos t ON t.id = p.posteo_id
                                                WHERE p.usuario_id = ".$usuario->id."
                                                AND p.resultado = 0
                                                AND p.intentos >= ".$mod_eval['nro_intentos']."
                                                ORDER BY p.id DESC"));

        // lista cursos evaluados
        $cursos = \DB::select( \DB::raw("SELECT c.id, c.nombre FROM pruebas p
                                                INNER JOIN posteos t ON t.id = p.posteo_id
                                                INNER JOIN cursos c ON c.id = t.curso_id
                                                WHERE p.usuario_id = ".$usuario->id."
                                                AND p.resultado = 0
                                                AND p.intentos >= ".$mod_eval['nro_intentos']."
                                                GROUP BY t.curso_id
                                                ORDER BY p.id DESC"));

        return $this->success(compact('temas', 'cursos', 'usuario'));

        // return view('usuarios.reset', compact('usuario', 'temas', 'cursos'));
    }


    // Reiniciar
    public function reset_x_tema(Usuario $usuario, Request $request){
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
            $posteo = Posteo::where('id',$posteo_id)->select('curso_id')->first();
            $posteos = Posteo::where('curso_id',$posteo->curso_id)->select('id')->get();
            if(count($posteos)==1){
                $res = Resumen_x_curso::where('usuario_id',$usuario->id)->where('curso_id',$posteo->curso_id)->update([
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
                $dip->posteo_id =  $posteo_id;
                $dip->curso_id = $posteo->curso_id;
                $dip->admin_id =  $user->id;
                $dip->acumulado =  1;
                $dip->save();

            }
            else{
                $res->acumulado = $res->acumulado + 1;
                $res->save();
            }
            // Fin registro reinicios
            return $this->success(['msg' => 'Reinicio por tema exitoso']);
        }

        return $this->error('No se pudo completar el reinicio');
    }

    public function reset_x_curso(Usuario $usuario, Request $request){
        if ($request->has('c')) {
            $curso_id = $request->input('c');

            $pruebas = \DB::select( \DB::raw("SELECT p.id FROM pruebas p
                                                    INNER JOIN posteos t ON t.id = p.posteo_id
                                                    INNER JOIN cursos c ON c.id = t.curso_id
                                                    WHERE p.usuario_id = ".$usuario->id."
                                                    AND c.id = ".$curso_id."
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
            $res = Reinicio::where('usuario_id', $usuario->id)->where('curso_id', $curso_id)->where('tipo','por_curso')->first();
            if (!$res) {
                $dip = new Reinicio;
                $dip->tipo = 'por_curso';
                $dip->usuario_id = $usuario->id;
                $dip->curso_id =  $curso_id;
                $dip->admin_id =  $user->id;
                $dip->acumulado =  1;
                $dip->save();
            }
            else{
                $res->acumulado = $res->acumulado + 1;
                $res->save();
            }
            // Fin registro reinicios

            return $this->success(['msg' => 'Reinicio por curso exitoso']);
        }

        return $this->error('No se pudo completar el reinicio');
    }

    public function reset_total(Usuario $usuario){
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
            $dip->admin_id =  $user->id;
            $dip->acumulado =  1;
            $dip->save();
        }
        else{
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
                return response()->json(['error'=>true,'msg'=> 'El DNI ya ha sido registrado.'], 422);

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
            $nuevo_usuario->botica =  $botica->nombre;
            $nuevo_usuario->grupo = $botica->criterio_id;
            $nuevo_usuario->grupo_nombre = $botica->criterio->valor;
            $nuevo_usuario->dni = $request->usuario['dni'];
            $nuevo_usuario->password =  Hash::make($request->usuario['password']);
            $nuevo_usuario->sexo =  $request->usuario['sexo'] == 'M' ? 'MASCULINO': 'FEMENINO';
            $nuevo_usuario->cargo =  $request->usuario['cargo'];

            $nuevo_usuario->estado =  $request->usuario['estado'];
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
                if(count($ciclos_activos) == 1 && $ciclo['estado'] == true ){
                    $matricula->estado = 1;
                }else if ($ciclo['secuencia'] == 0) {
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
                'usuario_id'=> $nuevo_usuario->id,
                'cur_asignados' => count($hel->help_cursos_x_matricula($nuevo_usuario->id)),
            ]);
            return $this->success(['msg'=> 'Usuario creado con éxito.']);
//            return response()->json(['msg'=> 'Usuario creado con éxito.'], 200);
        } else {
            // return $request->all();
            // EDITAR
            $usuario_editar = Usuario::find($request->usuario['id']);
//            $usuario_editar->config_id = $request->usuario['modulo'];
            $usuario_editar->nombre = $request->usuario['nombre'];
            // $usuario_editar->grupo_id = $request->usuario['grupo_sistema'];
            if ($usuario_editar->dni !== trim($request->usuario['dni']))
            {
                $already_exist = Usuario::where('dni', trim($request->usuario['dni']))->first();

                if ($already_exist)
                    return response()->json(['msg'=> 'El DNI ya ha sido registrado.'], 422);

                $usuario_editar->dni = trim($request->usuario['dni']);
            }

            if ( isset($request->usuario['password']) AND $request->usuario['password'] != "") {
                $usuario_editar->password =  Hash::make($request->usuario['password']);
            }

            $usuario_editar->sexo =  $request->usuario['sexo'] == 'M' ? 'MASCULINO': 'FEMENINO';
            $usuario_editar->cargo =  $request->usuario['cargo'];
            $usuario_editar->estado =  $request->usuario['estado'];
//            $usuario_editar->botica_id =  $request->usuario['botica_id'];
            $usuario_editar->botica_id = $request->usuario['botica']['id'];
//            $botica = Botica::with('criterio')->where('id', $request->usuario['botica_id'])->first();
            $botica = Botica::with('criterio')->where('id', $request->usuario['botica']['id'])->first();
            $usuario_editar->botica =  $botica->nombre;
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
                if(count($ciclos_activos) == 1 && $ciclo['estado'] == true ){
                    $matricula->estado = 1;
                }else if ($ciclo['secuencia'] == 0) {
                    $matricula->estado = 0;
                } else {
                    $matricula->estado = $ciclo['estado'] ? 1 : 0;
                }
                $matricula->save();
            }
            $helper = new HelperController();
            $curso_ids_matricula = $helper->help_cursos_x_matricula($usuario_editar->id);
            //LIMPIAR TABLAS RESUMENES
            Resumen_x_curso::where('usuario_id',$usuario_editar->id)->whereNotIn('curso_id',$curso_ids_matricula)->update(['estado_rxc'=>0]);
            // Resumen_general::where('usuario_id',$usuario_editar->id)->delete();
            //ACTUALIZAR TABLAS RESUMENES
            $rest_avance = new RestAvanceController();
            $ab_conf = Abconfig::select('mod_evaluaciones')->where('id',$request->usuario['modulo'])->first(['mod_evaluaciones']);
            $mod_eval = json_decode($ab_conf->mod_evaluaciones, true);
            foreach ($curso_ids_matricula as $cur_id) {
                // ACTUALIZAR RESUMENES
                $rest_avance->actualizar_resumen_x_curso($usuario_editar->id,$cur_id, $mod_eval['nro_intentos']);
            }
            $rest_avance->actualizar_resumen_general($usuario_editar->id);
            return $this->success(['msg'=> 'Usuario actualizado con éxito.']);
//            return response()->json(['msg'=> 'Usuario actualizado con éxito.'], 200);

        }
    }


    public function getCursosxUsuario($usuario_id)
    {
        $helper = new HelperController();
        $cursos_id = $helper->help_cursos_x_matricula($usuario_id);
        $categorias = Categoria::with([
                                    'cursos' => function($query) use($cursos_id){
                                        $query->select('cursos.nombre as nom_curso', 'cursos.categoria_id', 'cursos.id');
                                        $query->whereIn('cursos.id', $cursos_id);
                                        $query->where('cursos.estado', 1);
                                    }
                                ])
                                ->where('categorias.estado', 1)
                                ->get(['categorias.nombre', 'categorias.id', 'categorias.id']);
        $matricula_actual = Matricula::join('carreras', 'carreras.id', 'matricula.carrera_id')
                            ->join('ciclos', 'ciclos.id', 'matricula.ciclo_id')
                            ->where('presente', 1)
                            ->where('usuario_id', $usuario_id)
                            ->first(['carreras.nombre as nom_carrera', 'ciclos.nombre as nom_ciclo']);
        $usuario_id = Usuario::join('criterios', 'criterios.id', 'usuarios.grupo')
                                ->where('usuarios.id', $usuario_id )
                                ->first(['criterios.valor as nom_grupo']);
        $_categorias = collect();
        foreach ($categorias as $categoria) {
            if (count($categoria->cursos)> 0) {
                $_categorias->push($categoria);
            }
        }

        $data = array('categorias' => $_categorias, 'nom_grupo' => $usuario_id->nom_grupo, 'nom_carrera' => $matricula_actual->nom_carrera, 'nom_ciclo' => $matricula_actual->nom_ciclo);

        return response()->json($data, 200);
    }

    public function status($usuario_id){
        $user = Usuario::find($usuario_id);
        $estado = ($user->estado == 1) ? 0 : 1 ;
        $user->estado = $estado;
        $user->save();
        return back()->with(['info'=> 'Estado actualizado con éxito.']);
    }

    public function updateStatus(Usuario $usuario, Request $request)
    {
        $usuario->update(['estado' => !$usuario->estado]);

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
         $tipo     = $request->tipo; // TODOS o SOLO DESAPROBADOS
         $admin    = $request->admin;
         $config = Abconfig::select('mod_evaluaciones')->where('id', $request->modulo)->first();
         $mod_eval = json_decode($config->mod_evaluaciones, true);
         $data     = $this->validarDetallesReinicioIntentosMasivo($request->curso, $request->tema, $request->modulo, $tipo, $mod_eval, $admin['id']);
         return response()->json([
             'data'      => $data,
             'mod_eval'  => $mod_eval
         ], 200);
     }


     public function validarDetallesReinicioIntentosMasivo($curso_id, $posteo_id, $modulo_id, $tipo, $mod_eval, $admin_id)
     {
         $query_usuarios = Prueba::with('usuario')
                                 ->where('pruebas.fuente');

         if ($posteo_id == null) $query_usuarios->where('pruebas.curso_id', $curso_id); // Por Curso
         else $query_usuarios->where('pruebas.posteo_id', $posteo_id); // Por Tema

         if($tipo['id'] == 1) { // Solo desaprobados
             $query_usuarios->where('pruebas.resultado', 0)
                            ->where('pruebas.intentos', '>=', $mod_eval['nro_intentos'] );
         }

         $usuarios = $query_usuarios->orderBy('pruebas.nota')->get();

         foreach ($usuarios as $key => $usuario) {
             $usuario->selected = false;
         }
         return [
             'pruebas'        => $usuarios,
             'count_usuarios' => $usuarios->count()
         ];
     }

     public function reiniciarIntentosMasivos(Request $request)
     {
         $admin      = $request->admin;
         $config = Abconfig::select('mod_evaluaciones')->where('id', $request->modulo)->first();
         $mod_eval = json_decode($config->mod_evaluaciones, true);
         $usuarios   = $request->usuarios;
         $count_usuarios = count($usuarios);
         if ($request->tema == null) {
             $curso  = Curso::where('id', $request->curso)->first();
             $data   = $this->reinicioMasivoxCurso($request->curso, $request->modulo, $mod_eval, $admin['id'], $usuarios);
             $msg    = "Se reiniciaron los intentos de ".$count_usuarios." usuario(s) para el curso $curso->nombre.";
         } else {
             $tema   = Posteo::where('id', $request->tema)->first();
             $data   = $this->reinicioMasivoxTema($request->tema, $request->curso, $request->modulo, $mod_eval, $admin['id'], $usuarios);
             $msg    = "Se reiniciaron los intentos de ".$count_usuarios." usuario(s) para el tema $tema->nombre.";
         }
         return response()->json([
             'data'      => $data,
             'mod_eval'  => $mod_eval,
             'msg'       => $msg
         ], 200);
     }

     public function reinicioMasivoxCurso($curso_id, $modulo_id, $mod_eval, $admin_id, $usuarios)
     {
         // $rest = new RestAvanceController;
         foreach ($usuarios as $key => $usuario) {
             $usuario_id      = $usuario['usuario']['id'];
             $posteosIds        = Prueba::join('posteos', 'posteos.id', 'pruebas.posteo_id')
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
             $usuario_id      = $usuario['usuario']['id'];
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
             case 'por_tema': $reinicio  = Reinicio::where('usuario_id', $usuario_id)->where('posteo_id', $recurso_id)->first();
                 break;
             case 'por_curso': $reinicio = Reinicio::where('usuario_id', $usuario_id)->where('curso_id', $recurso_id)->first();
                 break;
         }
         if (!$reinicio) {
             $reinicio             = new Reinicio;
             $reinicio->tipo       = $tipo;
             $reinicio->usuario_id = $usuario_id;
             if ($tipo == 'por_tema')       $reinicio->posteo_id = $recurso_id;
             else if ($tipo == 'por_curso') $reinicio->curso_id  = $recurso_id;
             $reinicio->admin_id   = $admin_id;
             $reinicio->acumulado  = 1;
         } else {
             $reinicio->acumulado  = $reinicio->acumulado + 1;
         }
         $reinicio->save();
     }
     //**************************************************** REINICIO MASIVOS DE NOTAS POR CURSO ********************************************************/
}
