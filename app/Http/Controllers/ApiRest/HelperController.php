<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Botica;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Curricula;
use App\Models\Matricula;
use App\Models\Ev_abierta;
use App\Models\Usuario_rest;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Curricula_criterio;
use App\Models\Matricula_criterio;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Exports\MigracionBoticasExport;

class HelperController extends Controller
{
    // Helper : buscar cursos asignados por matricula en cierto ciclo
    public function help_cursos_x_matricula_v3($usuario_id)
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
    //No incluye cursos libres
    public function help_cursos_x_matricula($usuario_id){
        $matriculas = Matricula::with([
            'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
            'curricula'=>function($q){$q->select('id','ciclo_id','carrera_id','curso_id','estado','all_criterios')->where('estado',1);},
            'curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1)->where('libre',0);},
            'curricula.curricula_criterio'=>function($q){$q->select('id','curricula_id','criterio_id');},
        ])->where('usuario_id',$usuario_id)->where('matricula.estado', 1)->get();
        return $this->get_cursos_matriculas_id($matriculas);
    }

    public function help_cursos_x_matricula_con_cursos_libre($usuario_id){
        $matriculas = Matricula::with([
            'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
            'curricula'=>function($q){$q->select('id','ciclo_id','carrera_id','curso_id','estado','all_criterios')->where('estado',1);},
            'curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1);},
            'curricula.curricula_criterio'=>function($q){$q->select('id','curricula_id','criterio_id');},
        ])->where('usuario_id',$usuario_id)->where('matricula.estado', 1)->get();
        return $this->get_cursos_matriculas_id($matriculas);
    }
    public function get_cursos_matriculas_id($matriculas){
        $result = collect();
        foreach ($matriculas as $key => $matricula) {
            $curriculas = $matricula->curricula->where('ciclo_id',$matricula->ciclo_id);
            $criterios_x_matricula = collect($matricula->matricula_criterio->pluck('criterio_id'));
            // $curriculas->where('all_criterios',1);
            // $result->push($curriculas->pluck('curso_id'));
            foreach ($curriculas as $curricula) {
                if($curricula->curso){
                    if(!$curricula->all_criterios){
                        $result->push($curricula->curso_id);
                    }else{
                        $curricula_criterios=$curricula->curricula_criterio->pluck('criterio_id');
                        $intersec = $criterios_x_matricula->intersect($curricula_criterios);
                        if(count($intersec)>0){
                            $result->push($curricula->curso_id);
                        }
                    }
                }
            }
        }
        return $result->unique()->values()->all();
    }
    // public function help_cursos_x_matricula($usuario_id)
    // {

    //     $matriculas = Matricula::with(
    //             [
    //                 'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
    //                 'matricula_criterio.curricula_criterio'=>function($q){ $q->select('id','criterio_id','curricula_id');},
    //                 'matricula_criterio.curricula_criterio.curricula'=>function($q){$q->select('id','curso_id','ciclo_id');},
    //                 'matricula_criterio.curricula_criterio.curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1)->where('libre',0);},
    //             ])
    //         ->where('usuario_id', $usuario_id)
    //         ->where('estado', 1)
    //         ->select('id','ciclo_id')
    //         ->get();
    //     return $this->get_cursos_matriculas_id($matriculas);
    // }
    // public function help_cursos_x_matricula_con_cursos_libre($usuario_id)
    // {

    //     $matriculas = Matricula::with(
    //             [
    //                 'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
    //                 'matricula_criterio.curricula_criterio'=>function($q){ $q->select('id','criterio_id','curricula_id');},
    //                 'matricula_criterio.curricula_criterio.curricula'=>function($q){$q->select('id','curso_id','ciclo_id');},
    //                 'matricula_criterio.curricula_criterio.curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1);},
    //             ])
    //         ->where('usuario_id', $usuario_id)
    //         ->where('estado', 1)
    //         ->select('id','ciclo_id')
    //         ->get();
    //     return $this->get_cursos_matriculas_id($matriculas);
    // }
    // public function get_cursos_matriculas_id($matriculas){
    //     $result = collect();
    //     foreach ($matriculas as $matricula) {
    //         if(isset($matricula->matricula_criterio[0]->curricula_criterio) && count($matricula->matricula_criterio[0]->curricula_criterio)>0){
    //             $curriculas_criterios = $matricula->matricula_criterio[0]->curricula_criterio;
    //             foreach ($curriculas_criterios as $curricula_criterio) {
    //                 if($curricula_criterio->curricula){
    //                     // $cur = Curso::where('id',$curricula_criterio->curricula->curso_id)->select('id','estado')->first();
    //                     if (($curricula_criterio->curricula->curso) && $curricula_criterio->curricula->ciclo_id == $matricula->ciclo_id ){
    //                         $result->push($curricula_criterio->curricula->curso->id);
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     return $result->unique()->values()->all();
    // }
    // public function help_cursos_asignados($usuario_id)
    // {
    //     $result = DB::table('matricula AS m')
    //         ->select(DB::raw('COUNT(c.curso_id) AS cant'))
    //         ->join('curricula AS c', 'c.ciclo_id', '=', 'm.ciclo_id')
    //         ->join('cursos AS u', 'u.id', '=', 'c.curso_id')
    //         ->where('m.usuario_id', $usuario_id)
    //         ->where('m.estado', 1)
    //         ->where('u.estado', 1)
    //         ->first();
    //     return $result;
    // }

    public function actualizarTablasResumen($curso_id, $msg = "")
    {
        // \Log::info("--------------------- ACTUALIZAR TABLAS RESUMENES DE LOS USUARIOS QUE TENGAN ASIGNADOS EL CURSO => $curso_id ---------------------------");
        // \Log::info("$msg");

        // $usuarios_resumen_x_cursos = Resumen_x_curso::with([
        //                 'usuario' => function($query) {
        //                     $query->select('nombre', 'config_id', 'id');
        //                 }
        //             ])
        //             ->where('curso_id', $curso_id)
        //             // TEST
        //             // ->where('usuario_id', 3008)
        //             // TEST
        //             ->get();
        // $rest_avance_controller = new RestAvanceController();
        // $count_usuarios = count($usuarios_resumen_x_cursos);
        // $i = 1;

        // foreach ($usuarios_resumen_x_cursos as $usuario_resumen_x_curso) {
        //     \Log::info($i . ' de ' . $count_usuarios . ' ~ uid: ' . $usuario_resumen_x_curso->usuario_id);
        //     $config = Abconfig::select('mod_evaluaciones')->where('id', $usuario_resumen_x_curso->usuario->config_id)->first();
        //     $mod_eval = json_decode($config->mod_evaluaciones, true);
        //     $this->log_marker('ACTUALIZAR RESUMEN X CURSO');
        //     $rest_avance_controller->actualizar_resumen_x_curso($usuario_resumen_x_curso->usuario_id, $curso_id, $mod_eval['nro_intentos']);
        //     $this->log_marker('ACTUALIZAR RESUMEN GENERAL');
        //     $rest_avance_controller->actualizar_resumen_general($usuario_resumen_x_curso->usuario_id);
        //     $i++;
        // }
        // \Log::info("--------------------- ACTUALIZAR TABLAS RESUMENES DE LOS USUARIOS QUE TENGAN ASIGNADOS EL CURSO => $curso_id ---------------------------");
    }

    public function log_marker($title)
    {
        $sep = '-------------';
        $mem = memory_get_usage();
        info($sep . $title . $sep);
        info('RAM: ' . $this->convert($mem));
        info($sep . $sep);
    }

    public function convert($number)
    {
        $total = $number > 0 ? ($number /1024) /1024 : 0;
        return number_format($total) . "MB";
    }
    //REDISEÑO
    // public function configEvaluacionxModulo($modulo_id)
    // {
    //     $config = Abconfig::select('mod_evaluaciones')->where('id', $modulo_id)->first();
    //     $mod_eval = json_decode($config->mod_evaluaciones, true);

    //     return $mod_eval;
    // }
    public static function getDataReinicioAutomatico($data){
        //reinicios_progrmados
        $isset_rp = isset($data['reinicios_programado']);
        $tiempo_en_minutos = ($isset_rp) ? $data['reinicio_dias']*60*60+$data['reinicio_horas']*60+$data['reinicio_minutos'] : '';
        $array_info = [
            'activado' => $isset_rp,
            'tiempo_en_minutos' => $tiempo_en_minutos,
            'reinicio_dias' =>($isset_rp ) ? $data['reinicio_dias'] : 0,
            'reinicio_horas' =>($isset_rp ) ? $data['reinicio_horas'] : 0,
            'reinicio_minutos' =>($isset_rp ) ?  $data['reinicio_minutos'] : 0,
        ];
        return json_encode($array_info);
    }

    // public function actualizarUsuariosxCriterio()
    // {
    //     // set_time_limit(-1);
    //     $usuarios = Usuario::with('criterio')->get();
    //     $boticas = Botica::with('criterio')->get();

    //     foreach ($usuarios as $key => $usuario) {
    //         $botica = $boticas->where('nombre', $usuario->botica)->where('config_id', $usuario->config_id)->first();
    //         if ($botica) {
    //             $usuario->botica_id = $botica->id;
    //             $usuario->grupo = $botica->criterio_id;
    //             $usuario->grupo_nombre = $botica->criterio->valor;
    //             $usuario->save();
    //         }
    //     }
    // }

    // public function actualizarBoticasxCriterio()
    // {
    //     $boticas = Botica::with('criterio')->get();
    //     $criterios = Criterio::all();
    //     foreach ($boticas as $key => $botica) {
    //         if ($botica->criterio->config_id != $botica->config_id) {
    //             $criterio = $criterios->where('config_id', $botica->config_id)->where('valor', $botica->criterio->valor)->first();
    //             $botica->criterio_id = $criterio->id;
    //             $botica->save();
    //         }
    //     }
    // }

    // public function actualizarCurriculaxCriterio()
    // {
    //     // set_time_limit(-1);
    //     $curriculas_criterios = Curricula_criterio::with('curricula.carrera',  'criterio')->get();
    //     $criterios = Criterio::all();

    //     foreach ($curriculas_criterios as $key => $curr_criterio) {
    //         if ($curr_criterio->curricula->carrera) {
    //             if ($curr_criterio->curricula->carrera->config_id != $curr_criterio->criterio->config_id) {
    //                 $criterio = $criterios->where('config_id', $curr_criterio->curricula->carrera->config_id)
    //                                         ->where('valor', $curr_criterio->criterio->valor)
    //                                         ->first();
    //                 if ($criterio) {
    //                     $curr_criterio->criterio_id = $criterio->id;
    //                     $curr_criterio->save();
    //                 } else {
    //                     // info('error');
    //                     // info($curr_criterio->curricula->carrera->config_id);
    //                     // info($curr_criterio->criterio->valor);
    //                 }
    //             }
    //         } else {
    //             // info('no existe carrera');
    //             // info($curr_criterio->curricula->id);
    //         }
    //     }
    //     return 'ok';
    // }
    // public function actualizarMatriculasxCriterio()
    // {
    //     // set_time_limit(-1);
    //     $matriculas_criterio = Matricula_criterio::with('criterio', 'matricula.usuario')->get();
    //     $criterios = Criterio::all();

    //     foreach ($matriculas_criterio as $key => $matr_criterio) {
    //         if ($matr_criterio->matricula->usuario) {
    //             if ($matr_criterio->criterio->config_id != $matr_criterio->matricula->usuario->config_id) {
    //                 $criterio = $criterios->where('config_id', $matr_criterio->matricula->usuario->config_id)
    //                                         ->where('valor', $matr_criterio->criterio->valor)
    //                                         ->first();
    //                 if ($criterio) {
    //                     $matr_criterio->criterio_id = $criterio->id;
    //                     $matr_criterio->save();
    //                 }
    //             }
    //         } else {
    //             info($matr_criterio->matricula->usuario_id);
    //         }
    //     }
    //     return 'ok';
    // }

    public function migracionBoticas()
    {
        set_time_limit(-1);
        /*
         * Actualiza los campos grupo_nombre y botica_id de acuerdo a la nueva relacion de la tabla botica (con grupo)
         * Retorna un excel con especificacion de errores
         * Busca la botica correcta segun el nombre de la actual botica del usuario y el modulo del usuario
         */
        $usuarios = Usuario::where('rol', '<>', 'testing')->where('estado',1)->get();
        $boticas = Botica::with('criterio')->get();
        $i = 1;
        $data = [
            'no_existe_tabla_botica' => collect(),
            'no_coincide_botica_grupo' => collect()
        ] ;
        $count_usuarios = $usuarios->count();

        foreach ($usuarios as $key => $us) {
            $botica_usuario_tabla = $boticas->where('nombre', $us->botica)->where('config_id', $us->config_id)->first();
            if ($botica_usuario_tabla) {
                if ($us->grupo_nombre == $botica_usuario_tabla->criterio->valor && $us->grupo == $botica_usuario_tabla->criterio_id) {
                    $us->botica_id = $botica_usuario_tabla->id;
                    $us->save();
                } else {
                    $data['no_coincide_botica_grupo']->push($us->id);
                }
            } else {
                $data['no_existe_tabla_botica']->push($us->id);
            }
            $i++;
        }

        $migracion_boticas = new MigracionBoticasExport($data);
        $migracion_boticas->view();
        $random = rand(0, 10000);
        $date = date('mdY');
        // dd($migracion_boticas);
        ob_end_clean();
        ob_start();
        return Excel::download($migracion_boticas, "Migracion_boticas_".$date."_".$random.".xlsx");
    }

    public function actualizarCurriculaxCriterio()
    {
        // set_time_limit(-1);
        /**
         * Verifica que la curricula criterio tenga el correcto criterio
         * Comparando el modulo de la carrera a la que pertenece la curricula con el modulo de criterio
         * Busca el criterio correcto segun el modulo de la carrera de la curricula  y el nombre del criterio de la curricula_criterio
         */
        $curriculas_criterios = Curricula_criterio::with('curricula.carrera',  'criterio')->get();
        $criterios = Criterio::all();

        foreach ($curriculas_criterios as $key => $curr_criterio) {
            if ($curr_criterio->curricula->carrera) {
                if ($curr_criterio->curricula->carrera->config_id != $curr_criterio->criterio->config_id) {
                    $criterio = $criterios->where('config_id', $curr_criterio->curricula->carrera->config_id)
                                            ->where('valor', $curr_criterio->criterio->valor)
                                            ->first();
                    if ($criterio) {
                        $curr_criterio->criterio_id = $criterio->id;
                        $curr_criterio->save();
                    } else {
                        // info('error');
                        // info($curr_criterio->curricula->carrera->config_id);
                        // info($curr_criterio->criterio->valor);
                    }
                }
            } else {
                // info('no existe carrera');
                // info($curr_criterio->curricula->id);
            }
        }
        return 'ok';
    }

    public function actualizarBoticasxCriterio()
    {
        /**
         * Compara el modulo del criterio al que pertenece la botica
         * Con el modulo de la botica
         * Busca el criterio correcto segun el modulo de la botica
         */
        $boticas = Botica::with('criterio')->get();
        $criterios = Criterio::all();
        foreach ($boticas as $key => $botica) {
            if ($botica->criterio->config_id != $botica->config_id) {
                $criterio = $criterios->where('config_id', $botica->config_id)->where('valor', $botica->criterio->valor)->first();
                if ($criterio) {
                    $botica->criterio_id = $criterio->id;
                    $botica->save();
                }
            }
        }
    }

    public function actualizarUsuariosxCriterio()
    {
        /**
         * Actualiza los campo botica_id, grupo y grupo_nombre a la botica que le corresponde segun el modulo del usuario
         * Busca la botica correcta segun el nombre y modulo del usuario
         */
        // set_time_limit(-1);
        $usuarios = Usuario::with('criterio')->where('estado',1)->get();
        $boticas = Botica::with('criterio')->get();

        foreach ($usuarios as $key => $usuario) {
            $botica = $boticas->where('nombre', $usuario->botica)->where('config_id', $usuario->config_id)->first();
            if ($botica) {
                $usuario->botica_id = $botica->id;
                $usuario->grupo = $botica->criterio_id;
                $usuario->grupo_nombre = $botica->criterio->valor;
                $usuario->save();
            }else{
                \Log::channel('soporte_log')->info($usuario->id);
            }
        }
    }

    public function actualizarMatriculasxCriterio()
    {
        /**
         * Actualiza el criterio_id de las matriculas
         * (IF) Comparando el modulo del criterio de la matricula con el modulo del usuario de la matricula
         * Asigna el criterio_id correcto buscando el criterio segun el modulo del usuario
         */
        // set_time_limit(-1);
        $matriculas_criterio = Matricula_criterio::with('criterio', 'matricula.usuario')->get();
        $criterios = Criterio::all();

        foreach ($matriculas_criterio as $key => $matr_criterio) {
            if ($matr_criterio->matricula->usuario) {
                if ($matr_criterio->criterio->config_id != $matr_criterio->matricula->usuario->config_id) {
                    $criterio = $criterios->where('config_id', $matr_criterio->matricula->usuario->config_id)
                                            ->where('valor', $matr_criterio->criterio->valor)
                                            ->first();
                    if ($criterio) {
                        $matr_criterio->criterio_id = $criterio->id;
                        $matr_criterio->save();
                    }
                }
            } else {
                info($matr_criterio->matricula->usuario_id);
            }
        }
        return 'ok';
    }



}
