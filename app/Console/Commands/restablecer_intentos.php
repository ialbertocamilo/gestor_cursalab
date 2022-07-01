<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Reinicio;
use App\Models\Resumen_x_curso;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiRest\RestAvanceController;

class restablecer_intentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restablecer:intentos';

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
        $this->restablecer_intentos();
    }
     public function restablecer_intentos(){
        // $res = Resumen_x_curso::where('aprobados','>',0)->where(function ($query) {
        //     $query->where('intentos',0)
        //           ->orWhereNull('intentos');
        // })->get();
        // $res = Resumen_x_curso::where('intentos',0)->where('nota_prom','>',0)->get();
        // $res = Resumen_x_curso::where('intentos','<',DB::raw('aprobados'))->get();
        $restAvance = new RestAvanceController();
        $bar = $this->output->createProgressBar(count($res));
        $bar->start();
        foreach ($res as $re) {
            $curso = Curso::where('id',$re->curso_id)->where('estado',1)->select('id')->first();
            $temas = Posteo::where('curso_id',$re->curso_id)->where('estado',1)->where('tipo_ev','calificada')->select('id')->get();
            $pr = Prueba::where('curso_id',$re->curso_id)->whereIn('posteo_id',$temas->pluck('id'))->where('usuario_id',$re->usuario_id)->select('posteo_id','intentos','fuente')->get();   
            $intentos = $pr->sum->intentos;
            $intentos_fuente_reset = $this->get_sumatorio_desde_reset($re->usuario_id,$re->curso_id,$pr->pluck('posteo_id'));
            $re->intentos = $intentos + $intentos_fuente_reset;
            $re->save();
            $bar->advance();
            $restAvance->actualizar_resumen_x_curso($re->usuario_id, $re->curso_id,3);
        }
        $usus = $res->pluck('usuario_id');
        foreach ($usus as $usu) {
            $restAvance->actualizar_resumen_general($usu);
        }
        $bar->finish();
    }
    public function get_sumatorio_desde_reset($usuario_id,$curso_id,$posteos_id){
        /*
            EN UC LA CANTIDAD MAXIMA DE INTENTOS ES DE 3, SI SE CORRERA EL COMANDO OTRA VEZ SE TIENE QUE VERIFICAR
            SI AUN SIGUE FUNCIONANDO DE LA MISMA MANERA.
        */
        $query = Reinicio::where('usuario_id',$usuario_id);
        $sumatoria = 0;
        $max_intentos = 3;
        
        $reinicio_x_tema = $query->whereIn('posteo_id',$posteos_id)->where('tipo','por_tema')->get();
        if($reinicio_x_tema){
            $sumatoria = $sumatoria + ($max_intentos* $reinicio_x_tema->sum->acumulado * count($reinicio_x_tema)); 
        }

        $reinicio_x_curso = $query->where('curso_id',$curso_id)->where('tipo','por_curso')->first();
        if($reinicio_x_curso){
            $sumatoria = $sumatoria + ($max_intentos* $reinicio_x_curso->acumulado); 
        }

        $reinicio_total =$query->where('tipo','total')->first();
        if($reinicio_total){
            $sumatoria = $sumatoria + ($max_intentos* $reinicio_total->acumulado); 
        }
        return $sumatoria;
    }
}
