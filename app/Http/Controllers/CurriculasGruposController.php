<?php

namespace App\Http\Controllers;

use Excel;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Carrera;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\Matricula;
use App\Models\Usuario_rest;
use App\Models\Resumen_general;
use App\Models\Curricula_criterio;

use App\Exports\CurriculaExport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurriculasGruposController extends Controller
{
    public function index()
    {
        return view('curriculas_grupos.index');
    }

    function pre($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

    public function getCurriculaGrupos()
    {
        $configs = Abconfig::with([
            'categorias'=>function($q){
                $q->select('id','config_id','modalidad','nombre','orden','imagen');
//                    ->where('id', 19);
            },
            'categorias.cursos'=>function($q){
                $q->select('id','categoria_id','nombre','estado');
            },
            'categorias.cursos.temas'=>function($q){
                $q->select('id','curso_id','nombre','estado');
            },
            'categorias.cursos.curricula'=>function($q){
                $q->select('id','curso_id','carrera_id','ciclo_id')->where('estado',1)->groupBy('carrera_id');
            },
            // 'categorias.cursos.curricula.ciclo'=>function($q){
            //     $q->select('id','nombre');
            // },
            'categorias.cursos.curricula.carrera'=>function($q){
                $q->select('id','nombre');
            },
            // 'categorias.cursos.curricula.curricula_criterio'=>function($q){
            //     $q->select('id','curricula_id','criterio_id')->groupBy('criterio_id');
            // },
            // 'categorias.cursos.curricula.curricula_criterio.criterio'=>function($q){
            //     $q->select('id','valor as nombre_grupo');
            // },
        ])->select('id', 'etapa', 'estado')
//            ->limit(1)
            ->get();
        foreach ($configs as $modulo) {
            $criterios_activos = Usuario::select('grupo')->where('config_id',$modulo->id)->where('estado',1)->where('rol','default')->groupBy('grupo')->pluck('grupo');

            $modulo->total_escuelas = 0;
            $modulo->total_cursos_activos = 0;
            $modulo->total_cursos_inactivos = 0;
            $modulo->total_temas_activos = 0;
            $modulo->total_temas_inactivos = 0;
            $modulo->curso_seleccionado_temas_activos = 0;
            $modulo->curso_seleccionado_temas_inactivos = 0;

            foreach ($modulo->categorias as $escuela) {
                $escuela->curso_seleccionado = null;
                $escuela->curricula_mostrar = [];
                $escuela->total_cursos_activos = $escuela->cursos->where('estado',1)->count();
                $escuela->total_cursos_inactivos = $escuela->cursos->where('estado',0)->count();
                $escuela->total_temas_activos = 0;
                $escuela->total_temas_inactivos = 0;
                $escuela->curso_seleccionado_temas_activos = 0;
                $escuela->curso_seleccionado_temas_inactivos = 0;

                $escuela->alert = [
                    'show' => false,
                    'text' => null,
                    'type' => null,
                    'icon' => null
                ];

                $modulo->total_escuelas++;
                $modulo->total_cursos_activos = $modulo->total_cursos_activos + $escuela->total_cursos_activos;
                $modulo->total_cursos_inactivos = $modulo->total_cursos_inactivos + $escuela->total_cursos_inactivos;


                foreach ($escuela->cursos as $curso) {
                    $curso->temas_activos = $curso->temas->where('estado', 1)->count();
                    $curso->temas_inactivos = $curso->temas->where('estado', 0)->count();

                    $escuela->total_temas_activos = $escuela->total_temas_activos + $curso->temas_activos;
                    $escuela->total_temas_inactivos = $escuela->total_temas_inactivos + $curso->temas_inactivos;

                    $modulo->total_temas_activos = $modulo->total_temas_activos  + $escuela->total_temas_activos;
                    $modulo->total_temas_inactivos = $modulo->total_temas_inactivos + $escuela->total_temas_inactivos;
                    // foreach ($curso->curricula as $curricula[0]) {
                    //     $ciclos_seleccionados = Curricula::join('ciclos', 'ciclos.id', 'curricula.ciclo_id')
                    //         ->where('curricula.carrera_id', $curricula[0]->carrera_id)
                    //         ->where('curricula.curso_id', $curso->id)
                    //         ->where('curricula.estado', 1)
                    //         ->select('curricula.ciclo_id as ciclo_id', 'ciclos.nombre', 'curricula.id as curricula_id')
                    //         ->get();
                    //     $grupos_seleccionados = DB::table('curricula_criterio')->whereIn('criterio_id',$criterios_activos)->where('curricula_criterio.curricula_id', $curricula[0]->id)
                    //         ->join('criterios', 'criterios.id', 'curricula_criterio.criterio_id')
                    //         ->select('criterios.valor as nombre_grupo', 'criterios.id as criterio_id', 'curricula_criterio.id as curricula_criterio_id', 'curricula_criterio.curricula_id')
                    //         ->groupBy('criterio_id')->get();
                    //     $ciclos_totales = Ciclo::select('id as ciclo_id', 'nombre')->where('carrera_id', $curricula[0]->carrera_id)->where('estado', 1)->orderBy('nombre')->get();

                    //     $curricula[0]->ciclos_seleccionados = $ciclos_seleccionados;
                    //     $curricula[0]->grupos_seleccionados = $grupos_seleccionados;
                    //     $g_seleccionados = $grupos_seleccionados->pluck('criterio_id');
                    //     $criterios_activos->merge($grupos_seleccionados->pluck('criterio_id'));

                    //     $ctr_activos = Criterio::whereIn('criterios.id',$criterios_activos)->join('ab_config', 'ab_config.id', 'criterios.config_id')
                    //     ->where('tipo_criterio_id', 1)->select('criterios.id as criterio_id', DB::raw("CONCAT(ab_config.codigo_matricula,' - ', valor) as nombre_grupo "),
                    //     'tipo_criterio_id', 'tipo_criterio', 'config_id')->get();
                    //     $curricula[0]->ciclos_totales = $ciclos_totales;
                    //     $curricula[0]->criterios_listados = $ctr_activos;
                    //     // $curricula->criterios_totales = $criterios_totales;
                    //     // $curricula->criterios_activos = $criterios_activos;
                    // }
                    $curso->curricula = [];
                }
            }
        }
        return response()->json(compact('configs'), 200);
    }
    public function getCurriculaXCurso($curso_id){
        $curso = Curso::where('id',$curso_id)->select('config_id')->first();
        $curriculas = Curso::join('curricula', 'curricula.curso_id', 'cursos.id')
        // ->join('curricula_criterio as cc', 'cc.curricula_id', 'curricula.id')
        ->where('cursos.id', $curso_id)
        ->select('curricula.carrera_id', 'curricula.ciclo_id', 'curricula.curso_id', 'curricula.id as curricula_id','curricula.all_criterios')
        ->groupBy('curricula.carrera_id')
        ->with('temas')
        ->get();
        $grupos_activos = Usuario::select('grupo')->where('config_id',$curso->config_id)->where('estado',1)->where('rol','default')->groupBy('grupo')->pluck('grupo');
        // $criterios_activos = Criterio::whereIn('id',$grupos_activos)
        // ->select('valor as nombre_grupo', 'id as criterio_id')->get();
        // $criterios_activos = Criterio::whereIn('id',$grupos_activos)
        // ->select('id')->pluck('id');
        // $grupos_activos = DB::table('curricula_criterio')->whereIn('curricula_criterio.criterio_id', $criterios_activos)
        //         ->join('criterios', 'criterios.id', 'curricula_criterio.criterio_id')
        //         ->select('criterios.valor as nombre_grupo', 'criterios.id as criterio_id', 'curricula_criterio.id as curricula_criterio_id', 'curricula_criterio.curricula_id')
        //         ->get();
        foreach ($curriculas as $curricula) {
            $ciclos_seleccionados = Curricula::join('ciclos', 'ciclos.id', 'curricula.ciclo_id')
                ->where('curricula.carrera_id', $curricula->carrera_id)
                ->where('curricula.curso_id', $curso_id)
                ->where('curricula.estado', 1)
                ->select('curricula.ciclo_id as ciclo_id', 'ciclos.nombre', 'curricula.id as curricula_id')
                ->get();
            $grupos_seleccionados = Curricula_criterio::where('curricula_criterio.curricula_id', $curricula->curricula_id)
                ->join('criterios', 'criterios.id', 'curricula_criterio.criterio_id')
                ->join('ab_config', 'ab_config.id', 'criterios.config_id')
                ->select('criterios.id as criterio_id', DB::raw("CONCAT(ab_config.codigo_matricula,' - ', valor) as nombre_grupo "))->get();
            // $grupos_seleccionados = Curricula_criterio::where('curricula_criterio.curricula_id', $curricula->curricula_id)
            // ->join('criterios', 'criterios.id', 'curricula_criterio.criterio_id')
            // ->select('criterios.valor as nombre_grupo', 'criterios.id as criterio_id')
            // ->get();
            $curricula->grupos_seleccionados = $grupos_seleccionados;
            $criterios_seleccionados_o_activos = Criterio::whereIn('criterios.id',$grupos_seleccionados->pluck('criterio_id')->merge($grupos_activos))
            ->select('criterios.id as criterio_id', DB::raw("CONCAT(ab_config.codigo_matricula,' - ', valor) as nombre_grupo "))
            ->join('ab_config', 'ab_config.id', 'criterios.config_id')->get();

            $ciclos_totales = Ciclo::select('id as ciclo_id', 'nombre')->where('carrera_id', $curricula->carrera_id)->where('estado', 1)->orderBy('nombre')->get();
            $curricula->ciclos_seleccionados = $ciclos_seleccionados;
            $curricula->ciclos_totales = $ciclos_totales;
            // $merge = $grupos_seleccionados->merge($criterios_activos);
            // dd($grupos_seleccionados,$merge,$criterios_activos);
            $curricula->criterios_listados = $criterios_seleccionados_o_activos;
            $curricula->activos = $criterios_seleccionados_o_activos;
        }
        // dd($curriculas);
        return response()->json(compact('curriculas'));
    }
    public function getCarreras()
    {
        $carreras = Carrera::with('ciclos')->where('carreras.estado', 1)->get();

        return response()->json(compact('carreras'), 200);
    }

    public function getGrupos()
    {
        $grupos = [];
        $criterios_activos = Usuario::select('grupo')->where('estado',1)->where('rol','default')->groupBy('grupo')->pluck('grupo');
        $consulta = Criterio::join('ab_config', 'ab_config.id', 'criterios.config_id')
        ->where('tipo_criterio_id', 1)->select('criterios.id as criterio_id',DB::raw("CONCAT(ab_config.codigo_matricula,' - ', valor) as nombre_grupo "),
        'tipo_criterio_id', 'tipo_criterio', 'config_id')->get();
        $grupos['totales'] =  $consulta;
        $activos = $consulta->whereIn('criterio_id',$criterios_activos);
        $grupos['activos'] = [];
        foreach ($activos as $value) {
            $grupos['activos'][] = $value;
        }
        return response()->json($grupos, 200);
    }
    public function getGruposXCurricula($curricula_id){
        $criterios = Curricula_criterio::with(['criterio'=>function($q){
            return $q->select('id','valor');
        }])->select('id','criterio_id')->where('curricula_id',$curricula_id)->get();
        return response()->json($criterios, 200);
    }
    public function llenarTablaGrupos()
    {
        $grupos = Usuario_rest::select('usuarios.grupo')
            ->groupBy('usuarios.grupo')
            ->get();

        foreach ($grupos as $grupo) {
            $criterio = new Criterio();
            $criterio->valor = $grupo->grupo;
            $criterio->tipo_criterio = 'GRUPO';
            $criterio->tipo_criterio_id = 1;

            $criterio->save();
        }

        return response()->json($grupos, 200);
    }

    public function guardarCurricula(Request $request)
    {
        // ELIMINAR TODAS LAS CURRICULAS Y CURRICULAS_CRITERIOS EXISTENTES
        $curriculas = Curricula::where('curso_id', $request->curso_id)->get();

        foreach ($curriculas as $curricula) {
            DB::table('curricula_criterio')
                ->where('curricula_criterio.curricula_id', $curricula->id)->delete();
            DB::table('curricula')->where('id', $curricula->id)->delete();
        }
        $ids_carr=[];
        foreach ($request->data as $data) {
            if ($data['carrera_id'] != 0) {
                $ids_carr[] = $data['carrera_id'];
                foreach ($data['ciclos_seleccionados'] as $ciclo) {
                    // Guardamos la nueva curricula
                    $nueva_curricula = new Curricula();
                    $nueva_curricula->carrera_id = $data['carrera_id'];
                    $nueva_curricula->ciclo_id = isset($ciclo['id']) ? $ciclo['id'] : $ciclo['ciclo_id'];
                    $nueva_curricula->curso_id = $data['curso_id'];
                    $nueva_curricula->estado = 1;
                    $nueva_curricula->all_criterios = $data['all_criterios'];
                    $nueva_curricula->save();
                    // A cada grupo seleccionado le agregamos la nueva curricula
                    $this->agregarCurriculaGrupo($nueva_curricula->id, $data['grupos_seleccionados']);
                }
            }
        }
        if (isset($request->data[0]['curso_id'])){
            $this->actualizarUsuarios($request->data[0]['curso_id'],$ids_carr);
        }
        return response()->json(['msg' => 'Curricula actualizada'], 200);
    }
    public function actualizarUsuarios($curso_id,$ids_carr){
        $curso = Curso::where('id', $curso_id)->select('id', 'config_id')->first();
        $usuarios_modulo = Usuario::with(['matricula'=>function($q)use($ids_carr){ $q->select('id','usuario_id','carrera_id')->whereIn('carrera_id',$ids_carr);}])
                                ->where('config_id', $curso->config_id)
                                ->select('id')
                                ->get('id');
        $usus = [];
        foreach ($usuarios_modulo as $value) {
            if(count($value->matricula)>0){
                $usus[]=$value->id;
            }
        }
        $s=DB::table('update_usuarios')->where('tipo','update_resumen_general')->where('config_id',$curso->config_id)->where('estado',0)->first();
        if(is_null($s)){
            DB::table('update_usuarios')->insert([
                'usuarios_id' => json_encode($usus),
                'config_id' =>$curso->config_id,
                'tipo' => 'update_resumen_general',
                'total'=> count($usus),
            ]);
        }
    }


    public function agregarCurriculaGrupo($curricula_id, $criterios)
    {
        // Guardamos en la tabla curricula_criterios
        foreach ($criterios as $crit) {
            $guardar = DB::table('curricula_criterio')->insert(
                ['criterio_id' => $crit['criterio_id'], 'curricula_id' => $curricula_id]
            );
        }
    }

    public function cambioStringToIntGrupoId()
    {
        // $usuarios = Usuario_rest::all(['id', 'grupo']);
        // [916, 8294, 8979, 11478]
        // $usuarios = Usuario_rest::where('grupo', 'like', 'GRUPO%')->get(['id', 'grupo']);
        $usuarios = Usuario_rest::whereIn('id', [32419])->get(['id', 'grupo']);

        foreach ($usuarios as $alumno) {
            $criterio = Criterio::where('valor', $alumno->grupo)->first();
            if ($criterio) {
                $usuario = Usuario_rest::where('id', $alumno->id)->first(['id', 'grupo']);
                $usuario->grupo = $criterio->id;
                $usuario->save();
            }
        }

        return response()->json(['msg' => 'Grupos de Usuarios actualizados'], 200);
    }

    public function crear_matriculas_criterios()
    {
        // $usuarios = Usuario_rest::all(['id', 'grupo']);
        $usuarios = Usuario_rest::where('id',32419)->get(['id', 'grupo']);
        $data = [];
        for ($i = 0; $i < count($usuarios); $i++) {
            $matriculas = Matricula::where('usuario_id', $usuarios[$i]->id)->get(['id']);
            if (count($matriculas)) {
                foreach ($matriculas as $matricula) {
                    $data[] = [
                        'matricula_id' => $matricula->id,
                        'criterio_id' => $usuarios[$i]->grupo
                    ];
                }
            }
        }

        $chunks = array_chunk($data, 5000);
        foreach ($data as $matricula) {
            DB::table('matricula_criterio')->insert($matricula);
        }
        return response()->json(['msg' => 'Matriculas_criterio creadas'], 200);
    }

    public function llenarCurriculas()
    {
        $grupos = Criterio::all(['id']);
        $data = [];
        $curriculas = Curricula::all(['id']);
        for ($i = 0; $i < count($curriculas); $i++) {
            foreach ($grupos as $grupo) {
                $data[] = [
                    'criterio_id' => $grupo->id,
                    'curricula_id' => $curriculas[$i]->id
                ];
            }
        }

        $chunks = array_chunk($data, 300);
        foreach ($chunks as $create) {
            DB::table('curricula_criterio')->insert($create);
        }

        return response()->json(['msg' => 'ok'], 200);
    }

    public function exportarCurriculasExcel()
    {
        $filtros = array();
        $curricula_exportar = new CurriculaExport($filtros);
        $curricula_exportar->view();
        $random = rand(0, 10000);
        ob_end_clean();
        ob_start();
        return Excel::download($curricula_exportar, "curricula_$random.xlsx");
    }
}
