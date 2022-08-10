<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Posteo extends Model
{
    protected $table = 'topics';

    protected $fillable = [
        // 'nombre', 'resumen', 'contenido', 'cod_video', 'imagen', 'archivo', 'estado', 'created_at', 'updated_at', 'tipo_post', 'orden', 'requisito_id', 'categoria_id', 'curso_id', 'video', 'tipo_ev', 'media'
        'nombre', 'resumen', 'contenido', 'imagen', 'estado', 'created_at', 'updated_at', 'tipo_post', 'orden', 'requisito_id', 'categoria_id', 'curso_id', 'tipo_ev', 'media', 'evaluable', 'duplicado_id'
    ];

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function pruebas()
    {
        return $this->hasMany(Prueba::class, 'posteo_id');
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class, 'post_id');
    }

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class, 'post_id');
    }

    public function encuesta()
    {
        return $this->hasMany(Poll::class, 'post_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    //     public function tema()
    // {
    //     return $this->belongsTo(Posteo::class, 'requisito_id');
    // }


    public function posteo_perfiles()
    {
        return $this->hasMany(Posteo_perfil::class, 'posteo_id');
    }

    public function posteo_areas()
    {
        return $this->hasMany(Posteo_area::class, 'posteo_id');
    }

    // CONSULTA REQUISITO DE UN POSTEO
    public function posteo_requisito()
    {
        return $this->belongsTo(Posteo::class, 'requisito_id');
    }

    //
    public function compatibles()
    {
        return $this->hasMany(PosteoCompas::class, 'tema_id');
    }

    // Tag
    public function rel_tag()
    {
        return $this->hasMany(TagRelationship::class, 'element_id');
    }

    public function medias()
    {
        return $this->hasMany(MediaTema::class, 'tema_id');
    }

    public function getTipoEvaluacion()
    {
        $constantes = config('constantes.tipo_ev');
        return $constantes[$this->tipo_ev] ?? 'No evaluable';
    }

    protected function search($request, $paginate = 15)
    {
        $q = self::withCount('preguntas')
            ->where('categoria_id', $request->categoria_id)
            ->where('curso_id', $request->curso_id);

        if ($request->q)
            $q->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);
    }

    protected static function storeRequest($data, $tema = null)
    {
        try {
            DB::beginTransaction();

            if ($tema) :
                $tema->update($data);
            else :
                $tema = self::create($data);
            endif;

            $tema->medias()->delete();
            if (!empty($data['medias'])) :
                $medias = array();
                $now = date('Y-m-d H:i:s');
                foreach ($data['medias'] as $index => $media) {
                    $valor = isset($media['file']) ? Media::uploadFile($media['file']) : $media['valor'];
                    // if(!str_contains($string, 'http') && ($media['tipo']=='audio' || $media['tipo']=='video')){
                    //     $valor = env('DO_URL') . '/' .$valor;
                    // }
                    $medias[] = [
                        'orden' => ($index + 1),
                        'tema_id' => $tema->id,
                        'valor' => $valor,
                        'titulo' => $media['titulo'] ?? '',
                        'embed' => $media['embed'],
                        'descarga' => $media['descarga'],
                        'tipo' => $media['tipo'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('media_temas')->insert($medias);
            endif;

            if (!empty($data['tags'])) :
                $tema->rel_tag()->delete();
                $tempTags = [];
                foreach ($data['tags'] as $tag) {
                    $tempTags[] = [
                        'tag_id' => $tag,
                        'element_type' => 'tema',
                        'element_id' => $tema->id,
                    ];
                }
                DB::table('tag_relationships')->insert($tempTags);
            endif;


            if (!empty($data['file_imagen'])) :
                $path = Media::uploadFile($data['file_imagen']);
                $tema->imagen = $path;
            endif;

            ////// Si el tema es evaluable, marcar CURSO también como evaluable
            $curso = Curso::find($tema->curso_id);
            $tema_evaluable = Posteo::where('curso_id', $tema->id)->where('evaluable', 'si')->first();
            $curso->c_evaluable = $tema_evaluable ? 'si' : 'no';
            $curso->save();
            //////

            $tema->save();
            DB::commit();
            return $tema;
        } catch (\Exception $e) {
            DB::rollBack();
            info($e);
            return $e;
        }
    }

    protected function search_preguntas($request, $tema)
    {

        // $tax_type_eval = Taxonomy::where('group', 'topic')->where('type', 'evaluation-type')->where('id', $tema->type_evaluation_id)->get();

        // $tipo_preg = ($tax_type_eval->code === 'qualified') ? 'selecciona' : 'texto';

        $q = Question::where('topic_id', $tema->id)
            ->where('type_id', $tema->type_evaluation_id);

        if ($request->q)
            $q->where('pregunta', 'like', "%$request->q%");

        return $q->paginate($request->paginate ?? 10);
    }

    protected function validateTemaEliminar($tema, $curso)
    {
        $req_cursos = Curso::select('id', 'nombre')->where('requisito_id', $tema->curso_id)->get();
        $req_temas = Posteo::select('id', 'nombre')->where('requisito_id', $tema->id)->get();
        $temas_activos = Posteo::where('curso_id', $tema->curso_id)->where('estado', 1)->count();
        //        info("TEMAS ACTIVOS :: $temas_activos");
        //        info("REQUISITOS CURSOS COUNT :: {$req_cursos->count()}");
        //        info("REQUISITOS TEMAS COUNT :: {$req_temas->count()}");

        if ($temas_activos === 1 || $req_cursos->count() > 0) :
            //        if ($temas_activos === 1 && $tema->estado == 1) :

            //            if (($req_cursos->count() > 0 || $req_temas->count() > 0)) :

            $validate = collect();

            //                if ($temas_activos === 1 && $tema->estado === 1) {
            ////                info("validacion1");
            //                    $validacion = $this->avisoInactivacionCurso($tema, 'eliminas');
            //                    $validate->push($validacion);
            //                }

            //                if ($temas_activos === 1 && $req_cursos->count() > 0) :
            if ($req_cursos->count() > 0 && $tema->estado === 1) :
                //                info("validacion2");
                $validacion = $this->validateReqCursos($req_cursos, $tema, 'eliminar');
                $validate->push($validacion);
            endif;

            if ($req_temas->count() > 0) :
                //                info("validacion3");
                $validacion = $this->validateReqTemas($req_temas, $tema, 'eliminar');
                $validate->push($validacion);

            endif;

            if ($validate->count() === 0) return ['validate' => true];

            // Si existe algún tema que impida el envío de formulario (show_confirm = false)
            // no mostrar el botón de "Confirmar"
            $count = $validate->where('show_confirm', false)->count();

            return [
                'validate' => false,
                'data' => $validate->toArray(),
                'title' => 'Alerta',
                'show_confirm' => !($count > 0)
            ];
        //            endif;
        endif;

        return ['validate' => true];
    }

    protected function validateTemaUpdateStatus($tema, $estado)
    {
        $temas_activos = Posteo::where('curso_id', $tema->curso_id)->where('estado', 1)->get();
        $req_cursos = Curso::select('id', 'nombre')->where('requisito_id', $tema->curso->id)->get();
        $req_temas = Posteo::select('id', 'nombre')->where('requisito_id', $tema->id)->get();

        //        info("REQUISITOS CURSOS COUNT :: {$req_cursos->count()}");
        //        info("TEMAS ACTIVOS :: {$temas_activos->count()}");
        //        info("REQUISITOS TEMAS COUNT :: {$req_temas->count()}");
        //        info("TEMA ESTADO :: {$tema->estado}");
        //        info("CAMBIAR A ESTADO " . (int )($estado));

        if ((($temas_activos->count() === 1 && $tema->estado == 1)
            || $req_cursos->count() > 0
            || $req_temas->count() > 0) && !$estado) :
            //        if ($temas_activos->count() === 1 && $tema->estado == 1):

            //            if (($req_cursos->count() > 0 || $req_temas->count() > 0) && !$estado) :

            $validate = collect();

            //                if ($temas_activos->count() === 1 && $tema->estado == 1):
            ////                info("validacion1");
            //                    $validacion = $this->avisoInactivacionCurso($tema);
            //                    $validate->push($validacion);
            //                endif;

            if (($temas_activos->count() == 1 && $tema->estado == 1) && $req_cursos->count() > 0) :
                //                if ($req_cursos->count() > 0) :
                //                info("validacion2");
                $validacion = $this->validateReqCursos($req_cursos, $tema);
                $validate->push($validacion);
            endif;

            if ($req_temas->count() > 0) :
                //                info("validacion3");
                $validacion = $this->validateReqTemas($req_temas, $tema);
                $validate->push($validacion);
            endif;

            if ($validate->count() === 0) return ['validate' => true];


            // Si existe algún tema que impida el envío de formulario (show_confirm = false)
            // no mostrar el botón de "Confirmar"
            $count = $validate->where('show_confirm', false)->count();

            return [
                'validate' => false,
                'data' => $validate->toArray(),
                'title' => 'Alerta',
                'show_confirm' => !($count > 0)
            ];
        //            endif;

        endif;

        return ['validate' => true];
    }

    public function validateReqTemas($req_temas, $tema, $verb = 'desactivar')
    {
        $temp = [
            'title' => "No se puede {$verb} este tema.",
            'subtitle' => "Para poder {$verb} este tema es necesario quitar o cambiar el requisito en los siguientes temas:",
            'show_confirm' => false,
            'type' => 'req_tema_validate'
        ];
        $list2 = [];
        foreach ($req_temas as $req) {
            $route = route('temas.editTema', [$tema->curso->config_id, $tema->curso->categoria_id, $tema->curso->id, $req->id]);
            $list2[] = "<a href='{$route}' target='_blank'>" . $req->nombre . "</a>";
        }
        $temp['list'] = $list2;
        return $temp;
    }

    public function validateReqCursos($req_cursos, $tema, $verb = 'desactivar')
    {
        $temp = [
            'title' => "No se puede {$verb} este tema.",
            'subtitle' => "Para poder {$verb} este tema es necesario quitar o cambiar el requisito en los siguientes cursos:",
            'show_confirm' => false,
            'type' => 'req_curso_validate'
        ];
        $list1 = [];
        foreach ($req_cursos as $req) {
            $route = route('cursos.editCurso', [$tema->curso->config_id, $tema->curso->categoria_id, $req->id]);
            $list1[] = "<a href='{$route}' target='_blank'>" . $req->nombre . "</a>";
        }
        $temp['list'] = $list1;
        return $temp;
    }

    public function avisoInactivacionCurso($tema, $verbo = "desactivas")
    {
        $temp = [
            'title' => "Tener en cuenta",
            'subtitle' => null,
            'show_confirm' => true,
            'type' => 'course_inactivation_notice'
        ];
        $list[] = "Si {$verbo} el Tema: <strong> {$tema->nombre}</strong>, se inactivará el Curso: <strong>{$tema->curso->nombre}</strong>";
        $temp['list'] = $list;
        return $temp;
    }

    protected function getMessagesActions($tema, $data, $title)
    {
        $messages = [];

        if (
            // Al eliminar
            $tema->estado === 1 ||
            // Al crear
            (isset($data['estado']) && $data['estado'] == 1) ||
            // Al actualizar desde lisitado (estado) o desde formulario (estado o evaluable)
            ($tema->wasChanged('estado') || $tema->wasChanged('evaluable'))
        ) :

            $messages[] = [
                'title' => $title,
                'subtitle' => "Este cambio produce actualizaciones en el avance de los usuarios, que se ejecutarán dentro de 20 minutos.
                        Las actualizaciones se verán reflejadas en la app y en los reportes al finalizar este proceso.",
                'type' => 'update_message'
            ];

        endif;

        return [
            'title' => 'Aviso',
            'data' => $messages
        ];
    }
}
