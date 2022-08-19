<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Topic extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'content', 'imagen',
        'position', 'visits_count', 'assessable',
        'topic_requirement_id', 'type_evaluation_id', 'duplicate_id', 'course_id'
    ];

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function setAssessableAttribute($value)
    {
        $this->attributes['assessable'] = ($value === 'true' or $value === 'si' or $value === true or $value === 1 or $value === '1');
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Topic::class);
    }

    public function medias()
    {
        return $this->hasMany(MediaTema::class, 'topic_id');
    }
    public function models()
    {
        return $this->morphMany(Requirement::class, 'model');
    }
    public function requirements()
    {
        return $this->morphMany(Requirement::class, 'requirement');
    }

    public function evaluation_type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_evaluation_id');
    }

    protected static function search($request, $paginate = 15)
    {
        $q = self::withCount(['questions'])->where('course_id', $request->course_id);
        // $q = self::withCount('preguntas')
        //     ->where('categoria_id', $request->categoria_id)
        //     ->where('course_id', $request->course_id);

        if ($request->q)
            $q->where('name', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);
    }
    protected static function search_preguntas($request, $tema)
    {
        $q = Question::whereHas('type', function ($t) use ($tema) {
            $t->where('type_id', $tema->type_evaluation_id);
        })
            ->where('topic_id', $tema->id);

        if ($request->q)
            $q->where('pregunta', 'like', "%$request->q%");

        return $q->paginate($request->paginate ?? 10);
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
                        'position' => ($index + 1),
                        'topic_id' => $tema->id,
                        'value' => $valor,
                        'title' => $media['titulo'] ?? '',
                        'embed' => $media['embed'],
                        'downloadable' => $media['descarga'],
                        'type_id' => $media['tipo'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('media_topics')->insert($medias);
            endif;

            if (!empty($data['file_imagen'])) :
                $path = Media::uploadFile($data['file_imagen']);
                $tema->imagen = $path;
            endif;

            ////// Si el tema es evaluable, marcar CURSO también como evaluable
            $curso = Course::find($tema->course_id);
            $tema_evaluable = Topic::where('course_id', $tema->id)->where('assessable', 1)->first();
            $curso->assessable = $tema_evaluable ? 1 : 0;
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

    protected function getMessagesActions($tema, $data, $title)
    {
        $messages = [];

        if (
            // Al eliminar
            $tema->active === 1 ||
            // Al crear
            (isset($data['active']) && $data['active'] == 1) ||
            // Al actualizar desde lisitado (active) o desde formulario (active o assessable)
            ($tema->wasChanged('active') || $tema->wasChanged('assessable'))
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

    protected function validateTemaUpdateStatus($escuela, $curso, $tema, $estado)
    {
        $temas_activos = Topic::where('course_id', $tema->course_id)->where('active', 1)->get();

        $req_cursos = Requirement::whereHasMorph('model', [Course::class], function ($query) use ($tema) {
            $query->where('id', $tema->course->id);
        })->get();
        $req_temas = Requirement::whereHasMorph('model', [Topic::class], function ($query) use ($tema) {
            $query->where('id', $tema->id);
        })->get();

        if ((($temas_activos->count() === 1 && $tema->estado == 1)
            || $req_cursos->count() > 0
            || $req_temas->count() > 0) && !$estado) :

            $validate = collect();

            if (($temas_activos->count() == 1 && $tema->estado == 1) && $req_cursos->count() > 0) :

                $validacion = $this->validateReqCursos($req_cursos, $escuela, $curso, $tema);
                $validate->push($validacion);
            endif;

            if ($req_temas->count() > 0) :

                $validacion = $this->validateReqTemas($req_temas, $escuela, $curso, $tema);
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

    public function validateReqTemas($req_temas, $escuela, $curso,  $tema, $verb = 'desactivar')
    {
        $temp = [
            'title' => "No se puede {$verb} este tema.",
            'subtitle' => "Para poder {$verb} este tema es necesario quitar o cambiar el requisito en los siguientes temas:",
            'show_confirm' => false,
            'type' => 'req_tema_validate'
        ];
        $list2 = [];
        foreach ($req_temas as $req) {
            $requisito = Topic::where('id', $req->requirement_id)->first();
            $route = route('temas.editTema', [$escuela->id, $curso->id, $requisito->id]);
            $list2[] = "<a href='{$route}' target='_blank'>" . $requisito->name . "</a>";
        }
        $temp['list'] = $list2;
        return $temp;
    }

    public function validateReqCursos($req_cursos, $escuela, $curso, $tema, $verb = 'desactivar')
    {
        $temp = [
            'title' => "No se puede {$verb} este tema.",
            'subtitle' => "Para poder {$verb} este tema es necesario quitar o cambiar el requisito en los siguientes cursos:",
            'show_confirm' => false,
            'type' => 'req_curso_validate'
        ];
        $list1 = [];
        foreach ($req_cursos as $req) {
            $requisito = Course::where('id', $req->requirement_id)->first();
            $route = route('cursos.editCurso', [$escuela->id, $requisito->id]);
            $list1[] = "<a href='{$route}' target='_blank'>" . $requisito->name . "</a>";
        }
        $temp['list'] = $list1;
        return $temp;
    }

    protected function getDataToTopicsViewAppByUser($user, $user_courses): array
    {
        $schools = $user_courses->groupBy('schools.*.id');
        $summary_topics_user = SummaryTopic::whereHas('topic.course', function ($q) use ($user_courses) {
            $q->whereIn('id', $user_courses->pluck('id'))->where('active', ACTIVE)->orderBy('position');
        })
            ->where('user_id', $user->id)
            ->get();

        $data = [];


        return [];
    }
}
