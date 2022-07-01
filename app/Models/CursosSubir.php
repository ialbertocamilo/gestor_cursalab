<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;

class CursosSubir implements ToCollection
{
    private $errores = [],$q_inserts = 0,$q_errors=0;
    private $modulo,$escuela,$nombre,$estado,$t_error,$imagen;
    private $modulos;
    private $tipos_estados;

    // $requisito,
    public function collection(Collection $rows) {
        $this->tipos_estados = array_column(config('constantes.estado.select'),'nombre');
        $total = count($rows);
        $errores = [];
        $this->modulos = Abconfig::with(['categorias'=>function($q){
            $q->select('id','config_id','nombre');
        }])->select('id','etapa')->get();
        //I=3 por que los datos del excel comienzan en la fila 4
        for ($i=1; $i < $total ; $i++) {
            //SETEAR VALORES
            $this->modulo = trim($rows[$i][0]); //MODULO
            $this->escuela = trim($rows[$i][1]); //NOMBRE DE LA ESCUELA
            $this->nombre = trim($rows[$i][2]); //NOMBRE DEL CURSO
            // $this->requisito = trim($rows[$i][3]);//REQUISITO DEL CURSO
            $this->estado = trim($rows[$i][3]);//ESTADO
            $this->imagen = trim($rows[$i][4]);//ESTADO
            $r_data = $this->verificar_errores_y_r_data($rows[$i]);
            if(!($r_data['t_error'])){
                $this->crear_curso($r_data['data']);
                $this->q_inserts++;
            }
            $this->set_empty();
        }
        $this->q_errors = count($this->errores);
        if($this->q_errors>0){
            $err_insert = $this->errores;
            foreach ($err_insert as $err) {
                DB::table('err_masivos')->insert([
                    'err_data'=> json_encode($err['data']),
                    'err_type' => json_encode($err['error']),
                    'err_data_original' =>json_encode($err['err_data_original'],JSON_UNESCAPED_UNICODE),
                    'type_masivo' => 'cursos'
                ]);
            }
        }
    }
    public function crear_curso($curso){
        Curso::create($curso);
    }
    private function set_empty(){
        $this->modulo='';$this->escuela='';$this->nombre='';$this->orden='';$this->estado='';$this->t_error='';$this->imagen='';
        // $this->requisito='';
    }
    private function verificar_errores_y_r_data($datos){
        $t_error = false;
        $error = [];
        //COMPROBAR EXISTENCIA Y/O DATA VACIA
        //MARCA - config - empresa 
        $data = [];
        $data['config_id']=null;
        $data['categoria_id']=null;
        $data['nombre']=null;
        $data['estado']='';
        $data['imagen']=$this->imagen;
        foreach ($this->modulos as $mod) {
            if(strtoupper($mod->etapa)==strtoupper($this->modulo)){
                $data['config_id']=$mod->id;
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
            if(!isset($data['config_id'])){
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
        //NOMBRE DEL CURSO
        if(empty($this->nombre)){ 
            $t_error = true;
            array_push($error,['tipo'=>'nombre_error','mensajes'=>'Nombre del curso vacio']);
        }else{
            $data['nombre']=$this->nombre;
        }
        //ESTADO
        $existe_estado = in_array(ucfirst($this->estado),$this->tipos_estados);
        if($existe_estado){ 
            $data['estado']= ( strtoupper($this->estado) == 'ACTIVO')? 1 : 0 ;
        }else{
            $t_error = true;
            array_push($error,['tipo'=>'estado_error','mensajes'=>'Estado no encontrado']);
        }
        // //REQUISITO
        // if(empty($this->requisito)){ 
        //     $data['requisito_id']=null;
        // }else{
        //     if(strtoupper($this->requisito)=='NO TIENE'){
        //         $data['requisito_id']=null;
        //     }else{
        //         $req_id = Curso::where('nombre',$this->requisito)->select('id')->orderBy('created_at', 'desc')->first();
        //         if($req_id){
        //             $data['requisito_id']=$req_id->id;
        //         }else{
        //             array_push($error,['tipo'=>'req_ERROR','mensajes'=>'Requisito no encontrado']);
        //         }
        //     }
        // }
        //EVALUABLE
        $data['c_evaluable']='no';
        //IMAGEN
        $data['imagen']= (empty($this->imagen)) ? '' : 'images/'.$this->imagen;
        // if(empty($this->evaluable)){ 
        //     $t_error = true;
        //     array_push($error,['tipo'=>'eval_vacio','mensajes'=>'Evaluable vacio']);
        // }else{
        //     if(strtoupper($this->evaluable)=='SI'){
        //         $data['c_evaluable']='si';
        //     }else{
        //         $data['c_evaluable']='no';
        //     }
        // }
        //ORDEN
        if(isset($data['categoria_id'])){
            $orden = Curso::where('categoria_id',$data['categoria_id'])->max('orden') ;
            $data['orden']=(isset($orden)) ? $orden+1 : 1;
        }
        if(count($error)>0){
            $this->errores[]=[
                'data' =>[
                    'modulo' => $this->modulo,
                    'config_id' => $data['config_id'],
                    'escuela'=> $this->escuela,
                    'categoria_id'=> $data['categoria_id'],
                    'nombre'=> $data['nombre'],
                    'estado'=> $this->estado,
                    'imagen' => $data['imagen'],
                    'c_evaluable' => 'no',
                ],
                'error' => $error,
                'err_data_original'=>$datos,
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