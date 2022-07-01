<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Abconfig;
use App\Models\Reinicio;
use App\Models\Categoria;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class reinicios_programado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reinicios:programados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Se reinicia a los usuarios configurados por el cronometro a nivel de modulo,curso,categoria';

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
       $this->reinicios_programado_v2();
    }
    private $ids_a_reiniciar = [];
    private function reinicios_programado_v2(){
        // $date_now = Date::now();
        $modulos = Abconfig::select('id','mod_evaluaciones','reinicios_programado')->get();
        $categorias = Categoria::select('id','config_id')->get();
        $categorias_con_reinicios = Categoria::whereJsonContains('reinicios_programado->activado',true)->select('id','config_id','reinicios_programado')->get();
        $cursos_con_reinicios = Curso::whereJsonContains('reinicios_programado->activado',true)->select('id','config_id','reinicios_programado')->get();
        if(count($modulos)>0){
            foreach ($modulos as $modulo) {
                $mod_eval = json_decode($modulo->mod_evaluaciones, true);
                //Desaprobados del módulo
                $desaprobados = Prueba::whereIn('categoria_id',$categorias->where('config_id',$modulo->id)->pluck('id'))
                    ->where('intentos',$mod_eval['nro_intentos'])->where('resultado',0)
                    ->select('id','categoria_id','curso_id','usuario_id','posteo_id','last_ev')->get();
                $r_cursos_modulo = $cursos_con_reinicios->where('config_id',$modulo->id); //Cursos con reinicio del modulo
                $r_categorias_modulo = $categorias_con_reinicios->where('config_id',$modulo->id); //categorias con reinicio del modulo
                //Reinicio por curso
                $reinicios_x_curso = $desaprobados->whereIn('curso_id',$r_cursos_modulo->pluck('id'))->groupBy('curso_id');
                foreach ($reinicios_x_curso as $curso_id => $r_curso) {
                    $curso =$r_cursos_modulo->where('id',$curso_id)->first();
                    $programado = json_decode($curso->reinicios_programado,true);
                    $this->reiniciar_prueba($programado,$r_curso);
                }
                //Reinicio por categoria
                $reinicios_x_categoria = $desaprobados->whereNotIn('curso_id',$r_cursos_modulo->pluck('id'))
                                        ->whereIn('categoria_id',$r_categorias_modulo->pluck('id'))
                                        ->groupBy('categoria_id');
                foreach ($reinicios_x_categoria as $categoria_id => $r_categoria) {
                    $categoria = $r_categorias_modulo->where('id',$categoria_id)->first();
                    // dd($categoria);
                    $programado = json_decode($categoria->reinicios_programado,true);
                    $this->reiniciar_prueba($programado,$r_categoria);
                }
                //Reinicio por modulo
                if(($modulo->reinicios_programado) && json_decode($modulo->reinicios_programado)){
                    $reinicios_x_modulo = $desaprobados->whereNotIn('curso_id',$r_cursos_modulo->pluck('id'))
                                            ->whereNotIn('categoria_id',$r_categorias_modulo->pluck('id'));
                    $programado = json_decode($modulo->reinicios_programado,true);
                    if($programado['activado']){
                        $this->reiniciar_prueba($programado,$reinicios_x_modulo);
                    }
                }

            }
        }
        Prueba::whereIn('id',$this->ids_a_reiniciar)->update([
            'intentos' => 0,
            'fuente' => 'reset'
        ]);
    }
    private function reiniciar_prueba($programado,$pruebas){
        $tiempo_en_minutos = $programado['tiempo_en_minutos'];
        $final = Carbon::now()->subMinutes($tiempo_en_minutos);
        $final->second = 59;
        $inicio = Carbon::now()->subMinutes($tiempo_en_minutos);
        $inicio->second = 00;
        // $pr_a_reiniciar = $pruebas->whereBetween('last_ev',[$inicio,$final]);
        $pr_a_reiniciar = $pruebas->where('last_ev','<=',$final);
        // $pr_a_reiniciar = Prueba::where('id',$pruebas->pluck('id'))->select('id','categoria_id','curso_id','usuario_id','posteo_id')
        // ->whereBetween('last_ev',[$inicio,$final])->get();
        // ->whereDate('last_ev','>=',$inicio)->whereDate('last_ev','<=',$final)->get();
        //PONER ESTADO EN DESARROLLO
        if(count($pr_a_reiniciar)>0){
            foreach ($pr_a_reiniciar as $pr) {
                $reinicio = new Reinicio();
                $reinicio->save_reset_user($pr,0,'por_tema');
                $this->ids_a_reiniciar[] = $pr->id;
            }
        }
    }
}
