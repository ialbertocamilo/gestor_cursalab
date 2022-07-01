<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Diploma;
use App\Models\Abconfig;
use App\Models\Reinicio;
use App\Models\Categoria;
use App\Models\Matricula;
use App\Models\Ev_abierta;
use App\Models\Curso_encuesta;
use App\Models\Resumen_x_curso;
use App\Models\Encuestas_respuesta;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiRest\RestAvanceController;

class MigrarAvanceController extends Controller
{
    public function getData(){
        $modulos = Abconfig::select('id','etapa')->get();
        return response()->json(compact('modulos'));
    }

    public function listCategorias($config_id){
        $categorias = Categoria::select('id','nombre')->where('config_id',$config_id)->get();
        return response()->json(compact('categorias'));
    }
    public function listCursos($categoria_id){
        $cursos = Curso::select('id','nombre')->where('categoria_id',$categoria_id)->get();
        return response()->json(compact('cursos'));
    }
    public function listTemas($curso_id){
        $temas = Posteo::select('id','nombre','tipo_ev','duplicado_id')->where('curso_id',$curso_id)->get();
        $temas->each(function ($item, $key) {
            $item->checkbox = false;
            switch ($item->tipo_ev) {
                case 'calificada': $item->txt_ev  = 'E';break;
                case 'abierta': $item->txt_ev  = 'A'; break;
                default: $item->txt_ev  = 'NE'; break;
            }
        });
        $curriculas = Curso::join('curricula', 'curricula.curso_id', 'cursos.id')
        // ->join('curricula_criterio as cc', 'cc.curricula_id', 'curricula.id')
        ->join('carreras as ca', 'ca.id', 'curricula.carrera_id')
        ->join('ciclos as ci', 'ci.id', 'curricula.ciclo_id')
        ->where('curricula.curso_id', $curso_id)
        ->select('ca.nombre as carrera','curricula.carrera_id','ci.nombre as ciclo','curricula.ciclo_id', 'curricula.curso_id', 'curricula.id as curricula_id')
        ->orderBy('ci.secuencia')
        ->get();
        $curriculas = $curriculas->groupBy('carrera_id');
        return response()->json(compact('temas','curriculas'));
    }
    public function getDuplicatesData($tipo,$curso_id,$categoria_id){
        $data = [];
        if($tipo=='categorias'){
            $curso = Curso::where('id',$curso_id)->select('config_id')->first();
            $data = Categoria::whereHas('cursos', function($q)use($curso_id){
                $q->where('duplicado_id',$curso_id);
            })->where('config_id',$curso->config_id)->select('id','nombre')->get();
        }else{
            $data = Curso::where('categoria_id',$categoria_id)->where('duplicado_id',$curso_id)->select('id','nombre')->get();
        }
        return response()->json(compact('data'));
    }
    public function migrarTemas(Request $request){
        $data = $request->all();
        $usuarios_llevaron_curso = Resumen_x_curso::where('curso_id',$data['curso_origen']['id'])
                                    ->select('usuario_id')->pluck('usuario_id');
        $last = true;
        $i = 0;
        $q_curr = count($data['curriculas_destino'] );
        $usuarios_id_migrate = collect();
        foreach ($data['curriculas_destino'] as $curr) {
            $ci = collect($curr)->pluck('ciclo_id');
            $usuarios_id = Matricula::whereIn('ciclo_id',$ci)->whereIn('usuario_id',$usuarios_llevaron_curso)->select('usuario_id')->groupBy('usuario_id')->pluck('usuario_id');
            $usuarios_id_migrate = $usuarios_id_migrate->merge($usuarios_id);
            foreach ($data['temas_a_migrar'] as $migrar_temas) {
                $posteo_origen = Posteo::where('id',$migrar_temas['id_origen'])->select('id','curso_id','evaluable','tipo_ev')->first();
                $posteo_destino = Posteo::where('id',$migrar_temas['id_destino'])->select('id','curso_id','categoria_id','evaluable','tipo_ev')->first();
                if($posteo_origen->tipo_ev == $posteo_destino->tipo_ev){
                    if($posteo_origen->evaluable =='si'){
                        switch ($posteo_origen->tipo_ev) {
                            case 'calificada':
                                $this->migrar_pruebas($usuarios_id,$posteo_origen,$posteo_destino);
                            break;
                            case 'abierta':
                                $this->migrar_evas($usuarios_id,$posteo_origen,$posteo_destino);
                            break;
                        }
                    }
                    $this->migrar_reinicios($usuarios_id,$posteo_origen,$posteo_destino);
                    $this->migrar_visitas($usuarios_id,$migrar_temas,$data['curso_destino']);
                    //Solo migrar la diploma la primera vez <---- mejorar
                }
            }
        }
        $this->migrar_diploma($usuarios_id_migrate->unique(),$data['curso_origen'],$data['curso_destino']);
        $this->migrar_intentos_actualizar_resumenes($usuarios_id_migrate->unique(),$data['curso_origen'],$data['curso_destino']);

        // $cursos = $request->get('cursos');
        return response()->json('ok',200);
    }
    private function migrar_pruebas($usuarios_id,$posteo_origen,$posteo_destino){
        $pruebas = Prueba::whereIn('usuario_id',$usuarios_id)
        ->where('posteo_id',$posteo_origen->id)
        ->select('categoria_id','curso_id','posteo_id','usuario_id','intentos','rptas_ok','rptas_fail','nota','resultado','usu_rptas','historico','last_ev','fuente')
        ->groupBy('usuario_id','posteo_id')
        ->get();
        $enc_1 = Curso_encuesta::where('curso_id',$posteo_origen->curso_id)->first();
        $enc_2 = Curso_encuesta::where('curso_id',$posteo_destino->curso_id)->first();
        $migrar_pruebas = collect();
        $migrar_encuestas = collect();
        $pruebas->each(function($prueba) use($migrar_encuestas,$posteo_origen,$posteo_destino,$migrar_pruebas,$enc_1,$enc_2){
            if(isset($enc_1) && isset($enc_2) && ($enc_1->encuesta_id == $enc_2->encuesta_id)){
                $encs = Encuestas_respuesta::where('usuario_id', $prueba->usuario_id)->where('curso_id', $posteo_destino->curso_id)->get();
                // Si ya tiene encuesta no se migra
                if(count($encs)==0){
                    $ec_migrar = Encuestas_respuesta::where('usuario_id', $prueba->usuario_id)
                    ->where('curso_id', $posteo_origen->curso_id)
                    ->select('encuesta_id','pregunta_id','usuario_id','respuestas','tipo_pregunta')->get();
                    $ec_migrar->each(function($ec_m)use($posteo_destino,$migrar_encuestas){
                        $ec_m->curso_id = $posteo_destino->curso_id;
                        $migrar_encuestas->push($ec_m);
                    });
                }
            }
            $prueba_search = Prueba::where('usuario_id',$prueba->usuario_id)->where('posteo_id',$posteo_destino->id)->first();
            if(is_null($prueba_search)){
                $prueba->categoria_id = $posteo_destino->categoria_id;
                $prueba->curso_id = $posteo_destino->curso_id;
                $prueba->posteo_id = $posteo_destino->id;
                $migrar_pruebas->push($prueba);
            }else if($prueba_search->nota < $prueba->nota){
                $prueba_search->intentos = $prueba->intentos;
                $prueba_search->rptas_ok = $prueba->rptas_ok;
                $prueba_search->rptas_fail = $prueba->rptas_fail;
                $prueba_search->nota = $prueba->nota;
                $prueba_search->resultado = $prueba->resultado;
                $prueba_search->usu_rptas = $prueba->usu_rptas;
                $prueba_search->historico = $prueba->historico;
                $prueba_search->last_ev = $prueba->last_ev;
                $prueba_search->fuente = $prueba->fuente;
                $prueba_search->save();
            }
        });
        if($migrar_pruebas->count()>0){
            $chunks = array_chunk($migrar_pruebas->toArray(), 200);
            foreach ($chunks as $rs) {
                Prueba::insert($rs);
            }
        }
        if($migrar_encuestas->count()>0){
            $chunks = array_chunk($migrar_encuestas->toArray(), 200);
            foreach ($chunks as $rs) {
                Encuestas_respuesta::insert($rs);
            }
        }
        return 'ok';
    }
    private function migrar_evas($usuarios_id,$posteo_origen,$posteo_destino){
        $evas = Ev_abierta::whereIn('usuario_id',$usuarios_id)
        ->where('posteo_id',$posteo_origen->id)
        ->select('usuario_id','usu_rptas','eva_abierta','fuente')
        ->groupBy('usuario_id','posteo_id')
        ->get();
        $evas_migrar = collect();
        $evas->each(function($eva) use ($posteo_destino,$evas_migrar){
            $eva_search = Ev_abierta::where('usuario_id',$eva->usuario_id)->where('posteo_id',$posteo_destino->id)->first();
            if(is_null($eva_search)){
                $eva->categoria_id = $posteo_destino->categoria_id;
                $eva->curso_id = $posteo_destino->curso_id;
                $eva->posteo_id = $posteo_destino->id;
                $evas_migrar->push($eva);
            }
        });
        if($evas_migrar->count()>0){
            $chunks = array_chunk($evas_migrar->toArray(), 200);
            foreach ($chunks as $rs) {
                Ev_abierta::insert($rs);
            }
        }
    }
    private function migrar_diploma($usuarios_id,$curso_origen,$curso_destino){
        $diplomas =  Diploma::whereIn('usuario_id',$usuarios_id)
        ->select('usuario_id','fecha_emision')
        ->where('curso_id',$curso_origen['id'])
        ->groupBy('usuario_id','curso_id')
        ->get();
        $diplomas_migrar = collect();
        $diplomas->each(function($diploma)use($curso_destino,$diplomas_migrar){
            $diploma_search = Diploma::select('id')
            ->where('usuario_id',$diploma->usuario_id)
            ->where('curso_id', $curso_destino['id'])->first();
            if(is_null($diploma_search)){
                $diploma->curso_id = $curso_destino['id'];
                $diplomas_migrar->push($diploma);
            }
        });
        if($diplomas_migrar->count()>0){
            $chunks = array_chunk($diplomas_migrar->toArray(), 200);
            foreach ($chunks as $rs) {
                Diploma::insert($rs);
            }
        }
    }
    private function migrar_intentos_actualizar_resumenes($usuarios_id,$curso_origen,$curso_destino){
        $resumenes = Resumen_x_curso::whereIn('usuario_id',$usuarios_id)->where('curso_id',$curso_origen['id'])->get();
        $resumenes_migrar = collect();
        $curso = Curso::where('id',$curso_destino['id'])->select('config_id')->first();
        $modulo = Abconfig::where('id',$curso->config_id)->select('mod_evaluaciones')->first();
        $mod_eval = json_decode($modulo->mod_evaluaciones,true); 
        $restAvance = new RestAvanceController();
        $resumenes->each(function($resumen) use ($curso_destino,$restAvance,$mod_eval){
            $restAvance->actualizar_resumen_x_curso($resumen->usuario_id, $curso_destino['id'], intval($mod_eval['nro_intentos']));
            $restAvance->actualizar_resumen_general($resumen->usuario_id);
            $resumen_search = Resumen_x_curso::where('usuario_id',$resumen->usuario_id)->where('curso_id',$curso_destino['id'])->first();
            if($resumen_search && $resumen->intentos>0){
                $resumen_search->intentos = $resumen->intentos;
                $resumen_search->save();
            }
        });
    }
    private function migrar_visitas($usuarios_id,$migrar_temas,$curso_destino){
        $visitas = Visita::whereIn('usuario_id',$usuarios_id)
                                ->where('post_id',$migrar_temas['id_origen'])
                                ->select('usuario_id','sumatoria','descargas','tipo_tema','estado_tema','created_at','updated_at')
                                ->groupBy('usuario_id','post_id')
                                ->get();
        $insert_visitas = collect();
        $visitas->each(function($vis_origen) use($migrar_temas,$curso_destino,$insert_visitas){
            $vis_origen->post_id =  $migrar_temas['id_destino'];
            $vis_origen->curso_id = $curso_destino['id'];
            //En caso existe la visita se actualiza
            $vis_search = Visita::where('usuario_id',$vis_origen->usuario_id)->where('post_id',$migrar_temas['id_destino'])->first();
            if(is_null($vis_search)){
                $insert_visitas->push($vis_origen);
            }else{
                $vis_search->estado_tema = $vis_origen->estado_tema;
                $vis_search->sumatoria = $vis_origen->sumatoria;
                $vis_search->descargas = $vis_origen->descargas;
                $vis_search->save();
            }
        });
        if($insert_visitas->count()>0){
            $chunks = array_chunk($insert_visitas->toArray(), 200);
            foreach ($chunks as $rs) {
                Visita::insert($rs);
            }
        }
        return 'ok';
    }
    private function migrar_reinicios($usuarios_id,$tema_origen,$tema_destino){
        $reinicios = Reinicio::whereIn('usuario_id',$usuarios_id)->where(function($q)use($tema_origen){
            $q->where('posteo_id',$tema_origen->id)->orWhere('curso_id',$tema_origen->curso_id);
        })->select('usuario_id','admin_id','tipo','acumulado')->get();
        $reinicios_migrar = collect();

        $reinicios->each(function($reinicio) use($tema_destino,$reinicios_migrar){
            $reinicio_search = null;
            if($reinicio->tipo == 'por_tema'){
                $reinicio_search = Reinicio::where('posteo_id',$tema_destino->id)->first();
            }else{
                $reinicio_search = Reinicio::where('curso_id',$tema_destino->curso_id)->first();
            }
            if(is_null($reinicio_search)){
                $reinicio->curso_id = $tema_destino->curso_id;
                if($reinicio->tipo == 'por_tema'){
                    $reinicio->posteo_id = $tema_destino->id;
                }
                $reinicios_migrar->push($reinicio);
            }
        });

        if($reinicios_migrar->count()>0){
            $chunks = array_chunk($reinicios_migrar->toArray(), 200);
            foreach ($chunks as $rs) {
                Reinicio::insert($rs);
            }
        }
        return 'ok';
    }
}
