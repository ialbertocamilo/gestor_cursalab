<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Abconfig;
use App\Models\Pregunta;
use App\Models\Categoria;
use App\Models\MediaTema;
use App\Models\Curso_encuesta;

use Illuminate\Http\Request;

class DuplicarController extends Controller
{
    public function get_data($tipo,$id){
        $data;
        switch ($tipo) {
            case 'categorias':
                //id->id_categoria
                $data =  $this->get_data_categ($id);
            break;
            case 'cursos': 
                //id->id_cursos
                $data = $this->get_data_curs($id);
            break;
        }
        return response()->json(compact('data'),200);
    }
    public function save_copy(Request $request){
        $data = $request->all();
        $duplicados;
        switch ($data['tipo']) {
            case 'categorias':
                $this->save_copy_escuelas($data);
            break;
            case 'cursos':
                $duplicados = $this->save_copy_cursos($data);
            break;
        }
        return response()->json(['result'=>true,'data'=>$duplicados],200);
    }
    /******************************************SUBFUNCIONES******************************************* */
    private function get_data_categ($config_id){
        $categorias = Categoria::with(['cursos'=>function($q){
            $q->select('id','nombre as name','categoria_id')->orderBy('orden');
        },'cursos.temas'=>function($q){
            $q->select('id','nombre as name','curso_id','tipo_ev')->orderBy('orden');
        }])->where('config_id',$config_id)->select('nombre as name','id')->get();
        $categorias = $this->format_data_categorias($categorias);
        $configs = Abconfig::where('id','<>',$config_id)->select('id','etapa as name')->get();
        return compact('categorias','configs');
    }

    private function get_data_curs($categoria_id){
        $cursos = Curso::with(['temas'=>function($q){
            $q->select('id','nombre as name','curso_id','tipo_ev')->orderBy('orden');
        }])->select('id','nombre as name','categoria_id')->where('categoria_id',$categoria_id)->orderBy('orden')->get();
        $cursos = $this->format_data_cursos($cursos);
        
        $configs_s = Abconfig::with(['categorias'=>function($q) use($categoria_id){
            $q->select('nombre as name','id','config_id');
        }])->select('id','etapa as name')->get();
        $configs = [];
        foreach ($configs_s as $config) {
            $config->new_id = 'conf:'.$config->id;
            $categorias = $config->categorias;
            if(count($categorias)>0){
                foreach ($categorias as $categ) {
                    $categ->new_id = 'cat:'.$categ->id;
                }
                $config->children = $categorias;
                $configs[] = $config;
            }
        }
        return compact('cursos','configs');
    }
    private function format_data_categorias($categorias){
        foreach ($categorias as $categoria) {
            $cursos = $categoria->cursos;
            $categoria->new_id = 'esc:'.$categoria->id; 
            if(count($cursos)>0){
                $curs_f = $this->format_data_cursos($cursos);
                $categoria->children = $cursos;
            }
        }
        return $categorias;
    }
    private function format_data_cursos($cursos){
        foreach ($cursos as $curso) {
            $curso->new_id = 'cur:'.$curso->id; 
            $temas = $curso->temas;
            if(count($temas)>0){
                $curso->children = $temas;
                foreach ($temas as $tema) {
                    $tema->new_id = 'tem:'.$tema->id; 
                    if($tema->tipo_ev=='calificada' || $tema->tipo_ev=='abierta'){
                        $pr = [];
                        $pr[] = [
                            'new_id' => 'pru:'.$tema->id,
                            'name' => 'Evaluación',
                        ];
                        $tema->children=$pr;
                    }
                }
            }
        }
        return $cursos;
    }
    private function save_copy_escuelas($data){
        //SEPARAR DATA
        $duplicar = collect($data['duplicar']);
        $duplicar_c = $duplicar->map(function ($item, $key) {
            $d = explode(':',$item);
            $data =[
                'tipo' => $d[0],
                'id' => $d[1],
            ];
            return $data;
        });
        $ids_para_migrar_requisitos = [];
        $duplicar_g = $duplicar_c->groupBy('tipo');
        foreach ($data['configs'] as $config_id) {
            foreach ($duplicar_g as $key => $dups) {
                switch ($key) {
                    case 'esc':
                        // SE COPIA SOLO LA ESCUELA
                        //$dup['id'] es el ID de la categoria a duplicar
                        foreach ($dups as $dup) {
                            //Obtener ultimo orden
                            $this->insert_categoria($config_id,$dup['id']);
                        }
                    break;
                    case 'cur':
                        // SE COPIA SOLO EL CURSO Y LA ESCUELA
                        //$dup['id'] es el ID del curso a duplicar
                        foreach ($dups as $dup) {
                            //OBTENER CURSO
                            $curso = Curso::where('id',$dup['id'])->first();
                            $requisito_curso_id = $curso->requisito_id;
                            //Obtener orden categoria
                            $id_categoria = $this->insert_categoria($config_id,$curso->categoria_id);
                            //INSERTAR CURSO Y ENCUESTA
                            $enc = Curso_encuesta::where('curso_id',$dup['id'])->select('encuesta_id')->first();
                            $encuesta_id = ($enc) ? $enc->encuesta_id : null ;
                            $id_curso = $this->insert_curso($categ->config_id,$id_cat[1],$curso,$encuesta_id);
                            if(($requisito_curso_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'cursos',
                                    'new_id' => $id_curso,
                                    'requisito_id' =>$requisito_curso_id,
                                    'recurso_id' => $id_categoria,
                                    'recurso_nombre' => 'categoria_id',
                                ];
                            }
                        }
                    break;
                    case 'tem':
                        // SE COPIA EL TEMA, CURSO Y ESCUELA
                        //$dup['id'] es el ID del tema a duplicar
                        foreach ($dups as $dup) {
                            //OBTENER TEMA
                            $tema = Posteo::where('id',$dup['id'])->first();
                            $requisito_tema_id = $tema->requisito_id;
                            //CREAR CATEGORIA
                            $id_categoria = $this->insert_categoria($config_id,$tema->categoria_id);
                            //CREAR CURSO Y ENCUESTA
                            $curso = Curso::where('id',$tema->curso_id)->first();
                            $requisito_curso_id = $curso->requisito_id;
                            $enc = Curso_encuesta::where('curso_id',$tema->curso_id)->select('encuesta_id')->first();
                            $encuesta_id = ($enc) ? $enc->encuesta_id : null ;
                            $id_curso = $this->insert_curso($config_id,$id_categoria,$curso,$encuesta_id);
                            
                            if(($requisito_curso_id)){
                                    $ids_para_migrar_requisitos[] = [
                                        'tipo'=>'cursos',
                                        'new_id' => $id_curso,
                                        'requisito_id' =>$requisito_curso_id,
                                        'recurso_id' => $id_categoria,
                                        'recurso_nombre' => 'categoria_id',
                                    ];
                            }
                            //CREAR TEMA 
                            $tema_id = $this->insert_tema($config_id,$id_categoria,$id_curso,$tema,false);
                            if(($requisito_tema_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'temas',
                                    'new_id' => $tema_id,
                                    'requisito_id' =>$requisito_tema_id,
                                    'recurso_id' => $id_curso,
                                    'recurso_nombre' => 'curso_id',
                                ];
                            }
                        }
                    break;
                    case 'pru':
                        foreach ($dups as $dup) {
                            //OBTENER TEMA
                            $tema = Posteo::where('id',$dup['id'])->first();
                            $requisito_tema_id = $tema->requisito_id;
                            //CREAR CATEGORIA
                            $id_categoria = $this->insert_categoria($config_id,$tema->categoria_id);
                            //CREAR CURSO Y ENCUESTA
                            $curso = Curso::where('id',$tema->curso_id)->first();
                            $requisito_curso_id = $curso->requisito_id;
                            $enc = Curso_encuesta::where('curso_id',$tema->curso_id)->select('encuesta_id')->first();
                            $encuesta_id = ($enc) ? $enc->encuesta_id : null ;
                            $id_curso = $this->insert_curso($config_id,$id_categoria,$curso,$encuesta_id);
                            
                            if(($requisito_curso_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'cursos',
                                    'new_id' => $id_curso,
                                    'requisito_id' =>$requisito_curso_id,
                                    'recurso_id' => $id_categoria,
                                    'recurso_nombre' => 'categoria_id',
                                ];
                            }
                            //CREAR TEMA 
                            $evas = Pregunta::where('post_id',$dup['id'])->get();
                            $tema_id = $this->insert_tema($config_id,$id_categoria,$id_curso,$tema);
                            if(($requisito_tema_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'temas',
                                    'new_id' => $tema_id,
                                    'requisito_id' =>$requisito_tema_id,
                                    'recurso_id' => $id_curso,
                                    'recurso_nombre' => 'curso_id',
                                ];
                            }
                            // CREAR EVALUACIONES
                            if($tema->tipo_ev=='calificada' || $tema->tipo_ev=='abierta'){
                                $this->insert_evas($evas,$tema_id);
                            }
                        }
                    break;
                }
            }
        }
        if(count($ids_para_migrar_requisitos)>0){
            $this->agregar_requisitos($ids_para_migrar_requisitos);
        }
    }
   
    private function save_copy_cursos($data){
        //SEPARAR DATA
        $duplicar = collect($data['duplicar']);
        $duplicar_c = $duplicar->map(function ($item, $key) {
            $d = explode(':',$item);
            $data =[
                'tipo' => $d[0],
                'id' => $d[1],
            ];
            return $data;
        });
        $ids_para_migrar_requisitos=[];
        $duplicar_g = $duplicar_c->groupBy('tipo');
        $duplicados = [];
        foreach ($data['configs'] as $categ_id) {
            foreach ($duplicar_g as $key => $dups) {
                $id_cat = explode(':',$categ_id); //ID DE LA ESCUELA DONDE SE DUPLICARA
                switch ($key) {
                    case 'cur':
                        // SE COPIA SOLO EL CURSO Y LA ESCUELA
                        //$dup['id'] es el ID del curso a duplicar
                        foreach ($dups as $dup) {
                            //OBTENER CONFIG_ID
                            $categ = Categoria::where('id',$id_cat[1])->select('id','config_id','nombre')->first();
                            //OBTENER CURSO
                            $curso = Curso::where('id',$dup['id'])->first();
                            $requisito_curso_id = $curso->requisito_id;
                            //INSERTAR CURSO Y ENCUESTA
                            $enc = Curso_encuesta::where('curso_id',$dup['id'])->select('encuesta_id')->first();
                            $encuesta_id = ($enc) ? $enc->encuesta_id : null ;
                            $id_curso = $this->insert_curso($categ->config_id,$id_cat[1],$curso,$encuesta_id);
                            $find = in_array($categ->id,array_column($duplicados,'id'));
                            if(!$find){
                                $duplicados[] =[
                                    'id'=>$categ->id,
                                    'url'=>'/abconfigs/'.$categ->config_id.'/categorias/'.$categ->id.'/cursos',
                                    'nombre'=>$categ->nombre,
                                ];
                            }
                            if(($requisito_curso_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'cursos',
                                    'new_id' => $id_curso,
                                    'requisito_id' =>$requisito_curso_id,
                                    'recurso_id' => $id_cat[1],
                                    'recurso_nombre' => 'categoria_id',
                                ];
                            }
                        }
                    break;
                    case 'tem':
                        // SE COPIA EL TEMA, CURSO Y ESCUELA
                        //$dup['id'] es el ID del tema a duplicar
                        foreach ($dups as $dup) {
                            //OBTENER CONFIG_ID
                            $categ = Categoria::where('id',$id_cat[1])->select('id','config_id','nombre')->first();
                            //OBTENER TEMA
                            $tema = Posteo::where('id',$dup['id'])->first();
                            $requisito_tema_id = $tema->requisito_id;
                            //CREAR CURSO Y ENCUESTA
                            $curso = Curso::where('id',$tema->curso_id)->first();
                            $requisito_curso_id = $curso->requisito_id;
                            $enc = Curso_encuesta::where('curso_id',$tema->curso_id)->select('encuesta_id')->first();
                            $encuesta_id = ($enc) ? $enc->encuesta_id : null ;
                            $id_curso = $this->insert_curso($categ->config_id,$id_cat[1],$curso,$encuesta_id);
                            $find = in_array($categ->id,array_column($duplicados,'id'));
                            if(!$find){
                                $duplicados[] =[
                                    'id'=>$categ->id,
                                    'url'=>'/abconfigs/'.$categ->config_id.'/categorias/'.$categ->id.'/cursos',
                                    'nombre'=>$categ->nombre,
                                ];
                            }
                            if(($requisito_curso_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'cursos',
                                    'new_id' => $id_curso,
                                    'requisito_id' =>$requisito_curso_id,
                                    'recurso_id' => $id_cat[1],
                                    'recurso_nombre' => 'categoria_id',
                                ];
                            }
                            //CREAR TEMA 
                            $tema_id = $this->insert_tema($categ->config_id,$id_cat[1],$id_curso,$tema,false);
                            if(($requisito_tema_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'temas',
                                    'new_id' => $tema_id,
                                    'requisito_id' =>$requisito_tema_id,
                                    'recurso_id' => $id_curso,
                                    'recurso_nombre' => 'curso_id',
                                ];
                            }
                        }
                    break;
                    case 'pru':
                        foreach ($dups as $dup) {
                            //OBTENER CONFIG_ID
                            $categ = Categoria::where('id',$id_cat[1])->select('id','config_id','nombre')->first();
                            //OBTENER TEMA
                            $tema = Posteo::where('id',$dup['id'])->first();
                            $requisito_tema_id = $tema->requisito_id;
                            //CREAR CURSO Y ENCUESTA
                            $curso = Curso::where('id',$tema->curso_id)->first();
                            $requisito_curso_id = $curso->requisito_id;
                            $enc = Curso_encuesta::where('curso_id',$tema->curso_id)->select('encuesta_id')->first();
                            $encuesta_id = ($enc) ? $enc->encuesta_id : null ;
                            $id_curso = $this->insert_curso($categ->config_id,$id_cat[1],$curso,$encuesta_id);
                            $find = in_array($categ->id,array_column($duplicados,'id'));
                            if(!$find){
                                $duplicados[] =[
                                    'id'=>$categ->id,
                                    'url'=>'/abconfigs/'.$categ->config_id.'/categorias/'.$categ->id.'/cursos',
                                    'nombre'=>$categ->nombre,
                                ];
                            }
                            if(($requisito_curso_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'cursos',
                                    'new_id' => $id_curso,
                                    'requisito_id' =>$requisito_curso_id,
                                    'recurso_id' => $id_cat[1],
                                    'recurso_nombre' => 'categoria_id',
                                ];
                            }
                            //CREAR TEMA 
                            $evas = Pregunta::where('post_id',$dup['id'])->get();
                            $tema_id = $this->insert_tema($categ->config_id,$id_cat[1],$id_curso,$tema);
                            if(($requisito_tema_id)){
                                $ids_para_migrar_requisitos[] = [
                                    'tipo'=>'temas',
                                    'new_id' => $tema_id,
                                    'requisito_id' =>$requisito_tema_id,
                                    'recurso_id' => $id_curso,
                                    'recurso_nombre' => 'curso_id',
                                ];
                            }
                            // CREAR EVALUACIONES
                            if($tema->tipo_ev=='calificada' || $tema->tipo_ev=='abierta'){
                                $this->insert_evas($evas,$tema_id);
                            }
                        }
                    break;
                }
            }
        }
        if(count($ids_para_migrar_requisitos)>0){
            $this->agregar_requisitos($ids_para_migrar_requisitos);
        }
        return $duplicados;
    }
    private function agregar_requisitos($ids_para_migrar_requisitos){
        foreach ($ids_para_migrar_requisitos as $migrar) {
            $modelo = ($migrar['tipo']=='cursos') ? new Curso : new Posteo;
            $nombre_requisito = $modelo->where('id',$migrar['requisito_id'])->select('nombre')->pluck('nombre');   
            $buscar_si_se_migro = $modelo->where($migrar['recurso_nombre'],$migrar['recurso_id'])->where('nombre',$nombre_requisito)->select('id')->pluck('id');
            if(count($buscar_si_se_migro)>0){
                $r = $modelo->where('id',$migrar['new_id'])->update([
                    'requisito_id' => $buscar_si_se_migro[0]
                ]);
            }
        }
    }
    private function insert_categoria($config_id,$categoria_id){
        $orden =Categoria::where('config_id',$config_id)->select('orden')->max('orden');
        //Obtener categoria
        $categ = Categoria::where('id',$categoria_id)->select('id','modalidad','nombre','descripcion','imagen','color'
                ,'en_menu_sec','estado','plantilla_diploma','margin_top_diploma','estado_diploma','estado')->first();
        $categ->orden = $orden + 1; 
        $categ->config_id = $config_id; 
        $categ->duplicado_id = $categ->id;
        unset($categ->id);
        //VERIFICAR SI EXISTE LA CATEGORIA
        $v_categoria = Categoria::where('config_id',$config_id)->where('nombre',$categ->nombre)->select('id')->first();
        if($v_categoria){
            return $v_categoria->id;
        }
        return Categoria::insertGetId($categ->toArray());
    }
    private function insert_curso($config_id,$id_categoria,$curso,$encuesta_id){
        //VERIFICAR SI EXISTE EL CURSO
        $v_curso = Curso::where('categoria_id',$id_categoria)->where('nombre',$curso->nombre)->select('id')->first();
        if($v_curso){
            return $v_curso->id;
        }
        $curso->duplicado_id = $curso->id;
        unset($curso->id);
        unset($curso->created_at);
        unset($curso->updated_at);

        // $orden_cu =Curso::where('categoria_id',$id_categoria)->select('orden')->max('orden');
        $curso->categoria_id = $id_categoria;
        // $curso->orden = $orden_cu + 1;
        $curso->config_id = $config_id;
        $curso->requisito_id = null;
        $curso_id = Curso::insertGetId($curso->toArray()); 
        //CREAR ENCUESTA
        if($encuesta_id){
            Curso_encuesta::insert([
                'encuesta_id' => $encuesta_id,
                'curso_id' => $curso_id,
            ]);
        }
       
        
        return $curso_id;
    }
    private function insert_tema($config_id,$id_categoria,$id_curso,$tema,$inserta_evaluacion = true){
        $v_tema = Posteo::where('curso_id',$id_curso)->where('nombre',$tema->nombre)->select('id')->first();
        if($v_tema){
            return $v_tema->id;
        }
        $media_tema = MediaTema::where('tema_id',$tema->id)->get();

        $tema->duplicado_id = $tema->id;
        unset($tema->id);
        unset($tema->created_at);
        unset($tema->updated_at);
        $tema->categoria_id = $id_categoria;
        $tema->curso_id = $id_curso;
        $tema->requisito_id = null;

        $tema_id = Posteo::insertGetId($tema->toArray());
        //Duplicar evaluaciones
        foreach ($media_tema as $mt) {
            unset($mt->id);
            unset($mt->created_at);
            unset($mt->updated_at);
            $mt->tema_id = $tema_id;
            MediaTema::insert($mt->toArray());
        }
        //Duplicar contenido
        return $tema_id;
    }
    private function insert_evas($evas,$tema_id){
        $v_eva = Pregunta::where('post_id',$tema_id)->get();
        if(count($v_eva)>0 && count($evas)<1){
            return false;
        }
        foreach ($evas as $eva) {
            unset($eva->id);
            unset($eva->created_at);
            unset($eva->updated_at);
            $eva->post_id = $tema_id;
            Pregunta::insertGetId($eva->toArray());
        }
    }
}
