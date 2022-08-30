<?php

namespace App\Http\Controllers\ApiRest;

use Config;
use App\Models\Curso;
use App\Models\Posteo;

use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Diploma;
use App\Models\Usuario;
use App\Models\Usuario_rest;
use App\Models\Curso_encuesta;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiRest\HelperController;

class RestAvanceController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth.jwt');
    //     return auth()->shouldUse('api');
    // }
    /*************************REDISEÑO********************** */
    // public function guarda_visitas_post_rediseño($posteo_id = null)
    // {
    //     $user_id = Auth::id();
    //     $existe = DB::table('visitas')
    //         ->select('id', 'sumatoria')
    //         ->where('post_id', $posteo_id)
    //         ->where('usuario_id', $user_id)
    //         ->limit(1)
    //         ->get();

    //     $id = 0;
    //     $vistos = 0;

    //     if (count($existe) > 0) {
    //         foreach ($existe as $row) {
    //             $id = $row->id;
    //             $vistos = $row->sumatoria;
    //         }
    //         if ($id > 0) {
    //             $vistos++;
    //             $query = DB::table('visitas')->where('id', $id)->update(array('sumatoria' => $vistos));
    //         }
    //     } else {
    //         $res = DB::table('posteos')->select('curso_id')->where('id', $posteo_id)->first();
    //         $curso_id = ($res) ? $res->curso_id : NULL;
    //         $vistos++;
    //         $query = DB::table('visitas')->where('id', $id)->insert(array('estado_tema'=>'por-iniciar','sumatoria' => $vistos, 'curso_id' => $curso_id, 'post_id' => $posteo_id, 'usuario_id' => $user_id, 'descargas' => 0));
    //     }
    //     return array('error' => 0);
    // }
    // public function preguntasIntentos_v7_rediseño($post_id = null, $fuente)
    // {
    //     $id_user = auth()->user()->id;
    //     if (is_null($post_id) && is_null($id_user)) {
    //         $response = array('error' => 2, 'data' => null);
    //     } else {
    //         $user = DB::table('usuarios')->select('config_id')->where('id', $id_user)->first();
    //         $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);
    //         //
    //         $ev = Prueba::select('id', 'intentos', 'categoria_id', 'curso_id')->where('posteo_id', $post_id)->where('usuario_id', $id_user)->first();
    //         if ($ev) {
    //             if (intval($ev->intentos) < intval($mod_eval['nro_intentos'])) {
    //                 $intentos = intval($ev->intentos) + 1;
    //                 Prueba::where('id', $ev->id)->update(array('intentos' => $intentos,'last_ev' =>Carbon::now()));
    //             } else {
    //                 return array('error' => 0, 'data' => null);
    //             }
    //         } else {
    //             $posteo = Posteo::find($post_id);
    //             $intentos = 1;
    //             $new_prueba_id = Prueba::insertGetId(array(
    //                 'categoria_id' => $posteo->categoria_id,
    //                 'curso_id' => $posteo->curso_id,
    //                 'posteo_id' => $post_id,
    //                 'usuario_id' => $id_user,
    //                 'intentos' => $intentos,
    //                 'fuente' => $fuente,
    //                 'last_ev' =>Carbon::now(),
    //             ));

    //             $ev = Prueba::find($new_prueba_id);
    //         }

    //         // INSERTA / ACTUALIZA -> RESUMEN_X_CURSO
    //         $this->resumen_intentos($id_user, $ev->curso_id, $ev->categoria_id);

    //         if ($ev)
    //             $response = array('error' => 0, 'data' => $intentos);
    //         else
    //             $response = array('error' => 1, 'data' => null);
    //     }
    //     return $response;
    // }
    /*************************REDISEÑO********************** */

    /****************** VISTAS **************/
    // public function guarda_visitas_post($idvideo = null, $idusuario = null)
    // {
    //     /*NUEVO*/
    //     $res = DB::table('posteos')->select('curso_id')->where('id', $idvideo)->first();
    //     if(is_null($res)){
    //         return array('error' => 1);
    //     }
    //     $existe = Visita::select('id', 'sumatoria')
    //         ->where('post_id', $idvideo)
    //         ->where('usuario_id', $idusuario)
    //         ->first();
    //     $curso_id = ($res) ? $res->curso_id : NULL;
    //     if(isset($existe)){
    //         $sumatoria = intval($existe->sumatoria);
    //         $existe->sumatoria = $sumatoria+1;
    //         $existe->save();
    //     }else{
    //         Visita::insert(
    //             ['sumatoria' => 1,
    //             'curso_id' => $curso_id,
    //             'post_id' => $idvideo,
    //             'usuario_id' => $idusuario,
    //             'descargas' => 0
    //         ]);
    //     }

    //     // $existe = DB::table('visitas')
    //     //     ->select('id', 'sumatoria')
    //     //     ->where('post_id', $idvideo)
    //     //     ->where('usuario_id', $idusuario)
    //     //     ->limit(1)
    //     //     ->get();

    //     // $id = 0;
    //     // $vistos = 0;
    //     // $res = DB::table('posteos')->select('curso_id')->where('id', $idvideo)->first();
    //     // $curso_id = ($res) ? $res->curso_id : NULL;
    //     // if (count($existe) > 0) {
    //     //     foreach ($existe as $row) {
    //     //         $id = $row->id;
    //     //         $vistos = $row->sumatoria;
    //     //     }
    //     //     if ($id > 0) {
    //     //         $vistos++;
    //     //         $query = DB::table('visitas')->where('id', $id)->update(array('sumatoria' => $vistos));
    //     //     }
    //     // } else {
    //     //     $vistos++;
    //     //     $query = DB::table('visitas')->where('id', $id)->insert(array('sumatoria' => $vistos, 'curso_id' => $curso_id, 'post_id' => $idvideo, 'usuario_id' => $idusuario, 'descargas' => 0));
    //     // }

    //     $rxc = Resumen_x_curso::where('curso_id',$curso_id)->where('usuario_id',$idusuario)->select('id','visitas')->first();
    //     if($rxc){
    //         $visita_x_curso = intval($rxc->visitas)+1;
    //         Resumen_x_curso::where('id',$rxc->id)->update([
    //             'visitas' => $visita_x_curso
    //         ]);
    //     }
    //     return array('error' => 0);
    // }
    /**
     *   Crea por primera vez, y actualiza para los siguiente INTENTOS del usuario ante una prueba
     */
    // public function preguntasIntentos_v7($post_id = null, $id_user = null, $fuente)
    // {
    //     if (is_null($post_id) && is_null($id_user)) {
    //         $response = array('error' => 2, 'data' => null);
    //     } else {
    //         $user = DB::table('usuarios')->select('config_id')->where('id', $id_user)->first();
    //         $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);
    //         $ultima_evaluacion = Carbon::now();
    //         $ev = Prueba::select('id', 'intentos', 'categoria_id', 'curso_id')->where('posteo_id', $post_id)->where('usuario_id', $id_user)->first();
    //         if ($ev) {
    //             if (intval($ev->intentos) < intval($mod_eval['nro_intentos'])) {
    //                 $intentos = intval($ev->intentos) + 1;
    //                 Prueba::where('id', $ev->id)->update(array('intentos' => $intentos,'last_ev' => $ultima_evaluacion));
    //             } else {
    //                 return array('error' => 0, 'data' => null);
    //             }
    //         } else {
    //             $posteo = Posteo::find($post_id);
    //             $intentos = 1;
    //             $new_prueba_id = Prueba::insertGetId(array(
    //                 'categoria_id' => $posteo->categoria_id,
    //                 'curso_id' => $posteo->curso_id,
    //                 'posteo_id' => $post_id,
    //                 'usuario_id' => $id_user,
    //                 'intentos' => $intentos,
    //                 'fuente' => $fuente,
    //                 'last_ev' => $ultima_evaluacion,
    //             ));

    //             $ev = Prueba::find($new_prueba_id);
    //         }
    //         DB::table('resumen_general')->where('usuario_id',$id_user)->update([
    //             'last_ev' =>$ultima_evaluacion,
    //         ]);
    //         DB::table('resumen_x_curso')->where('usuario_id',$id_user)->where('curso_id',$ev->curso_id)->update([
    //             'last_ev' =>$ultima_evaluacion,
    //         ]);
    //         // INSERTA / ACTUALIZA -> RESUMEN_X_CURSO
    //         $this->resumen_intentos($id_user, $ev->curso_id, $ev->categoria_id);

    //         if ($ev)
    //             $response = array('error' => 0, 'data' => $intentos);
    //         else
    //             $response = array('error' => 1, 'data' => null);
    //     }
    //     return $response;
    // }
    /**
     * GUARDAR CALIFICACION Y RESPUESTAS DE EVALUACION
     */
    // public function evaluarpreguntas_v2($post_id = null, $id_user = null, $rpta_ok = null, $rpta_fail = null, $usu_rptas = null)
    // {
    //     $data_ev = null;
    //     $response = array('error' => 3, 'msg' => 'Error. Algo ha ocurrido.');
    //     if (is_null($post_id) && is_null($id_user) && is_null($rpta_ok) && is_null($rpta_fail)) {
    //         $response = array('error' => 2, 'data' => $data_ev, 'msg' => 'No values');
    //     } else {
    //         $user = DB::table('usuarios')->select('config_id')->where('id', $id_user)->first();
    //         $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);
    //         $nota_aprobatoria = $mod_eval['nota_aprobatoria'];
    //         $nota_calculada = ($rpta_ok == 0) ? 0 : ((20 / ($rpta_ok + $rpta_fail)) * $rpta_ok);
    //         $resultado = ($nota_calculada >= $nota_aprobatoria) ? 1 : 0;

    //         $ev = Prueba::select('id', 'nota', 'rptas_ok', 'rptas_fail', 'resultado', 'categoria_id', 'curso_id')
    //             ->where('posteo_id', $post_id)
    //             ->where('usuario_id', $id_user)
    //             ->first();

    //         $ultima_evaluacion = Carbon::now();
    //         $data_ev = array(
    //             'rptas_ok' => $rpta_ok,
    //             'rptas_fail' => $rpta_fail,
    //             'resultado' => $resultado,
    //             'usu_rptas' => $usu_rptas,
    //             'nota' => round($nota_calculada),
    //             'last_ev' => $ultima_evaluacion
    //         );

    //         if ($ev) {
    //             $nota_bd = $ev->nota;
    //             $actividad = Visita::select('id')
    //                     ->where('post_id', $post_id)
    //                     ->where('usuario_id', $id_user)
    //                     ->first();
    //             if ($nota_calculada >= $nota_bd) {
    //                 Prueba::where('id', $ev->id)->update($data_ev);
    //                 $data_ev['intentos_realizados'] = $ev->intentos;
    //                 if ($actividad) {
    //                     $actividad->tipo_tema = 'calificada';
    //                     $actividad->estado_tema = ($resultado == 1) ? 'aprobado' : 'desaprobado';
    //                     $actividad->save();
    //                 }else{
    //                     Visita::insert(array(
    //                             'sumatoria' => 1,
    //                             'curso_id' => $ev->curso_id,
    //                             'post_id' => $post_id,
    //                             'usuario_id' => $id_user,
    //                             'descargas' => 0,
    //                             'tipo_tema' => 'calificada',
    //                             'estado_tema'=> ($resultado == 1) ? 'aprobado' : 'desaprobado' ,
    //                         )
    //                     );
    //                 }
    //                 $data_ev['ev_updated'] = 1;
    //                 $data_ev['ev_updated_msg'] = "(1) EV actualizada";
    //                 $response = array('error' => false, 'data' => $data_ev);
    //             } else {
    //                 DB::table('pruebas')->where('id',$ev->id)->update([
    //                     'last_ev' =>$ultima_evaluacion,
    //                 ]);
    //                 if (is_null($actividad)) {
    //                     Visita::insert(array(
    //                         'sumatoria' => 1,
    //                         'curso_id' => $ev->curso_id,
    //                         'post_id' => $post_id,
    //                         'usuario_id' => $id_user,
    //                         'descargas' => 0,
    //                         'tipo_tema' => 'calificada',
    //                         'estado_tema'=> ($ev->resultado == 1) ? 'aprobado' : 'desaprobado' ,
    //                     )
    //                     );
    //                 }
    //                 $data_ev['intentos_realizados'] = $ev->intentos;
    //                 $data_ev['ev_updated'] = 0;
    //                 $data_ev['ev_updated_msg'] = "(0) EV no actualizada (nota obtenida menor que nota existente)";
    //                 $response = array('error' => false, 'data' => $data_ev);
    //             }
    //             // ACTUALIZAR RESUMENES
    //             $this->actualizar_resumen_x_curso($id_user, $ev->curso_id, $mod_eval['nro_intentos']);
    //             $this->actualizar_resumen_general($id_user);
    //             DB::table('resumen_general')->where('usuario_id',$id_user)->update([
    //                 'last_ev' =>$ultima_evaluacion,
    //             ]);
    //             DB::table('resumen_x_curso')->where('usuario_id',$id_user)->where('curso_id',$ev->curso_id)->update([
    //                 'last_ev' =>$ultima_evaluacion,
    //             ]);
    //         } else {
    //             $response = array('error' => true, 'error_msg' => 'EV no existe');
    //         }
    //     }
    //     // $this->actualizaResumenTotal($id_user);
    //     // $this->actualizaResumenCategorias( $id_user, $post_id );
    //     return $response;
    // }

    /**
     * GUARDAR EVALUACION ABIERTA
     */

    public function guardaEvaluacionAbierta(Request $request)
    {
        $post_id = strip_tags($request->input('post_id'));
        $user_id = strip_tags($request->input('user_id'));
        $fuente = strip_tags($request->input('fuente'));
        $rptas = strip_tags($request->input('rptas'));
        $rptas_dec = urldecode($rptas);
        $rptas_jc = json_decode($rptas_dec);

        if (is_null($post_id) && is_null($user_id)) {
            $response = array('error' => 2);
        } else {

            $existe = DB::table('ev_abiertas')
                ->where('posteo_id', $post_id)
                ->where('usuario_id', $user_id)
                ->first();

            $posteo = Posteo::select('categoria_id', 'curso_id')->find($post_id);
            $categoria_id = ($posteo) ? $posteo->categoria_id : "";
            $curso_id = ($posteo) ? $posteo->curso_id : "";
            if (!$existe) {
                //return $posteo;
                $rptas_jc = json_encode($rptas_jc, JSON_UNESCAPED_UNICODE);

                $array = array(
                    'categoria_id' => $categoria_id,
                    'curso_id' => $curso_id,
                    'posteo_id' => $post_id,
                    'usuario_id' => $user_id,
                    'usu_rptas' => $rptas_jc,
                    'fuente' => $fuente
                );

                DB::table('ev_abiertas')->insert($array);

                

                $response = array('error' => 0);
            } else {
                $response = array('error' => '3', 'msg' => 'ya fue insertado');
            }
            $actividad = Visita::select('id')
                    ->where('post_id', $post_id)
                    ->where('usuario_id', $user_id)
                    ->first();
            if ($actividad) {
                $actividad->tipo_tema = 'abierta';
                $actividad->estado_tema = 'realizado';
                $actividad->save();
            }
            // ACTUALIZAR RESUMENES 
            $user = DB::table('usuarios')->select('config_id')->where('id', $user_id)->first();
            $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);

            $this->actualizar_resumen_x_curso($user_id,  $curso_id, $mod_eval['nro_intentos']);
            $this->actualizar_resumen_general($user_id);
            return $response;
        }
    }

    // Actualizar estado de tema según botón presionado
    public function actividad_tema_revisado($posteo_id = null, $usuario_id = null)
    {
        $response = array('status' => 'error', 'msg' => 'Ha ocurrido un error al actualizar la actividad');

        $usuario_id = auth()->user()->id ?? $usuario_id;
        
        $actividad = Visita::select('id')
            ->where('post_id', $posteo_id)
            ->where('usuario_id', $usuario_id)
            ->first();

        if ($actividad) {
            $actividad->tipo_tema = 'no-evaluable';
            $actividad->estado_tema = 'revisado';
            $actividad->save();

            // ACTUALIZAR RESUMENES
            $posteo = Posteo::select('categoria_id', 'curso_id')->find($posteo_id);
            $categoria_id = ($posteo) ? $posteo->categoria_id : "";
            $curso_id = ($posteo) ? $posteo->curso_id : "";

            $user = DB::table('usuarios')->select('config_id')->where('id', $usuario_id)->first();
            $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);

            $this->actualizar_resumen_x_curso($usuario_id, $curso_id, $mod_eval['nro_intentos']);
            $this->actualizar_resumen_general($usuario_id);

            $response = array('status' => 'exito', 'msg' => 'Actividad actualizada correctamente');
        } else {
            $response = array('status' => 'alerta', 'msg' => 'No se ha actualizado el registro');
        }

        return $response;
    }

    // guardar Encuesta por Posteo
    public function guardarEncuestaCurso(Request $request)
    {
        $enc_id = strip_tags($request->input('enc_id'));
        $curso_id = strip_tags($request->input('curso'));
        $user_id = strip_tags($request->input('user'));
        $data = strip_tags($request->input('data'));
        $datadec = urldecode($data);
        $data = json_decode($datadec);

        // return $data;
        if (!is_null($data)) {
            foreach ($data as $key_data => $value_data) {
                if (!is_null($value_data) && $value_data->tipo == 'multiple') {
                    $multiple = array();
                    $ddd = array_count_values($value_data->respuesta);
                    if (!is_null($ddd)) {
                        foreach ($ddd as $key => $value) {
                            if ($value % 2 != 0) {
                                array_push($multiple, $key);
                            }
                        }
                    }
                    $query1 = $this->actualizarEncuestaRespuestas($enc_id, $curso_id, $value_data->id, $user_id, $value_data->tipo, json_encode($multiple, JSON_UNESCAPED_UNICODE));
                }
                if (!is_null($value_data) && $value_data->tipo == 'califica') {
                    $multiple = array();
                    $array_respuestas = $value_data->respuesta;
                    $ddd = array_count_values(array_column($array_respuestas, 'preg_cal'));
                    $ttt = array();
                    if (!is_null($array_respuestas) && count($array_respuestas) > 0) {
                        foreach ($array_respuestas as $key => $value) {
                            if (!is_null($value)) {
                                foreach ($ddd as $key2 => $val2) {
                                    if ($key2 == $value->preg_cal) {
                                        $ttt[$value->preg_cal] = $value;
                                    }
                                }
                            }
                        }
                    }
                    if (!is_null($ttt) && count($ttt) > 0) {
                        foreach ($ttt as $elemento) {
                            array_push($multiple, $elemento);
                        }
                    }
                    $query2 = $this->actualizarEncuestaRespuestas($enc_id, $curso_id, $value_data->id, $user_id, $value_data->tipo, json_encode($multiple, JSON_UNESCAPED_UNICODE));
                }
                if (!is_null($value_data) && $value_data->tipo == 'texto') {
                    $query3 = $this->actualizarEncuestaRespuestas($enc_id, $curso_id, $value_data->id, $user_id, $value_data->tipo, trim($value_data->respuesta));
                }
                if (!is_null($value_data) && $value_data->tipo == 'simple') {
                    $query4 = $this->actualizarEncuestaRespuestas($enc_id, $curso_id, $value_data->id, $user_id, $value_data->tipo, $value_data->respuesta);
                }
            }
        }

        // Actualizar resumenes
        $this->actualizar_resumen_encuesta($user_id, $curso_id);

        $user = auth()->user();

        $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
        $mod_eval = json_decode($config->mod_evaluaciones, true);

        $this->actualizar_resumen_x_curso($user_id, $curso_id, $mod_eval['nro_intentos']);

        $response = array('error' => 0, 'data' => null);

        return $response;
    }
    // -------------------------------------------SUBFUNCIONES DE AYUDA------------------------------------------------//

    public function actualizar_resumen_x_curso($usuario_id, $curso_id, $max_intentos)
    {
        $helper = new HelperController();


        $posteos_activos = Posteo::where('curso_id', $curso_id)->where('estado', 1)->select('id','evaluable','tipo_ev')->get();
        $curso = Curso::where('id',$curso_id)->select('id','libre','categoria_id')->first();

        $cant_asignados = count($posteos_activos);
        $posteos_calificados =  $posteos_activos->where('tipo_ev','calificada');
        $posteos_abiertos = $posteos_activos->where('tipo_ev','abierta');
        $posteos_para_revisar = $posteos_activos->where('evaluable','no');
        // APROBADOS CALIFICADOS
        // $helper->log_marker('RxC PRUEBAS');
        $cant_aprob = 0;
        $cant_abiertos = 0;
        $cant_revisados = 0;
        $cant_desaprob = 0;
        $asing = $helper->help_cursos_x_matricula_con_cursos_libre($usuario_id);
        $el_curso_esta_asignado = false;
        if(count($asing)>0){
            $el_curso_esta_asignado = in_array($curso_id,$asing);
        }

        if($el_curso_esta_asignado){
            if(count($posteos_calificados)>0){
                $cant_aprob = DB::table('pruebas')
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->whereIn('posteo_id',$posteos_calificados->pluck('id'))
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->where('pruebas.resultado', 1)
                ->where('pruebas.usuario_id', $usuario_id)
                ->where('pruebas.curso_id', $curso_id)
                ->count();
                $cant_desaprob = DB::table('pruebas AS u')
                    ->whereIn('posteo_id',$posteos_calificados->pluck('id'))
                    ->where('u.resultado', 0)
                    ->where('u.intentos', '>=', $max_intentos)
                    ->where('u.usuario_id', $usuario_id)
                    ->where('u.curso_id', $curso_id)
                    ->count();
            }
            if(count($posteos_abiertos)>0){
                // APROBADOS ABIERTOS
                $cant_abiertos = DB::table('ev_abiertas')
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->whereIn('posteo_id',$posteos_abiertos->pluck('id'))
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->where('ev_abiertas.usuario_id', $usuario_id)
                ->where('ev_abiertas.curso_id', $curso_id)
                ->count();
            }
            if(count($posteos_para_revisar)>0){
                $cant_revisados = DB::table('visitas')
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->whereIn('post_id',$posteos_para_revisar->pluck('id'))
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->where('visitas.usuario_id', $usuario_id)
                ->where('visitas.curso_id', $curso_id)
                ->where('visitas.estado_tema', "revisado")
                ->count();
            }
            // REVISADOS (TEMAS NO EVALUABLES QUE FUERON VISTOS)
            // Cambiar estado de resumen_x_curso
            $tot_completados = $cant_aprob + $cant_abiertos + $cant_revisados;

            // Porcentaje avance por curso
            $percent_curso = ($cant_asignados > 0) ? (($tot_completados / $cant_asignados) * 100) : 0;
            $percent_curso = ($percent_curso > 100) ? 100 : $percent_curso; // Maximo porcentaje = 100

            // Valida estados
            $estado_curso = "desarrollo";
            if ($tot_completados >= $cant_asignados) {
                // $helper->log_marker('RxC Encuestas Respuestas');
                $existe_encuesta = Curso_encuesta::select('id')->where('curso_id', $curso_id)->first();
                if($existe_encuesta){
                    $hizo_encuesta = DB::table('encuestas_respuestas')
                    ->select('curso_id')
                    ->where('usuario_id', $usuario_id)
                    ->where('curso_id', $curso_id)
                    ->first();
                    if ($hizo_encuesta) {
                        $estado_curso = "aprobado";
                        // Genera diploma
                        // $helper->log_marker('RxC Diplomas');
                        Diploma::generar_diploma_x_curso_y_escuela($asing,$curso,$usuario_id);
                    } else {
                        $estado_curso = "enc_pend";
                    }
                }else {
                    // Si el curso no tiene encuesta ASOCIADA, por defecto el curso ya está aprobado (validando que todos los temas se hayan completado)
                    $estado_curso = "aprobado";
                    // Genera/valida diploma para curso y escuela
                    Diploma::generar_diploma_x_curso_y_escuela($asing,$curso,$usuario_id);
                }

            } else {
                // $helper->log_marker('RxC Pruebas tot_completados >= $cant_asignados');


                if ($cant_desaprob >= $cant_asignados) {
                    $estado_curso = "desaprobado";
                }
            }
            // nueva Nota promedio CURSO
            // $helper->log_marker('RxC nueva Nota promedio CURSO');

            $res_nota = DB::table('pruebas')
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->whereIn('posteo_id',$posteos_calificados->pluck('id'))
                ->select(DB::raw('AVG(IFNULL(nota, 0)) AS nota_avg'))
                ->where('pruebas.usuario_id', $usuario_id)
                ->where('pruebas.curso_id', $curso_id)
                ->first();

            $nota_prom_curso = number_format((float)$res_nota->nota_avg, 2, '.', '');
            $visitas = Visita::select(DB::raw('SUM(sumatoria) as suma_visitas'))
                    ->whereIn('post_id',$posteos_activos->pluck('id'))
                    ->where('visitas.usuario_id', $usuario_id)
                    ->where('visitas.curso_id', $curso_id)->first();
            $suma_visitas = (isset($visitas)) ? $visitas->suma_visitas : 0;
            /*-----------------------------------------------------------------------------------------------------------------------------------------*/
            // $helper->log_marker('RxC Update');
            if($tot_completados>0 || $cant_desaprob>0 || $nota_prom_curso>0){
                $this->crear_actualizar_resumenes($usuario_id, $curso_id);
            }
            Resumen_x_curso::where('usuario_id', $usuario_id)->where('curso_id', $curso_id)->update(array(
                'estado_rxc'=>1,
                'asignados' => $cant_asignados,
                'aprobados' => $cant_aprob,
                'realizados' => $cant_abiertos,
                'revisados' => $cant_revisados,
                'desaprobados' => $cant_desaprob,
                'nota_prom' => $nota_prom_curso,
                'estado' => $estado_curso,
                'porcentaje' => $percent_curso,
                'visitas' => $suma_visitas,
                'libre' => $curso->libre
                // 'intentos' => $suma_intentos->intentos,
            ));
            // $helper->log_marker('RxC Update FIN');
        }else{
            Resumen_x_curso::where('usuario_id',$usuario_id)->where('curso_id',$curso_id)->update([
                'estado_rxc'=>0
            ]);
        }

    }

    // Actualizar solo resumen general
    public function actualizar_resumen_general($usuario_id)
    {
        $res_general = Resumen_general::where('usuario_id', $usuario_id)->first();
        if (is_null($res_general)) {
            $res_general = $this->new_res_general($usuario_id);
        }
        // Asignados
        $help_cursos_x_matricula = new HelperController;
        $q_idsXcursos = $help_cursos_x_matricula->help_cursos_x_matricula($usuario_id);
        $general_asignados = count($q_idsXcursos);

        $helper = new HelperController();
        // $helper->log_marker('RG pruebas');
        $res_nota = DB::table('pruebas')
                        // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                    ->join('posteos', 'posteos.id', 'pruebas.posteo_id')
                    ->whereIn('pruebas.curso_id',$q_idsXcursos)
                    ->where('posteos.estado', 1)
                    // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                    ->select(DB::raw('AVG(IFNULL(pruebas.nota, 0)) AS nota_avg'))
                    ->where('pruebas.usuario_id', $usuario_id)
                    ->first();

        $nota_prom_gen = number_format((float)$res_nota->nota_avg, 2, '.', '');

        // $helper->log_marker('RG tot_completed_gen');
        $tot_completed_gen = Resumen_x_curso::where('usuario_id', $usuario_id)
                                            // TOMAR EN CUENTA SOLO LOS CURSOS ACTIVOS
                                            ->where('estado_rxc', 1)
                                            ->where('libre', 0)
                                            ->whereIn('curso_id',$q_idsXcursos)
                                            ->where('estado', 'aprobado')->count();

        // \Log::info($general_asignados. '   =   '. $tot_completed_gen);
        // porcentaje general
        $percent_general = ($general_asignados > 0) ? (($tot_completed_gen / $general_asignados) * 100) : 0;
        $percent_general = ($percent_general > 100) ? 100 : $percent_general; // maximo porcentaje = 100
        $percent_general = round($percent_general);
        // Calcula ranking
        // $helper->log_marker('RG Calcula ranking');
        $intentos_x_curso = Resumen_x_curso::select(DB::raw('SUM(intentos) as intentos'))
                                            // TOMAR EN CUENTA SOLO LOS CURSOS ACTIVOS
                                            ->whereIn('curso_id',$q_idsXcursos)
                                            ->where('estado_rxc', 1)
                                            ->where('libre', 0)
                                            // TOMAR EN CUENTA SOLO LOS CURSOS ACTIVOS
                                            ->where('usuario_id', $usuario_id)->first();
        $intentos_gen = (isset($intentos_x_curso)) ? $intentos_x_curso->intentos : 0;
        $rank_user = $this->calcular_puntos($tot_completed_gen, $nota_prom_gen, $intentos_gen);

        // $helper->log_marker('RG UPDATE');
        $tot_com = ($tot_completed_gen > $general_asignados) ? $general_asignados : $tot_completed_gen;
        Resumen_general::where('usuario_id', $usuario_id)->update(array(
            'tot_completados' => $tot_com,
            'nota_prom' => $nota_prom_gen,
            'cur_asignados' => $general_asignados,
            'intentos' => $intentos_gen,
            'rank' => $rank_user,
            'porcentaje' => $percent_general
        ));
        // $helper->log_marker('RG UPDATE FIN');

    }

    // VALIDAR ESTADO CURSO PARA ENCUESTA
    private function actualizar_resumen_encuesta($usuario_id, $curso_id)
    {
        $result_proceso = $this->crear_actualizar_resumenes($usuario_id, $curso_id);
        $res_x_curso = $result_proceso['res_x_curso'];
        $res_general = $result_proceso['res_general'];

        // ACTUALIZA -> RESUMEN_X_CURSO
        DB::table('resumen_x_curso')->where('usuario_id', $usuario_id)->where('curso_id', $curso_id)->update(array(
            'estado' => 'aprobado',
            'porcentaje' => '100'
        ));
        // Sumas
        $q_aprobados = Resumen_x_curso::select('id')
            ->where('estado_rxc', 1)
            ->where('libre', 0)
            ->where('usuario_id', $usuario_id)
            ->where('estado', 'aprobado')
            ->count();

        // $percent_general = number_format((float)(($q_aprobados / $res_general->cur_asignados) * 100), 2, '.', '');
        $percent_general = ($res_general->cur_asignados > 0) ? (($q_aprobados / $res_general->cur_asignados) * 100) : 0;
        $percent_general = ($percent_general > 100) ? 100 : $percent_general; // maximo porcentaje = 100
        $percent_general = round($percent_general);
        // dd($q_aprobados, $res_general->cur_asignados, $percent_general);

        $rank_user = $this->calcular_puntos($q_aprobados, $res_general->nota_prom, $res_general->intentos);

        DB::table('resumen_general')->where('usuario_id', $usuario_id)->update(array(
            'tot_completados' => $q_aprobados,
            'porcentaje' => $percent_general,
            'rank' => $rank_user
        ));
    }

    // private function crear_actualizar_resumenes($usuario_id, $curso_id)
    // {

    //     $res_x_curso = Resumen_x_curso::where('usuario_id', $usuario_id)->where('curso_id', $curso_id)->first();
    //     $res_general = Resumen_general::where('usuario_id', $usuario_id)->first();
    //     // insert resumen if not exists
    //     if (is_null($res_x_curso)) {
    //         $intentos_default = 0;
    //         $res_x_curso = $this->new_res_x_curso($usuario_id, $curso_id, $intentos_default);
    //     }
    //     // insert resumen if not exists
    //     if (is_null($res_general)) {
    //         $res_general = $this->new_res_general($usuario_id);
    //     }
    //     return [
    //         "res_x_curso" => $res_x_curso,
    //         "res_general" => $res_general
    //     ];
    // }

    // private function new_res_x_curso($id_user, $curso_id, $intentos_default)
    // {
    //     $curso = Curso::select('categoria_id','libre')->find($curso_id);

    //     $asignados = Posteo::where('curso_id', $curso_id)->where('estado', 1)->count();
    //     $data = Resumen_x_curso::create([
    //         'usuario_id' => $id_user,
    //         'curso_id' => $curso_id,
    //         'categoria_id' => $curso->categoria_id,
    //         'asignados' => $asignados,
    //         'intentos' => $intentos_default,
    //         'libre' => $curso->libre,
    //         'estado' => 'desarrollo'
    //     ]);

    //     return $data;
    // }

    // private function new_res_general($id_user)
    // {
    //     // Asignados
    //     $help_cursos_x_matricula = new HelperController;
    //     $q_idsXcursos = $help_cursos_x_matricula->help_cursos_x_matricula($id_user);
    //     $q_cursos_asignados = count($q_idsXcursos);

    //     $cant_asignados = ($q_cursos_asignados) ? $q_cursos_asignados : 0;

    //     $res_x_curso = DB::table('resumen_x_curso')
    //         ->select(DB::raw('SUM(intentos) AS sum_intentos'))
    //         ->where('usuario_id', $id_user)
    //         ->where('estado_rxc', 1)
    //         ->where('libre', 0)
    //         ->first();

    //     $intentos = ($res_x_curso) ? $res_x_curso->sum_intentos : 0;

    //     $data = Resumen_general::create([
    //         'usuario_id' => $id_user,
    //         'tot_completados' => 0,
    //         'cur_asignados' => $cant_asignados,
    //         'intentos' => $intentos,
    //         'rank' => 0,
    //         'porcentaje' => 0
    //     ]);
    //     return $data;
    // }

    // ACtualiza por CURSO
    private function actualizarEncuestaRespuestas($enc_id, $curso_id, $pregunta_id, $user_id, $tipo_pregunta, $respuestas)
    {
        $select = DB::table('encuestas_respuestas')
            ->select('id')
            ->where('encuesta_id', $enc_id)
            ->where('curso_id', $curso_id)
            ->where('pregunta_id', $pregunta_id)
            ->where('usuario_id', $user_id)
            ->limit(1)
            ->get();
        if (count($select) > 0) {
            $query = DB::table('encuestas_respuestas')
                ->where('id', $select[0]->id)
                ->update([
                    'tipo_pregunta' => $tipo_pregunta,
                    'respuestas' => $respuestas
                ]);
        } else {
            $query = DB::table('encuestas_respuestas')
                ->insert([
                    'encuesta_id' => $enc_id,
                    'curso_id' => $curso_id,
                    'pregunta_id' => $pregunta_id,
                    'usuario_id' => $user_id,
                    'tipo_pregunta' => $tipo_pregunta,
                    'respuestas' => $respuestas
                ]);
        }
        return $query;
    }

    // public function resumen_intentos($id_user, $curso_id, $categoria_id)
    // {
    //     // INSERTA / ACTUALIZA -> RESUMEN_X_CURSO
    //     $res_x_curso = Resumen_x_curso::select('id', 'intentos')->where('usuario_id', $id_user)->where('curso_id', $curso_id)->first();
    //     $posteos_activos = Posteo::where('curso_id',$curso_id)->where('estado',1)->select('id')->get();
    //     $visitas = Visita::select(DB::raw('SUM(sumatoria) as suma_visitas'))
    //         ->whereIn('post_id',$posteos_activos->pluck('id'))
    //         ->where('visitas.usuario_id', $id_user)
    //         ->where('visitas.curso_id', $curso_id)->first();
    //     $suma_visitas = (isset($visitas)) ? $visitas->suma_visitas : 0;
    //     if ($res_x_curso) { // Actualiza
    //         $suma_intentos = $res_x_curso->intentos + 1;
    //         Resumen_x_curso::where('id', $res_x_curso->id)->where('curso_id', $curso_id)->update(
    //             array('intentos' => $suma_intentos,'visitas'=>$suma_visitas)
    //         );
    //     } else { // Inserta
    //         $intentos_default = 1;
    //         $this->new_res_x_curso($id_user, $curso_id, $intentos_default);
    //         Resumen_x_curso::where('usuario_id', $id_user)->where('curso_id', $curso_id)->update(
    //             array('visitas'=>$suma_visitas)
    //         );
    //     }

    //     // INSERTA / ACTUALIZA -> RESUMEN_GENERAL
    //     $res_general = Resumen_general::select('id', 'intentos')->where('usuario_id', $id_user)->first();
    //     if ($res_general) { // Actualiza
    //         // $suma_intentos = $res_general->intentos + 1;
    //         // Resumen_general::where('id', $res_general->id)->update(array('intentos' => $suma_intentos));
    //         $this->actualizar_resumen_general($id_user);
    //     } else { // Inserta
    //         $this->new_res_general($id_user);
    //     }
    // }

    // public function calcular_puntos($tot_completed_gen, $nota_prom_gen, $intentos)
    // {
    //     //Calcular puntajes
    //     $puntos_cursos = $tot_completed_gen * 150; //CURSOS COMPLETADOS
    //     $puntos_promedio = $nota_prom_gen * 100;  //PROMEDIO
    //     $puntos_intentos = $intentos * 0.5; //intentos
    //     $total_puntos = $puntos_promedio + $puntos_cursos  - $puntos_intentos;
    //     // if($total_puntos<0) $total_puntos = 0;
    //     if ($nota_prom_gen == 0) $total_puntos = 0;
    //     return $total_puntos;
    // }
}
