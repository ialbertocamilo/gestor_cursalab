<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\PosteoCompas;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportReporteCompatible;
use App\Http\Controllers\ApiRest\HelperController;

class CompatibleController extends Controller
{
    public function get_cursos_compatibles(){
        $cursos = Curso::with(
        ['config'=>function($q){
            return $q->select('etapa','id');
        },'categoria'=>function($q){
            return $q->select('nombre','id');
        },'curricula'=>function($q){
            return $q->select('curso_id','carrera_id','ciclo_id','id');
        },'curricula.carrera'=>function($q){
            return $q->select('nombre','id');
        },'curricula.ciclo'=>function($q){
            return $q->select('nombre','id');
        },'temas'=>function($q){
            return $q->select('curso_id','nombre','id');
        }])->select('id','config_id','categoria_id','nombre')->get();

        foreach ($cursos as $curso) {
            foreach ($curso->temas as $tema) {
                $tema->selec = 0;
            }
        }
        $modulos = Abconfig::all(['id','etapa']);
        $data=[
            'cursos' => $cursos,
            'modulos' => $modulos
        ];
        return response()->json(['error'=>false,'data'=>$data], 200);
    }

    public function get_coincidencias(Request $request){
        $d_request = $request->all();
        $curso_id = $d_request['curso_id'];
        $temas = $d_request['temas'];
        $se_posteo = PosteoCompas::where('curso_id',$curso_id)->get(); 
        $ids_temas = collect($temas)->pluck('id')->toArray();
        //IDS QUE NO SE DEBEN CONSIDERAR
        $relaciones = [];
        //BUSCAR LAS RELACIONES COMPATIBLES
        foreach ($se_posteo as $posteo_c) {
            $idx = in_array($posteo_c->tema_id,$ids_temas);
            if($idx){
                $p_comp = Posteo::where('id',$posteo_c->tema_compa_id)->select('id','nombre','curso_id')->first();
                $p_comp['id_compatible']=$posteo_c->tema_id;
                $p_comp['selec'] = 1; 
                $relaciones[] =$p_comp;
            }
        }
        $data = [
            'relaciones' => $relaciones
        ];
        return response()->json(['error'=>false,'data'=>$data], 200);
    }
    public function guardar_compatibles(Request $request){
        $datos = $request->all();
        $p_compatibles = $datos['nuevos_compatibles'];
        foreach ($p_compatibles as $p_compatible) {
            //ELIMINAR LOS REGISTROS
            if(count($p_compatible['ids_eliminados'])>0){
                foreach ($p_compatible['ids_eliminados'] as $eliminado) {
                    PosteoCompas::where('tema_id',$p_compatible['tema_id'])->where('tema_compa_id',$eliminado)->delete();
                    PosteoCompas::where('tema_id',$eliminado)->where('tema_compa_id',$p_compatible['tema_id'])->delete();
                }
            }
            //INSERTAR NUEVOS REGISTROS
            if(count($p_compatible['children'])>0){
                    $q_posteo = count($p_compatible['children']);
                    foreach ($p_compatible['children'] as $n_compatible) {
                        //IDA
                        PosteoCompas::updateOrinsert([
                                'tema_id'=>$p_compatible['tema_id'],
                                'tema_compa_id'=>$n_compatible['id'],
                            ],[
                            'config_id' =>$p_compatible['config_id'],
                            'curso_id' =>$p_compatible['curso_id'],
                            'tema_id'=>$p_compatible['tema_id'],
                            'curso_compa_id'=>$n_compatible['curso_id'],
                            'tema_compa_id'=>$n_compatible['id'],
                        ]);
                        //VUELTA
                        $tem = Posteo::with(['categoria'=>function($q){
                            $q->select('id','config_id');
                        }])->where('id',$n_compatible['id'])->select('id','categoria_id')->first(); 
                        PosteoCompas::updateOrinsert([
                            'tema_id'=>$n_compatible['id'],
                            'tema_compa_id'=>$p_compatible['tema_id'],
                            ],[
                            'config_id' =>$tem->categoria->config_id,
                            'curso_compa_id' =>$p_compatible['curso_id'],
                            'tema_compa_id'=>$p_compatible['tema_id'],
                            'curso_id'=>$n_compatible['curso_id'],
                            'tema_id'=>$n_compatible['id'],
                        ]);
                        //COMBINACION
                        for ($i=0; $i < $q_posteo ; $i++) { 
                            if($n_compatible['id']!= $p_compatible['children'][$i]['id']){
                                $tem_comb = Posteo::with(['categoria'=>function($q){
                                    $q->select('id','config_id');
                                }])->where('id',$n_compatible['id'])->select('id','categoria_id')->first(); 
                                PosteoCompas::updateOrinsert([
                                    'tema_id' => $n_compatible['id'],
                                    'tema_compa_id' => $p_compatible['children'][$i]['id'],
                                ],[
                                    'tema_id' => $n_compatible['id'],
                                    'tema_compa_id' => $p_compatible['children'][$i]['id'],
                                    'config_id' => $tem_comb->categoria->config_id,
                                    'curso_id' => $n_compatible['curso_id'],
                                    'curso_compa_id' => $p_compatible['children'][$i]['curso_id'],
                                ]);
                            }
                        }
                }
                foreach ($p_compatible['children'] as $value) {
                }
            }
        }
        return response()->json(true,200);
    }

    public function compatibles_lista(Request $request){
        $drop_groups = $request->all();
        $get_ids = [];

        foreach ($drop_groups as $dg) {
            foreach ($dg['children'] as $ch) {
                $get_ids[] = $ch['curso_id'];                
            }
        }
        $cursos = Curso::with(
            ['config'=>function($q){
                return $q->select('etapa','id');
            },'categoria'=>function($q){
                return $q->select('nombre','id');
            },'curricula'=>function($q){
                return $q->select('curso_id','carrera_id','ciclo_id','id');
            },'curricula.carrera'=>function($q){
                return $q->select('nombre','id');
            },'curricula.ciclo'=>function($q){
                return $q->select('nombre','id');
            },'temas'=>function($q){
                return $q->select('curso_id','nombre','id');
            }])->whereIn('id',$get_ids)->select('id','config_id','categoria_id','nombre')->get();
       
        return response()->json(['error'=>false,'data'=>$cursos], 200);
    }
    public function search_tema(Request $request){
        $searchs = $request->all();
        
        $s_modulo = $searchs['s_modulo'];
        $s_escuela = $searchs['s_escuela'];
        $b_curso_l2 = $searchs['b_curso_l2'];
        $s_tema = $searchs['s_tema'];

        $cursos = Curso::with(
            ['config'=>function($q)use($s_modulo){
                if(($s_modulo) && ($s_modulo!='Todos')){
                    return $q->where('etapa','like','%'.$s_modulo.'%')->select('etapa','id');
                }
                return $q->select('etapa','id');
            },'categoria'=>function($q)use($s_escuela){
                if($s_escuela){
                    return $q->where('nombre','like','%'.$s_escuela.'%')->select('nombre','id');
                }
                return $q->select('nombre','id');
            },'curricula'=>function($q){
                return $q->select('curso_id','carrera_id','ciclo_id','id');
            },'curricula.carrera'=>function($q){
                return $q->select('nombre','id');
            },'curricula.ciclo'=>function($q){
                return $q->select('nombre','id');
            },'temas'=>function($q) use($s_tema){
                if($s_tema){
                    return $q->select('curso_id','nombre','id')->where('nombre','like','%'.$s_tema.'%');
                }
                return $q->select('curso_id','nombre','id');
            }]);
            if($b_curso_l2){
                $cursos->where('nombre','like','%'.$b_curso_l2.'%');
            }
            $cursos_r = $cursos->select('id','config_id','categoria_id','nombre')->get();
            $temas_not_empty = $this->array_remove_empty($cursos_r);
            return response()->json(['error'=>false,'data'=>$temas_not_empty], 200);
    }
    public function reporte(){
        $obj=new ExportReporteCompatible();
        $obj->view();
        ob_end_clean();
        ob_start();
        return Excel::download($obj,'reporte_compatible.xlsx');
    }
    //ELIMINA LOS DATOS INNECESARIOS
    private function array_remove_empty($item){
        $new_array = [];
        foreach ($item as $value) {
            if(count($value->temas)>0 && ($value->categoria) && ($value->config)){
                $new_array[] = $value;
            }
        }
        return $new_array;
    }

    public function migracion_compatibles_x_usuario($carrera_id){
        $helper = new HelperController;
        //CARRERA TECNICO DE FARMACIA
        $cursos_id = $helper->help_cursos_x_matricula(4858);
        $temas = Posteo::whereIn('curso_id',$cursos_id)->get();
        //CARRERA QUIMICO FARMACEUTICO
        $cursos_id_2 = $helper->help_cursos_x_matricula(7);
        $temas_2 = Posteo::whereIn('curso_id',$cursos_id_2)->pluck('id')->toArray();
        //Temas que se deberian migrar
        $migrados = [];
        foreach ($temas as $tema) {
            $veri = in_array($tema->id,$temas);
            if(!$veri){
                $compa = PosteoCompas::where($tema->id)->get();
                $migrado = [];
            }            
        }
    }
}