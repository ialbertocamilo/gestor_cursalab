<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Botica;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Usuario;
use App\Models\Reinicio;
use App\Models\Matricula;
use App\Models\Incidencia;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Matricula_criterio;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class restablecer_visitas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restablecer:grupos';

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
        // $this->restablecer_visitas();
        // $this->restablecer_visitas_de_pruebas();
        // $this->restablecer_ranking();
        // $this->obtener_promedio();
        // $this->restablecer_grupos();
        $this->restablecer_dates();
    }
    public function restablecer_dates(){
        $curso_id_o = 337;
        $tema_id_o=1219;

        $curso_id_m = 828;
        $tema_id_m = 2879;

        $visitas = Visita::where('curso_id',$curso_id_m)->where('post_id',$tema_id_m)->whereDate('created_at','<','2021-11-19 23:59:59')->get();
        $i=0;
        $bar = $this->output->createProgressBar(count($visitas));
        $bar->start();
        foreach ($visitas as $visita) {
            $s = Visita::where('curso_id',$curso_id_o)
            ->where('post_id',$tema_id_o)
            ->where('usuario_id',$visita->usuario_id)
            ->where('estado_tema',$visita->estado_tema)
            ->where('tipo_tema',$visita->tipo_tema)
            ->first();
            if($s){
                $visita->created_at = $s->created_at;
                $visita->save();
            }
            $bar->advance();
        }
        $bar->finish();
    }
    public function obtener_promedio(){
        $usuarios = Usuario::select('dni','id');
        $datos = [];
        foreach($usuarios as $usuario) {
            $matriculas = Matricula::with(
                [
                    'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
                    'matricula_criterio.curricula_criterio'=>function($q){ $q->select('id','criterio_id','curricula_id');},
                    'matricula_criterio.curricula_criterio.curricula'=>function($q){$q->select('id','curso_id','ciclo_id');},
                    'matricula_criterio.curricula_criterio.curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1)->where('libre',0);},
                ])
            ->where('usuario_id', $usuario->id)
            ->where('estado', 1)
            ->select('id','ciclo_id')
            ->get();
            $result = collect();
            foreach ($matriculas as $matricula) {
                if(isset($matricula->matricula_criterio[0]->curricula_criterio) && count($matricula->matricula_criterio[0]->curricula_criterio)>0){
                    $curriculas_criterios = $matricula->matricula_criterio[0]->curricula_criterio;
                    foreach ($curriculas_criterios as $curricula_criterio) {
                        if($curricula_criterio->curricula){
                            //CURSOS QUE NO ESTEEN EN CICLO 0
                            $ciclo_cero = Ciclo::where('id',$matricula->ciclo_id)->where('nombre','<>','Ciclo 0')->select('id')->first();
                            if (isset($ciclo_cero) && ($curricula_criterio->curricula->curso) && $curricula_criterio->curricula->ciclo_id == $matricula->ciclo_id ){
                                $result->push($curricula_criterio->curricula->curso->id);
                            }
                        }
                    }
                }
            }
            $cursos_id =  $result->unique()->values()->all();
            $promedio = Prueba::where('usuario_id',$usuario->id)->where('created_at','<','2021-06-04 00:00:00')->whereIn('curso_id',$cursos_id)->select('nota')->avg('nota');
            $datos[]=[
                'usuario_id' => $usuario->id,
                'nota' => $promedio
            ];
        }
    }
    public function restablecer_grupos(){
        $usuarios = Usuario::select('usuarios.id','usuarios.botica_id','usuarios.grupo','b.criterio_id')->join('boticas as b','b.id','=','usuarios.botica_id')->where('b.criterio_id','<>','usuarios.grupo')->get();
        $bar = $this->output->createProgressBar(count($usuarios));
        $bar->start();
        $helper = new HelperController();
        $restAvance = new RestAvanceController();
        $max_intentos = 3;
        foreach ($usuarios as $user) {
            $botica = Botica::with(['criterio'=>function($q){
                $q->select('id','valor');
            }])->select('id','criterio_id')->where('id',$user->botica_id)->first();
            $matricula = Matricula::where('usuario_id',$user->id)->select('id')->pluck('id');
            //Actualizar matricula_criterio y usuario
            DB::table('matricula_criterio')->whereIn('matricula_id',$matricula)->update([
                'criterio_id'=>$botica->criterio->id
            ]);
            DB::table('usuarios')->where('id',$user->id)->update([
                'grupo'=> $botica->criterio->id,
                'grupo_nombre' =>$botica->criterio->valor,
            ]);
            $ids_cursos_asignados = $helper->help_cursos_x_matricula($user->id);
            Resumen_x_curso::where('usuario_id',$user->id)->whereIn('curso_id',$ids_cursos_asignados)->update([
                'estado_rxc'=>1,
            ]);
            Resumen_x_curso::where('usuario_id',$user->id)->whereNotIn('curso_id',$ids_cursos_asignados)->update([
                'estado_rxc'=>0,
            ]);
            // $resumenes = Resumen_x_curso::where('usuario_id',$user->id)->where('estado_rxc',1)->select('curso_id')->get();
            // foreach ($resumenes as $res) {
            //     $restAvance->actualizar_resumen_x_curso($user->id, $res->curso_id, $max_intentos);
            // }
            $restAvance->actualizar_resumen_general($user->id);
            $bar->advance();
        }
        $bar->finish();
    }
    public function restablecer_ranking(){
        // $res =Resumen_general::select('id','usuario_id')->get();
        $res =Resumen_x_curso::select('id','usuario_id','curso_id')->get();
        $bar = $this->output->createProgressBar(count($res));
        $bar->start();
        foreach ($res as $re) {
            $prueba = Prueba::where('usuario_id',$re->usuario_id)->where('curso_id',$re->curso_id)
            ->orderBy('last_ev','desc')->select('last_ev')->first();
            if($prueba){
                DB::table('resumen_x_curso')->where('id',$re->id)->update([
                    'last_ev' =>$prueba->last_ev,
                ]);
            }

            $bar->advance();
        }
        $bar->finish();
    }
    //ESTA FUNCION SIRVE PARA ARREGLAR LA INCIDENCIA DE PRUEBAS QUE NO TIENEN VISITAS
    public function restablecer_visitas_de_pruebas(){
        $incs = Incidencia::where('mensaje','Pruebas que no tienen visitas')->where('estado',2)->first();
        $posteos = Posteo::select('id','curso_id')->get();
        $afectados = json_decode($incs->afectados,true);
        $helper = $helper = new HelperController();
        $restAvance = new RestAvanceController();
        $bar = $this->output->createProgressBar(count($afectados));
        $bar->start();
        $max_intentos = 3; // LA CANTIDAD MAXIMA DE INTENTOS EN UC ES 3, VERIFICAR SI SIGUE SIENDO ASI AL CORRER EL COMANDO
        $restAvance = new RestAvanceController();
        foreach ($afectados as $afectado) {
            $ids_cursos_asignados = $helper->help_cursos_x_matricula($afectado['usuario_id']);
            $curso_id = $posteos->where('id',$afectado['posteo_id'])->first()->curso_id;
            if(in_array($curso_id,$ids_cursos_asignados)){
                $pr = Prueba::where('posteo_id',$afectado['posteo_id'])->where('usuario_id',$afectado['usuario_id'])->select('posteo_id','intentos','fuente')->first();
                if($pr){
                    $visita = Visita::where('post_id',$afectado['posteo_id'])->where('usuario_id',$afectado['usuario_id'])->select('id')->first();
                    if(is_null($visita)){
                        $sumatoria = $pr->intentos;
                        if($pr->fuente=='reset'){
                            $sumatoria = $sumatoria + intval($this->get_sumatorio_desde_reset($afectado['usuario_id'],$curso_id,$afectado['posteo_id']));
                        }
                        Visita::insert([
                            'curso_id' => $curso_id,
                            'post_id'=>$afectado['posteo_id'],
                            'usuario_id'=>$afectado['usuario_id'],
                            'sumatoria'=>$sumatoria,
                            'descargas'=>0,
                        ]);
                    }
                }

            }
            $bar->advance();
        }
        $bar->finish();
    }
    public function restablecer_visitas(){
        $res = Resumen_x_curso::whereNull('visitas')->get();
        $helper = new HelperController();
        $bar = $this->output->createProgressBar(count($res));
        $bar->start();
        foreach ($res as $re) {
            $ids_cursos_asignados = $helper->help_cursos_x_matricula($re->usuario_id);
            $f_in_array = in_array($re->curso_id,$ids_cursos_asignados);
            if($f_in_array){
                $curso = Curso::where('id',$re->curso_id)->where('estado',1)->select('id')->first();
                $temas = Posteo::where('curso_id',$re->curso_id)->where('estado',1)->where('tipo_ev','calificada')->select('id')->get();
                if(($curso) && count($temas)>0){
                    foreach ($temas as $tema) {
                        $pr = Prueba::where('posteo_id',$tema->id)->where('usuario_id',$re->usuario_id)->select('posteo_id','intentos','fuente')->first();
                        if($pr){
                            $visita = Visita::where('post_id',$pr->posteo_id)->where('usuario_id',$re->usuario_id)->select('id')->first();
                            if(is_null($visita)){
                                $sumatoria = $pr->intentos;
                                if($pr->fuente=='reset'){
                                    $sumatoria = $sumatoria + intval($this->get_sumatorio_desde_reset($re->usuario_id,$re->curso_id,$tema->id));
                                }
                                Visita::insert([
                                    'curso_id' => $re->curso_id,
                                    'post_id'=>$pr->posteo_id,
                                    'usuario_id'=>$re->usuario_id,
                                    'sumatoria'=>$sumatoria,
                                    'descargas'=>0,
                                ]);
                                $this->info('Se creo registro de visita, usuario_id: '.$re->usuario_id.' curso_id: '.$re->curso_id);
                                $visitas = Visita::select(DB::raw('SUM(sumatoria) as suma_visitas'))
                                ->join('posteos as p','p.id','visitas.post_id')
                                ->where('p.estado', 1)
                                ->where('visitas.usuario_id', $re->usuario_id)
                                ->where('visitas.curso_id', $re->curso_id)->first();
                                $suma_visitas = (isset($visitas)) ? $visitas->suma_visitas : 0;
                                $re->visitas = $suma_visitas;
                                $re->save();
                            }
                        }
                    }
                }
            }else{
                $re->delete();
            }
            $bar->advance();
        }
        $bar->finish();
    }

    public function get_sumatorio_desde_reset($usuario_id,$curso_id,$posteo_id){
        /*
            EN UC LA CANTIDAD MAXIMA DE INTENTOS ES DE 3, SI SE CORRERA EL COMANDO OTRA VEZ SE TIENE QUE VERIFICAR
            SI AUN SIGUE FUNCIONANDO DE LA MISMA MANERA.
        */
        $query = Reinicio::where('usuario_id',$usuario_id);
        $sumatoria = 0;
        $max_intentos = 3;

        $reinicio_x_tema = $query->where('posteo_id',$posteo_id)->where('tipo','por_tema')->first();
        if($reinicio_x_tema){
            $sumatoria = $sumatoria + ($max_intentos* $reinicio_x_tema->acumulado);
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
