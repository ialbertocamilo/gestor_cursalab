<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Usuario;
use App\Models\Curricula;
use App\Models\Matricula;
use App\Models\Ev_abierta;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class resumenes_update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:update_resumen_general';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->update_resumen_general();
    }
    // Este comando se esta ejecutando cada 15 minutos
    public function update_resumen_general(){
        $s = DB::table('update_usuarios')->where('estado',1)->first();
        if(is_null($s)){
            $sql = DB::table('update_usuarios')->where('tipo','update_resumenes_curso')->where('estado',0);
            $uu = $sql->orderBy('created_at','asc')->first();
            if($uu){
                // SI SE CREAR,ELIMINA O CAMBIA DE ESTADO UN POSTEO O CURSO SE ACTUALIZA LAS TABLAS RESUMENES SEGUN EL CASO
                DB::table('update_usuarios')->where('id', $uu->id)->update(['estado'=>1]);
                //SI CAMBIAN DE ESTADOS LOS TEMAS
                // $usus = Resumen_x_curso::where('curso_id',$uu->curso_id)->select('usuario_id')->pluck('usuario_id');
                $usus = $this->get_usus($uu->curso_id);
                //Actualizar resumenes
                if(json_decode($uu->extra_info)){
                    $extra_info = json_decode($uu->extra_info);
                    foreach ($extra_info as $info) {
                        if($info->accion=='posteo_actualizado'){
                            $posteo = Posteo::where('id',$info->posteo_id)->select('evaluable','tipo_ev')->first();
                            if(isset($info->convalidar_evaluacion)){
                                //Cuando cambia el tema de calificada a no evaluable
                                $this->convalidar_visitas($posteo->evaluable,$info->posteo_id,$info->convalidar_evaluacion);
                            }
                            //Todos los demas casos
                            $this->actualizar_visitas($posteo->tipo_ev,$info->posteo_id);
                        }
                    }
                }
                // $this->update_resumenes_v2($usus,$uu->curso_id);
                $this->update_x_usuario_2($usus,$uu->curso_id,$uu->curso_id);
                DB::table('update_usuarios')->where('id', $uu->id)->update(['estado'=>2,'total'=>count($usus)]);
                //TOMAR EN CUENTA LA RECURSIVIDAD
                $l_m = Carbon::parse($uu->created_at)->addMinutes(15);
                $uu2 = $sql->whereBetween('created_at',[$uu->created_at,$l_m])->first(); 
                if(isset($uu2)){
                    $this->update_resumen_general();
                }
            }else{
                //SI SE ACTUALIZA UNA CURRICULA, SE ACTUALIZA LOS CURSOS ASGINADOS DE TODOS LOS USUARIOS PERTENECIENTES AL MODULO DEL CURSO MODIFICADO.
                $uu =DB::table('update_usuarios')->where('tipo','update_resumen_general')->where('estado',0)->first();
                if($uu){
                    DB::table('update_usuarios')->where('id', $uu->id)->update(['estado'=>1]);
                    $usuarios_modulo = json_decode($uu->usuarios_id,true);
                    $this->update_x_usuario($usuarios_modulo);
                    DB::table('update_usuarios')->where('id', $uu->id)->update(['estado'=>2]);
                }else{
                    $uu =DB::table('update_usuarios')->where('tipo','update_resumenes_from_masivo')->where('estado',0)->first();
                    if($uu){
                        DB::table('update_usuarios')->where('id', $uu->id)->update(['estado'=>1]);
                        $usus = json_decode($uu->usuarios_id,true);
                        $this->update_x_usuario($usus);
                        DB::table('update_usuarios')->where('id', $uu->id)->update(['estado'=>2]);
                    }
                }
            }
        }
    }
    private function update_x_usuario_2($usus_id,$curso_id){
        $restAvance = new RestAvanceController();
        $helper = new HelperController();
        $max_intentos = 3;
        $usuario_resumenes = Resumen_general::select('usuario_id')->whereIn('usuario_id', $usus_id)->orderBy('id', 'DESC')->get();
        $bar = $this->output->createProgressBar(count($usuario_resumenes));
        $bar->start();
        foreach ($usuario_resumenes as $usu_res) {
            $cur_asignados =  $helper->help_cursos_x_matricula_con_cursos_libre($usu_res->usuario_id);
            Resumen_x_curso::where('usuario_id',$usu_res->usuario_id)->whereNotIn('curso_id',$cur_asignados)->update([
                'estado_rxc'=> 0
            ]);
            Resumen_x_curso::where('usuario_id',$usu_res->usuario_id)->whereIn('curso_id',$cur_asignados)->update([
                'estado_rxc'=> 1
            ]);
            $restAvance->actualizar_resumen_x_curso($usu_res->usuario_id, $curso_id, $max_intentos);
            $restAvance->actualizar_resumen_general($usu_res->usuario_id);
            $bar->advance();
        }
        $bar->finish();
    }
    private function update_x_usuario($usus_id){
        $restAvance = new RestAvanceController();
        $helper = new HelperController();
        $max_intentos = 3;
        $usuario_resumenes = Resumen_general::select('usuario_id')->whereIn('usuario_id', $usus_id)->orderBy('id', 'DESC')->get();
        $bar = $this->output->createProgressBar(count($usuario_resumenes));
        $bar->start();
        foreach ($usuario_resumenes as $usu_res) {
            $cur_asignados =  $helper->help_cursos_x_matricula_con_cursos_libre($usu_res->usuario_id);
            Resumen_x_curso::where('usuario_id',$usu_res->usuario_id)->whereNotIn('curso_id',$cur_asignados)->update([
                'estado_rxc'=> 0
            ]);
            Resumen_x_curso::where('usuario_id',$usu_res->usuario_id)->whereIn('curso_id',$cur_asignados)->update([
                'estado_rxc'=> 1
            ]);
            foreach ($cur_asignados as $key => $curso_id) {
                $restAvance->actualizar_resumen_x_curso($usu_res->usuario_id, $curso_id, $max_intentos);
            }
            $restAvance->actualizar_resumen_general($usu_res->usuario_id);
            $bar->advance();
        }
        $bar->finish();
    }
    public function get_usus($curso_id){
        $curriculas = Curricula::select('carrera_id','ciclo_id')->where('curso_id',$curso_id)->get();
        $carreras_id = $curriculas->pluck('carrera_id');
        $ciclos_id = $curriculas->pluck('ciclo_id'); 
        $matriculas = Matricula::select('usuario_id','presente')->whereIn('carrera_id',$carreras_id)->whereIn('ciclo_id',$ciclos_id)->get();
        $usuarios = $matriculas->pluck('usuario_id')->unique();
        return $usuarios;
    }
    private function update_resumenes_v2($usus_id,$curso_id){
        $posteos = Posteo::where('curso_id', $curso_id)->where('estado', 1)->select('id','tipo_ev','evaluable')->get();

        $p_aprob = $posteos->where('evaluable','si')->where('tipo_ev','calificada')->pluck('id');
        $p_abiertas = $posteos->where('evaluable','si')->where('tipo_ev','abierta')->pluck('id');
        $p_revisados = $posteos->where('evaluable','no')->pluck('id');
        
        $posteos_id = $posteos->pluck('id');
        $cant_asignados = count($posteos);
        //DATOS PRECARGADOS
        $res_notas = DB::table('pruebas')
                        // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                    ->join('posteos', 'posteos.id', 'pruebas.posteo_id')
                    ->where('posteos.estado', 1)
                    // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                    ->select(DB::raw('AVG(IFNULL(pruebas.nota, 0)) AS nota_avg'),'usuario_id')
                    ->groupBy('usuario_id')
                    ->whereIn('usuario_id', $usus_id)
                    ->get();
        $dts = Usuario::with(
            [
                'config'=>function($q){ 
                    $q->select('id','mod_evaluaciones');
                },
                'pruebas'=>function($q)use ($posteos_id){ 
                    $q->select('id','nota','curso_id','intentos','posteo_id','resultado','usuario_id')->whereIn('posteo_id',$posteos_id);
                },
                'ev_abiertas'=>function($q) use ($posteos_id){ 
                    $q->select('id','posteo_id','usuario_id')->whereIn('posteo_id',$posteos_id);
                },
                'visitas'=>function($q) use ($posteos_id){ 
                    $q->select('id','post_id','usuario_id','estado_tema')->whereIn('post_id',$posteos_id);
                },
                'encuestas_respuestas' =>function($q) use ($curso_id){ 
                    $q->select('id','curso_id','usuario_id')->where('curso_id',$curso_id);
                },
                'diplomas' =>function($q) use ($curso_id){ 
                    $q->select('id','curso_id','usuario_id')->where('curso_id',$curso_id);
                },
                'resumen_x_curso'=>function($q){ 
                    $q->join('cursos','cursos.id','resumen_x_curso.curso_id')->select('resumen_x_curso.id','resumen_x_curso.intentos','resumen_x_curso.curso_id','resumen_x_curso.usuario_id','resumen_x_curso.estado','resumen_x_curso.nota_prom','cursos.estado as curso_estado');
                },
                'resumen_general'=>function($q) use ($curso_id){ 
                    $q->select('id','usuario_id');
                }
           ]
        )
        ->select('id','config_id')->whereIn('id',$usus_id)->get();
        $i=1;
        $bar = $this->output->createProgressBar(count($usus_id));
        $bar->start();
        $i = 1;
        $helper = new HelperController();
        foreach ($usus_id as $usu_id) {
            $f_usu = $dts->where('id',$usu_id)->first();
            if(isset($f_usu)){
                $cursos_asignados = $helper->help_cursos_x_matricula($usu_id);
                $el_curso_esta_asignado = in_array($curso_id,$cursos_asignados);

                if($el_curso_esta_asignado ){
                    $res_c = $f_usu->resumen_x_curso->where('curso_id',$curso_id)->first();
                    $pru= $f_usu->pruebas;
                    $cant_aprob = $pru->where('resultado',1)->count(); 
                    $cant_abiertos = $f_usu->ev_abiertas->count(); 
                    $cant_revisados = $f_usu->visitas->where('estado_tema','revisado')->count(); 
                    $mod_eval = json_decode($f_usu->config->mod_evaluaciones, true);
                    $cant_desaprob = $pru->where('resultado',0)->where('max_intentos','>=',$mod_eval['nro_intentos'])->count();
                    // Cambiar estado de resumen_x_curso
                    $tot_completados = $cant_aprob + $cant_abiertos + $cant_revisados;
                    // Porcentaje avance por curso
                    $percent_curso = ($cant_asignados > 0) ? (($tot_completados / $cant_asignados) * 100) : 0;
                    $percent_curso = ($percent_curso > 100) ? 100 : $percent_curso; // Maximo porcentaje = 100
                    $nota_prom_curso = number_format((float)$pru->where('resultado',1)->avg('nota'), 2, '.', '');
                    $estado_curso = "desarrollo";
                    if ($tot_completados >= $cant_asignados) {
                        $hizo_encuesta = $f_usu->encuestas_respuestas;
                        if ($hizo_encuesta) {
                            $estado_curso = "aprobado";
                            // Genera diploma
                            $diploma_curso = $f_usu->diplomas;
                            if (!$diploma_curso) {
                                DB::table('diplomas')->insert(['usuario_id' => $usuario_id, 'curso_id' => $curso_id, 'fecha_emision' => date('Y-m-d')]);
                            }
                        } else {
                            $estado_curso = "enc_pend";
                        }
                    } else {
                        if ($cant_desaprob >= $cant_asignados) {
                            $estado_curso = "desaprobado";
                        }
                    }
                    $sum_visitas = $f_usu->visitas->sum('sumatoria');
                    if(isset($res_c)){
                        $res_c->update(array(
                            'asignados' => $cant_asignados, //DONE
                            'aprobados' => $cant_aprob, //DONE
                            'realizados' => $cant_abiertos, //DONE
                            'revisados' => $cant_revisados, //DONE
                            'desaprobados' =>$cant_desaprob,
                            'nota_prom' => $nota_prom_curso, //DONE x Ver
                            'estado' => $estado_curso, //DONE
                            'porcentaje' => $percent_curso, //DONE
                            'visitas' => $sum_visitas,
                            'estado_rxc' => 1
                        ));
                    }else{
                        if($tot_completados>0 || $cant_desaprob>0){
                            Resumen_x_curso::create(array(
                                'asignados' => $cant_asignados, //DONE
                                'aprobados' => $cant_aprob, //DONE
                                'realizados' => $cant_abiertos, //DONE
                                'revisados' => $cant_revisados, //DONE
                                'desaprobados' =>$cant_desaprob,
                                'nota_prom' => $nota_prom_curso, //DONE x Ver
                                'estado' => $estado_curso, //DONE
                                'porcentaje' => $percent_curso, //DONE
                                'visitas' => $sum_visitas
                            ));
                        }
                    }
                }else{
                    Resumen_x_curso::where('usuario_id',$usu_id)->where('curso_id',$curso_id)->update([
                        'estado_rxc'=>0
                    ]);
                }
                $res_g = $f_usu->resumen_general;
                if(isset($res_g)){
                    $tot_completed_gen = $f_usu->resumen_x_curso->where('estado_rxc',1)->where('curso_estado',1)->where('estado','aprobado')->count();
                    $intentos_x_curso = $f_usu->resumen_x_curso->where('estado_rxc',1)->where('curso_estado',1)->sum('intentos');
                    $intentos_gen = (isset($intentos_x_curso)) ? $intentos_x_curso : 0;
                    $general_asignados = count($cursos_asignados);
                    $percent_general = ($general_asignados > 0) ? (($tot_completed_gen / $general_asignados) * 100) : 0;
                    $percent_general = ($percent_general > 100) ? 100 : $percent_general; // maximo porcentaje = 100
                    $percent_general = number_format($percent_general,1);
                    $nota_avg = 0;
                    if(isset($res_notas->where('usuario_id',$usu_id)->first()->nota_avg)){
                        $nota_avg = $res_notas->where('usuario_id',$usu_id)->first()->nota_avg;
                    }
                    $nota_prom_gen = number_format((float)$nota_avg, 2, '.', '');
                    $rank_user = $this->calcular_puntos($tot_completed_gen, $nota_prom_gen, $intentos_gen);
                    $res_g->update([
                        'tot_completados' => $tot_completed_gen,
                        'nota_prom' => $nota_prom_gen,
                        'cur_asignados' => $general_asignados,
                        'intentos' => $intentos_gen,
                        'rank' => $rank_user,
                        'porcentaje' => $percent_general,
                    ]);
                }
            }
            $i++;
            $bar->advance();
        }
        $bar->finish();
    }
    public function calcular_puntos($tot_completed_gen, $nota_prom_gen, $intentos)
    {
        //Calcular puntajes 
        $puntos_cursos = $tot_completed_gen * 150; //CURSOS COMPLETADOS
        $puntos_promedio = $nota_prom_gen * 100;  //PROMEDIO
        $puntos_intentos = $intentos * 0.5; //intentos
        $total_puntos = $puntos_promedio + $puntos_cursos  - $puntos_intentos;
        // if($total_puntos<0) $total_puntos = 0; 
        if ($nota_prom_gen == 0) $total_puntos = 0;
        return intval($total_puntos);
    }
    // public function help_cursos_x_matricula($usuarios_modulo)
    // {
    //     $mats = Matricula::with(
    //             [
    //                 'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
    //                 'matricula_criterio.curricula_criterio'=>function($q){ $q->select('id','criterio_id','curricula_id');},
    //                 'matricula_criterio.curricula_criterio.curricula'=>function($q){$q->select('id','curso_id','ciclo_id');},
    //                 'matricula_criterio.curricula_criterio.curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1);},
    //             ])
    //         ->whereIn('usuario_id', $usuarios_modulo)
    //         ->where('estado', 1)
    //         ->select('id','ciclo_id','usuario_id')
    //         ->get();
    //     $usus_m = $mats->groupBy('usuario_id')->all();
    //     $i=1;
    //     foreach ($usus_m as $key => $usu_m) {
    //         $result = collect();
    //         $matriculas = $usu_m;
    //         foreach ($matriculas as $matricula) {
    //             if(isset($matricula->matricula_criterio[0]->curricula_criterio) && count($matricula->matricula_criterio[0]->curricula_criterio)>0){
    //                 $curriculas_criterios = $matricula->matricula_criterio[0]->curricula_criterio;
    //                 foreach ($curriculas_criterios as $curricula_criterio) {
    //                     if($curricula_criterio->curricula){
    //                         if (isset($curricula_criterio->curricula->curso) && ($curricula_criterio->curricula->ciclo_id == $matricula->ciclo_id)){
    //                             $result->push($curricula_criterio->curricula->curso->id);
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         $q_res = count($result->unique()->values()->all());
    //         if($q_res>0){
    //             $cur_asignados =  $this->help_cursos_x_matricula_v3($key);
    //             Resumen_x_curso::where('usuario_id',$key)->whereNotIn('curso_id',$cur_asignados)->update([
    //                 'estado_rxc'=> 0
    //             ]);
    //             Resumen_x_curso::where('usuario_id',$key)->whereIn('curso_id',$cur_asignados)->update([
    //                 'estado_rxc'=> 1
    //             ]);
    //             Resumen_general::where('usuario_id',$key)->update([
    //                 'cur_asignados' => $q_res,
    //             ]);
    //         }
    //         $i++;
    //     }
    //     return true;
    // }

    public function help_cursos_x_matricula_v3($usuario_id)
    {
        $result = collect();
        $matriculas = Matricula::with(
                [
                    'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
                    'matricula_criterio.curricula_criterio'=>function($q){ $q->select('id','criterio_id','curricula_id');},
                    'matricula_criterio.curricula_criterio.curricula'=>function($q){$q->select('id','curso_id','ciclo_id');},
                    'matricula_criterio.curricula_criterio.curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1);},
                ])
            ->where('usuario_id', $usuario_id)
            ->where('estado', 1)
            ->select('id','ciclo_id')
            ->get();
        foreach ($matriculas as $matricula) {
            if(isset($matricula->matricula_criterio[0]->curricula_criterio) && count($matricula->matricula_criterio[0]->curricula_criterio)>0){
                $curriculas_criterios = $matricula->matricula_criterio[0]->curricula_criterio;
                foreach ($curriculas_criterios as $curricula_criterio) {
                    if($curricula_criterio->curricula){
                        // $cur = Curso::where('id',$curricula_criterio->curricula->curso_id)->select('id','estado')->first();
                        if (($curricula_criterio->curricula->curso) && $curricula_criterio->curricula->ciclo_id == $matricula->ciclo_id ){
                            $result->push($curricula_criterio->curricula->curso->id);
                        }
                    }
                }
            }
        }
        return $result->unique()->values()->all();
    }
    private function actualizar_visitas($tipo_ev,$posteo_id){
        if($tipo_ev=='calificada' || $tipo_ev=='abierta'){
            $visitas = Visita::where('post_id',$posteo_id)
            ->where(function($q){
                $q->whereNotNull('estado_tema')->orWhereNotNull('tipo_tema');
            })->select('id','usuario_id','estado_tema')->get();
            switch ($tipo_ev) {
                case 'calificada':
                    Visita::where('post_id',$posteo_id)->update(['tipo_tema'=>'calificada']);
                    $pruebas = Prueba::where('posteo_id',$posteo_id)
                    ->where('historico',1)->whereIn('usuario_id',$visitas->pluck('usuario_id'))
                    ->select('usuario_id','resultado')->get();
                    $pruebas_aprobados = $pruebas->where('resultado',1);
                    $this->update_visita($posteo_id,$pruebas_aprobados->pluck('usuario_id'),'aprobado');
                    $pruebas_desaprobados = $pruebas->where('resultado',0);
                    $this->update_visita($posteo_id,$pruebas_desaprobados->pluck('usuario_id'),'desaprobado');
                break;
                case 'abierta':
                    Visita::where('post_id',$posteo_id)->update(['tipo_tema'=>'abierta']);
                    $evas = Ev_abierta::where('posteo_id',$posteo_id)
                    ->where('eva_abierta',0)->whereIn('usuario_id',$visitas->pluck('usuario_id'))->select('usuario_id')->get();
                    $this->update_visita($posteo_id,$evas->pluck('usuario_id'),'realizado');
                break;
            }
        }
    }
    private function convalidar_visitas($evaluable,$posteo_id,$convalidar){
        if($evaluable!='no'){return false;}
        Visita::where('post_id',$posteo_id)->update(['tipo_tema'=>'no-evaluable']);
        $visitas = Visita::where('post_id',$posteo_id)
        ->where(function($q){
            $q->whereNotNull('estado_tema')->orWhereNotNull('tipo_tema');
        })->select('id','usuario_id','estado_tema')->get();
        $pruebas = Prueba::where('posteo_id',$posteo_id)
        ->where('historico',0)->whereIn('usuario_id',$visitas->pluck('usuario_id'))
        ->select('usuario_id','resultado')->get();
        if($convalidar){
            $pruebas_aprobados = $pruebas->where('resultado',1);
            $this->update_visita($posteo_id,$pruebas_aprobados->pluck('usuario_id'),'revisado');
            $pruebas_desaprobados = $pruebas->where('resultado',0);
            $this->update_visita($posteo_id,$pruebas_desaprobados->pluck('usuario_id'),null);
        }else{
            $this->update_visita($posteo_id,$pruebas->pluck('usuario_id'),null);
        }
    }
    private function update_visita($posteo_id,$usuarios_id,$estado_tema){
        Visita::where('post_id',$posteo_id)->whereIn('usuario_id',$usuarios_id)->update([
            'estado_tema'=>$estado_tema
        ]);
    }
}