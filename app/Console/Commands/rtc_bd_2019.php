<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;
use App\Models\Abconfig;
use App\Models\Carrera;
use App\Models\Criterio;
use App\Models\Diploma;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Reinicio;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Usuario;
use App\Models\Visita;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class rtc_bd_2019 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:rtc_bd_2019';

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
        $this->rtc_bd_2019();
    }

    private function rtc_bd_2019(){
        $users = DB::connection('mysql3')->table('usuarios')->where('dni','42991214')->get();
        foreach ($users as $user) {
            //BUSCAR SU GRUPO PARA LA BD FARMA_UNIVERSIDAD
            $grupo = Criterio::where('valor',$user->grupo)->first(['id']);
            //BUSCAR SU CARRERA
                $perf = DB::connection('mysql3')->table('perfiles')
                ->where('id',$user->perfil_id)
                ->first(['nombre','id']);
            $carrera = Carrera::where('nombre',$perf->nombre)->where('config_id',$user->config_id)->first(['id']);
            //CREAR USUARIO O BUSCAR USUARIO
            $user_b = Usuario::where('dni',42991214)->first();
            //MATRICULAR EN LA NUEVA BD
            // $ciclos = Ciclo::select('id', 'secuencia')
            // ->where([
            // ['carrera_id', $carrera->id],
            // ['estado',1],
            // ])->get();
            // $b_ciclo = Ciclo::select('id','secuencia')->where('carrera_id',$carrera->id)->where('nombre','Ciclo 1')->first();
            // foreach ($ciclos as $ci) {
            //     if($ci->secuencia != 0 || $user['secuencia'] == 0 ){
            //         $mat = new Matricula();
            //         $mat->usuario_id = $user['usuario_id'];
            //         $mat->carrera_id= $carrera->id;
            //         $mat->ciclo_id=  $ci->id;
            //         $mat->secuencia_ciclo = $ci->secuencia;
            //         if($ci->id == $b_ciclo->id){
            //             $mat->presente=1;
            //         }else{
            //             $mat->presente=0;
            //         }
            //         if($ci->secuencia <= $b_ciclo->secuencia){
            //             $mat->estado =1;
            //         }else{
            //             $mat->estado =0;
            //         }
            //         $mat->save();
            //         //Crear Matricula Criterio
            //         Matricula_criterio::insert([
            //             'matricula_id' => $mat->id,
            //             'criterio_id' => $user['grupo'],
            //         ]);
            //     }
            // }
            //CURSOS
                $post_old = DB::connection('mysql3')
                ->table('posteo_perfil as pp')
                ->join('posteos as pos','pos.id','pp.posteo_id')
                ->select('pos.nombre','pos.id as posteo_id','pos.curso_id','pos.evaluable')
                ->where('pp.perfile_id',$perf->id)
                ->get();
            $helper = new HelperController();
            $curso_ids = $helper->help_cursos_x_matricula($user_b->id);
            $temas_corres = Posteo::whereIn('curso_id', $curso_ids)->get(['nombre','curso_id','id','categoria_id'])->toArray();
            $plk_temas = collect($temas_corres)->pluck('nombre')->toArray();
            // $pr = [] ;
            //MIGRAR DE LOS POSTEOS CON NOMBRE IGUAL
            foreach($post_old as $post) {
                if(in_array($post->nombre,$plk_temas)){
                    //MIGRAR PRUEBAS
                    $prueba = DB::connection('mysql3')
                        ->table('pruebas')
                        ->where('posteo_id',$post->posteo_id)
                        ->where('usuario_id',$user->id)
                        ->first();
                    if($prueba){
                        $idx = array_search($post->nombre,$plk_temas);
                        $post_new = $temas_corres[$idx];
                        Prueba::updateOrInsert(
                            ['posteo_id'=>$post_new['id'],'usuario_id'=>$user_b->id],
                            [
                            'categoria_id'=> $post_new['categoria_id'],
                            'curso_id' => $post_new['curso_id'],
                            'posteo_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            'intentos' => $prueba->intentos ,
                            'rptas_ok' => $prueba->rptas_ok ,
                            'rptas_fail'=> $prueba->rptas_fail ,
                            'nota' => $prueba->nota ,
                            'resultado' => $prueba->resultado ,
                        ]);
                        //MIGRAR ENCUESTA_RESPUESTA
                        // $enc_rpts = DB::connection('mysql3')->table('encuestas_respuestas')->where('usuario_id', $user->id)->where('curso_id',$post->curso_id)->get();
                        // foreach ($enc_rpts as $enc_rpta) {
                            // $enc = DB::connection('mysql3')->table('encuestas')->where('id',$enc_rpta->encuesta_id)->first(['titulo']);
                            // $enc_new = Encuesta::where('titulo',$enc->titulo)->first(['id']);
                            // //PREGUNTA
                            // $preg = DB::connection('mysql3')->table('encuestas_preguntas')->where('id',$enc_rpta->pregunta_id)->first(['titulo']);
                            // if($enc_new){
                            //     $preg_new = Encuesta::where('encuesta_id',$enc_new->id)->where('titulo',$preg->titulo)->first(['id']);
                            //     if($preg_new){
                            //         dd($preg_new);
                            //         Encuestas_respuesta::updateOrInsert([
                            //             'post_id' => $post_new['id'],
                            //             'usuario_id' => $user_b->id,
                            //             ],[
                            //             'encuesta_id' => $enc_new->id,
                            //             'curso_id' => $post->curso_id,
                            //             // 'post_id' =>
                            //             'pregunta_id' => $reg_new->id,
                            //             'usuario_id' => $user_b->id,
                            //             'respuestas'=> $enc->respuestas,
                            //             'tipo_pregunta'=> $enc->tipo_pregunta,
                            //         ]);
                            //     }
                            // }
                        // }
                    }
                    //MIGRAR VISITAS (de donde sacar tipo_tema, estado_tema)
                    $visita = DB::connection('mysql3')
                    ->table('visitas')
                    ->where('post_id',$post->posteo_id)
                    ->where('usuario_id',$user->id)
                    ->first();
                    if($visita){
                        $idx = array_search($post->nombre,$plk_temas);
                        $post_new = $temas_corres[$idx];
                        $evaluable = ($post->evaluable=='si') ? '' : 'no-evaluable' ;
                        Visita::updateOrInsert([
                            'post_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            ],
                            [
                            'post_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            'sumatoria' => $visita->sumatoria,
                            'descargas' => $visita->descargas,
                            'tipo_tema' => $evaluable,
                        ]);
                    }
                    //MIGRAR DIPLOMAS
                    $dip = DB::connection('mysql3')
                    ->table('diplomas')
                    ->where('posteo_id',$post->posteo_id)
                    ->where('usuario_id',$user->id)
                    ->first();
                    if($dip){
                        $idx = array_search($post->nombre,$plk_temas);
                        $post_new = $temas_corres[$idx];
                        Diploma::updateOrInsert([
                            'posteo_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            ],
                            [
                            'posteo_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            'curso_id' => $post_new['curso_id'],
                            'fecha_emision' => $dip->fecha_emision,
                        ]);
                    }
                    //MIGRAR REINICIOS
                    $rei = DB::connection('mysql3')
                    ->table('reinicios')
                    ->where('posteo_id',$post->posteo_id)
                    ->where('usuario_id',$user->id)
                    ->first();
                    if($rei){
                        $idx = array_search($post->nombre,$plk_temas);
                        $post_new = $temas_corres[$idx];
                        Reinicio::updateOrInsert([
                            'posteo_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            ],
                            [
                            'posteo_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            'curso_id' => $post_new['curso_id'],
                            'posteo_id' => $post_new['id'],
                            // 'admin_id' =>,
                            'tipo' => $rei->tipo,
                            'acumulado' => $rei->acumulado
                        ]);
                    }
                }
            }
            //LIMPIAR TABLAS RESUMENES
            Resumen_x_curso::where('usuario_id',$user_b->id)->delete();
            Resumen_general::where('usuario_id',$user_b->id)->delete();
            //ACTUALIZAR TABLAS RESUMENES
            $rest_avance = new RestAvanceController();
            $ab_config = Abconfig::where('id',$user_b->config_id)->first(['mod_evaluaciones']);
            $mod_eval = json_decode($ab_config->mod_evaluaciones, true);
            foreach ($curso_ids as $cur_id) {
                // ACTUALIZAR RESUMENES
                $rest_avance->actualizar_resumen_x_curso($user_b->id,$cur_id, $mod_eval['nro_intentos']);
            }
            $rest_avance->actualizar_resumen_general($user_b->id);
        }
    }
}
