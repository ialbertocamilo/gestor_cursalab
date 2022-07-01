<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Reinicio;
use App\Models\Curricula;
use App\Models\Resumen_x_curso;
use App\Models\Curricula_criterio;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiRest\HelperController;

class retablecer_visitas_en_rxc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prueba:slack';

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
        // REVISAR LA CANTIDAD DE INTENTOS SIGUE SIENDO 3 PARA TODOS LOS MODULOS ANTES DE CORRER EL COMANDO
        // $this->restablecer_visitas_rxc();
        // $this->update_recuperar_intentos();
        // $this->llenar_grupo_nombre();
        $mensaje = 'El usuario con DNI *_11111111_* ha enviado una incidencia \n Verificalo en 👇 \n https://test.gestor.universidadcorporativafp.com.pe/usuario_ayuda/index';
        // $this->send_message_to_slack($mensaje);
        $this->fix_curricula();
    }
    private function fix_curricula(){
        $cursos = Curso::where('config_id',6)->select('id')->get();
        $criterios_activos = Usuario::select('grupo')->where('config_id',6)->where('estado',1)
        ->where('rol','default')->groupBy('grupo')->get();
        $curriculas = Curricula::whereIn('curso_id',$cursos->pluck('id'))->get();
        $bar = $this->output->createProgressBar(count($curriculas));
        $bar->start();
        $nuevo = [];
        foreach ($curriculas as $curr) {
            $criterio = Curricula_criterio::where('curricula_id',$curr->id)->select('criterio_id')->pluck('criterio_id');
            $cr_update = $criterios_activos->whereNotIn('grupo',$criterio);
            foreach ($cr_update as $up) {
                $nuevo[]=[
                    'criterio_id'=>$up->grupo,
                    'curricula_id'=>$curr->id,
                ];
            }
            $bar->advance();
        }
        Curricula_criterio::insert($nuevo);
        $bar->finish();
    }
    private function send_message_to_slack($mensaje){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('LOG_SLACK_WEBHOOK_URL'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"text\":\"$mensaje'\"}");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text'=>$mensaje]));
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }
    // REVISAR LA CANTIDAD DE INTENTOS SIGUE SIENDO 3 PARA TODOS LOS MODULOS ANTES DE CORRER EL COMANDO
    public function restablecer_visitas_rxc(){
        $res = Resumen_x_curso::select('id','curso_id','usuario_id','visitas')->get();
        $cursos = Curso::with(['temas'=>function($q){
            $q->select('id','curso_id','estado')->where('estado',1)->where('tipo_ev','calificada');
        }])->where('estado',1)->select('id')->get();
        $bar = $this->output->createProgressBar(count($res));
        $bar->start();
        $max_intentos= 3;
        foreach ($res as $re) {
            // $visitas = Visita::select(DB::raw('SUM(sumatoria) as suma_visitas'))
            //     ->join('posteos as p','p.id','visitas.post_id')
            //     ->where('p.estado', 1)
            //     ->where('visitas.usuario_id', $re->usuario_id)
            //     ->where('visitas.curso_id', $re->curso_id)->first();
            // $suma_visitas = (isset($visitas)) ? $visitas->suma_visitas : 0;
            $curso = $cursos->where('id',$re->curso_id)->first();
            $cant_desaprob = 0;
            if(isset($curso->temas)){
                $posteos_calificados_id =$curso->temas->pluck('id')->toArray();
                if(count($posteos_calificados_id)>0){
                    $cant_desaprob = DB::table('pruebas AS u')
                        ->whereIn('posteo_id',$posteos_calificados_id)
                        ->where('u.resultado', 0)
                        ->where('u.intentos', '>=', $max_intentos)
                        ->where('u.usuario_id', $re->usuario_id)
                        ->where('u.curso_id', $re->curso_id)
                        ->count();
                    // if($cant_desaprob>0){
                    //     $this->info('debe cambiar usuario_id: '.$re->usuario_id.' cusrso_id: '.$re->curso_id);
                    // }
                }
            }
            Resumen_x_curso::where('id',$re->id)->update([
                'desaprobados' =>$cant_desaprob
            ]);
            // $re->desaprobados = $cant_desaprob;
            // $re->visitas = $suma_visitas;
            // $re->save();
            $bar->advance();
        }
        $bar->finish();
    }
    // public function update_recuperar_intentos(){
    //     $helper = new HelperController();
    //     $reinicios = Reinicio::where('tipo','por_tema')->get();
    //     $array_update = collect();
    //     $bar = $this->output->createProgressBar(count($reinicios));
    //     $bar->start();
    //     foreach ($reinicios as $reinicio) {
    //         $posteo = Posteo::where('id',$reinicio->posteo_id)->first();
    //         if($posteo){
    //             $cur = Curso::where('id',$posteo->curso_id)->first();
    //             if($cur){
    //                 $config = Abconfig::select('mod_evaluaciones')->where('id', $cur->config_id)->first();
    //                 $mod_eval = json_decode($config->mod_evaluaciones, true);
    //                 $array_update->push([
    //                     'usuario_id'=> $reinicio->usuario_id,
    //                     'curso_id' => $reinicio->curso_id,
    //                     'acumulado' => intval($mod_eval['nro_intentos'])*$reinicio->acumulado,
    //                 ]);
    //             }
    //             $bar->advance();
    //         }
    //     }
    //     $bar->finish();
    //     $res_2 = Reinicio::where('tipo','por_curso')->get();
    //     $bar = $this->output->createProgressBar(count($res_2));
    //     $bar->start();
    //     foreach ($res_2 as $re) {
    //         $cur = Curso::where('id',$re->curso_id)->first();
    //         if($cur){
    //             $prue = Prueba::where('usuario_id',$re->usuario_id)->where('curso_id',$re->curso_id)->count();
    //             $config = Abconfig::select('mod_evaluaciones')->where('id', $cur->config_id)->first();
    //             $mod_eval = json_decode($config->mod_evaluaciones, true);
    //             $array_update->push([
    //                 'usuario_id'=> $re->usuario_id,
    //                 'curso_id' => $re->curso_id,
    //                 'acumulado' => intval($mod_eval['nro_intentos'])*$re->acumulado * $prue,
    //             ]);
    //         }
    //         $bar->advance();
    //     }
    //     $bar->finish();
    //     $res_3 = Reinicio::where('tipo','total')->get();
    //     $bar = $this->output->createProgressBar(count($res_3));
    //     $bar->start();
    //     foreach ($res_3 as $re) {
    //         $cur_asignados = $helper->help_cursos_x_matricula($re->usuario_id);
    //         if(count($cur_asignados)>0){
    //             foreach ($cur_asignados as $cur_id) {
    //                 $cur = Curso::where('id',$cur_id)->first();
    //                 if($cur){
    //                     $prue = Prueba::where('usuario_id',$re->usuario_id)->where('curso_id',$cur_id)->count();
    //                     $config = Abconfig::select('mod_evaluaciones')->where('id', $cur->config_id)->first();
    //                     $mod_eval = json_decode($config->mod_evaluaciones, true);
    //                     $array_update->push([
    //                         'usuario_id'=> $re->usuario_id,
    //                         'curso_id' => $cur_id,
    //                         'acumulado' => intval($mod_eval['nro_intentos'])*$re->acumulado * $prue,
    //                     ]);
    //                 }
    //             }
    //         }
    //         $bar->advance();
    //     }
    //     $bar->finish();

    //     $usus_id = $array_update->pluck('usuario_id')->toArray();
    //     $curs_id = $array_update->pluck('curso_id')->toArray();
    //     $res = Resumen_x_curso::whereIn('usuario_id',$usus_id)->whereIn('curso_id',$curs_id)->get();
    //     // $res = Resumen_x_curso::where('usuario_id',7432)->where('curso_id',70)->get();
    //     $bar = $this->output->createProgressBar(count($res));
    //     $bar->start();
    //     foreach ($res as $re) {
    //         $intentos = $array_update->where('usuario_id',$re->usuario_id)->where('curso_id',$re->curso_id)->sum('acumulado');
    //         if($intentos){
    //             $lqt = Prueba::select(DB::raw('SUM(intentos) as intentos'))->where('usuario_id', $re->usuario_id)->where('curso_id', $re->curso_id)->first();
    //             $re->intentos = $lqt->intentos + $intentos ;
    //             $re->save();
    //         }
    //         $bar->advance();
    //     }
    //     $bar->finish();
    // }
    // public function llenar_grupo_nombre(){
    //     $this->info('-- Inicio Proceso--');
    //     $grupos = Criterio::select('id','valor')->get();
    //     $count = 1;
    //     $total = count($grupos);
    //     foreach ($grupos as $value) {
    //         Usuario::where('grupo',$value->id)->update(['grupo_nombre'=>$value->valor]);
    //         $this->info($count.'/'.$total);
    //         $count++;
    //     }
    //     $this->info('-- Proceso Final--');
    // }
}
