<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Abconfig;
use App\Models\Pregunta;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\Matricula;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\ApiRest\HelperController;


class RestCursosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.jwt');
        return auth()->shouldUse('api');
        Carbon::setLocale('es');
    }
    /***********************************REDISEÑO******************* */
    public function cursos()
    {
        /**
         * 1) Retornar escuelas>cursos
         *  1.1) Detalles x escuela
         *  1.2) (Último curso + último tema visto) x escuela
         */
        $apiResponse = [];
        $appUser = auth()->user();
        $helper = new HelperController();
        $cursos_id = $helper->help_cursos_x_matricula($appUser->id);
        $cursos_ciclos = DB::table('cursos AS c')
            ->select(
                'c.id',
                'c.categoria_id',
                'c.nombre',
                'c.descripcion',
                'c.imagen',
                'c.requisito_id',
                'c.c_evaluable',
                'u.ciclo_id',
                'i.nombre AS ciclo_nombre'
            )
            ->join('curricula AS u', 'u.curso_id', '=', 'c.id')
            ->join('ciclos AS i', 'i.id', '=', 'u.ciclo_id')
            ->whereIn('c.id', $cursos_id)
            ->where('c.estado', 1)
            ->orderBy('c.orden', 'ASC')
            ->groupBy('c.id')
            ->get();
        $modulo = Abconfig::where('id', $appUser->config_id)->first();
        // 'cursos.temas'
        $categorias = Categoria::with(['cursos', 'temas' => function ($q) {
            $q->select('id', 'estado', 'orden','tipo_ev')->orderBy('orden','ASC');
        }])->where('config_id', $appUser->config_id)
            ->whereIn('id', $cursos_ciclos->pluck('categoria_id'))
            ->where('estado', 1)
            ->orderBy('orden', 'ASC')
            ->get();
        $data_categorias = [];
        $arr_estados = [
            "" => "",
            "aprobado" => "Aprobado",
            "realizado" => "Realizado",
            "revisado" => "Revisado",
            "desaprobado" => "Desaprobado",
            "desarrollo" => "En desarrollo",
            "pendiente" => "Pendiente",
            "enc_pend" => "Encuesta Pendiente",
            "bloqueado" => "Bloqueado",
            "completado" => "Completado",
            "por-iniciar" => "Por iniciar",
            "continuar" => "En desarrollo"
        ];
        $visitas = Visita::where('usuario_id', $appUser->id)
                            ->select('curso_id', 'post_id','estado_tema','usuario_id')
                            ->whereIn('curso_id',$cursos_id)
                            ->where(function($v){
                                $v->orWhereNull('estado_tema')
                                    ->orWhere('estado_tema','desaprobado')
                                    ->orWhere('estado_tema','')
                                    ->orWhere('estado_tema','por-iniciar');
                            })->get();
        $pruebas_aprobadas = Prueba::where('usuario_id', $appUser->id)
                                ->where('historico',1)
                                ->where('resultado',1)
                                ->whereIn('curso_id',$cursos_id)
                                ->select('posteo_id')
                                ->get();
        foreach ($categorias as $key => $categoria) {
            $escuela_asignados   = 0;
            $escuela_completados = 0;
            $escuela_porcentaje  = 0;
            $escuela_resultado   = "";
            $cursos = $categoria->cursos->whereIn('id', $cursos_id)
                ->where('estado', 1)
                ->sortBy('orden');
            // $ultimo_curso_visto_x_escuela        = $visitas->whereIn('curso_id', $cursos->pluck('id'))->sortByDesc('updated_at')->first();
            // $estado_ultimo_curso_visto_x_escuela = null;
            $data_ultimo_curso  = null;
            $data_cursos = [];
            foreach ($cursos as $key => $curso) {
                $escuela_asignados++;
                $last_tema = null;
                $estado_curso = $this->estadoCurso($appUser->id, $curso->id, $curso->requisito_id);
                if ($estado_curso['status'] == 'completado') {
                    $escuela_completados++;
                }
                $temas = $curso->temas->where('estado', 1);
                $visita_temas = $visitas->whereIn('post_id', $temas->pluck('id'));
                if($visita_temas->count()>0){
                    foreach ($temas as $key => $tema) {
                        $v = $visita_temas->where('post_id',$tema->id)->first();
                        $last_item = ($tema->id == $temas->last()->id);
                        if($v){
                            if($tema->tipo_ev=='calificada' && ($pruebas_aprobadas->where('posteo_id',$tema->id))->first() && !$last_item){
                                continue;
                            }
                            $last_tema = ($tema->id);
                            break;
                        }
                        if(is_null($last_tema) && $last_item){
                            $last_tema = ($tema->id);
                            break;
                        }
                    }
                }

                $ultimo_tema_visto = $last_tema ?? $temas->first()->id ?? null;

                if(is_null($data_ultimo_curso) && $estado_curso['status'] != 'completado'){
                    $data_ultimo_curso = [
                        'id' => $curso->id,
                        'nombre' => $curso->nombre,
                        'imagen' => $curso->imagen,
                        'porcentaje' => $estado_curso['porcentaje'],
                        'ultimo_tema_visto' => $ultimo_tema_visto,
                    ];
                }
                // $ultimo_tema_visto_x_curso = $visitas->where('curso_id', $curso->id)->first();
                $requisito_curso = Curso::select('id', 'nombre')->where('id', $curso->requisito_id)->first();
                $data_cursos[] = [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'descripcion' => $curso->descripcion,
                    'imagen' => $curso->imagen,
                    'requisito' => $requisito_curso,
                    'c_evaluable' => $curso->c_evaluable,
                    'disponible' => $estado_curso['disponible'],
                    'status' => $arr_estados[$estado_curso['status']],
                    'encuesta' => $estado_curso['enc_disponible'],
                    'encuesta_habilitada' => $estado_curso['enc_habilitada'],
                    'encuesta_resuelta' => $estado_curso['enc_resuelta'],
                    'encuesta_id' => $estado_curso['enc_id'],
                    'ciclo_nombre' => $cursos_ciclos->where('id', $curso->id)->first()->ciclo_nombre,
                    'temas_asignados' => $estado_curso['existe_rxc'] ?
                        $estado_curso['temas_asignados'] :
                        $temas->count(),
                    'temas_completados' => $estado_curso['temas_completados'],
                    'porcentaje' => $estado_curso['porcentaje'],
                    'ultimo_tema_visto' => $ultimo_tema_visto
                ];
            }
            // Escuela estados y porcentaje
            if ($escuela_completados > 0 && $escuela_completados >= $escuela_asignados) {
                $escuela_resultado = 'aprobado';
                $escuela_porcentaje = ($escuela_completados / $escuela_asignados) * 100;
            } else if ($escuela_completados > 0) {
                $escuela_resultado = 'desarrollo';
                $escuela_porcentaje = ($escuela_completados / $escuela_asignados) * 100;
            } else {
                $escuela_resultado = 'pendiente';
                $escuela_porcentaje = 0;
            }
            $escuela_porcentaje =  round($escuela_porcentaje);
            $data_categorias[] = [
                'categoria_id' => $categoria->id,
                'categoria' => $categoria->nombre,
                'completados' => $escuela_completados,
                'asignados' => $escuela_asignados,
                'porcentaje' => $escuela_porcentaje,
                'estado' => $arr_estados[$escuela_resultado],
                'ultimo_curso' => $data_ultimo_curso,
                'cursos' => $data_cursos
            ];
        }
        $apiResponse['data'] = $data_categorias;

        return response()->json($apiResponse, 200);
    }

    public function temas(Curso $curso)
    {
        $apiResponse = [];
        $appUser = auth()->user();
        $helper = new HelperController();
        $cursos_id = $helper->help_cursos_x_matricula_con_cursos_libre($appUser->id);
        $modulo = Abconfig::where('id', $appUser->config_id)->first();
        $mod_eval = json_decode($modulo->mod_evaluaciones, true);
        $max_intentos = (int) $mod_eval['nro_intentos'];
        $nota_aprobatoria_arr = $mod_eval['nota_aprobatoria'];
        $categoria = Categoria::with('cursos.temas.medias')->where('id', $curso->categoria_id)->first();
        $cursos_x_usuario = $categoria->cursos->whereIn('id', $cursos_id)->where('estado', 1)->sortBy('orden');
        $data_cursos = [];
        $arr_estados = [
            "" => "",
            "aprobado" => "Aprobado",
            "realizado" => "Realizado",
            "revisado" => "Revisado",
            "desaprobado" => "Desaprobado",
            "desarrollo" => "En desarrollo",
            "pendiente" => "Pendiente",
            "enc_pend" => "Encuesta Pendiente",
            "bloqueado" => "Bloqueado",
            "completado" => "Completado",
            "por-iniciar" => "Por iniciar",
            "continuar" => "En desarrollo"
        ];
        $arr_estados_cursos = [
            "" => "",
            "aprobado" => "Completado",
            "completado" => "Completado",
            "desaprobado" => "Desaprobado",
            "desarrollo" => "En desarrollo",
            "continuar" => "En desarrollo",
            "pendiente" => "Pendiente",
            "enc_pend" => "Encuesta Pendiente",
            "bloqueado" => "Bloqueado",
            "por-iniciar" => "Por iniciar",
        ];
        foreach ($cursos_x_usuario as $key => $curso) {
            $estado_curso = $this->estadoCurso($appUser->id, $curso->id, $curso->requisito_id);
            $data_temas = [];
            $ultimo_tema_visto = "";
            $temas = $curso->temas->where('estado', 1)
                ->where('curso_id', $curso->id)
                ->sortBy('orden');
            foreach ($temas as $key => $tema) {
                $estado_tema = $this->estadoTemas($appUser->id, $tema, $max_intentos);
                $ultimo_tema_visto = $estado_tema['ultimo_tema_visto'];
                $tema_estado = "por-iniciar";
                if ($estado_tema['actividad'] && $estado_tema['actividad']['estado_tema']) {
                    $tema_estado = $estado_tema['actividad']['estado_tema'];
                }
                $media_temas = $tema->medias->sortBy('orden')->values()->all();
                foreach ($media_temas as $md) {
                    if($md->tipo == 'audio' && !str_contains('https',$md->valor)){
                        $md->valor=env('BUCKET_BASE_URL').'/'.$md->valor;
                    }
                }
                $data_temas[] = [
                    'id' => $tema->id,
                    'nombre' => $tema->nombre,
                    'requisito_id' => $tema->requisito_id,
                    'imagen' => $tema->imagen,
                    'contenido' => $tema->contenido,
                    'media' => $media_temas,
                    'evaluable' => $tema->evaluable,
                    'tipo_ev' => $tema->tipo_ev,
                    'tipo_cal' => $tema->tipo_cal,
                    'nota' => $estado_tema['nota'],
                    'disponible' => $estado_tema['disponible'],
                    'intentos_restantes' => $estado_tema['intentos_restantes'],
                    't_evaluacion' => $estado_tema['t_evaluacion'],
                    'estado_tema' => $tema_estado,
                    'estado_tema_str' => $arr_estados[$tema_estado],
                    'tags' => $estado_tema['tags']
                ];
            }

            $data_cursos[] = [
                'id' => $curso->id,
                'nombre' => $curso->nombre,
                'descripcion' => $curso->descripcion,
                'imagen' => $curso->imagen,
                'requisito_id' => $curso->requisito_id,
                'c_evaluable' => $curso->c_evaluable,
                'porcentaje' => $estado_curso['porcentaje'],
                'disponible' => $estado_curso['disponible'],
                'status' => $arr_estados_cursos[$estado_curso['status']],
                'encuesta' => $estado_curso['enc_disponible'],
                'encuesta_habilitada' => $estado_curso['enc_habilitada'],
                'encuesta_resuelta' => $estado_curso['enc_resuelta'],
                'encuesta_id' => $estado_curso['enc_id'],
                'temas_asignados' => $estado_curso['existe_rxc'] ?
                    $estado_curso['temas_asignados'] :
                    $curso->temas->where('estado', 1)->count(),
                'temas_completados' => $estado_curso['temas_completados'],
                'porcentaje' => $estado_curso['porcentaje'],
                'temas' => $data_temas
            ];
        }
        $apiResponse = [
            'id' => $categoria->id,
            'nombre' => $categoria->nombre,
            'cursos' => $data_cursos
        ];
        return $apiResponse;
    }

    public function estadoCurso($usuario_id, $curso_id, $requisito_id)
    {
        $porcentaje = 0;
        $disponible = true;
        $status = 'por-iniciar';
        $enc_disponible = false;
        $enc_habilitada = false;
        $enc_resuelta = false;
        $enc_id = null;
        $temas_completados = 0;
        $temas_asignados = 0;

        // Disponible : enc_disponible
        if ($requisito_id !== null) {
            // Buscar en resumenes si el curso requisito ya fue aprobado
            $res_x_curso1 = DB::table('resumen_x_curso')
                ->select('estado')
                ->where('usuario_id', $usuario_id)
                ->where('curso_id', $requisito_id)
                ->where('estado', 'aprobado')
                ->first();
            // Si el curso requisito no está aprobado, el curso actual se bloquea
            if (!$res_x_curso1) {
                $disponible = false;
                $status = 'bloqueado';
            }
        }

        // Porcentaje
        if ($disponible) {
            // Status encuesta
            $enc_asignada = DB::table('encuestas AS e')
                ->join('curso_encuesta AS ce', 'e.id', '=', 'ce.encuesta_id')
                ->join('encuestas_preguntas AS ep', 'e.id', '=', 'ep.encuesta_id')
                ->select('e.id')
                ->where('ce.curso_id', $curso_id)
                ->where('e.estado', 1)
                ->where('ep.estado', 1)
                ->first();

            if ($enc_asignada) {
                $enc_disponible = true;
                $enc_id = $enc_asignada->id;


                $hizo_encuesta = DB::table('encuestas_respuestas')
                    ->select('curso_id')
                    ->where('usuario_id', $usuario_id)
                    ->where('curso_id', $curso_id)
                    ->first();

                if ($hizo_encuesta) {
                    $enc_resuelta = true;
                }
            }

            $res_x_curso2 = DB::table('resumen_x_curso')
                ->select('estado', 'porcentaje', 'asignados', 'aprobados', 'realizados', 'revisados', 'desaprobados')
                ->where('usuario_id', $usuario_id)
                ->where('curso_id', $curso_id)
                ->first();

            // Status curso
            if ($res_x_curso2) {

                $temas_completados = $res_x_curso2->aprobados + $res_x_curso2->realizados + $res_x_curso2->revisados;
                $temas_asignados = $res_x_curso2->asignados;

                $porcentaje = $res_x_curso2->porcentaje;
                if ($porcentaje == 100 && $res_x_curso2->estado == 'aprobado') {
                    $status = 'completado';
                } else if ($porcentaje == 100 && $res_x_curso2->estado == 'enc_pend') {
                    $status = 'enc_pend';
                } else if ($res_x_curso2->estado == 'desaprobado') {
                    $status = 'desaprobado'; // Nuevo estado para mostrar en app
                    $enc_habilitada = true; // Habilitar encuesta cuando se ha desaprobado el curso (y se acabaron los intentos)
                } else {
                    $status = 'continuar';
                    // En caso de estado "desarrollo" y "pendiente" calcular temas desarrollados para ver si habilitar la encuesta
                    $temas_desarrollados = $res_x_curso2->aprobados + $res_x_curso2->realizados + $res_x_curso2->revisados + $res_x_curso2->desaprobados;
                    if ($res_x_curso2->asignados <= $temas_desarrollados) {
                        $enc_habilitada = true;
                    }
                }

                // Habilitar Encuesta cuando se completaron todos los temas del curso
                if ($porcentaje == 100) {
                    $enc_habilitada = true;
                }
            }
        }

        return [
            'porcentaje' => $porcentaje,
            'disponible' => $disponible,
            'status' => $status,
            'enc_disponible' => $enc_disponible,
            'enc_habilitada' => $enc_habilitada,
            'enc_id' => $enc_id,
            'enc_resuelta' => $enc_resuelta,
            'temas_asignados' => $temas_asignados,
            'temas_completados' => $temas_completados,
            'existe_rxc' => ($disponible && $res_x_curso2)
        ];
    }

    public function estadoTemas($usuario_id, $tema, $max_intentos)
    {
        $nota = "";
        $disponible = false;
        $intentos_restantes = "";
        $t_evaluacion = true;
        $ultimo_tema_visto = "";
        //VERIFICAR EVALUACION DISPONIBLE
        // $preguntas = Pregunta::where('post_id', $tema->id)
        //             ->where('estado', 1)
        //             ->first();
        // if (floatval($preguntas->where('')) < floatval($preguntas->count())) {
        //     $t_evaluacion = false;
        // }
        // TEMA : Actividad
        $actividad = Visita::select('post_id', 'estado_tema')
            ->where('usuario_id', $usuario_id)
            ->where('post_id', $tema->id)
            ->first();

        $tema_visto_default = $tema->id;
        if ($actividad && $disponible) {
            $ultimo_tema_visto = $tema->id;
        } else {
            if ($ultimo_tema_visto == "") {
                $ultimo_tema_visto = $tema_visto_default;
            }
        }
        // TEMA: Nota e intentos
        if ($tema->evaluable == 'si' && $tema->tipo_ev == 'calificada') {
            $tema_nota = Prueba::select('nota', 'intentos', 'resultado')
                ->where('usuario_id', $usuario_id)
                ->where('posteo_id', $tema->id)
                ->first();

            if ($tema_nota) {
                if ($actividad) {
                    $actividad['estado_tema'] = ($tema_nota->resultado == 1) ? 'aprobado' : 'desaprobado';
                }
                $nota = $tema_nota->nota;
                $resta = $max_intentos - $tema_nota->intentos;
                $intentos_restantes = $resta > 0 ? $resta : 0;
            } else {
                $intentos_restantes = $max_intentos; // Por defecto
            }
        }
        // TEMA: Disponible (verificando si tiene requisitos)
        $requisito = Posteo::select('id', 'categoria_id', 'evaluable', 'tipo_ev')
            ->where('id', $tema->requisito_id)->first();

        if (!$requisito) {
            $disponible = true;
        } else {
            $actividad_requisito = Visita::select('post_id', 'estado_tema')
                ->where('usuario_id', $usuario_id)
                ->where('post_id', $requisito->id)
                ->whereIn('estado_tema', ['aprobado', 'realizado', 'revisado'])
                ->first();
            $prueba_requisito = Prueba::select('resultado')
                ->where('usuario_id', $usuario_id)
                ->where('posteo_id', $requisito->id)
                ->where('resultado', 1)
                ->first();
            if ($actividad_requisito || $prueba_requisito) {
                $disponible = true;
            }
        }


        // TEMA: Tags
        $tags = DB::table('tag_relationships AS p')
            ->select('p.element_id', 't.nombre', 't.color')
            ->leftJoin('tags AS t', 'p.tag_id', '=', 't.id')
            ->where('p.element_type', 'tema')
            ->where('p.element_id', $tema->id)
            ->get();

        $respuesta = [
            'nota' => $nota,
            'disponible' => $disponible,
            'intentos_restantes' => $intentos_restantes,
            't_evaluacion' => $t_evaluacion,
            'actividad' => $actividad ?? null,
            'tags' => $tags,
            'ultimo_tema_visto' => $ultimo_tema_visto
        ];
        return $respuesta;
    }

    /***********************************REDISEÑO******************* */
    public function escuelas_matricula($config_id, $usuario_id)
    {
        if ($usuario_id) {
            $helper = new HelperController();
            $cursos_id = $helper->help_cursos_x_matricula_con_cursos_libre($usuario_id);
            // dd($cursos_id);
            $categorias = DB::table('categorias as c')
                ->select('c.id', 'c.config_id', 'c.modalidad', 'c.nombre', 'c.descripcion', 'c.imagen', 'c.color', 'c.en_menu_sec')
                ->join('cursos AS r', 'c.id', '=', 'r.categoria_id')
                // ->where('c.config_id', $config_id)
                ->whereIn('r.id', $cursos_id)
                ->where('c.estado', 1)
                ->where('r.estado', 1)
                ->orderBy('c.orden', 'ASC')
                ->groupBy('r.categoria_id')
                ->get();

            $matricula = Matricula::select('id', 'carrera_id', 'secuencia_ciclo')->where('usuario_id', $usuario_id)->where('presente', 1)->where('estado', 1)->first();

            foreach ($categorias as $cat) {
                if ($matricula->secuencia_ciclo == 0) {
                    // CAMBIO DEL NOMBRE DE LA ESCUELA
                    $nombre_escuela_ciclo0 = DB::table('nombre_escuelas')->select('nombre')->where('escuela_id', $cat->id)->first();
                    if ($nombre_escuela_ciclo0) {
                        $cat->nombre = $nombre_escuela_ciclo0->nombre;
                    }
                }
            }

            $data = array('error' => 0, 'categorias' => $categorias);
        } else {
            $data = array('error' => 1, 'categorias' => null);
        }

        return $data;
    }


    public function cursos_x_escuela($config_id, $usuario_id)
    {

        if ($usuario_id) {
            $helper = new HelperController();
            $cursos_id = $helper->help_cursos_x_matricula_con_cursos_libre($usuario_id);
            $cate_cursos = DB::table('cursos AS c')
                ->select('c.id', 'c.categoria_id', 'c.nombre', 'c.descripcion', 'c.imagen', 'c.requisito_id', 'c.c_evaluable', 'u.ciclo_id', 'i.nombre AS ciclo_nombre')
                ->join('curricula AS u', 'u.curso_id', '=', 'c.id')
                ->join('ciclos AS i', 'i.id', '=', 'u.ciclo_id')
                ->whereIn('c.id', $cursos_id)
                ->where('c.estado', 1)
                ->orderBy('c.orden', 'ASC')
                ->groupBy('c.id')
                ->get();
            $data = array('error' => 0, 'data' => $cate_cursos);
        } else {
            $data = array('error' => 1, 'data' => null);
        }
        return $data;
    }
}
