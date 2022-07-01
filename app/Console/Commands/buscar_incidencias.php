<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Usuario;
use App\Models\Reinicio;
use App\Models\Categoria;
use App\Models\Incidencia;
use App\Models\UsuarioCurso;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiRest\HelperController;

class buscar_incidencias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buscar:incidencias {tipo}';

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
        $this->buscar_incidencias();
    }
    public function buscar_incidencias(){
        $ejecutando = DB::table('incidencias')->where('tipo','ejecutando')->where('total','>',0)->first();
        if(isset($ejecutando)){
            \Log::channel('soporte_log')->info("No entro por cron");
            return;
        }
        $commands = Incidencia::select('comando')->orderBy('comando','desc')->first();
        $id_comando = ($commands->comando) ? intval($commands->comando)+1 : 1;
        $ejecutando = Incidencia::where('tipo','ejecutando')->first();
        $argumento = $this->argument('tipo'); 
        if( $argumento == 'desde_front'){
            $procesos_a_ejecutar = json_decode($ejecutando->afectados,true);
        }else{
            $procesos_a_ejecutar=
            ['1','2','3','4','5','6','7_8_9_10_11_12',
            '13','14','15','16','17','18',
            '19','20','21','22','23'];
            \Log::channel('soporte_log')->info("Entro desde back");
        }
        Incidencia::updateOrCreate(
            ['tipo' => 'ejecutando'],
            ['total' => 1]
        );
        // Proceso 1.La suma de aprobados,realizados,revisados y desaprobados no deben pasar la cantidad de temas asignados que posee el curso
        // Proceso 2.Si la suma de aprobados, realizados, revisados es igual a asignados el estado deberia ser aprobado o enc_pend
        // Proceso 3. No debería haber registros duplicados en la tabla resumen_x_curso
        // Proceso 4. No debería haber registros duplicados en la tabla resumen_general
        // Proceso 5.No debería haber registros duplicados en la tabla visitas
        // Proceso 6.No debería haber registros duplicados en la pru_duplicados
        // Proceso 7.VERIFICAR SI LOS CURSOS ASIGNADOS DEL USUARIO ESTAN ACORDE AL DE RESUMEN GENERAL 
        // Proceso 8.El usuario no deberia tener asignados cursos de otros modulos
        // Proceso 9.Usuario que tiene resumen_x_curso de cursos no asignados
        // Proceso 10.Si el total de cursos completados es igual a la cantidad de cursos asignados entonces el avance debe ser el 100%
        // Proceso 11.RESUMEN GENERAL CON NOTA PROMEDIO SIN RESUMEN_X_CURSO
        // Proceso 12.todos los usuario_id debe exisitir en la tabla resumen regenera (si existe en tabla usuarios pero no existe en tabla resumen_genera es un error)
        // Proceso 13.Toda evaluacion registrada(aprobado o  desaprobado) en tabla pruebas y resumen por curso debe tener visitas
        // Proceso 14.Todos los cursos activos pertenecen a una escuela activa
        // Proceso 15.Toda escuela inactiva debe tener cursos inactivos
        // Proceso 16.El reinicio por tema debe tener el curso_id  
        // Proceso 17.El reinicio debe tener un admin_id 
        // Proceso 18.La visita no puede tener un curso_id o posteo_id vacio
        // Proceso 19.No deberia haber pruebas con intentos 0 y nota_promedio
        // Proceso 20.No deberia haber aprobados con nota < 12
        // Proceso 21.No deberia haber desaprobados con nota > 12
        // Proceso 22.No deberia haber visitas con sumatoria 0 o null si el campo estado_tema tiene valor o vacio
        // Proceso 23.La cantidad de visitas en resumen_x_curso deber ser igual a la suma de visitas por curso en la tabla visitas
        foreach ($procesos_a_ejecutar as $proceso) {
            $proceso_function = 'proceso_'.$proceso; 
            $this->{$proceso_function}($id_comando,$ejecutando);
            \Log::channel('soporte_log')->info("Se ejecuto el ".$proceso_function.' desde '.$argumento);
        }
        //FINALIZAR
        $ejecutando->update(['total' =>0]);
        \Log::channel('soporte_log')->info("Termino de ejecutarse el comando");
    }
    //La suma de aprobados,realizados,revisados y desaprobados no deben pasar la cantidad de temas asignados que posee el curso
    private function proceso_1($id_comando,$ejecutando){
        $res_c = Resumen_x_curso::where('estado_rxc',1)->where(DB::raw('aprobados+realizados+revisados+desaprobados'),'>',DB::raw('asignados'))->select('id','curso_id')->get();
        $mensaje = 'Suma de aprobados,realizados,revisados y desaprobados pasan la cantidad de temas asignados que posee el curso';
        $this->insertar_incidencia($res_c,$id_comando,$mensaje,'resumen_x_curo');
    }
    //Si la suma de aprobados, realizados, revisados es igual a asignados el estado deberia ser aprobado o enc_pend
    private function proceso_2($id_comando,$ejecutando){
        $ejecutando->update(['total' =>2]);
        $res_c_2 = Resumen_x_curso::where('estado_rxc',1)->where(DB::raw('aprobados+realizados+revisados'),'=',DB::raw('asignados'))->where('estado','<>','aprobado')->where('estado','<>','enc_pend')->select('id','curso_id')->get();
        $mensaje = 'la suma de aprobados, realizados, revisados es igual a asignados tiene un estado diferente a aprobado o enc_pend';
        $this->insertar_incidencia($res_c_2,$id_comando,$mensaje,'resumen_x_curso');
    }
    // No debería haber registros duplicados en la tabla resumen_x_curso
    private function proceso_3($id_comando,$ejecutando){
        $ejecutando->update(['total' =>3]);
        $res_c_duplicadas = Resumen_x_curso::select('id','curso_id','usuario_id', DB::raw('COUNT(*) as `count`'))
        ->groupBy('usuario_id', 'curso_id')
        ->havingRaw('COUNT(*) > 1')
        ->get();
        $mensaje = 'Regitro de resumen_x_curso duplicado';
        $this->insertar_incidencia($res_c_duplicadas,$id_comando,$mensaje,'resumen_x_curso');
    }
    // No debería haber registros duplicados en la tabla resumen_general
    private function proceso_4($id_comando,$ejecutando){
        $ejecutando->update(['total' =>4]);
        $res_g_duplicadas = Resumen_general::select('id','usuario_id', DB::raw('COUNT(*) as `count`'))
        ->groupBy('usuario_id')
        ->havingRaw('COUNT(*) > 1')
        ->get();
        $mensaje = 'Regitro de resumen_general duplicado';
        $this->insertar_incidencia($res_g_duplicadas,$id_comando,$mensaje,'resumen_general');
    }
     //No debería haber registros duplicados en la tabla visitas
    private function proceso_5($id_comando,$ejecutando){
        $ejecutando->update(['total' =>5]);
        $vis_duplicadas = Visita::select('id','post_id','usuario_id', DB::raw('COUNT(*) as `count`'))
        ->groupBy('usuario_id', 'post_id')
        ->havingRaw('COUNT(*) > 1')
        ->get();
        $mensaje = 'Regitro de visitas duplicado';
        $this->insertar_incidencia($vis_duplicadas,$id_comando,$mensaje,'visitas');
    }
    //No debería haber registros duplicados en la pru_duplicados
    private function proceso_6($id_comando,$ejecutando){
        $ejecutando->update(['total' =>6]);
        $pru_duplicadas = Prueba::select('id','posteo_id','usuario_id', DB::raw('COUNT(*) as `count`'))
        ->groupBy('usuario_id', 'posteo_id')
        ->havingRaw('COUNT(*) > 1')
        ->get();
        $mensaje = 'Regitro de pruebas duplicado';
        $this->insertar_incidencia($pru_duplicadas,$id_comando,$mensaje,'pruebas');
    }
     // 7.VERIFICAR SI LOS CURSOS ASIGNADOS DEL USUARIO ESTAN ACORDE AL DE RESUMEN GENERAL 
    // 8.El usuario no deberia tener asignados cursos de otros modulos
    // 9.Usuario que tiene resumen_x_curso de cursos no asignados
    // 10.Si el total de cursos completados es igual a la cantidad de cursos asignados entonces el avance debe ser el 100%
    // 11.RESUMEN GENERAL CON NOTA PROMEDIO SIN RESUMEN_X_CURSO
    // 12.todos los usuario_id debe exisitir en la tabla resumen regenera (si existe en tabla usuarios pero no existe en tabla resumen_genera es un error)
    private function proceso_7_8_9_10_11_12($id_comando,$ejecutando){
        $ejecutando->update(['total' =>7]);
        $helper = new HelperController();
        $res_general = Resumen_general::with(['usuario'=>function($q){
            $q->select('id','config_id');
        }])->select('usuario_id','cur_asignados','tot_completados','porcentaje','nota_prom')->get();
        // VERIFICAR SI LOS CURSOS ASIGNADOS DEL USUARIO ESTAN ACORDE AL DE RESUMEN GENERAL 
        $dif_asignados =[];
        //El usuario no deberia tener asignados cursos de otros modulos
        $dif_modulo = [];
        // Usuario que tiene resumen_x_curso de cursos no asignados
        $res_x_cur_no_asignados = [];
        //Si el total de cursos completados es igual a la cantidad de cursos asignados entonces el avance debe ser el 100%
        $percent_error = [];
        //RESUMEN GENERAL CON NOTA PROMEDIO SIN RESUMEN_X_CURSO
        $sin_rxc = [];
        $i = 0;
        foreach ($res_general as $res) {
            $ids_cursos = $helper->help_cursos_x_matricula($res->usuario_id);
            $q = count($ids_cursos);
            if($res->cur_asignados!=$q){
                $dif_asignados[]=[
                    'usuario_id' =>$res->usuario_id
                ];
            }
            if(isset($res->usuario->config_id)){
                $cursos = Curso::whereIn('id',$ids_cursos)->where('config_id','<>', $res->usuario->config_id)->select('id')->get();
                if(count($cursos)>0){
                    $dif_modulo[]=[
                        'usuario_id' =>$res->usuario_id,
                        'cursos_id' => $cursos->pluck('id'),
                    ];
                }
            }
            $rxc=Resumen_x_curso::where('estado_rxc',1)->where('usuario_id',$res->usuario_id)->whereNotIn('curso_id',$ids_cursos)->select('curso_id')->get();
            if(count($rxc)>0){
                $res_x_cur_no_asignados [] =[
                    'usuario_id' => $res->usuario_id,
                    'cursos_id' => $rxc->pluck('curso_id'),
                ];
            }
            if($res->tot_completados == 0 && $res->nota_prom > 0){
                $rxc_asignados = Resumen_x_curso::where('estado_rxc',1)->where('usuario_id',$res->usuario_id)->whereIn('curso_id',$ids_cursos)->select('curso_id')->first();
                if(is_null($rxc_asignados)){
                    $sin_rxc[] = [
                        'usuario_id' =>$res->usuario_id
                    ];
                }
            }
            if($res->porcentaje==100 && ($res->tot_completados != $res->cur_asignados)){
                $percent_error[]=[
                    'usuario_id' =>$res->usuario_id
                ];
            }
            $i++;
        }
        $mensaje = 'Usuarios tienen cursos asignados de un modulo al que no pertenece';
        $this->insertar_incidencia($dif_modulo,$id_comando,$mensaje,'cursos_y_matricula');
        $ejecutando->update(['total' =>8]);
        $mensaje = 'Usuarios con cursos asignados diferente a la cantidad que dice en resumen_general';
        $this->insertar_incidencia($dif_asignados,$id_comando,$mensaje,'resumen_general_y_matricula');
        $ejecutando->update(['total' =>9]);
        $mensaje = 'Usuarios con resumen_x_curso de cursos que no tiene asignado';
        $this->insertar_incidencia($res_x_cur_no_asignados,$id_comando,$mensaje,'resumen_x_curso_y_matricula');
        $ejecutando->update(['total' =>10]);
        $mensaje = 'Usuarios con cursos asignados y total completados iguales con porcentaje diferente a 100';
        $this->insertar_incidencia($percent_error,$id_comando,$mensaje,'resumen_general');
        $ejecutando->update(['total' =>11]);
        $mensaje = 'Resumen general con nota promedio no tiene resumen_x_curso';
        $this->insertar_incidencia($sin_rxc,$id_comando,$mensaje,'resumen_general_y_resumen_x_curso');
        $ejecutando->update(['total' =>12]);
        $usuarios_con_rg =  $res_general->pluck('usuario_id');
        $usuarios_sin_rg = Usuario::select('id')->whereNotIn('id',$usuarios_con_rg)->pluck('id');
        $mensaje = 'Hay usuarios sin resumen_general';
        $this->insertar_incidencia($usuarios_sin_rg,$id_comando,$mensaje,'resumen_general_y_usuarios');
    }
    // 13.Toda evaluacion registrada(aprobado o  desaprobado) en tabla pruebas y resumen por curso debe tener visitas
    private function proceso_13($id_comando,$ejecutando){
        $ejecutando->update(['total' =>13]);
        $pruebas = Prueba::select('id','usuario_id','posteo_id')->get();
        $pr_sin_visita = [];
        foreach ($pruebas as $pr) 
        {
            $v = Visita::where('usuario_id',$pr->usuario_id)->where('post_id',$pr->posteo_id)->select('id')->first();
            if(is_null($v)){
                $pr_sin_visita [] =[
                    'usuario_id' => $pr->usuario_id,
                    'posteo_id' => $pr->posteo_id,
                ];
            }
        }
        $mensaje = 'Pruebas que no tienen visitas';
        $this->insertar_incidencia($pr_sin_visita,$id_comando,$mensaje,'pruebas_y_visitas');
    }
    // Todos los cursos activos pertenecen a una escuela activa
    private function proceso_14($id_comando,$ejecutando){
        $ejecutando->update(['total' =>14]);
        $cursos = Curso::join('categorias','categorias.id','cursos.categoria_id')
        ->where('cursos.estado',1)->where('categorias.estado',0)->select('cursos.id')->get();
        $mensaje = 'Cursos activos que estan dentro de una categoria inactiva';
        $this->insertar_incidencia($cursos,$id_comando,$mensaje,'cursos');
    }
    // Toda escuela inactiva debe tener cursos inactivos
    private function proceso_15($id_comando,$ejecutando){
        $ejecutando->update(['total' =>15]);
        $cursos_act_en_esc_inac = [];
        $categorias = Categoria::with(['cursos'=>function($q){
            $q->select('id','categoria_id','estado')->where('estado',1);
        }])->where('estado',0)->select('id','estado');
        $mensaje = 'Categoria que tiene cursos activos';
        foreach ($categorias as $ca) {
            if(count($ca->cursos)>0){
                $cursos_act_en_esc_inac[]=[
                    'cate_id' => $ca->id,
                ];
            }
        }
        $this->insertar_incidencia($cursos_act_en_esc_inac,$id_comando,$mensaje,'cursos_y_categorias');
    }
    // 16.El reinicio por tema debe tener el curso_id  
    private function proceso_16($id_comando,$ejecutando){
        $ejecutando->update(['total' =>16]);
        $reinicios_x_tema = Reinicio::select('usuario_id')->where('tipo','por_tema')->whereNull('curso_id')->pluck('usuario_id');
        $mensaje = 'Hay reinicios por tema con el curso_id vacio';
        $this->insertar_incidencia($reinicios_x_tema,$id_comando,$mensaje,'reinicios');
    }
    // 17.El reinicio debe tener un admin_id 
    private function proceso_17($id_comando,$ejecutando){
        $ejecutando->update(['total' =>17]);
        $reinicios_sin_admin = Reinicio::select('usuario_id')->whereNull('admin_id')->pluck('usuario_id');
        $mensaje = 'Hay reinicios sin admin_id';
        $this->insertar_incidencia($reinicios_sin_admin,$id_comando,$mensaje,'reinicios');
    }
     // 18.La visita no puede tener un curso_id o posteo_id vacio
    private function proceso_18($id_comando,$ejecutando){
        $ejecutando->update(['total' =>18]);
        $visitas = Visita::select('id')->where(function ($query) {
            $query->whereNull('curso_id')
                  ->orWhereNull('post_id');
        })->pluck('id');
        $mensaje = 'Visitas con curso_id o posteo_id vacio';
        $this->insertar_incidencia($visitas,$id_comando,$mensaje,'visitas');
    }
    // Proceso 19.No deberia haber pruebas con intentos 0 y nota_promedio
    private function proceso_19($id_comando,$ejecutando){
        $ejecutando->update(['total' =>19]);
        $pr = Prueba::where('fuente','<>','reset')->where('intentos',0)
        ->whereNotNull('nota')->select('usuario_id','posteo_id')->get();
        $mensaje = 'Pruebas (sin reseteo) con intentos 0 y con nota promedio';
        $this->insertar_incidencia($pr,$id_comando,$mensaje,'pruebas');
    }
    // Proceso 20.No deberia haber aprobados con nota < 12
    private function proceso_20($id_comando,$ejecutando){
        $ejecutando->update(['total' =>20]);
        $pr = Prueba::where('resultado',0)->where('nota','>=',12)->select('usuario_id','posteo_id')->get();
        $mensaje = 'Desaprobados con nota aprobatoria';
        $this->insertar_incidencia($pr,$id_comando,$mensaje,'pruebas');
    }
    // Proceso 21.No deberia haber desaprobados con nota > 12
    private function proceso_21($id_comando,$ejecutando){
        $ejecutando->update(['total' =>21]);
        $pr = Prueba::where('resultado',1)->where('nota','<',12)->select('usuario_id','posteo_id')->get();
        $mensaje = 'Aprobados con nota desaprobatoria';
        $this->insertar_incidencia($pr,$id_comando,$mensaje,'pruebas');
    }
    // Proceso 22.No deberia haber visitas con sumatoria 0 o null si el campo estado_tema tiene valor o vacio
    private function proceso_22($id_comando,$ejecutando){
        $ejecutando->update(['total' =>22]);
        $visitas = Visita::select('id')->where(function ($query) {
            $query->where('sumatoria',0)
                  ->orWhereNull('sumatoria');
        })->where(function ($query) {
            $query->whereNotNull('estado_tema')
                  ->orWhere('estado_tema','');
        })->pluck('id');
        $mensaje = 'Visitas con sumatoria 0 o nulo con estado_tema';
        $this->insertar_incidencia($visitas,$id_comando,$mensaje,'visitas');
    }
    // Proceso 23.La cantidad de visitas en resumen_x_curso deber ser igual a la suma de visitas por curso en la tabla visitas
    private function proceso_23($id_comando,$ejecutando){
        $ejecutando->update(['total' =>23]);
        $res = Resumen_x_curso::where('estado_rxc',1)->select('id','usuario_id','curso_id','visitas')->get();
        // $visitas = Visita::select('usuario_id','sumatoria','post_id')->get();
        $posteos = Posteo::select('id','curso_id')->where('estado',1)->get();
        $visitas_diferentes=[];
        \Log::channel('soporte_log')->info(count($res));
        $i=0;
        try {
            foreach ($res as $re) {
                $p_activos = $posteos->where('curso_id',$re->curso_id);
                $visitas = Visita::select(DB::raw('SUM(sumatoria) as suma_visitas'))
                    ->whereIn('post_id',$p_activos->pluck('id'))
                    ->where('visitas.usuario_id', $re->usuario_id)
                    ->where('visitas.curso_id', $re->curso_id)->first();
                $suma_visitas = (isset($visitas)) ? intval($visitas->suma_visitas) : 0;
                // $suma_visitas = $visitas->where('usuario_id',$re->usuario_id)->where('post_id',$p_activos)->sum('sumatoria');
                if($suma_visitas != intval($re->visitas)){
                    $visitas_diferentes[]=$re->id;
                }
            }
            $mensaje = 'Suma de visitas incorrecta en la tabla resumen_x_curso';
            $this->insertar_incidencia($visitas_diferentes,$id_comando,$mensaje,'resumen_x_curso_posteo_visitas');
        } catch (\Throwable $th) {
            \Log::channel('soporte_log')->info($th);
        }
    }
    private function insertar_incidencia($incidencias,$id_comando,$mensaje,$tipo){
        if(count($incidencias)>0){
            DB::table('incidencias')->insert([
                'comando' => $id_comando,
                'tipo'=>$tipo,
                'mensaje'=>$mensaje,
                'afectados'=>json_encode($incidencias),
                'total'=>count($incidencias),
            ]);
        }
    }
}