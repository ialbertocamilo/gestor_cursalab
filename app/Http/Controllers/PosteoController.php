<?php

namespace App\Http\Controllers;

use App\Models\Abconfig;
use App\Models\Tag;
use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Carrera;
// use App\Models\Perfil;
// use App\Models\Posteo_perfil;
use App\Models\Pregunta;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\PosteoCompas;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\TagRelationship;
use App\Models\Update_usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PosteoStoreRequest;
use App\Http\Resources\Posteo\PosteoSearchResource;
use App\Http\Controllers\ApiRest\HelperController;

class PosteoController extends Controller
{
    public function preguntas(Curso $curso, Posteo $posteo)
    {
        $tipo_pregunta = ($posteo->tipo_ev=='calificada') ? 'selecciona' : 'texto' ;
        $preguntas  = $posteo->preguntas()->where('tipo_pregunta',$tipo_pregunta)->paginate();
        // $preguntas = $posteo->with(['preguntas'=>function($q)use ($tipo_pregunta){
        //     $q->select('preguntas.*')->where('tipo_pregunta',$tipo_pregunta);
        // }])->paginate();
        return view('posteos.preguntas', compact('posteo', 'preguntas', 'curso'));
    }
    // public function index(Curso $curso, Request $request)
    // {
    //     if ($request->has('q')) {
    //         $question = $request->input('q');
    //         // return $question;
    //         $posteos = Posteo::where('nombre', 'like', '%'.$question.'%')->paginate();
    //     }else{
    //         $posteos = Posteo::paginate();
    //     }

    //     return view('posteos.index', compact('posteos'));
    // }

    public function create(Curso $curso)
    {
        $categoria_array = Categoria::select('id', 'nombre')->where('id', $curso->categoria->id)->orderBy('orden')->pluck('nombre', 'id');
        $cursos_array = Curso::select('id', 'nombre')->where('id', $curso->id)->orderBy('orden')->pluck('nombre', 'id');
        $posteos =  Posteo::select('id', 'nombre','orden')->where('curso_id', $curso->id)->orderBy('orden')->get();
        $temas_array = $posteos->pluck('nombre', 'id');
        // $temas_array = Posteo::select('id','nombre')->where('categoria_id', $curso->categoria->id )->orderBy('orden')->pluck('nombre','id' );
        $temas_array->prepend('NINGUNO', '');
        $orden = isset($posteos->last()->orden) ? $posteos->last()->orden+1 : 1 ;
        return view('posteos.create', compact('categoria_array', 'temas_array', 'cursos_array', 'curso','orden'));
    }


    public function store(Curso $curso, PosteoStoreRequest $request)
    {
        // dd($request->all());
        $data = $request->all();
        // if ($request->has('imagen')) {
        //     $imagen= $request->file('imagen');
        //     $new_nombre = rand() . '.' . $imagen->getClientOriginalExtension();
        //     $imagen->move(public_path('images'), $new_nombre);

        //     $data['imagen'] = 'images/'.$new_nombre;
        // }
        // if ($request->has('archivo')) {
        //     $archivo= $request->file('archivo');
        //     $new_nombre = rand() . '.' . $archivo->getClientOriginalExtension();
        //     $archivo->move(public_path('archivos'), $new_nombre);

        //     $data['archivo'] = 'archivos/'.$new_nombre;
        // }
        // if ($request->has('video')) {
        //     $video= $request->file('video');
        //     $new_nombre = rand() . '.' . $video->getClientOriginalExtension();
        //     $video->move(public_path('archivos'), $new_nombre);

        //     $data['video'] = 'archivos/'.$new_nombre;
        // }

        if ($request->filled('imagen')) {
            $data['imagen'] = $request->imagen;
        }

        // if ($request->filled('video')) {
        //     $data['video'] = $request->video;
        // }

        // if ($request->filled('archivo')) {
        //     $data['archivo'] = $request->archivo;
        // }

        $posteo = Posteo::create($data);

        // ACTUALIZAR TABLAS RESUMENES SI EL ESTADO DEL TEMA ESTA ACTIVO
        if ($request->estado == "1") {
            /**
             * REGISTRAR AUDITORIA
             */
            $descripcion = $request->estado == "1" ? "Se creó el tema: $posteo->id, con estado ACTIVO." : "Se creó el tema: $posteo->id, con estado INACTIVO.";
            $afecta = [];
            $afecta[] = 'tablas_resumen';
            $auditoria = new AuditoriaController();
            $auditoria->registrarAuditoria('tema', $posteo->id, $descripcion, $afecta, auth()->user()->id);
        }

        // $this->actualizar_table_resumen($curso->id);
        //guardar tags
        if ($request->has('tags') && $request->input('tags') !== null && $request->input('tags') !== '') {
            $tags_arr = explode(",", $request->tags);
            foreach ($tags_arr as $key => $value) {
                $tag_rel = new TagRelationship;
                $tag_rel->tag_id = $value;
                $tag_rel->element_type = 'tema';
                $tag_rel->element_id = $posteo->id;
                $tag_rel->save();
            }
        }

        ////// Si el tema es evaluable, marcar CURSO también como evaluable
        $tema_evaluable = Posteo::where('curso_id', $curso->id)->where('evaluable', 'si')->first();
        if ($tema_evaluable) {
            $curso->c_evaluable = 'si';
        } else {
            $curso->c_evaluable = 'no';
        }
        $curso->save();
        //////

        if ($request->estado == "1") {
            return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
                    ->with('modal-info', '<strong>Tema creado con éxito.</strong> <br>
                    Este cambio produce actualizaciones en el avance de los usuarios, que se ejecutarán dentro de 20 minutos.
                    Las actualizaciones se verán reflejadas en la app y en los reportes al finalizar este proceso.');
        } else {
            return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
                ->with('info', 'Tema creado con éxito');
        }
    }

    public function edit(Curso $curso, Posteo $posteo)
    {
        $categoria_array = Categoria::select('id', 'nombre')->where('id', $curso->categoria->id)->orderBy('orden')->pluck('nombre', 'id');
        $cursos_array = Curso::select('id', 'nombre')->where('id', $curso->id)->orderBy('orden')->pluck('nombre', 'id');
        $all_cursos_array = Curso::select('id', 'nombre')->where('config_id', $curso->config->id)->orderBy('orden')->pluck('nombre', 'id');
        $temas_array = Posteo::select('id', 'nombre')->where('curso_id', $curso->id)->orderBy('orden')->pluck('nombre', 'id');
        $temas_array->pull($posteo->id);
        $temas_array->prepend('NINGUNO', '');
        $tiene_pregunta = Pregunta::where('post_id',$posteo->id)->where('estado',1)->first();
        // $perfiles = Perfil::get();
        //
        // $curso_ids = Curso::where('categoria_id', $curso->categoria_id)->pluck('id');
        // $carrera_ids = Curricula::where('curso_id', $curso_ids)->pluck('carrera_id');
        // $carreras = Carrera::whereIn('id', $carrera_ids)->where('estado', 1)->orderBy('nombre')->get();
        // dd($carreras);
        // $carreras = Carrera::where('config_id', $curso->config_id)->where('estado', 1)->orderBy('nombre')->get();

        // if ($posteo->imagen != "") {
        //     $posteo->imagen = str_replace("images/", "", $posteo->imagen);
        // }

        // if ($posteo->video != "") {
        //     $posteo->video = str_replace("archivos/", "", $posteo->video);
        // }

        // if ($posteo->archivo != "") {
        //     $posteo->archivo = str_replace("archivos/", "", $posteo->archivo);
        // }

        return view('posteos.edit', compact('posteo', 'categoria_array', 'temas_array', 'cursos_array', 'all_cursos_array', 'curso','tiene_pregunta'));
    }


    public function update(Curso $curso, PosteoStoreRequest $request, Posteo $posteo)
    {
        $data = $request->all();

        // if ($request->has('imagen')) {
        //     $imagen= $request->file('imagen');
        //     $new_nombre = rand() . '.' . $imagen->getClientOriginalExtension();
        //     $imagen->move(public_path('images'), $new_nombre);
        //     $data['imagen'] = 'images/'.$new_nombre;

        //     \File::delete(public_path().'/'.$posteo->imagen);
        // }

        // if ($request->has('archivo')) {
        //     $archivo= $request->file('archivo');
        //     $new_nombre = rand() . '.' . $archivo->getClientOriginalExtension();
        //     $archivo->move(public_path('archivos'), $new_nombre);
        //     $data['archivo'] = 'archivos/'.$new_nombre;

        //     \File::delete(public_path().'/'.$posteo->archivo);
        // }

        // if ($request->has('video')) {
        //     $video= $request->file('video');
        //     $new_nombre = rand() . '.' . $video->getClientOriginalExtension();
        //     $video->move(public_path('archivos'), $new_nombre);
        //     $data['video'] = 'archivos/'.$new_nombre;

        //     \File::delete(public_path().'/'.$posteo->video);
        // }

        if ($request->filled('imagen')) {
            $data['imagen'] = $request->imagen;
        }

        // if ($request->filled('video')) {
        //     $data['video'] = $request->video;
        // }

        // if ($request->filled('archivo')) {
        //     $data['archivo'] = $request->archivo;
        // }
        if($data['evaluable']=='no'){
            $data['tipo_ev'] = null;
        }
        $posteo->update($data);

        if ($posteo->wasChanged('evaluable')) {
            $convalidar_evaluacion = ($data['check_tipo_ev']=='true') ? true : false ;
            $uu = Update_usuarios::where('tipo','update_resumenes_curso')->where('curso_id',$posteo->curso_id)->where('estado',0)->select('id','extra_info')->first();
            if(json_decode($uu->extra_info)){
                $extra_info = json_decode($uu->extra_info);
                if(!in_array($posteo->id,array_column($extra_info,'posteo_id'))){
                    $extra_info[]=[
                        'posteo_id' => $posteo->id,
                        'convalidar_evaluacion' => $convalidar_evaluacion,
                        'nombre'=>$posteo->curso->nombre,
                        'accion'=>'posteo_actualizado',
                    ];
                    Update_usuarios::where('id',$uu->id)->update([
                        'extra_info' => json_encode($extra_info)
                    ]);
                }
            }else{
                Update_usuarios::where('id',$uu->id)->update([
                    'extra_info' => json_encode([[
                        'posteo_id' => $posteo->id,
                        'convalidar_evaluacion' => $convalidar_evaluacion,
                        'nombre'=>$posteo->curso->nombre,
                        'accion'=>'posteo_actualizado',
                    ]])
                ]);
            }
        }

        $estado_posteo = false;

        if ($posteo->wasChanged('estado')) {
            $estado_posteo = true;
            /**
             * DESACTIVAR EL CURSO SI ES QUE YA NO TIENE TEMAS ACTIVOS\
             */
            $count_temas_activos = Posteo::where('curso_id', $posteo->curso_id)->where('estado', 1)->count();
            //si el curso tiene requisito no desactiva el tema
            if ($count_temas_activos == 0) {
                $q_requisito = Curso::select('id','nombre')->where('requisito_id',$curso->id)->get();
                if(count($q_requisito)>0){
                    $posteo->update(['estado'=>1]);
                    $li  = "<ul>";
                    foreach ($q_requisito as $req) {
                        $li = $li."<li class='ml-4 mt-2'>".$req->nombre."</li>";
                    }
                    $li = $li."</ul>";
                    return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
                        ->with('modal-info', '<strong>No se puede desactivar este tema.</strong> <br>
                        Para poder desactivar este tema es necesario quitar el requisito en los siguientes cursos'.$li);
                }else{
                    Curso::where('id', $posteo->curso_id)->update(['estado' => 0]);
                }
            }
            /**
             * REGISTRAR AUDITORIA
             */
            $descripcion = $request->estado == "1" ? "El tema: $posteo->id, se actualizó con estado ACTIVO." : "El tema: $posteo->id, se actualizó con estado INACTIVO.";
            $afecta = [];
            $afecta[] = 'tablas_resumen';
            $auditoria = new AuditoriaController();
            $auditoria->registrarAuditoria('tema', $posteo->id, $descripcion, $afecta, auth()->user()->id);
        }


        //eliminar anteriores datos de posteo perfil
        $posteo->rel_tag()->delete();
        //actualiar tags
        if ($request->has('tags') && $request->input('tags') !== null && $request->input('tags') !== '') {
            $tags_arr = explode(",", $request->tags);
            foreach ($tags_arr as $key => $value) {
                $tag_rel = new TagRelationship;
                $tag_rel->tag_id = $value;
                $tag_rel->element_type = 'tema';
                $tag_rel->element_id = $posteo->id;
                $tag_rel->save();
            }
        }
        //eliminar anteriores datos de posteo perfil
        // $posteo->posteo_perfiles()->delete();

        // if ($request->has('perfiles')) {
        //             //guardar perfiles
        //     $perfiles = $request->perfiles;
        //     foreach ($perfiles as $key => $value) {
        //         $posteo_perfiles = new Posteo_perfil;
        //         $posteo_perfiles->posteo_id = $posteo->id;
        //         $posteo_perfiles->perfile_id = $value;
        //         $posteo_perfiles->save();
        //     }
        // }

        // Si tema no tiene EV *(preguntas) entonces debe ser NO-EVALUABLE
        // $tema_preguntas = Pregunta::where('post_id', $posteo->id)->where('estado', '1')->first();
        // if ($tema_preguntas) {
        //     $posteo->evaluable = 'si';
        //     // $posteo->tipo_ev = 'calificada'; //debe guardar la que se selecciona o deja seleccionada en la vista
        // } else {
        //     $posteo->evaluable = 'no';
        //     $posteo->tipo_ev = '';
        // }
        // $posteo->save();

        ////// Si el tema es evaluable, marcar CURSO también como evaluable
        $tema_evaluable = Posteo::where('curso_id', $curso->id)->where('evaluable', 'si')->first();
        if ($tema_evaluable) {
            $curso->c_evaluable = 'si';
        } else {
            $curso->c_evaluable = 'no';
        }
        $curso->save();
        //////


        // temas comaptibles
        if ($request->has('temas')) {
            $posteo->compatibles()->delete();
            $temas_com = $request->temas;
            foreach ($temas_com as $key => $value) {
                if ($value > 0) {

                    $existe_pos_compa = PosteoCompas::where('tema_id', $posteo->id)->where('tema_compa_id', $value)->first();

                    if (!isset($existe_pos_compa)) {
                        $tema_compa_obj = Posteo::find($value);

                        $ciclocompa = new PosteoCompas;
                        $ciclocompa->config_id = $curso->config_id;
                        $ciclocompa->curso_id = $curso->id;
                        $ciclocompa->tema_id = $posteo->id;
                        $ciclocompa->curso_compa_id = $tema_compa_obj->curso_id;
                        $ciclocompa->tema_compa_id = $value;
                        $ciclocompa->save();
                    }
                }
            }
        }
        // if ($posteo->wasChanged('tipo_ev')) {
        //     $query_pregunta = Pregunta::where('post_id',$posteo->id);
        //     $tipo_pregunta = ($posteo->tipo_ev == 'calificada') ? 'selecciona' : 'texto' ;
        //     $update_preguntas = $query_pregunta->update(['tipo_pregunta'=>$tipo_pregunta]);
        //     $verificar_preguntas = ($posteo->tipo_ev=='calificada') ? $query_pregunta->whereNull('rpta_ok')->first() : false;
        //     if($verificar_preguntas){
        //         return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
        //                     ->with('modal-info', '<strong>Tema actualizado con éxito.</strong> <br>
        //                     Se ha econtrado preguntas activas que no pertenecen al tipo de evaluación seleccionada. Porfavor dirijase a la
        //                     sección de evaluación o haz click <a href="/cursos/'.$curso->id.'/posteos/'.$posteo->id.'/preguntas" target="_blank" >aquí</a> para poder editarlas.');
        //     }
        //     if($update_preguntas){
        //         redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
        //             ->with('modal-info', '<strong>Tema actualizado con éxito.</strong> <br>
        //             Nota: Se ha configurado las preguntas a este tipo de evaluación.');
        //     }
        // }
        if ($estado_posteo || $posteo->wasChanged('evaluable')) {
            return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
                    ->with('modal-info', '<strong>Tema actualizado con éxito.</strong> <br>
                    Este cambio produce actualizaciones en el avance de los usuarios, que se ejecutarán dentro de 20 minutos.
                    Las actualizaciones se verán reflejadas en la app y en los reportes al finalizar este proceso.');
        }

        return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
                ->with('info', 'Tema actualizado con éxito.');
    }

    public function destroy(Curso $curso, Posteo $posteo)
    {
        $q_requisito = Curso::select('id','nombre')->where('requisito_id',$curso->id)->get();
        $q_temas = Posteo::where('curso_id', $posteo->curso_id)->where('estado', 1)->count();
        if($q_temas==1 && count($q_requisito)>0){
            $li  = "<ul>";
            foreach ($q_requisito as $req) {
                $li = $li."<li class='ml-4 mt-2'>".$req->nombre."</li>";
            }
            $li = $li."</ul>";
            return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
                ->with('modal-info', '<strong>No se puede eliminar este tema.</strong> <br>
                Para poder eliminar este tema es necesario quitar el requisito en los siguientes cursos'.$li);
        }
        // dd($posteo);
        $posteo->delete();

        // SOLO ACTUALIZAR LAS TABLAS RESUMEN SI EL TEMA ESTABA ACTIVO
        $descripcion = "El tema $posteo->id, se eliminó.";
        $afecta = [];
        if ($posteo->estado == 1) {
            /**
             * REGISTRAR AUDITORIA
             */
            $afecta[] = 'tablas_resumen';
        }
        $auditoria = new AuditoriaController();
        $auditoria->registrarAuditoria('tema', $posteo->id, $descripcion, $afecta, auth()->user()->id);

        // \File::delete(public_path().'/'.$posteo->imagen);
        // \File::delete(public_path().'/'.$posteo->archivo);
        // \File::delete(public_path().'/'.$posteo->video);
        //$posteo->preguntas->delete();
        //$posteo->posteo_perfiles->delete();
        //$posteo->pruebas->delete();
        //$posteo->visitas->delete();

        ////// Si el tema es evaluable, marcar CURSO también como evaluable
        $tema_evaluable = Posteo::where('curso_id', $curso->id)->where('evaluable', 'si')->first();
        if ($tema_evaluable) {
            $curso->c_evaluable = 'si';
        } else {
            $curso->c_evaluable = 'no';
        }
        $curso->save();
        //////
        if ($posteo->estado == "1") {
            return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
                ->with('modal-info', '<strong>Tema eliminado correctamente.</strong> <br>
                Este cambio produce actualizaciones en el avance de los usuarios, que se ejecutarán dentro de 20 minutos.
                Las actualizaciones se verán reflejadas en la app y en los reportes al finalizar este proceso.');
        } else {
            return redirect()->route('cursos.temas', [$curso->categoria->id, $curso->id])
                ->with('info', 'Tema eliminado correctamente');
        }
    }
    // Eliminar adjuntos
    public function del_attached_video(Posteo $posteo)
    {
        // \File::delete(public_path().'/'.$posteo->video);
        $posteo->video = NULL;
        $posteo->save();

        return redirect()->back();
    }

    public function del_attached_archivo(Posteo $posteo)
    {
        // \File::delete(public_path().'/'.$posteo->archivo);
        $posteo->archivo = NULL;
        $posteo->save();

        return redirect()->back();
    }

    /// Lista para compatibles
    public static function para_compatible($categoria_id)
    {
        // $ciclo_ids = $this->ciclos()->pluck('id');
        // $curso_ids = Curricula::whereIn('ciclo_id', $ciclo_ids)->pluck('curso_id');
        // $curso_ids = Curso::where('categoria_id', $categoria_id)->pluck('id');
        $cursos = Curso::select('id', 'nombre')->where('categoria_id', $categoria_id)->orderBy('nombre')->get();

        // $posteos = Posteo::select('id', 'nombre', 'curso_id')
        //                 ->where('categoria_id', $categoria_id)
        //                 ->whereIn('curso_id', $curso_ids)
        //                 ->where('id', '!=' ,$posteo_id)
        //                 ->where('estado', 1)
        //                 ->orderBy('curso_id')
        //                 ->orderBy('nombre')
        //                 ->get();

        return $cursos;
    }

    //UPO
    // public function actualizar_table_resumen($curso_id){
    //     $cantidad = Posteo::where('curso_id',$curso_id)->count();
    //     Resumen_x_curso::where('curso_id',$curso_id)->update(['asignados' => $cantidad]);
    //     // $resumenXcurso =Resumen_x_curso::where('curso_id',$curso_id)
    //     // $resumen_users = $resumenXcurso->get();
    //     // foreach ($resumen_users as $value) {
    //     //     dd($value);
    //     // }
    //     // ->update(['asignados' => $cantidad]);

    // }
}
