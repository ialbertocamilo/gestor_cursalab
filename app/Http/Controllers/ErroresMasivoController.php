<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Posteo;
use App\Models\Carrera;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Matricula;
use App\Models\TemasSubir;
use App\Models\CursosSubir;
use App\Models\ErroresMasivo;
use App\Models\CambiosMasivos;
use App\Models\EvaluacionSubir;
use App\Models\Resumen_x_curso;
use App\Models\UsuariosMasivos;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Exports\ExportReporteError;
use App\Exports\ExportReporteErrores;

use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\MasivoController;
use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class ErroresMasivoController extends Controller
{
    private $rest_avance;
    private $helper;
    public function get_errores($tipo){
        $err_masivos =new ErroresMasivo();
        $headers =$err_masivos->get_header($tipo);
        $errors_g = DB::table('err_masivos')->where('type_masivo',$tipo)->get();
        $errors = $this->o_errores($headers,$errors_g);
        $multi_page=[];
        if($tipo=='cursos' || $tipo=='temas' || $tipo=='evaluaciones'){
            $multi_page=[
                ['value'=>'cursos'],
                ['value'=>'temas'],
                // ['value'=>'evaluaciones'],
            ];
        }
        return response()->json(compact('headers','errors','multi_page'),200);
    }
    public function o_errores($headers,$errors){
        $data=[];
        foreach ($errors as $err) {
            $err_type = json_decode($err->err_type);
            $err_data = json_decode($err->err_data);
            $error=[];
            $id_e =1;
            foreach ($headers as $header) {
                $err_s = array_search(strtolower($header['text'].'_error'),array_column($err_type,'tipo'));
                // if($header['text']=='Módulo'){
                //     dd(strtolower($header['text'].'_error'),$err_s,array_column($err_type,'tipo'));
                // }
                if($err_s!== false){
                    $extra = ["data"=>'','item_text'=>''];
                    if($header['data_extra']){
                        if($header['data_extra']=='table'){
                            $extra = $this->get_data_extra_for_select($header,$err_data);
                        }else{
                            $global= config('constantes.'.strtolower($header['text']));
                            $extra['item_text']=$global['item_text'];
                            $extra['data']=$global['select'];
                        }
                    }
                    $error[]=[
                        'id_e' => $id_e,
                        'error'=>true,
                        'data' => $err_type[$err_s],
                        'data_extra'=>$extra['data'],
                        'item_text' => $extra['item_text'],
                        'tipo' => $header['tipo'],
                        'change_select' => $header['change_select'],
                        'atributo' => $header['atributo'],
                        'atr_change' => $header['atr_change'],
                        'rules' => $header['rules'],
                        'extra_rules' => $header['extra_rules'],
                        'message_rule'=>$header['message_rule'],
                        'table_info'=>$header['table_info'],
                        'value_model' =>null,
                        'could_be_empty'=>$header['could_be_empty'],
                    ];
                }else{
                    $error[]=[
                        'id_e' => $id_e,
                        'error'=>false,
                        'data' =>['mensajes'=>''],
                        'data_extra'=>false,
                        'item_text'=>'',
                        'tipo' => $header['tipo'],
                        'change_select'=>$header['change_select'],
                        'atributo' => $header['atributo'],
                        'atr_change' => $header['atr_change'],
                        'rules' => $header['rules'],
                        'extra_rules' => $header['extra_rules'],
                        'message_rule'=>$header['message_rule'],
                        'table_info'=>$header['table_info'],
                        'value_model' =>null,
                        'could_be_empty'=>$header['could_be_empty'],
                    ];
                }
                $id_e ++;
            }
            $data[] = [
                'id' => $err->id,
                'error' => $error,
                'err_data' => $err_data
            ];
        }
        return $data;
    
    }
    public function get_change(Request $request){
        $data = $request->all();
        $err_data = $data['err_data'];
        $tables =$data['table_info']['table_dep']; 
        $return=[];
        foreach ($tables as $table) {
            $search = DB::table($table['name']);
            $ver=true;
            if(isset($table['not_verify_if'])){
                foreach ($table['not_verify_if'] as $verify) {
                    if($err_data[$verify['attr']] == $verify['value'] ){
                        $ver = false;
                    }
                }
            }
            if($ver){
                foreach ($table['data'] as $value) {
                    $search = $search->where($value['value'],$err_data[$value['bind']]);
                }
                $ret = $search->first();
                if($ret){
                    $err_data[$table['atributo']]=$ret->id;
                    $return[]=[
                        'atributo'=>$table['atributo'],
                        'error' =>false,  
                        'data_extra'=>false,
                        'atributo_id'=>$ret->id,
                    ];
                }else{
                    $search_2 = DB::table($table['name']);
                    foreach ($table['data'] as $value) {
                        if($value['data_extra']){
                            $search_2 = $search_2->where($value['value'],$err_data[$value['bind']]);
                        }
                    }
                    $ret = $search_2->get();
                    $d_extra = (count($ret)>0) ? $ret : [] ;
                    $return[]=[
                        'atributo'=>$table['atributo'],
                        'error' =>true,  
                        'item_text' => $table['item_text'],
                        'mensajes' => $table['mensajes'],
                        'data_extra'=>$d_extra,
                        'atributo_id'=>false,
                    ];
                }
            }
        }
        return response()->json($return,200);
    }
    private function get_data_extra_for_select($header, $err_data){
        $extra['data'] = [];
        $extra['item_text'] = '';
        $tables =$header['table_info']['table']['deps'];
        $err_data = json_decode(json_encode($err_data), true);
        $search = DB::table($header['table_info']['table']['name']);
        foreach ($tables as $table) {
            $search = $search->where($table['value'],$err_data[$table['bind']]);
        }
        $extra['data'] = $search->get();
        $extra['item_text'] = $header['table_info']['table']['item_text'];
        return $extra;
    }

    //Eliminar varios
    public function eliminar_errores(Request $request){
        $data = $request->all();
        foreach ($data['items'] as $value) {
            DB::table('err_masivos')->delete($value['id']);
        }
    }
    //eliminar errores uno x uno 
    public function eliminar_error($id){
        $de_err =DB::table('err_masivos')->delete($id);
        if($de_err){
            return response()->json('ok',200);
        }
        return response()->json('error',500);
    }

    public function guardar_data(Request $request){
        $errores = $request->get('items');
        $errors = []; //DATA SUBIDA INCORRECTAMENTE
        $success = [];
        $this->rest_avance = new RestAvanceController();
        $this->helper = new HelperController();
        foreach ($errores as $error) {
            $err_data = $error['err_data'];
            $err = array_column($error['error'],'atributo');
            //VERIFICAR REGLAS
            $validado = true;
            $ret_err=[];
            foreach ($err_data as $key => $data) {
                $s_rules = array_search($key,$err);
                if($s_rules){
                    //VERIFICAR SI EXISTEN REGLAS
                    if(isset($error['error'][$s_rules]['rules'][0])){
                        $rules = $error['error'][$s_rules]['rules'][0];
                        $messages =  $error['error'][$s_rules]['message_rule'];
                        $extra_rules = $error['error'][$s_rules]['extra_rules']; 
                        //APARTADO UNICO PARA CREACIÓN y ACTUALIZACIÓN DE USUARIOS.
                        if($extra_rules){
                            $accion = $error['err_data']['accion'];
                            if($accion=='Nuevo'){
                                foreach ($extra_rules as $value) {
                                    $rules = $rules.'|'.$value;
                                }
                            }
                        }
                            $val = Validator::make([$key=>$data], [
                                $key => $rules,
                            ],$messages);
                        if ($val->fails()){
                            $validado = false;
                            $ret_err[]=[
                                'id' => $error['id'],
                                'id_e'=>$error['error'][$s_rules]['id_e'],
                                'mensaje' => $val->errors()->first(),
                            ];
                        }
                    }
                }
            }
            if($validado){
                $guardar=$this->guardar_data_tipo($err_data,$request->get('tipo'),$error['id']);
                \Log::channel('soporte_log')->info($guardar);
                if($guardar['success']){
                    $success[] = $error['id'];
                }else{
                    $idx = array_search($guardar['atr'],$err);
                    $ret_err[]=[
                        'id' => $error['id'],
                        'id_e'=>$error['error'][$idx]['id_e'],
                        'mensaje' => $guardar['msg'],
                    ];
                    $errors[] = ['msg'=>$ret_err];
                }
            }else{
                $errors[] = ['msg'=>$ret_err];
            }
        }
        if(count($errors)>0){
            return response()->json(compact('errors','success'),500);
        }else{
            return response()->json(compact('success'),200);
        }
    }
    public function guardar_data_tipo($data,$tipo,$error_id){
        $success=true;
        $msg='';
        $atr='';
        // $helper = new HelperController();
        switch ($tipo) {
            case 'usuarios':
                $data['password'] = Hash::make($data['dni']);
                $data['codigo_matricula'] = Abconfig::where('id',$data['config_id'])->select('codigo_matricula')->pluck('codigo_matricula')->first();
                $data['secuencia'] = Ciclo::where('id',$data['ciclo_id'])->select('secuencia')->pluck('secuencia')->first();
                $data['grupo_nombre'] = Criterio::where('id',$data['grupo_id'])->select('valor')->pluck('valor')->first();
                if($data['codigo_matricula'] && !is_null($data['secuencia'])  && $data['grupo_nombre']){
                    $new_user_masivo = new UsuariosMasivos();
                    switch($data['accion']) {
                        case 'Nuevo':
                            $ciclos = Ciclo::all();
                            $grupos = Grupo::all();
                            $date_code = date('dmy');
                            $user_id =  $new_user_masivo->crear_nuevo_usuario($data,$ciclos,$grupos,$date_code);
                            $curso_ids_matricula = $this->helper->help_cursos_x_matricula($user_id);
                            //LIMPIAR TABLAS RESUMENES
                            Resumen_x_curso::where('usuario_id',$user_id)->whereNotIn('curso_id',$curso_ids_matricula)->update(['estado_rxc'=>0]);
                            // Resumen_general::where('usuario_id',$user_id)->delete();
                            //ACTUALIZAR TABLAS RESUMENES
                            $ab_conf = Abconfig::select('mod_evaluaciones')->where('id',$data['config_id'])->first(['mod_evaluaciones']);
                            $mod_eval = json_decode($ab_conf->mod_evaluaciones, true);
                            foreach ($curso_ids_matricula as $cur_id) {
                                // ACTUALIZAR RESUMENES
                                $this->rest_avance->actualizar_resumen_x_curso($user_id,$cur_id, $mod_eval['nro_intentos']);
                            }
                            $this->rest_avance->actualizar_resumen_general($user_id);
                            $this->eliminar_error($error_id);
                        break;
                        case 'datos':
                            $user = Usuario::where('dni',$data['dni'])->first();
                            if($user){
                                $new_user_masivo->actualizar_usuario($data,$user);
                                $this->eliminar_error($error_id);
                            }else{
                                $success=false;
                                $msg='DNI no existe';
                                $atr='dni';
                            }
                        break;
                   }
                }
                break;
            case 'cesados':
                $user = Usuario::where('dni',$data['dni'])->first();
                if($user){
                    $user->estado=1;
                    $user->save();
                    $this->eliminar_error($error_id);
                }else{
                    $success=false;
                    $msg='DNI no existe';
                    $atr='dni';
                }
            break;
            case 'activos':
                $user = Usuario::where('dni',$data['dni'])->first();
                if($user){
                    $user->estado=1;
                    $user->save();
                    $this->eliminar_error($error_id);
                }else{
                    $success=false;
                    $msg='DNI no existe';
                    $atr='dni';
                }
            break;
            case 'cursos':
                $cur = new CursosSubir();
                $orden = Curso::where('categoria_id',$data['categoria_id'])->max('orden') ;
                $data['orden']=(isset($orden)) ? $orden+1 : 1;
                $data['estado']= ( strtoupper($data['estado']) == 'ACTIVO')? 1 : 0 ;
                $unset_elements=['modulo','escuela'];
                if(empty($data['imagen']) || is_null($data['imagen'])) array_push($unset_elements,'imagen'); 
                $data = $this->unset_elements($data,$unset_elements);
                $cur->crear_curso($data);
                $this->eliminar_error($error_id);
            break;
            case 'temas':
                $tem = new TemasSubir();
                $orden = Posteo::where('curso_id',$data['curso_id'])->max('orden') ;
                $data['orden']=(isset($orden)) ? $orden+1 : 1;
                $data['media']= '[]';
                $data['estado']= ( strtoupper($data['estado']) == 'ACTIVO')? 1 : 0 ;
                $unset_elements = ['modulo','config_id','curso','escuela','requisito'];
                if(empty($data['imagen']) || is_null($data['imagen'])) array_push($unset_elements,'imagen'); 
                $data = $this->unset_elements($data,$unset_elements);
                $tem->crear_tema($data);
                $this->eliminar_error($error_id);
            break;
            case 'evaluaciones':
                $ev = new EvaluacionSubir();
                $opt_rpta_correcta = [
                    'ALTERNATIVA 1' =>1,
                    'ALTERNATIVA 2' =>2,
                    'ALTERNATIVA 3' =>3,
                    'ALTERNATIVA 4' =>4,
                    'ALTERNATIVA 5' =>5,
                    'ALTERNATIVA 6' =>6,
                    'ALTERNATIVA 7' =>7,
                ];
                if(isset($opt_rpta_correcta[strtoupper($data['respuesta'])])){
                    $data['rpta_ok'] = $opt_rpta_correcta[strtoupper($data ['respuesta'])];
                    $data = $this->unset_elements($data,['modulo','config_id','curso','curso_id','tema','respuesta']);
                    $ev->crear_pregunta($data);
                    $this->eliminar_error($error_id);
                }else{
                    $success=false;
                    $msg='Respuesta no encontrada';
                    $atr='respuesta';
                }
            break;
            case 'ciclos_carreras':
                $cambios_masivos = new CambiosMasivos();
                $grupo = Criterio::where('id',$data['grupo_id'])->select('valor')->first();
                $data['grupo_nombre'] = ($grupo) ? $grupo->valor : '' ;
                $data['secuencia'] = Ciclo::where('id',$data['ciclo_id'])->select('secuencia')->pluck('secuencia')->first();
                $usuario = Usuario::where('dni',$data['dni'])->select('id','config_id')->first();
                $data['usuario_id'] =  $usuario->id;
                $data['grupo'] = $data['grupo_id']; 
                $data['mod_evaluaciones'] = Abconfig::where('id',$data['config_id'])->select('mod_evaluaciones')->pluck('mod_evaluaciones')->first();
                $matricula_actual = Matricula::where('usuario_id',$data['usuario_id'])->where('presente',1)->where('estado',1)->first(['ciclo_id','carrera_id']);
                $exl_tipo = 'Actualizar datos';
                if($matricula_actual->ciclo_id != $data['ciclo_id']){
                    $exl_tipo = 'Cambio de ciclo';
                }
                if($matricula_actual->carrera_id != $data['carrera_id']){
                    $exl_tipo = 'Cambio de carrera';
                }
                if($usuario->config_id!=$data['config_id']){
                    $exl_tipo = 'Cambio de módulo';
                }
                switch($exl_tipo) {
                    case 'Cambio de ciclo':
                        $cambios_masivos->cambiar_ciclo($data);
                        $cambios_masivos->actualizar_usuario($data);
                        break;
                    case 'Cambio de carrera':
                        $cambios_masivos->cambiar_carrera($data);
                        $cambios_masivos->actualizar_usuario($data);
                    break;
                    case 'Cambio de módulo':
                        $cambios_masivos->cambiar_carrera($data);
                        $cambios_masivos->actualizar_usuario($data);
                    break;
                    case 'Actualizar datos':
                        $cambios_masivos->actualizar_usuario($data);
                    break;
                }
                DB::table('update_usuarios')->insert([
                    'usuarios_id' => json_encode([$data['usuario_id']]),
                    'tipo' => 'update_resumenes_from_masivo',
                ]);
                $this->eliminar_error($error_id);
            break;
        }
        return ['success'=>$success,'msg'=>$msg,'atr'=>$atr];
    }
    private function unset_elements($data,$u_data){
        foreach ($u_data as $u) { unset($data[$u]) ; }
        return $data;
    }
    public function fix_err_cic_carr(Request $request){
        $data = $request->all();
        $errors = []; //DATA SUBIDA INCORRECTAMENTE
        $success = [];
        foreach ($data['items'] as $value) {
            $usuario = [
                'config_id' => $value['err_data']['modulo_id'],
                'dni' => $value['err_data']['dni'], 
                'carrera_id' => $value['err_data']['carrera_id'],
                'ciclo_id' => $value['err_data']['ciclo_id'],
            ];
            $dni_length = strlen($usuario['dni']);
            if($dni_length>'8'){
                    $b_user = Usuario::where('dni',$usuario['dni'])->first(['id','grupo']);
                    //sacar secuencia y push_code
                    $usuario_masivo = new MasivoController();
                    switch ($value['err_data']['accion']) {
                        case 'ciclo':
                            if($b_user){
                                $usuario['usuario_id']=$b_user->id;
                                $usuario['grupo']=$b_user->grupo;
                                $usuario['secuencia'] = Ciclo::where('id',$usuario['ciclo_id'])->select('secuencia')->pluck('secuencia')->first();
                                $usuario['mod_evaluaciones'] = Abconfig::where('id',$usuario['config_id'])->select('mod_evaluaciones')->pluck('mod_evaluaciones')->first();
                                $usuario_masivo->cambiar_ciclo($usuario);
                                $this->eliminar_error($value['id']);
                                $success[] = $value['id'];
                            }else{
                                $errors[] = ['msg'=>'El usuario con dni: '.$usuario['dni'].' no se puede cambiar de ciclo, por que no se encuentra registrado'];
                            }
                        break;
                        case 'carrera':
                            if($b_user){
                                $usuario['usuario_id']=$b_user->id;
                                $usuario['grupo']=$b_user->grupo;
                                $usuario['secuencia'] = Ciclo::where('id',$value['err_data']['ciclo_id'])->select('secuencia')->pluck('secuencia')->first();
                                $usuario['mod_evaluaciones'] = Abconfig::where('id',$usuario['config_id'])->select('mod_evaluaciones')->pluck('mod_evaluaciones')->first();
                                $usuario_masivo->cambiar_carrera($usuario);
                                $this->eliminar_error($value['id']);
                                $success[] = $value['id'];
                            }else{
                                $errors[] = ['msg'=>'El usuario con dni: '.$usuario['dni'].' no se puede cambiar de carrera, por que no se encuentra registrado'];
                            }
                        break;
                    }
                    
            }else{
                $errors[] = ['msg'=>'El dni: '.$usuario['dni'].' tiene '.$dni_length.' caracteres'];
            }
        }
        if(count($errors)>0){
            return response()->json(compact('errors','success'),500);
        }else{
            return response()->json(['success'=>$success],200);
        }
    }

    public function reporte_errores($tipo){
        if($tipo=='curs_tema_eva'){
            $obj=new ExportReporteErrores();
            $obj->tipo=$tipo;
            return ($obj)->download('reporte-general.xlsx');
        }else{
            $obj=new ExportReporteError();
            $obj->tipo = $tipo;
            $obj->view();
            $date = Carbon::now()->format('Y-m-d-H-i');
            $name_excel = 'reporte_'.$tipo.'_'.$date.'.xlsx';
            ob_end_clean();
            ob_start();
            return Excel::download($obj,$name_excel);
        }
    }
}