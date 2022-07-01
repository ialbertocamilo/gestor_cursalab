<?php

namespace App\Models;

use App\Ciclo;
use App\Posteo;
use App\Prueba;
use App\Visita;
use App\Diploma;
use App\Criterio;
use App\Reinicio;
use App\Curricula;
use App\Matricula;
use App\Ev_abierta;
use App\PosteoCompas;
use App\Curso_encuesta;
use App\Resumen_general;
use App\Resumen_x_curso;
use App\Curricula_criterio;
use App\Matricula_criterio;
use App\Encuestas_respuesta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class CambiosMasivos implements ToCollection
{
    private $q_cambio_ciclo = 0;
    private $q_cambio_carrera = 0;
    private $q_errors = 0;
    private $q_cambio_modulo = 0;
    private $q_actualizaciones = 0;
    private $helper;
    private $rest_avance;
    
    public function __construct(){
        $this->helper = new HelperController();
        $this->rest_avance = new RestAvanceController();
    }
     
    public function collection(Collection $rows)
    {
        $total = count($rows);
        $errores = [];
        $modulos = Abconfig::with('carreras','criterios')->get(['id','etapa','mod_evaluaciones']);
        $cargos = Cargo::all();
        // $update_usuarios=[];
      
        \Log::channel('soporte_log')->info("Empieza migración");
        for ($i=1; $i < $total ; $i++) {
            /* 
                Columnas del excel: Módulo(0),Área(1),Sede(2),DNI(3),Apellidos y Nombres(4),Genero(5),Carrera(6),Ciclo(7),Cargo(8)
             */
            $exl_tipo = 'Actualizar datos';
            if(!empty($rows[$i][1])){
                $exl_modulo = trim($rows[$i][0]); //config id es el id del modulo
                $grupo =strtoupper(trim($rows[$i][1]));
                $botica = trim($rows[$i][2]);
                $exl_dni = trim($rows[$i][3]);
                $nombre = trim($rows[$i][4]);
                $sexo = trim($rows[$i][5]);
                $exl_carrera = trim($rows[$i][6]);
                $exl_ciclo = trim($rows[$i][7]);
                $cargo = trim($rows[$i][8]);
                $mod_id = null;
                $carr_id = null;
                $ciclo_id = null;
                $user_b = null;
                $us_exi = null;
                $mod_evaluaciones = null;
                //VERIFICAR SI EXISTE USUARIO
                $user_b = Usuario::where('dni',$exl_dni)->first(['id','grupo','dni','config_id']);
                if($user_b){
                    $us_exi = $user_b;
                }   
                //BUSCAR LOS IDS DE CARRERA y MODULO
                $grupo_id = null;
                $b_grupo_nombre = null;
                foreach ($modulos as $mod) {
                    if($mod->etapa==$exl_modulo){
                        $mod_id = $mod->id;
                        $mod_evaluaciones = $mod->mod_evaluaciones;
                        foreach ($mod->carreras as $carr) {
                            if($carr->nombre==$exl_carrera){
                                $carr_id = $carr->id;
                            }
                        }
                        if($mod->criterios){
                            foreach ($mod->criterios as $crit) {
                                if($crit->valor==$grupo){
                                    $grupo_id = $crit->id;
                                    $b_grupo_nombre =$crit->valor;
                                }
                            } 
                        } 
                    }
                }
                $botica_id = null;
                $bot = Botica::select('id','nombre','criterio_id')->where('config_id',$mod_id)->where('nombre',$botica)->where('criterio_id',$grupo_id)->first();
                if($bot){
                    $botica_id = $bot->id;
                }
                //BUSCAR CARGO
                $carg_id = null;
                $cargo_s = $cargos->where('nombre',$cargo)->first();
                if($cargo_s){
                    $carg_id = $cargo_s->id;
                }
                // $tipos_acciones = array_column(config('constantes.acción_migración.select'),'nombre');
                // $existe_accion = in_array($exl_tipo,$tipos_acciones);
                if($carr_id && $carg_id){
                    $b_ciclo = Ciclo::select('id','secuencia')->where('carrera_id',$carr_id)->where('nombre',$exl_ciclo)->first();
                    if($b_ciclo && $us_exi && ($botica_id) && ($grupo_id)){
                        $ciclo_id = $b_ciclo->id;
                        //Ver estado de la matricula
                        $matricula_actual = Matricula::where('usuario_id',$us_exi->id)->where('presente',1)->where('estado',1)->first(['ciclo_id','carrera_id']);
                        if($matricula_actual->ciclo_id != $ciclo_id){
                            $exl_tipo = 'Cambio de ciclo';
                        }
                        if($matricula_actual->carrera_id != $carr_id){
                            $exl_tipo = 'Cambio de carrera';
                        }
                        if($us_exi->config_id!=$mod_id){
                            $exl_tipo = 'Cambio de módulo';
                        }
                        // $update_usuarios[]=$us_exi->id;
                        $user = [
                            'config_id' => $mod_id,
                            'dni' => $exl_dni,
                            'carrera_id' => $carr_id,
                            'ciclo_id' => $ciclo_id,
                            'secuencia' => $b_ciclo->secuencia,
                            'usuario_id' => $us_exi->id,
                            'grupo' => $grupo_id,
                            'grupo_nombre' => $b_grupo_nombre,
                            'mod_evaluaciones'=>$mod_evaluaciones,
                            'nombre' => $nombre,
                            'cargo' => $cargo,
                            'sexo' => $sexo,
                            'botica' => $botica,
                            'botica_id'=>$botica_id,
                        ];
                        
                        switch ($exl_tipo) {
                            case 'Cambio de ciclo':
                                $user['ciclo_nombre']= $exl_ciclo;
                                //CAMBIAR DE ESTADO EL ANTERIOR
                                $this->cambiar_ciclo($user);
                                $this->actualizar_usuario($user);
                                $this->q_cambio_ciclo++;
                                break;
                            case 'Cambio de carrera':
                                $this->cambiar_carrera($user);
                                $this->actualizar_usuario($user);
                                $this->q_cambio_carrera ++ ;
                            break;
                            case 'Cambio de módulo':
                                $this->cambiar_carrera($user);
                                $this->actualizar_usuario($user);
                                $this->q_cambio_modulo ++;
                            break;
                            default:
                                $this->actualizar_usuario($user);
                                $this->q_actualizaciones ++;
                            break;
                        }
                    }
                }
                //ERRORES
                $error =[];
                if(!($mod_id) || !($carr_id) || !($botica_id) || !($grupo_id) || !($ciclo_id) || !($us_exi) || !($carg_id)){
                    // if(!$existe_accion){
                    //     array_push($error,['tipo'=>'acción_migración_error','celda'=>'F'.($i+1),'mensajes'=>'Acción no encontrada']);
                    // }
                    if(!($mod_id)){
                        array_push($error,['tipo'=>'módulo_error','celda'=>'B','mensajes'=>'error en el modulo']);
                    }else{
                        if(!($carr_id)){
                            array_push($error,['tipo'=>'carrera_error','celda'=>'I','mensajes'=>'error en la carrera']);
                        }else{
                            if(!($ciclo_id)){
                                array_push($error,['tipo'=>'ciclo_error','celda'=>'J','mensajes'=>'error en el ciclo']);
                            }
                        }
                    }
                    if(!($carg_id)){
                        if(empty($cargo)){
                            array_push($error,['tipo'=>'cargo_error','celda'=>'C'.($i+1),'mensajes'=>'Cargo vacio']);
                        }else{
                            array_push($error,['tipo'=>'cargo_error','celda'=>'F'.($i+1),'mensajes'=>'Cargo no encontrado']);
                        }
                    }
                    if(empty($us_exi)){
                        array_push($error,['tipo'=>'dni_error','celda'=>'C','mensajes'=>'Dni no existe']);
                    }
                    
                    if(!($grupo_id)){
                        array_push($error,['tipo'=>'grupo_error','celda'=>'F'.($i+1),'mensajes'=>'Grupo no encontrado']);
                    }
                    if(!($botica_id)){
                        array_push($error,['tipo'=>'botica_error','celda'=>'F'.($i+1),'mensajes'=>'Botica no encontrada']);
                    }

                    $errores[]=[
                        'data' =>[
                            'modulo' => $exl_modulo,
                            'config_id' => $mod_id,
                            'dni' => $exl_dni,
                            'nombre' => $nombre,
                            'botica' => $botica,
                            'botica_id'=>$botica_id,
                            'grupo' => $grupo,
                            'grupo_id' => $grupo_id,
                            'cargo' => $cargo,
                            'sexo' => $sexo,
                            'carrera' => $exl_carrera,
                            'carrera_id'=>$carr_id,
                            'ciclo' => $exl_ciclo,
                            'ciclo_id' => $ciclo_id,
                            'accion' =>$exl_tipo
                        ],
                        'error' => $error,
                        'err_data_original'=>$rows[$i],
                    ];  
                }
            }
        }
        // if(count($update_usuarios)>0){
        //     DB::table('update_usuarios')->insert([
        //         'usuarios_id' => json_encode($update_usuarios),
        //         'tipo' => 'update_resumenes_from_masivo',
        //     ]);
        //     \Log::channel('soporte_log')->info("Usuarios_migrados: ".json_encode($update_usuarios));
        // }
        //GUARDAR ERRORES
        $this->q_errors = count($errores);
        foreach ($errores as $err) {
            DB::table('err_masivos')->insert([
                'err_data'=> json_encode($err['data'],JSON_UNESCAPED_UNICODE),
                'err_type' => json_encode($err['error'],JSON_UNESCAPED_UNICODE),
                'err_data_original' =>json_encode($err['err_data_original'],JSON_UNESCAPED_UNICODE),
                'type_masivo' => 'ciclos_carreras'
            ]);
        }
    }
    public function cambiar_ciclo($user){
        try {
            // if($user['ciclo_nombre']=='Ciclo 1'){
                // $result = collect();
                // //CURSOS DEL CICLO 0
                // $matricula=Matricula::where('usuario_id',$user['usuario_id'])->where('carrera_id',$user['carrera_id'])->where('presente',1)->first();
                // $matriculas_criterio = Matricula_criterio::select('criterio_id')->where('matricula_id', $matricula->id)->first();
                // $criterio_id = $matriculas_criterio->criterio_id;
                // $curriculas_criterios = Curricula_criterio::select('curricula_id')->where('criterio_id', $criterio_id)->get();
                // foreach ($curriculas_criterios as $curricula_criterio) {
                //     $curricula = Curricula::join('cursos', 'cursos.id', 'curricula.curso_id')
                //         ->select('ciclo_id', 'curso_id')
                //         ->where('cursos.estado', 1)
                //         ->where('curricula.id', $curricula_criterio->curricula_id)->first();
                //     if (isset($curricula) && $curricula->ciclo_id == $matricula->ciclo_id) {
                //         $result->push($curricula->curso_id);
                //     }
                // }
                // $curso_ids_cic_cero = $result->unique()->values()->all();
                // $temas_corres = Posteo::whereIn('curso_id', $curso_ids_cic_cero)->pluck('id')->toArray();
    
                // //MIGRAR TEMAS
                // $this->migrar_temas($user['usuario_id'],$temas_corres);
            // }
            $m_b = Matricula::where('usuario_id',$user['usuario_id'])
             ->where('carrera_id',$user['carrera_id'])->where('ciclo_id',$user['ciclo_id'])->first();
             if(is_null($m_b)){
                 $mat = new Matricula();
                 $mat->usuario_id = $user['usuario_id'];
                 $mat->carrera_id= $user['carrera_id'];
                 $mat->ciclo_id=  $user['ciclo_id'];
                 $mat->secuencia_ciclo = $user['secuencia'];
                 $mat->presente = 1;
                 $mat->estado = 1;
                 $mat->save();
                 //Crear Matricula Criterio
                 Matricula_criterio::insert([
                     'matricula_id' => $mat->id,
                     'criterio_id' => $user['grupo'],
                 ]);
            }
            Matricula::where('usuario_id',$user['usuario_id'])->where('carrera_id',$user['carrera_id'])->where('presente',1)->update([
                'presente' => 0,
                'estado' => 1
            ]);
            Matricula::where('usuario_id',$user['usuario_id'])->where('ciclo_id',$user['ciclo_id'])->update([
                'presente' => 1,
                'estado' => 1
            ]);
            //CAMBIOS DE ESTADO
            Matricula::where('usuario_id',$user['usuario_id'])->where('secuencia_ciclo','>',$user['secuencia'])->update([
                'estado' => 0
            ]);
            Matricula::where('usuario_id',$user['usuario_id'])->where('secuencia_ciclo','<>',0)->where('secuencia_ciclo','<',$user['secuencia'])->update([
                'estado' => 1
            ]);
            if($user['secuencia'] != 0 ){
                Matricula::where('usuario_id',$user['usuario_id'])->where('secuencia_ciclo',0)->update([
                    'estado' => 0,
                    'presente' => 0
                ]);
            }
            // CONSULTAR LOS CURSOS MATRICULADOS
            $curso_ids_matricula = $this->helper->help_cursos_x_matricula($user['usuario_id']);
            // //LIMPIAR TABLAS RESUMENES
            Resumen_x_curso::where('usuario_id',$user['usuario_id'])->whereNotIn('curso_id',$curso_ids_matricula)->update(['estado_rxc'=>0]);
            Resumen_x_curso::where('usuario_id',$user['usuario_id'])->whereIn('curso_id',$curso_ids_matricula)->update(['estado_rxc'=>1]);
            //ACTUALIZAR TABLAS RESUMENES
            $mod_eval = json_decode($user['mod_evaluaciones'], true);
            foreach ($curso_ids_matricula as $cur_id) {
                // ACTUALIZAR RESUMENES
                $this->rest_avance->actualizar_resumen_x_curso($user['usuario_id'],$cur_id, $mod_eval['nro_intentos']);
            }
            $this->rest_avance->actualizar_resumen_general($user['usuario_id']);
        } catch (\Throwable $th) {
        //    dd($th);
        }
    }
    public function cambiar_carrera($user){
        $matri = Matricula::select('carrera_id','id')
        ->where('usuario_id', $user['usuario_id'])
        ->get();
        //ELIMINAR MATRICULA Y MATRICULA CRITERIO ACTUAL
        foreach ($matri as $value) {
            Matricula_criterio::where('matricula_id',$value->id)->delete();
            Matricula::where('usuario_id',$user['usuario_id'])->where('id',$value->id)->delete();
        }
        // $carrera_actual = $matri[0]->carrera_id;
        //MATRICULAR EN SU NUEVA CARRERA
        $ciclos = Ciclo::select('id', 'secuencia')
        ->where([
        ['carrera_id', $user['carrera_id']],
        ['estado',1],
        ])->get();
        foreach ($ciclos as $ci) {
            if($ci->secuencia != 0 || $user['secuencia'] == 0 ){
                $mat = new Matricula();
                $mat->usuario_id = $user['usuario_id'];
                $mat->carrera_id= $user['carrera_id'];
                $mat->ciclo_id=  $ci->id;
                $mat->secuencia_ciclo = $ci->secuencia;
                if($ci->id == $user['ciclo_id']){
                    $mat->presente=1;
                }else{
                    $mat->presente=0;
                }
                if($ci->secuencia <= $user['secuencia']){
                    $mat->estado =1;
                }else{
                    $mat->estado =0;
                }
                $mat->save();
                //Crear Matricula Criterio
                Matricula_criterio::insert([
                    'matricula_id' => $mat->id,
                    'criterio_id' => $user['grupo'],
                ]);
            }
        }
        // CONSULTAR LOS CURSOS MATRICULADOS
        $curso_ids_matricula = $this->helper->help_cursos_x_matricula_con_cursos_libre($user['usuario_id']);
        $curso_ids_todos = $this->help_cursos_x_todas_matricula($user['usuario_id']);
        $temas_corres = Posteo::whereIn('curso_id', $curso_ids_todos)->pluck('id')->toArray();
        //MIGRAR TEMAS
        $res_migrar = $this->migrar_temas($user['usuario_id'],$temas_corres);
        //LIMPIAR TABLAS RESUMENES
        Resumen_x_curso::where('usuario_id',$user['usuario_id'])->whereNotIn('curso_id',$curso_ids_matricula)->update(['estado_rxc'=>0]);
        Resumen_x_curso::where('usuario_id',$user['usuario_id'])->whereIn('curso_id',$curso_ids_matricula)->update(['estado_rxc'=>1]);
        // Resumen_general::where('usuario_id',$user['usuario_id'])->delete();
        //ACTUALIZAR TABLAS RESUMENES
        $mod_eval = json_decode($user['mod_evaluaciones'], true);
        $migrados =  array_column($res_migrar,'migrado');
        foreach ($curso_ids_matricula as $cur_id) {
            // ACTUALIZAR RESUMENES
            $this->rest_avance->actualizar_resumen_x_curso($user['usuario_id'],$cur_id, $mod_eval['nro_intentos']);
            //Migrar intentos
            $clave = array_search($cur_id,$migrados);
            if (false !== $clave){
                $intentos = $res_migrar[$clave]['intentos'];
                Resumen_x_curso::where('usuario_id',$user['usuario_id'])->where('curso_id',$cur_id)->update([
                    'intentos' => $intentos
                ]);
            }
        }
        $this->rest_avance->actualizar_resumen_general($user['usuario_id']);
    }
    private function migrar_temas($user_id,$temas_corres){
        //MIGRAR NOTAS DE TEMAS COMPATIBLES
        $evas = Prueba::where('usuario_id', $user_id)->get();
        /*
            PRUEBA                  temas_corres            temas_compas
            posteo_id : 15          [14,53,45,23]           [45,34,22]
        */
        $temas_migrados = [];
        $resumen_x_curso_migrado = [];
    
        foreach ($evas as $eva) {
            if (!in_array($eva->posteo_id, $temas_corres)) {
                // Consultar de IDA else DE VUELTA
                if ($compas =PosteoCompas::where('tema_id', $eva->posteo_id)->pluck('tema_compa_id')) {
                    $migrado = $this->update_pruebas($eva,$user_id,$temas_corres,$compas);
                    if(isset($migrado['original'])){
                        $f_resumen = in_array($migrado['original'],array_column($resumen_x_curso_migrado,'original'));
                        if(!$f_resumen){
                            $intentos = Resumen_x_curso::where('usuario_id',$user_id)->where('curso_id',$migrado['original'])->select('intentos')->first();
                            $migrado['intentos'] = ($intentos) ? $intentos->intentos : 0 ;
                            $resumen_x_curso_migrado[] = $migrado;
                        }
                    }
                }
                else if($compas2 =PosteoCompas::where('tema_compa_id', $eva->posteo_id)->pluck('tema_id')) {
                    $migrado = $this->update_pruebas($eva,$user_id,$temas_corres,$compas2);
                    if(isset($migrado['original'])){
                        $f_resumen = in_array($migrado['original'],array_column($resumen_x_curso_migrado,'original'));
                        if(!$f_resumen){
                            $intentos = Resumen_x_curso::where('usuario_id',$user_id)->where('curso_id',$migrado['original'])->select('intentos')->first();
                            $migrado['intentos'] = ($intentos) ? $intentos->intentos : 0 ;
                            $resumen_x_curso_migrado[] = $migrado;
                        }
                    }
                }
            }
        }
        //MIGRAR VISITAS
        $visitas = Visita::with('posteo')->where('usuario_id',$user_id)->get();
        foreach ($visitas as $visita) {
            if (!in_array($visita->post_id, $temas_corres)) {
                // Consultar de IDA else DE VUELTA
                if ($compas =PosteoCompas::where('tema_id', intval($visita->post_id))->pluck('tema_compa_id')) {
                    $temas_migra =  $this->update_visitas($visita,$user_id,$temas_corres,$compas);
                    if(count($temas_migra)>0)$temas_migrados[] = $temas_migra ;
                }else if($compas2 =PosteoCompas::where('tema_compa_id', $eva->post_id)->pluck('tema_id')) {
                    $temas_migra = $this->update_visitas($visita,$user_id,$temas_corres,$compas2);
                    if(count($temas_migra)>0)$temas_migrados[] = $temas_migra ;
                }
            }
        }
        //MIGRAR EVA ABIERTAS
        $ev_abiertas = Ev_abierta::where('usuario_id',$user_id)->get();
        foreach ($ev_abiertas as $ev_abierta) {
            if (!in_array($ev_abierta->posteo_id, $temas_corres)) {
                // Consultar de IDA else DE VUELTA
                if ($compas =PosteoCompas::where('tema_id', $ev_abierta->posteo_id)->pluck('tema_compa_id')) {
                   $this->update_evas_abiertas($ev_abierta,$user_id,$temas_corres,$compas);
                }else if($compas2 =PosteoCompas::where('tema_compa_id', $ev_abierta->posteo_id)->pluck('tema_id')) {
                   $this->update_evas_abiertas($ev_abierta,$user_id,$temas_corres,$compas2);
                }
            }
        }
        //MIGRAR DIPLOMAS
        $diplomas = Diploma::where('usuario_id',$user_id)->get();
        foreach ($diplomas as $diploma) {
            if (!in_array($diploma->posteo_id, $temas_corres)) {
                // Consultar de IDA else DE VUELTA
                if ($compas =PosteoCompas::where('tema_id', $diploma->posteo_id)->pluck('tema_id')) {
                    $temas_migra =  $this->update_diplomas($diploma,$user_id,$temas_corres,$compas);
                }else if($compas2 =PosteoCompas::where('tema_compa_id', $diploma->posteo_id)->pluck('tema_compa_id')) {
                    $temas_migra =  $this->update_diplomas($diploma,$user_id,$temas_corres,$compas2);
                }
            }
        }
        //MIGRAR REINICIOS
        $reinicios = Reinicio::where('usuario_id',$user_id)->get();
        foreach ($reinicios as $reinicio) {
            if (!in_array($reinicio->posteo_id, $temas_corres)) {
                // Consultar de IDA else DE VUELTA
                if ($compas =PosteoCompas::where('tema_id', $reinicio->posteo_id)->pluck('tema_id')) {
                    $this->update_reinicios($reinicio,$user_id,$temas_corres,$compas);
                }else if($compa2 =PosteoCompas::where('tema_compa_id', $reinicio->posteo_id)->pluck('tema_compa_id')) {
                    $this->update_reinicios($reinicio,$user_id,$temas_corres,$compa2);
                }
            }
        }
        return $resumen_x_curso_migrado;
    }
    public function actualizar_usuario($usuario){
        Usuario::where('id',$usuario['usuario_id'])->update([
            'config_id' => $usuario['config_id'],
            'botica' => $usuario['botica'],
            'botica_id'=>$usuario['botica_id'],
            'sexo' => $usuario['sexo'],
            'cargo' => $usuario['cargo'],
            'nombre' => $usuario['nombre'],
            'grupo' => $usuario['grupo'],
            'grupo_nombre' => $usuario['grupo_nombre'],
        ]);
    }
    public function get_q_errors(){
        return $this->q_errors;
    }
    public function get_q_cambio_ciclo(){
        return $this->q_cambio_ciclo;
    }
    public function get_q_cambio_carrera(){
        return $this->q_cambio_carrera;
    }
    public function get_q_cambio_modulo(){
        return $this->q_cambio_modulo;
    }
    public function get_q_actualizaciones(){
        return $this->q_actualizaciones;
    }
    private function update_pruebas($eva,$user_id,$temas_corres,$compas){
        $temas_migrados = [];
        if(count($compas)>0){
            foreach ($compas as $compa) {
                if(in_array($compa,$temas_corres)){
                    //Si es la misma encuesta, se reemplaza por el nuevo curso_id
                    $pmigra = Posteo::select('categoria_id', 'curso_id')->where('id', $compa)->first();
                    //MIGRAR ENCUESTA
                    $q_post_o = Posteo::where('curso_id',$eva->curso_id)->where('estado',1)->select('id')->count();
                    $q_post_m = Posteo::where('curso_id',$pmigra->curso_id)->where('estado',1)->select('id')->count();
                    if($q_post_o >= $q_post_m){
                        $enc_1 = Curso_encuesta::where('curso_id',$eva->curso_id)->first();
                        $enc_2 = Curso_encuesta::where('curso_id',$pmigra->curso_id)->first();
                        if(isset($enc_1) && isset($enc_2) && ($enc_1->encuesta_id == $enc_2->encuesta_id)){
                            $ec = Encuestas_respuesta::where('usuario_id', $user_id)->where('curso_id', $eva->curso_id)->update([
                                'curso_id' => $pmigra->curso_id,
                            ]);
                            $temas_migrados= [
                                "original" => $eva->curso_id,
                                "migrado" => $pmigra->curso_id,
                                'enc_1' => $enc_1->encuesta_id,
                                'enc_2' => $enc_2->encuesta_id,

                            ];
                        }
                    }
                    $eva->categoria_id = $pmigra->categoria_id;
                    $eva->curso_id = $pmigra->curso_id;
                    $eva->posteo_id = $compa;
                    $eva->save();
                    //VERIFICAR SI TIENE MAS DE 2 NOTAS
                    $veri_pruebas = Prueba::where('usuario_id',$user_id)->where('posteo_id',$eva->posteo_id)->get();
                    if(count($veri_pruebas)>1){
                        $max=[
                            'id'=> 0,
                            'nota' => 0,
                        ];
                        foreach ($veri_pruebas as $veri_prueba) {
                            if($veri_prueba->nota > $max['nota']){
                                $max['id'] = $veri_prueba->id;
                                $max['nota'] = $veri_prueba->nota;
                            }
                        }
                        if($max['id'] != 0){
                            Prueba::where('usuario_id',$user_id)->where('posteo_id',$eva->posteo_id)->where('id','<>',$max['id'])->delete();
                        }
                    }

                }
            }
        }
        return $temas_migrados;
    }
    private function update_visitas($visita,$user_id,$temas_corres,$compas){
        $temas_migrados = [];
        if(count($compas)>0){
            foreach ($compas as $compa) {
                if(in_array($compa,$temas_corres)){
                    $pmigra = Posteo::select('categoria_id', 'curso_id','tipo_ev')->where('id', $compa)->first();
                     //MIGRAR ENCUESTA
                     $q_post_o = Posteo::where('curso_id',$visita->curso_id)->where('estado',1)->select('id')->count();
                     $q_post_m = Posteo::where('curso_id',$pmigra->curso_id)->where('estado',1)->select('id')->count();
                     if($q_post_o >= $q_post_m){
                         $enc_1 = Curso_encuesta::where('curso_id',$visita->curso_id)->first();
                         $enc_2 = Curso_encuesta::where('curso_id',$pmigra->curso_id)->first();
                        
                         if(isset($enc_1) && isset($enc_2) && ($enc_1->encuesta_id == $enc_2->encuesta_id)){
                             $ec = Encuestas_respuesta::where('usuario_id', $user_id)->where('curso_id', $visita->curso_id)->update([
                                 'curso_id' => $pmigra->curso_id,
                             ]);
                         }
                     }
                    
                    //VERIFICAR SI YA TIENE UNA VISITA ANTES DE MIGRAR
                    $vis_2 = Visita::where('usuario_id',$user_id)->where('post_id',$compa)->first();
                    if(is_null($vis_2)){
                        if($visita->posteo->tipo_ev == $pmigra->tipo_ev){
                            $visita->estado_tema = $visita->estado_tema;
                        }else{
                            $visita->estado_tema = '';
                        }
                        $visita->curso_id =  $pmigra->curso_id;
                        $visita->post_id = $compa;
                        $visita->tipo_tema = $pmigra->tipo_ev;
                        $visita->save();
                    }else{
                        if($vis_2->posteo->tipo_ev == $pmigra->tipo_ev){
                            $vis_2->estado_tema = $vis_2->estado_tema;
                        }else{
                            $vis_2->estado_tema = '';
                        }
                        $vis_2->sumatoria = $visita->sumatoria;
                        $vis_2->descargas = $visita->descargas;
                        $vis_2->tipo_tema = $pmigra->tipo_ev;
                        $vis_2->save();
                        \Log::channel('soporte_log')->info($vis_2);
                        \Log::channel('soporte_log')->info($compa);
                    }
                    $temas_migrados= [
                        "original" => $visita->posteo_id,
                        "migrado" => $compa,
                    ];
                }
            }
        }
        return $temas_migrados;
    }
    private function update_evas_abiertas($ev_abierta,$user_id,$temas_corres,$compas){
        if(count($compas)>0){
            foreach ($compas as $compa) {
                if(in_array($compa,$temas_corres)){
                     //VERIFICAR SI YA TIENE UNA EVA_abierta ANTES DE MIGRAR
                    $v_pr = Ev_abierta::where('usuario_id',$user_id)->where('posteo_id',$compa)->first();
                    if(is_null($v_pr)){
                        $pmigra = Posteo::select('categoria_id', 'curso_id')->where('id', $compa)->first();
                        $ev_abierta->posteo_id = $compa;
                        $ev_abierta->curso_id = $pmigra->curso_id;
                        $ev_abierta->categoria_id = $pmigra->categoria_id;
                        $ev_abierta->save();
                    }
                }
            }
        }
    }
    private function update_diplomas($diploma,$user_id,$temas_corres,$compas){
        $temas_migrados = [];
        if(count($compas)>0){
            foreach ($compas as $compa) {
                if(in_array($compa,$temas_corres)){
                     //VERIFICAR SI YA TIENE UNA DIPLOMA ANTES DE MIGRAR
                     $v_pr = Diploma::where('usuario_id',$user_id)->where('curso_id',$pmigra->curso_id)->first();
                    if(is_null($v_pr)){
                        $pmigra = Posteo::select('categoria_id', 'curso_id')->where('id', $compa)->first();
                        $diploma->posteo_id = $compa;
                        $diploma->curso_id = $pmigra->curso_id;
                        $diploma->save();
                        $temas_migrados []= [
                            "original" => $diploma->posteo_id,
                            "migrado" => $compa
                        ];
                    }
                }
            }
        }
        return $temas_migrados;
    }
    private function update_reinicios($reinicio,$user_id,$temas_corres,$compas){
        if(count($compas)>0){
            foreach ($compas as $compa) {
                if(in_array($compa,$temas_corres)){
                     //VERIFICAR SI YA TIENE UNA DIPLOMA ANTES DE MIGRAR
                     $v_pr = Reinicio::where('usuario_id',$user_id)->where('posteo_id',$compa)->first();
                    if(is_null($v_pr)){
                        $pmigra = Posteo::select('categoria_id', 'curso_id')->where('id', $compa)->first();
                        $reinicio->posteo_id = $compa;
                        $reinicio->curso_id = $pmigra->curso_id;
                        $reinicio->save();
                    }
                }
            }
        }
    }
    public function help_cursos_x_todas_matricula($usuario_id)
    {
        // $usuario = Usuario_rest::where('id', $usuario_id)->first(['id', 'grupo']);
        $matriculas = Matricula::with([
            'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
            'curricula'=>function($q){$q->select('id','ciclo_id','carrera_id','curso_id','estado','all_criterios')->where('estado',1);},
            'curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1);},
            'curricula.curricula_criterio'=>function($q){$q->select('id','curricula_id','criterio_id');},
        ])->where('usuario_id',$usuario_id)->get();
        return $this->helper->get_cursos_matriculas_id($matriculas);
        // $result = collect();
        // $matriculas = DB::table('matricula AS m')
        //     ->where('m.usuario_id', $usuario->id)
        //     ->get(['ciclo_id', 'id', 'carrera_id']);
        // foreach ($matriculas as $matricula) {
        //     $matriculas_criterio = Matricula_criterio::select('criterio_id')->where('matricula_id', $matricula->id)->first();
        //     $criterio_id = $matriculas_criterio->criterio_id;
        //     $curriculas_criterios = Curricula_criterio::select('curricula_id')->where('criterio_id', $criterio_id)->get();
        //     foreach ($curriculas_criterios as $curricula_criterio) {
        //         $curricula = Curricula::join('cursos', 'cursos.id', 'curricula.curso_id')
        //             ->select('ciclo_id', 'curso_id')
        //             ->where('cursos.estado', 1)
        //             ->where('curricula.id', $curricula_criterio->curricula_id)->first();
        //         if (isset($curricula) && $curricula->ciclo_id == $matricula->ciclo_id) {
        //             $result->push($curricula->curso_id);
        //         }
        //     }
        // }
        // return $result->unique()->values()->all();
    }
}
