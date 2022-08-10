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
}
