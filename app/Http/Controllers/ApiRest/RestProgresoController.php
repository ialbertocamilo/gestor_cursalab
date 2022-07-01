<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Prueba;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\Matricula;
use App\Models\Usuario_rest;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Curricula_criterio;
use App\Models\Matricula_criterio;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class RestProgresoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.jwt', ['except' => ['progreso_escuela_ciclos_v4', 'progresoResumen_v8']]);
        return auth()->shouldUse('api');
        Carbon::setLocale('es');
    }
    /*-----------Rediseño----------------------------- */
    public function progreso()
    {
        $apiResponse = [];
        $appUser = auth()->user();
        // Cambiar por tabla usuarios cursos
        $helper = new HelperController();
        $cursos_id = $helper->help_cursos_x_matricula_con_cursos_libre($appUser->id);
        // dd($cursos_id);
        // Cambiar por tabla usuarios cursos
        $resume = new RestAvanceController();
        $resume->actualizar_resumen_general($appUser->id);

        // 1) Data resumen usuario, #cursos, #aprobados, #desaprobaods, porcentaje general
        $ciclo = Matricula::where('usuario_id', $appUser->id)->with(['ciclo'=>function($q){
            $q->select('id','nombre');
        }])->where('presente', 1)->first();
        $q_asignados = count($cursos_id);
        $tot_completados = $q_desaprobados = 0;
        $res_usu = Resumen_general::select('cur_asignados', 'tot_completados', 'porcentaje')->where('usuario_id', $appUser->id)->first();
        if ($res_usu) {
            $tot_completados = $res_usu->tot_completados;
            $q_asignados = $res_usu->cur_asignados;
            $q_desaprobados = Resumen_x_curso::select('id')->where('usuario_id', $appUser->id)->where('libre', 0)->where('estado', 'desaprobado')->count();
        }
        $q_pendientes =  $q_asignados - $tot_completados;
        $porcentaje = ($q_asignados > 0) ? ($res_usu->porcentaje) : 0;
        $porcentaje = ($porcentaje > 100) ? 100 : $porcentaje;
        $apiResponse['resumen_usuario'] = [
            'nombre' => $appUser->nombre,
            'cargo' => $appUser->cargo,
            'botica' => $appUser->botica,
            'ciclo' => $ciclo->ciclo->nombre,
            'asignados' => $q_asignados,
            'aprobados' => $tot_completados,
            'desaprobados' => $q_desaprobados,
            'pendientes' => $q_pendientes,
            'porcentaje' => $porcentaje,
        ];

        // 2) Resumen de progreso
        $escuelas = $this->escuelas_matricula($appUser->config_id, $cursos_id);
        $matricula_actual = Matricula::select('carrera_id', 'secuencia_ciclo', 'ciclo_id')->where('usuario_id', $appUser->id)->where('presente', 1)->where('estado', 1)->first();
        $cursos = $this->cursos_x_escuela($appUser->config_id, $cursos_id, $matricula_actual->carrera_id);
        $data = $this->organizar_data_rediseño($escuelas, $cursos, $appUser->id, $matricula_actual);
        $apiResponse['progreso_escuelas'] = [
            'capacitacion' => $data['data'],
            'extracurricular' => $data['data_extra'],
            'data_cursos_libres' => $data['data_cursos_libres']
        ];
        return response()->json($apiResponse, 200);
    }
    public function progreso_ciclos(Request $request)
    {
        $apiResponse = [];
        $appUser = auth()->user();
        $escuela = Categoria::where('id', $request->escuela)->first();
        $es_libre = $request->es_libre;
        // Cambiar por tabla usuarios cursos
        $helper = new HelperController();
        $cursos_id = ($es_libre) ? $helper->help_cursos_x_matricula($appUser->id) : $helper->help_cursos_x_matricula_con_cursos_libre($appUser->id);
        // Cambiar por tabla usuarios cursos
        $cursos_x_escuela = Curso::whereIn('id', $cursos_id)->where('categoria_id', $escuela->id)->get();
        $matricula_actual = Matricula::select('carrera_id', 'secuencia_ciclo', 'ciclo_id')->where('usuario_id', $appUser->id)->where('presente', 1)->where('estado', 1)->first();
        $cursos = $this->cursos_x_escuela($appUser->config_id, $cursos_x_escuela->pluck('id')->all(), $matricula_actual->carrera_id);
        $data = $this->organizarDataXescuela($escuela, $cursos, $appUser->id, $matricula_actual);
        // $ultimo_ciclo_id = $this->ultimo_ciclo_id($appUser->id,$data['ciclos']);
        $apiResponse = [
            'data'  => $data['ciclos'],
            // 'ultimo_ciclo_id' => $ultimo_ciclo_id,
        ];
        return response()->json($apiResponse, 200);
    }
    /*-----------Rediseño----------------------------- */
    public function progreso_escuela_ciclos_v4($config_id, $usuario_id)
    {
        if (is_null($config_id) || is_null($usuario_id)) {
            $response = array('error' => 2, 'data' => null);
            return $response;
        }
        // Log::info('trae data helper: ');
        $helper = new HelperController();
        $cursos_id = $helper->help_cursos_x_matricula_con_cursos_libre($usuario_id);
        // Log::info('finaliza data helper: ');
        // Log::info('escuelas y cursos');
        $escuelas = $this->escuelas_matricula($config_id, $cursos_id);
        $matricula_actual = Matricula::select('carrera_id', 'secuencia_ciclo', 'ciclo_id')->where('usuario_id', $usuario_id)->where('presente', 1)->where('estado', 1)->first();
        $cursos = $this->cursos_x_escuela($config_id, $cursos_id, $matricula_actual->carrera_id);
        // Log::info('finaliza data helper: ');
        // // Log::info('Inicia organizar');
        $data = $this->organizar_data($escuelas, $cursos, $usuario_id, $matricula_actual);
        // Log::info('Finaliza organizar');
        return array('error' => 0, 'data' => $data['data'], 'data_extra' => $data['data_extra'], 'data_cursos_libres' => $data['data_cursos_libres']);
    }

    public function progresoResumen_v8($config_id = null, $user_id = null)
    {
        //INICIO CALCULO CANTIDAD DE CURSOS MATRICULADOS
        // $cursos_id = $this->help_cursos_x_matricula($user_id);
        // $q_asignados = count($cursos_id);

        // FIN CALCULO
        $tot_completados = $q_desaprobados = 0;
        $res_usu = Resumen_general::select('cur_asignados', 'tot_completados', 'porcentaje')->where('usuario_id', $user_id)->first();
        $q_asignados =  (is_null($res_usu->cur_asignados)) ? 0 : $res_usu->cur_asignados;
        if ($res_usu) {
            $tot_completados = $res_usu->tot_completados;
            $q_desaprobados = Resumen_x_curso::select('id')->where('usuario_id', $user_id)->where('estado', 'desaprobado')->count();
            // $tot_completados = Resumen_x_curso::select('id')->whereIn('curso_id', $cursos_id)->where('usuario_id', $user_id)->where('estado', 'aprobado')->count();
            // $q_desaprobados = Resumen_x_curso::select('id')->whereIn('curso_id', $cursos_id)->where('usuario_id', $user_id)->where('estado', 'desaprobado')->count();
        }
        $q_pendientes =  $q_asignados - $tot_completados;

        $porcentaje = ($q_asignados > 0) ? ($res_usu->porcentaje) : 0;
        $porcentaje = ($porcentaje > 100) ? 100 : $porcentaje; // maximo porcentaje = 100

        $data = array('error' => 0, 'data' => array(
            'asignados' => $q_asignados,
            'aprobados' => $tot_completados,
            'desaprobados' => $q_desaprobados,
            'pendientes' => $q_pendientes,
            'porcentaje' => $porcentaje,
        ));
        return $data;
    }

    // Todos los asignados sin restriccion, (EVALUABLES CALIFICADAS, EVALUABLES ABIERTAS, NO EVALUABLES)
    public function consultaAsignadosxCurso($config_id = null, $user_id)
    {
        $helper = new HelperController();
        $cursos_id = $helper->help_cursos_x_matricula($user_id);
        // $cursos_id_str = implode(",", json_decode($cursos_id));
        $cursos_id_str = (isset($cursos_id)) ? implode(",", $cursos_id) : 0;

        $result_cursos = \DB::select(\DB::raw("
                        SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id, c.categoria_id
                        FROM cursos AS c
                        INNER JOIN posteos AS p
                        ON c.id = p.curso_id
                        WHERE c.config_id = " . $config_id . "
                        AND c.estado = 1 AND p.estado = 1
                        AND c.id IN (" . $cursos_id_str . ")
                        GROUP BY p.curso_id
                        ORDER BY c.categoria_id
                    "));

        $result_temas = array();

        return array('cursos' => $result_cursos, 'temas' => $result_temas);
    }

    // PROGRESO RESUMEN
    // public function progresoResumen_v8($config_id = null, $user_id = null)
    // {
    //     if (is_null($config_id) || is_null($user_id)) {
    //         $data = array('error' => 2, 'data' => null);
    //     } else {
    //         $config = DB::table('ab_config')->where('id', $config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);

    //         $result_asignados = $this->consultaAsignadosxCurso($config_id, $user_id);

    //         $asignados = (count($result_asignados['cursos']) > 0) ? count($result_asignados['cursos']) : 0;

    //         $cant_aprobados = 0;
    //         $cant_desa = 0;

    //         $temas_apro_curso = 0;
    //         $temas_desa_curso = 0;
    //         foreach ($result_asignados['cursos'] as $curso) {
    //             // aprob
    //             $aprobados = DB::table('pruebas')
    //                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                 ->where('resultado', 1)
    //                 ->where('usuario_id', $user_id)
    //                 ->where('curso_id', $curso->id)
    //                 ->first();

    //             // APROBADOS ABIERTOS
    //             $aprobados_ab = DB::table('ev_abiertas')
    //                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                 ->where('usuario_id', $user_id)
    //                 ->where('curso_id', $curso->id)
    //                 ->first();

    //             // REVISADOS (SIN EV)
    //             $revisados = DB::table('visitas')
    //                 ->select(DB::raw('COUNT(id) AS cant'))
    //                 ->where('usuario_id', $user_id)
    //                 ->where('curso_id', $curso->id)
    //                 ->where('estado_tema', 'revisado')
    //                 ->first();

    //             $cant_aprobados1 = ($aprobados) ? intval($aprobados->tot_aprobados) : 0;
    //             $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;
    //             $cant_revisados = ($revisados) ? intval($revisados->cant) : 0;

    //             $cant_aprobados = $cant_aprobados1 + $cant_aprobados2 + $cant_revisados;

    //             // $cant_aprobados = ($aprobados) ? intval($aprobados->tot_aprobados) : 0;

    //             if ($cant_aprobados >= $curso->txc) {
    //                 // Verifica si ha realizado Encuesta
    //                 $realiza_enc = DB::table('encuestas_respuestas')
    //                     ->select('curso_id')
    //                     ->where('usuario_id', $user_id)
    //                     ->where('curso_id', $curso->id)
    //                     ->get();

    //                 if (count($realiza_enc) > 0) {
    //                     $temas_apro_curso += 1;
    //                 }
    //             }

    //             //desaprob
    //             $desa = DB::table('pruebas AS u')
    //                 ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
    //                 ->select(DB::raw('COUNT(po.curso_id) AS tot_desaprobados'))
    //                 ->where('u.resultado', 0)
    //                 ->where('intentos', '>=', $mod_eval['nro_intentos'])
    //                 ->where('u.usuario_id', $user_id)
    //                 ->where('po.curso_id', $curso->id)
    //                 ->groupBy('po.curso_id')
    //                 ->first();

    //             $cant_desa = ($desa) ? intval($desa->tot_desaprobados) : 0;

    //             if ($curso->txc == $cant_desa) {
    //                 $temas_desa_curso += 1;
    //             }
    //         }

    //         $q_pendientes =  $asignados - $temas_apro_curso;
    //         $porcentaje = ($asignados > 0) ? ($temas_apro_curso / $asignados) * 100 : 0;
    //         $porcentaje = ($porcentaje > 100) ? 100 : $porcentaje; // maximo porcentaje = 100

    //         $data = array('error' => 0, 'data' => array(
    //             'asignados' => $asignados,
    //             'aprobados' => $temas_apro_curso,
    //             'desaprobados' => $temas_desa_curso,
    //             'pendientes' => $q_pendientes,
    //             'porcentaje' => $porcentaje
    //         ));
    //     }
    //     return $data;
    // }

    /*-----------------------------------------------------------SUBFUNCIONES----------------------------------------------------------------------------------*/
    private function organizar_data($escuelas, $cursos, $usuario_id, $matricula_actual)
    {
        $data = [];
        $data_extra = [];
        $data_cursos_libres = [];
        $collection = collect($cursos['data']);

        foreach ($escuelas['categorias'] as $escuela) {
            // \Log::info('ESCUELA => '.$escuela->id);
            // \Log::info('SECUENCIA CICLO => '.$secuencia_ciclo);
            /** VALIDAR SI EL USUARIO SE ENCUENTRA MATRICULADO EN EL CICLO 0 */
            // if ($secuencia_ciclo == 0) {
            //     // CAMBIO DEL NOMBRE DE LA ESCUELA
            //     $nombre_escuela_ciclo0 = DB::table('nombre_escuelas')->select('nombre')->where('escuela_id', $escuela->id)->first();
            //     if ($nombre_escuela_ciclo0) {
            //         // \Log::info('NOMBRE ESCUELA CICLO 0 => '.$nombre_escuela_ciclo0->nombre);
            //         $escuela->nombre = $nombre_escuela_ciclo0->nombre;
            //     }
            // }
            /** VALIDAR SI EL USUARIO SE ENCUENTRA MATRICULADO EN EL CICLO 0 */

            $cursosXesc = $collection->where('categoria_id', $escuela->id)->where('libre', 0);
            $detalle_ciclo = $this->obtener_temasXcurso($cursosXesc, $usuario_id, $matricula_actual);
            if (count($cursosXesc) > 0) {
                if ($escuela->modalidad == 'regular') {
                    $data[] = [
                        'escuela_id' => $escuela->id,
                        'escuela' => $escuela->nombre,
                        'detalle_escuela' => $detalle_ciclo,
                    ];
                } else {
                    $data_extra[] = [
                        'escuela_id' => $escuela->id,
                        'escuela' => $escuela->nombre,
                        'detalle_escuela' => $detalle_ciclo
                    ];
                }
            }
            /*CURSOS LIBRES*/
            $cursos_lib_Xesc = $collection->where('categoria_id', $escuela->id)->where('libre', 1);
            if (count($cursos_lib_Xesc) > 0) {
                $detalle_ciclo = $this->obtener_temasXcurso($cursos_lib_Xesc, $usuario_id, $matricula_actual);
                $data_cursos_libres[] = [
                    'escuela_id' => $escuela->id,
                    'escuela' => $escuela->nombre,
                    'detalle_escuela' => $detalle_ciclo,
                ];
            }
        }
        return compact('data', 'data_extra', 'data_cursos_libres');
    }
    private function organizar_data_rediseño($escuelas, $cursos, $usuario_id, $matricula_actual)
    {
        $data = [];
        $data_extra = [];
        $data_cursos_libres = [];
        $collection = collect($cursos['data']);
        foreach ($escuelas['categorias'] as $escuela) {
            $cursosXesc = $collection->where('categoria_id', $escuela->id)->where('libre', 0);
            $detalle_escuela = $this->obtenerTemasXcursoXresumen_x_curso($cursosXesc, $usuario_id, $matricula_actual);
            $data_escuela = [
                'id' => $escuela->id,
                'nombre' => $escuela->nombre,
                'imagen' => $escuela->imagen,
                'asignados' => $detalle_escuela['asignados'],
                'completados' => $detalle_escuela['completados'],
                'porcentaje' => $detalle_escuela['porcentaje'],
                'estado' => $detalle_escuela['estado'],
                'estado_str' => $detalle_escuela['estado_str'],
                'es_libre' => false,
            ];
            if ($escuela->modalidad == 'regular') $data[] = $data_escuela;
            else if ($escuela->modalidad == 'extra') $data_extra[] = $data_escuela;
            $cursos_libresXesc = $collection->where('categoria_id', $escuela->id)->where('libre', 1);
            if (count($cursos_libresXesc) > 0) {
                $detalle_escuela = $this->obtenerTemasXcursoXresumen_x_curso($cursos_libresXesc, $usuario_id, $matricula_actual);
                $data_escuela = [
                    'id' => $escuela->id,
                    'nombre' => $escuela->nombre,
                    'imagen' => $escuela->imagen,
                    'asignados' => $detalle_escuela['asignados'],
                    'completados' => $detalle_escuela['completados'],
                    'porcentaje' => $detalle_escuela['porcentaje'],
                    'estado' => $detalle_escuela['estado'],
                    'estado_str' => $detalle_escuela['estado_str'],
                    'es_libre' => true,
                ];
                $data_cursos_libres[] = $data_escuela;
            }
        }
        return compact('data', 'data_extra', 'data_cursos_libres');
    }

    private function obtener_temasXcurso($cursos, $usuario_id, $matricula_actual)
    {
        $prueba = null;
        $cursos_x_ciclo = collect($cursos)->groupBy('ciclo_nombre');
        $data_return = [];
        foreach ($cursos_x_ciclo as $key => $cursos) {
            $max_intentos = 3;
            $cursosXestados = [];
            $pendiente_requisito = [];
            $enc_pend = [];
            $pendientes = [];
            $aprobados = [];
            $desaprobados = [];
            foreach ($cursos as $curso) {
                $curso_id = $curso->id;
                $curso_states = $this->help_curso_estados($usuario_id, $curso_id, $curso->requisito_id);
                $cur_status = $curso_states['status'];
                $nota_prom = $curso_states['nota_prom'];
                $temas = null;
                if ($cur_status != 'bloqueado') {
                    $temas_query = DB::table('posteos AS p')
                        ->leftJoin('ev_abiertas AS u', function ($join) use ($usuario_id) {
                            $join->on('p.id', '=', 'u.posteo_id')
                                ->where('u.usuario_id', "=", $usuario_id);
                        })
                        ->select(DB::raw('p.id, p.requisito_id, p.nombre, p.evaluable, p.tipo_ev, u.id existe_evab'))
                        ->where('p.estado', 1)
                        ->where('p.curso_id', $curso_id)
                        ->orderBy('p.orden', 'ASC');

                    $temas = $temas_query->get();
                    $temas_arr = [];
                    foreach ($temas as $tema) {
                        $nota = "";
                        $intentos = $max_intentos;
                        $disponible = false;
                        // TEMA: Nota e intentos
                        if ($tema->tipo_ev == 'calificada') {
                            $tema_nota = DB::table('pruebas')
                                ->select('nota')
                                ->where('usuario_id', $usuario_id)
                                ->where('posteo_id', $tema->id)
                                ->first();
                            if ($tema_nota) {
                                $nota = $tema_nota->nota;
                            }
                        } else if ($tema->tipo_ev == '' || $tema->evaluable == 'no') {
                            $estado_tema = DB::table('visitas')
                                ->select('estado_tema', 'sumatoria')
                                ->where('usuario_id', $usuario_id)
                                ->where('post_id', $tema->id)
                                ->first();
                            if ($estado_tema) {
                                $tema->estado_tema = $estado_tema->estado_tema;
                                $tema->sumatoria = $estado_tema->sumatoria;
                            }
                        }

                        // TEMA: Disponible (verificando si tiene requisitos)
                        $requisito = DB::table('posteos')->select('id', 'categoria_id', 'evaluable', 'tipo_ev')->find($tema->requisito_id);

                        if (!$requisito) {
                            $disponible = true;
                        } else {
                            $actividad = DB::table('visitas')
                                ->select('post_id', 'estado_tema')
                                ->where('usuario_id', $usuario_id)
                                ->where('post_id', $requisito->id)
                                ->whereIn('estado_tema', ['aprobado', 'realizado', 'revisado'])
                                ->first();
                            if ($actividad) {
                                // dd($actividad);
                                $disponible = true;
                            }
                        }

                        $tema->disponible = $disponible;
                        $tema->nota = $nota;
                    }
                }
                $q_temas = ($temas) ? count($temas) : 0;

                $data = [
                    'categoria_id' => $curso->categoria_id,
                    'curso_ciclo' => $curso->ciclo_nombre,
                    'txc' => $q_temas,
                    'id' => $curso_id,
                    'nombre' => $curso->nombre,
                    'nota_prom' => $nota_prom,
                    'requisito' =>  $curso_states['requisito'],
                    'temas' => $temas
                ];

                switch ($cur_status) {
                    case 'enc_pend':
                        $enc_pend[] = $data;
                        break;
                    case 'desarrollo':
                        $pendientes[] = $data;
                        break;
                    case 'por-iniciar':
                        $pendientes[] = $data;
                        break;
                    case 'bloqueado':
                        $pendiente_requisito[] = $data;
                        break;
                    case 'completado':
                        $aprobados[] = $data;
                        break;
                    case 'aprobado':
                        $aprobados[] = $data;
                        break;
                    case 'desaprobado':
                        $desaprobados[] = $data;
                        break;
                }
            }
            if (count($aprobados) > 0) $cursosXestados['aprobados'] = $aprobados;
            if (count($desaprobados) > 0) $cursosXestados['desaprobados'] = $desaprobados;
            if (count($enc_pend) > 0) $cursosXestados['enc_pend'] = $enc_pend;
            if (count($pendientes) > 0) $cursosXestados['pendientes'] = $pendientes;
            if (count($pendiente_requisito) > 0) $cursosXestados['pendiente_requisito'] = $pendiente_requisito;


            /** VALIDAR SI EL USUARIO SE ENCUENTRA MATRICULADO EN EL CICLO 0 y FORZAR EL NOMBRE*/
            $nombre_ciclo_forzado = $key;
            if ($matricula_actual->secuencia_ciclo == 0) {
                $ciclo = Ciclo::select('nombre')->find($matricula_actual->ciclo_id);
                $nombre_ciclo_forzado = isset($ciclo) ? $ciclo->nombre : "~";
            }
            /** VALIDAR SI EL USUARIO SE ENCUENTRA MATRICULADO EN EL CICLO 0 y FORZAR EL NOMBRE*/

            $data_return[] = [
                'ciclo_id' => $cursos[0]->ciclo_id,
                'nombre' => $nombre_ciclo_forzado,
                'detalle_ciclo' => $cursosXestados,
            ];
        }
        return $data_return;
    }
    private function help_curso_estados($usuario_id, $curso_id, $requisito_id)
    {
        $porcentaje = 0;
        $status = 'por-iniciar';
        $enc_disponible = false;
        $enc_resuelta = false;
        $enc_id = null;
        $requisito = null;
        $nota_prom = null;
        $enc_id = null;
        // Disponible
        if ($requisito_id !== null) {
            $res_x_curso1 = Resumen_x_curso::select('estado')
                ->where('usuario_id', $usuario_id)
                ->where('curso_id', $requisito_id)
                ->where('estado', 'aprobado')
                ->first();
            // Si el curso requisito no está aprobado, el curso actual se bloquea Y SE RETONAR EL ESTADO
            if (!$res_x_curso1) {
                $status = 'bloqueado';
                $requisito = Curso::select('id', 'nombre')->where('id', $requisito_id)->first();
                return ['status' => $status, 'requisito' => $requisito, 'nota_prom' => $nota_prom];
            }
        }
        //ESTADO DEL CURSO
        $res_x_curso2 =  Resumen_x_curso::select('aprobados', 'estado', 'porcentaje', 'nota_prom')
            ->where('usuario_id', $usuario_id)
            ->where('curso_id', $curso_id)
            ->first();
        if ($res_x_curso2) {
            $nota_prom = floatval($res_x_curso2->nota_prom);
            $nota_prom = ($res_x_curso2->aprobados > 0 || $nota_prom > 0) ? $nota_prom : null;
            $porcentaje = $res_x_curso2->porcentaje;

            if ($res_x_curso2->estado == 'enc_pend') {
                $enc_id = null;
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
                }
            }
            return ['status' => $res_x_curso2->estado, 'requisito' => $requisito, 'nota_prom' => $nota_prom, 'enc_id' => $enc_id];
        }
        return ['status' => $status, 'requisito' => $requisito, 'nota_prom' => $nota_prom];
    }

    private function escuelas_matricula($config_id, $cursos_id)
    {
        $categorias = DB::table('categorias as c')
            ->select('c.id', 'c.config_id', 'c.modalidad', 'c.nombre', 'c.descripcion', 'c.imagen', 'c.color', 'c.en_menu_sec')
            ->join('cursos AS r', 'c.id', '=', 'r.categoria_id')
            ->whereIn('r.id', $cursos_id)
            ->where('c.estado', 1)
            ->where('r.estado', 1)
            ->orderBy('c.orden', 'ASC')
            ->groupBy('r.categoria_id')
            ->get();
        $data = array('error' => 0, 'categorias' => $categorias);
        return $data;
    }


    private function cursos_x_escuela($config_id, $cursos_id, $carrera_id)
    {
        $cate_cursos = DB::table('cursos AS c')
            ->select('c.id', 'c.categoria_id', 'c.libre', 'c.nombre', 'c.descripcion', 'c.imagen', 'c.requisito_id', 'c.c_evaluable', 'u.ciclo_id', 'i.nombre AS ciclo_nombre')
            ->join('curricula AS u', 'u.curso_id', '=', 'c.id')
            ->join('ciclos AS i', 'i.id', '=', 'u.ciclo_id')
            ->whereIn('c.id', $cursos_id)
            ->where('u.carrera_id', $carrera_id)
            ->where('c.estado', 1)
            ->orderBy('i.id', 'ASC')
            ->orderBy('c.orden', 'ASC')
            ->groupBy('c.id')
            ->get();
        $data = array('error' => 0, 'data' => $cate_cursos);
        return $data;
    }

    private function help_cursos_x_matricula($usuario_id)
    {
        $usuario = Usuario_rest::where('id', $usuario_id)->first(['id', 'grupo']);
        $result = collect();
        $matriculas = DB::table('matricula AS m')
            ->where('m.usuario_id', $usuario->id)
            ->where('m.estado', 1)
            ->get(['ciclo_id', 'id', 'carrera_id']);
        foreach ($matriculas as $matricula) {
            $matriculas_criterio = Matricula_criterio::select('criterio_id')->where('matricula_id', $matricula->id)->first();
            $criterio_id = $matriculas_criterio->criterio_id;
            $curriculas_criterios = Curricula_criterio::select('curricula_id')->where('criterio_id', $criterio_id)->get();
            foreach ($curriculas_criterios as $curricula_criterio) {
                $curricula = Curricula::join('cursos', 'cursos.id', 'curricula.curso_id')
                    ->select('ciclo_id', 'curso_id')
                    ->where('cursos.estado', 1)
                    ->where('curricula.id', $curricula_criterio->curricula_id)->first();
                if (isset($curricula) && $curricula->ciclo_id == $matricula->ciclo_id) {
                    $result->push($curricula->curso_id);
                }
            }
        }
        return $result->unique()->values()->all();
    }
    private function obtenerTemasXcursoXresumen_x_curso($cursos, $usuario_id, $matricula_actual)
    {
        $resumenes_x_curso = Resumen_x_curso::whereIn('curso_id', $cursos->pluck('id')->all())->where('usuario_id', $usuario_id)->get();
        $asignados = $cursos->count();
        $completados = $resumenes_x_curso->where('estado', 'aprobado')->count();
        if ($completados > 0 && $completados >= $asignados) {
            $estado = 'aprobado';
            $porcentaje = ($completados / $asignados) * 100;
        } else if ($completados > 0) {
            $estado = 'desarrollo';
            $porcentaje = ($completados / $asignados) * 100;
        } else {
            $estado = 'pendiente';
            $porcentaje = 0;
        }
        $arr_estados = config('constantes.arr_estados');
        $porcentaje = round($porcentaje);
        return [
            'estado' => $estado,
            'estado_str' => $arr_estados[$estado],
            'asignados' => $asignados,
            'completados' => $completados,
            'porcentaje' => $porcentaje,
        ];
    }
    private function organizarDataXescuela($escuela, $cursos, $usuario_id, $matricula_actual)
    {
        $collection = collect($cursos['data']);
        $detalle_ciclo = $this->obtener_temasXcurso_rediseño($collection, $usuario_id, $matricula_actual);
        $data_escuela = [
            'id' => $escuela->id,
            'nombre' => $escuela->nombre,
            'imagen' => $escuela->imagen,
            'asignados' => $detalle_ciclo['data_adicional_escuela']['escuela_asignados'],
            'completados' => $detalle_ciclo['data_adicional_escuela']['escuela_completados'],
            'porcentaje' => $detalle_ciclo['data_adicional_escuela']['escuela_porcentaje'],
            'estado' => $detalle_ciclo['data_adicional_escuela']['escuela_resultado'],
            'estado_str' => $detalle_ciclo['data_adicional_escuela']['escuela_resultado_str'],
            'ciclos' => $detalle_ciclo['data_curso']
        ];
        return $data_escuela;
    }
    private function obtener_temasXcurso_rediseño($cursos, $usuario_id, $matricula_actual)
    {
        $rest_avance = new RestAvanceController();
        $mod_eval = null;
        try {
            $config_usuario = auth()->user()->config_id;
            $config = Abconfig::select('mod_evaluaciones')->where('id', $config_usuario)->first();  
            $mod_eval = json_decode($config->mod_evaluaciones, true);
        } catch (\Throwable $th) {
            //throw $th;
        }

        $prueba = null;
        $cursos_x_ciclo = collect($cursos)->groupBy('ciclo_nombre');
        $data_return = [];
        $escuela_asignados = 0;
        $escuela_completados = 0;
        $escuela_porcentaje = 0;
        $escuela_resultado = "";
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
        foreach ($cursos_x_ciclo as $key => $cursos) {
            $max_intentos = 3;
            $detalle_cursos = collect();
            $pendiente_requisito = [];
            $enc_pend = [];
            $pendientes = [];
            $aprobados = [];
            $desaprobados = [];
            foreach ($cursos as $curso) {

                $escuela_asignados++;
                $curso_id = $curso->id;
                if($mod_eval){
                    $rest_avance->actualizar_resumen_x_curso($usuario_id,$curso->id, $mod_eval['nro_intentos']);
                }
                $curso_states = $this->help_curso_estados($usuario_id, $curso_id, $curso->requisito_id);
                $cur_status = $curso_states['status'];
                $nota_prom = $curso_states['nota_prom'];
                $temas = null;
                // info($cur_status);
                if ($cur_status == 'completado' || $cur_status == 'aprobado') {
                    $escuela_completados++;
                }
                if ($cur_status != 'bloqueado') {
                    $temas_query = DB::table('posteos AS p')
                        ->leftJoin('ev_abiertas AS u', function ($join) use ($usuario_id) {
                            $join->on('p.id', '=', 'u.posteo_id')
                                ->where('u.usuario_id', "=", $usuario_id);
                        })
                        ->select(DB::raw('p.id, p.requisito_id, p.nombre, p.evaluable, p.tipo_ev, u.id existe_evab'))
                        ->where('p.estado', 1)
                        ->where('p.curso_id', $curso_id)
                        ->orderBy('p.orden', 'ASC');

                    $temas = $temas_query->get();
                    $temas_arr = [];
                    foreach ($temas as $tema) {
                        // dd($tema);
                        $nota = "";
                        $intentos = $max_intentos;
                        $disponible = false;
                        $tema_estado = "por-iniciar";
                        $estado_tema = DB::table('visitas')
                                ->select('estado_tema', 'sumatoria')
                                ->where('usuario_id', $usuario_id)
                                ->where('post_id', $tema->id)
                                ->first();
                        if ($estado_tema && $estado_tema->estado_tema) {
                            $tema_estado = $estado_tema->estado_tema;
                        }
                        // TEMA: Nota e intentos
                        if ($tema->tipo_ev == 'calificada') {
                            $tema_nota = DB::table('pruebas')
                                ->select('nota','resultado')
                                ->where('usuario_id', $usuario_id)
                                ->where('posteo_id', $tema->id)
                                ->first();
                            if ($tema_nota) {
                                $nota = $tema_nota->nota;
                                $tema_estado = ($tema_nota->resultado) ? 'aprobado' : 'desaprobado';
                            }
                        } else if ($tema->tipo_ev == '' || $tema->evaluable == 'no') {
                            if ($estado_tema) {
                                // $tema->estado_tema = $estado_tema->estado_tema;
                                $tema->sumatoria = $estado_tema->sumatoria;
                            }
                        }

                        // TEMA: Disponible (verificando si tiene requisitos)
                        $requisito = DB::table('posteos')->select('id', 'categoria_id', 'evaluable', 'tipo_ev')->find($tema->requisito_id);

                        if (!$requisito) {
                            $disponible = true;
                        } else {
                            $actividad = DB::table('visitas')
                                ->select('post_id', 'estado_tema')
                                ->where('usuario_id', $usuario_id)
                                ->where('post_id', $requisito->id)
                                ->whereIn('estado_tema', ['aprobado', 'realizado', 'revisado'])
                                ->first();
                            
                            if ($actividad) {
                                // dd($actividad);
                                $disponible = true;
                            }
                            if($requisito->tipo_ev=='calificada'){
                                $prueba = Prueba::where('usuario_id',$usuario_id)
                                                ->where('posteo_id', $requisito->id)
                                                ->where('historico',1)
                                                ->where('resultado',1)
                                                ->first();
                                if($prueba){
                                    $disponible = true;
                                }
                            }
                        }

                        $tema->disponible = $disponible;
                        $tema->nota = $nota;
                        $temas_arr[] = [
                            'id' => $tema->id,
                            'nombre' => $tema->nombre,
                            // 'evaluable' => $tema->evaluable,
                            // 'tipo_ev' => $tema->tipo_ev,
                            // 'existe_evab' => $tema->existe_evab,
                            'disponible' => $disponible,
                            'nota' => $nota,
                            'estado' => $tema_estado,
                            'estado_str' => $arr_estados[$tema_estado]
                        ];
                    }
                }

                $data = [
                    'id' => $curso_id,
                    'nombre' => $curso->nombre,
                    'nota' => $nota_prom,
                    'estado' => $cur_status,
                    'estado_str' => $arr_estados_cursos[$cur_status],
                    'temas' => $temas_arr
                ];
                $detalle_cursos->push($data);
            }
            /** VALIDAR SI EL USUARIO SE ENCUENTRA MATRICULADO EN EL CICLO 0 y FORZAR EL NOMBRE*/
            $nombre_ciclo_forzado = $key;
            if ($matricula_actual->secuencia_ciclo == 0) {
                $ciclo = Ciclo::select('nombre')->find($matricula_actual->ciclo_id);
                $nombre_ciclo_forzado = isset($ciclo) ? $ciclo->nombre : "~";
            }
            /** VALIDAR SI EL USUARIO SE ENCUENTRA MATRICULADO EN EL CICLO 0 y FORZAR EL NOMBRE*/
            $data_return[] = [
                'id' => $cursos[0]->ciclo_id,
                'nombre' => $nombre_ciclo_forzado,
                'cursos' => $detalle_cursos,
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
        $escuela_porcentaje =  round($escuela_porcentaje, 2);
        return [
            'data_curso' => $data_return,
            'data_adicional_escuela' => [
                'escuela_asignados' => $escuela_asignados,
                'escuela_completados' => $escuela_completados,
                'escuela_porcentaje' => $escuela_porcentaje,
                'escuela_resultado' => $escuela_resultado,
                'escuela_resultado_str' => $arr_estados[$escuela_resultado]
            ]
        ];
    }
}
