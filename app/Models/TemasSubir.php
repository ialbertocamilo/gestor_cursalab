<?php

namespace App\Models;


use App\Models\Posteo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;

class TemasSubir implements ToCollection
{
    private $errores = [],$q_inserts = 0,$q_errors=0;
    private $modulos,$modulo,$escuela, $nom_curso,$nom_tema,$requisito,$evaluable,$estado,$contenido,$imagen;
    private $tipos_estados;
     // $tipo_ev;
    public function collection(Collection $rows)
    {
        $this->tipos_estados = array_column(config('constantes.estado.select'),'nombre');
        //I=3 por que los datos del excel comienzan en la fila 4
        $total = count($rows);
        Log::info('Inicio Subida de temas');
        
        $this->modulos = Abconfig::with(['categorias'=>function($q){
            $q->select('id','config_id','nombre');
        }])->select('id','etapa')->get();

        for ($i=1; $i < $total ; $i++) {
            //SETEAR VALORES
            $this->modulo = trim($rows[$i][0]); //MODULO
            $this->escuela = trim($rows[$i][1]); //NOMBRE DE LA ESCUELA
            $this->nom_curso = trim($rows[$i][2]); //CURSO
            $this->nom_tema = trim($rows[$i][3]); //TEMA
            $this->requisito = trim($rows[$i][4]); //REQUISITO DEL TEMA
            $this->contenido = trim($rows[$i][5]); //DESCRIPCION DEL TEMA
            $this->estado = trim($rows[$i][6]); //ESTADO
            $this->imagen = trim($rows[$i][7]);
            // $this->tipo_ev = trim($rows[$i][5]); //TIPO DE EVALUACION
            // $this->tipo_cal = trim($rows[$i][7]); //TIPO DE CALIFICACÓN
            // $this->cal_min = trim($rows[$i][5]); //CALIFICACIÓN MINIMA
            if(!empty($this->modulo)){
                $r_data = $this->verificar_errores_y_r_data($rows[$i]);
                if(!($r_data['t_error'])){
                    $this->crear_tema($r_data['data']);
                    $this->q_inserts++;
                }
            }
            $this->set_empty();
        }
        $this->q_errors = count($this->errores);
        Log::info('Termina Subida de temas');
        if($this->q_errors>0){
            $err_insert = $this->errores;
            foreach ($err_insert as $err) {
                DB::table('err_masivos')->insert([
                    'err_data'=> json_encode($err['data']),
                    'err_type' => json_encode($err['error']),
                    'type_masivo' => 'temas',
                    'err_data_original' =>json_encode($err['err_data_original'],JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }
    public function crear_tema($posteo)
    {
        $posteo = Posteo::insert($posteo);
    }

    private function set_empty()
    {
        $this->modulo='';$this->escuela='';$this->nom_curso = '';$this->nom_tema = '';$this->requisito = '';$this->contenido='';$this->estado = '';$this->imagen='';
    }

    private function verificar_errores_y_r_data($datos){
        $t_error = false;
        $error = [];
        $data = [];
        //COMPROBAR EXISTENCIA Y/O DATA VACIA
        //MARCA - config - empresa 
        $data = [];
        $data['categoria_id']=null;
        $data['curso_id']=null;
        $data['nombre']=null;
        $data['requisito_id']=null;
        $data['resumen']=null;
        $data['estado']=null;
        $mod_id = null;
        foreach ($this->modulos as $mod) {
            if(strtoupper($mod->etapa)==strtoupper($this->modulo)){
                $mod_id=$mod->id;
                foreach ($mod->categorias as $cat) {
                    if(strtoupper($cat->nombre)==strtoupper($this->escuela)){
                        $data['categoria_id']=$cat->id;
                    }
                } 
            }
        } 
        if(empty($this->modulo)){ 
            $t_error = true;
            array_push($error,['tipo'=>'módulo_error','mensajes'=>'Modulo vacio']);
        }else{
            if(!$mod_id){
                $t_error = true;
                array_push($error,['tipo'=>'módulo_error','mensajes'=>'Modulo no existe']);
            }
        }
        //NOMBRE DE LA ESCUELA
        if(empty($this->escuela)){ 
            $t_error = true;
            array_push($error,['tipo'=>'escuela_error','mensajes'=>'Escuela vacio']);
        }else{
            //VERIFICAR SI EXISTE LA ESCUELA
            if(!isset($data['categoria_id'])){
                $t_error = true;
                array_push($error,['tipo'=>'escuela_error','mensajes'=>'Escuela no existe']);
            }
        }
        //COMPROBAR EXISTENCIA Y/O DATA VACIA
        $cur = Curso::select('id','categoria_id','c_evaluable')->where('config_id',$mod_id)->where('categoria_id',$data['categoria_id'])->where('nombre',strtoupper($this->nom_curso))->first();
        //NOMBRE CURSO
        if(empty($this->nom_curso)){ 
            $t_error = true;
            array_push($error,['tipo'=>'curso_error','mensajes'=>'Curso vacio']);
        }else{
            if($cur){
                $data['curso_id']=$cur->id;
            }else{
                $t_error = true;
                array_push($error,['tipo'=>'curso_error','mensajes'=>'Curso no existe']);
            }
        }
        //NOMBRE TEMA
        if(empty($this->nom_tema)){ 
            $t_error = true;
            array_push($error,['tipo'=>'nombre_error','mensajes'=>'Nombre del tema vacio']);
        }else{
            $data['nombre']=$this->nom_tema;
        }
        //REQUISITO TEMA
        if(empty($this->requisito)){ 
            $data['requisito_id']=null;
        }else{
            if(strtoupper($this->requisito)=='NO TIENE'){
                $data['requisito_id']=null;
            }else{
                if(isset($cur->id)){
                    $req_id = Posteo::where('nombre',$this->requisito)->where('curso_id',$cur->id)->select('id')->orderBy('created_at', 'desc')->first();
                    if($req_id){
                        $data['requisito_id']=$req_id->id;
                    }else{
                        array_push($error,['tipo'=>'requisito_ERROR','mensajes'=>'Requisito no encontrado']);
                    }
                }else{
                    $data['requisito_id']=null;
                }
            }
        }
        //EVALUABLE
        $data['evaluable']='no';
        $data['contenido']=$this->contenido;
        //DESCRIPCION
        // if(empty($this->contenido)){
        //     $data['resumen']=null;            
        // }else{
        //     $data['resumen']=$this->contenido;
        // }
        //IMAGEN
        // if(empty($this->imagen)){
        //     $data['imagen']=null;
        // }else{
        //     $data['imagen']=$this->imagen;
        // }
        // if(empty($this->evaluable)){ 
        //     $t_error = true;
        //     array_push($error,['tipo'=>'eval_vacio','mensajes'=>'Evaluable vacio']);
        // }else{
        //     if(strtoupper($this->evaluable)=='SI'){
        //         $data['evaluable']='si';
        //         //CONVERTIR EL CURSO EVALUABLE
        //         if($cur){
        //             $cur->c_evaluable = 'si';
        //         }
        //     }else{
        //         $data['evaluable']='no';
        //     }
        // }
        //TIPO DE EVALUACION
        // if(empty($this->tipo_ev)){
        //     $t_error = true;
        //     array_push($error,['tipo'=>'t_ev_error','mensajes'=>'Tipo evaluación vacio']);
        // }else{
        //     $data['tipo_ev']= strtolower($this->tipo_ev);
        // }
        // ESTADO
        $existe_estado = in_array(ucfirst($this->estado),$this->tipos_estados);
        if($existe_estado){ 
            $data['estado']= ( strtoupper($this->estado) == 'ACTIVO')? 1 : 0 ;
        }else{
            $t_error = true;
            array_push($error,['tipo'=>'estado_error','mensajes'=>'Estado no encontrado']);
        }
        $data['imagen']= (empty($this->imagen)) ? '' : 'images/'.$this->imagen;
        //MEDIA SE GUARDA ARRAY
        $data['media']= '[]';
        //TIPO DE CALIFICACIÓN
        // if(empty($this->tipo_cal)){ 
        //     $t_error = true;
        //     array_push($error,['tipo'=>'cal_err','mensajes'=>'Tipo calificación vacio']);
        // }else if($data['tipo_ev']!='abierta'){
        //     $arr_tipo_cal = config('constantes.input_tipo_cal');
        //     $idx = array_search($this->tipo_cal,array_column($arr_tipo_cal, 'nombre'));
        //     $s_ev_base =$arr_tipo_cal[$idx]['value'];
        //     if($s_ev_base){
        //         $data['tipo_cal']= 'CAL'.$s_ev_base;
        //     }else {
        //         $t_error = true;
        //         array_push($error,['tipo'=>'cal_err','mensajes'=>'Tipo calificación no existe']);
        //     }
        // }
        if(isset($data['curso_id'])){
            $orden = Posteo::where('curso_id',$data['curso_id'])->max('orden') ;
            $data['orden']=(isset($orden)) ? $orden+1 : 1;
        }
        if(count($error)>0){
            $this->errores[]=[
                'data' =>[
                    'modulo' => $this->modulo,
                    'config_id'=>$mod_id, 
                    'escuela' => $this->escuela,
                    'categoria_id'=>$data['categoria_id'], 
                    'curso' => $this->nom_curso,
                    'curso_id'=>$data['curso_id'], 
                    'nombre'=>$data['nombre'], 
                    'requisito'=>$this->requisito, 
                    'requisito_id'=>$data['requisito_id'], 
                    'resumen'=>$data['resumen'], 
                    'estado'=> $this->estado,
                    'imagen' => $data['imagen'],
                ],
                'error' => $error,
                'err_data_original' =>$datos,
            ]; 
        }
        $r_data=[
            't_error'=>$t_error,
            'data' => $data,
        ];
        return $r_data;
    }
    public function get_q_inserts(){
        return $this->q_inserts;
    }
    public function get_q_errors(){
        return count($this->errores);
    }
}