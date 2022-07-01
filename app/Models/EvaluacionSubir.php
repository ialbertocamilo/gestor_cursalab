<?php

namespace App\Models;

use App\Curso;
use App\Posteo;
use App\Abconfig;
use App\Pregunta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;


class EvaluacionSubir implements ToCollection
{
    private $errores = [],$q_inserts = 0,$q_errors=0;
    public $modulos,$modulo,$nom_curso,$nom_tema,$pregunta,$alt_1,$alt_2,$alt_3,$alt_4,$alt_5,$alt_6,$alt_7,$rpta_correcta,$estado;
    public $opt_rpta_correcta = [
        'ALTERNATIVA 1' =>1,
        'ALTERNATIVA 2' =>2,
        'ALTERNATIVA 3' =>3,
        'ALTERNATIVA 4' =>4,
        'ALTERNATIVA 5' =>5,
        'ALTERNATIVA 6' =>6,
        'ALTERNATIVA 7' =>7,
    ];
    public function collection(Collection $rows) {
        $total = count($rows);
        $this->modulos = Abconfig::select('id','etapa')->get();
        $r_datas = [];
        for ($i=2; $i < $total ; $i++) {
            $this->modulo = trim($rows[$i][0]); //MODULO
            $this->nom_curso = trim($rows[$i][1]); //CURSO
            $this->nom_tema = trim($rows[$i][2]); //TEMA
            $this->pregunta = trim($rows[$i][3]); //NOMBRE DE PREGUNTA
            $this->alt_1 = trim($rows[$i][4]); //ALTERNATIVA 1
            $this->alt_2 = trim($rows[$i][5]); //ALTERNATIVA 2
            $this->alt_3 = trim($rows[$i][6]); //ALTERNATIVA 3
            $this->alt_4 = trim($rows[$i][7]); //ALTERNATIVA 4
            $this->alt_5 = trim($rows[$i][8]); //ALTERNATIVA 5
            $this->alt_6 = trim($rows[$i][9]); //ALTERNATIVA 6
            $this->alt_7 = trim($rows[$i][10]); //ALTERNATIVA 7

            $this->rpta_correcta = trim($rows[$i][11]); //RESPUESTA CORRECTA
            $this->estado = trim($rows[$i][12]); //ESTADO
            //REVISAR DATA
            $r_data = $this->verificar_errores_y_r_data($rows[$i]);
            if(!($r_data['t_error'])){
                $this->crear_pregunta($r_data['data']);
                $r_datas [] = $r_data['data'];
                $this->q_inserts++;
            }
            $this->set_empty();
        }
        $this->insertar_errores();
    }
    private function insertar_errores(){
        $this->q_errors = count($this->errores);
        if($this->q_errors>0){
            $err_insert = $this->errores;
            foreach ($err_insert as $err) {
                DB::table('err_masivos')->insert([
                    'err_data'=> json_encode($err['data']),
                    'err_type' => json_encode($err['error']),
                    'type_masivo' => 'evaluaciones',
                    'err_data_original' =>json_encode($err['err_data_original'],JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }
    public function crear_pregunta($pregunta){
        Pregunta::insert($pregunta);
        $posteo = Posteo::where('id',$pregunta['post_id'])->first();
        $curso = Curso::where('id',$posteo->curso_id)->first();
        if(strtolower($posteo->evaluable)!='si' || $posteo->tipo_ev != 'calificada'){
            $posteo->evaluable = 'si';
            $posteo->tipo_ev = 'calificada';
            $posteo->save();
        }
        if(strtolower($curso->c_evaluable)!='si'){
            $curso->c_evaluable = 'si';
            $curso->save();
        }
    }
    private function set_empty(){
        $this->modulo='';$this->nom_curso='';$this->nom_tema='';$this->pregunta='';$this->alt_1='';$this->alt_2='';$this->alt_3='';$this->alt_4='';$this->alt_5='';$this->alt_6='';$this->alt_7='';$this->rpta_correcta='';$this->estado='';
    }
    public function verificar_errores_y_r_data($datos){
        $t_error = false;
        $error = [];
        $data = [];
        $data['post_id']=null;
        $data['estado']=null;
        $data['pregunta']=null;
        $data['rpta_ok']=null;
       
        //COMPROBAR EXISTENCIA Y/O DATA VACIA
        //MARCA - config - empresa 
        $mod_id = null;
        foreach ($this->modulos as $mod) {
            if(strtoupper($mod->etapa)==strtoupper($this->modulo)){
                $mod_id=$mod->id;
            }
        }
        if(empty($this->modulo)){ 
            $t_error = true;
            array_push($error,['tipo'=>'modulo_error','mensajes'=>'Modulo vacio']);
        }else{
            if(!$mod_id){
                $t_error = true;
                array_push($error,['tipo'=>'modulo_error','mensajes'=>'Modulo no existe']);
            }
        }
        //NOMBRE CURSO
        $cur_id = null;
        if(empty($this->nom_curso)){ 
            $t_error = true;
            array_push($error,['tipo'=>'curso_error','mensajes'=>'Curso vacio']);
        }else{
            $cur = Curso::select('id','categoria_id')->where('config_id',$mod_id)->where('nombre',$this->nom_curso)->first();
            if($cur){
                $cur_id=$cur->id;
            }else{
                $t_error = true;
                array_push($error,['tipo'=>'curso_error','mensajes'=>'Curso no existe']);
            }
        }
        //NOMBRE TEMA
        if(empty($this->nom_tema)){ 
            $t_error = true;
            array_push($error,['tipo'=>'tema_error','mensajes'=>'Empresa vacia']);
        }else{
            $posteo = Posteo::select('id','categoria_id')->where('curso_id',$cur_id)->where('nombre',$this->nom_tema)->first();
            if($posteo){
                $data['post_id']=$posteo->id;
            }else{
                $t_error = true;
                array_push($error,['tipo'=>'tema_error','mensajes'=>'Posteo no existe']);
            }
        }
        // ESTADO
        if(empty($this->estado)){ 
            $t_error = true;
            array_push($error,['tipo'=>'estado_error','mensajes'=>'Estado vacio']);
        }else{
            $data['estado']= ( strtoupper($this->estado) == 'ACTIVO')? 1 : 0 ;
        }
        //TIPO PREGUNTA
        $data['tipo_pregunta'] = 'selecciona';
        //PREGUNTA
        if(empty($this->pregunta)){ 
            $t_error = true;
            array_push($error,['tipo'=>'pregunta_error','mensajes'=>'Pregunta vacia']);
        }else{
            $data['pregunta']= $this->pregunta;
        }
        //RPTS JSON
        $respuestas = [];
        $j=1;
        if(!empty($this->alt_1)){ $respuestas[$j] = $this->alt_1; $j++;}
        if(!empty($this->alt_2)){ $respuestas[$j] = $this->alt_2;$j++;}
        if(!empty($this->alt_3)){ $respuestas[$j] = $this->alt_3;$j++;}
        if(!empty($this->alt_4)){ $respuestas[$j] = $this->alt_4;$j++;}
        if(!empty($this->alt_5)){ $respuestas[$j] = $this->alt_5;$j++;}
        if(!empty($this->alt_6)){ $respuestas[$j] = $this->alt_6;$j++;}
        if(!empty($this->alt_7)){ $respuestas[$j] = $this->alt_7;$j++;}

        $data['rptas_json'] = json_encode($respuestas,JSON_UNESCAPED_UNICODE);
        $data['ubicacion'] = 'despues';
        //RESPUESTA CORRECTA
        if(empty($this->rpta_correcta)){ 
            $t_error = true;
            array_push($error,['tipo'=>'respuesta_error','mensajes'=>'Respuesta vacia']);
        }else{
            $rpta_v= $this->opt_rpta_correcta[strtoupper($this->rpta_correcta)];
            if($rpta_v){
                $data['rpta_ok'] = $rpta_v;
            }else{
                $t_error = true;
                array_push($error,['tipo'=>'respuesta_error','mensajes'=>'Respuesta no existe']);
            }
        }
        if(count($error)>0){
            $this->errores[]=[
                'data' =>[
                    'modulo' => $this->modulo,
                    'config_id'=>$mod_id, 
                    'curso' => $this->nom_curso,
                    'curso_id'=>$cur_id, 
                    'tema'=>$this->nom_tema,
                    'post_id'=>$data['post_id'],
                    'estado' => $data['estado'],
                    'pregunta' => $data['pregunta'],
                    'respuesta'=>$this->rpta_correcta,
                    'rpta_ok'=>$data['rpta_ok'],
                    'rptas_json'=>$data['rptas_json'], 
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
