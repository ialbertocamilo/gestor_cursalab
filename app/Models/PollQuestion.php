<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PollQuestion extends BaseModel
{
    protected $table = 'poll_questions';

    protected $fillable = [
        'poll_id', 'type_id', 'titulo', 'opciones', 'active'
    ];

    /*

        Relationships

    --------------------------------------------------------------------------*/

    public function poll()
    {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    public function answers()
    {
        return $this->hasMany(
            PollQuestionAnswer::class, 'poll_question_id'
        );
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    /*

        Methods

    --------------------------------------------------------------------------*/


    // public function respuestasGet()
    // {
    //     return $this->hasMany(Encuestas_respuesta::class, 'pregunta_id')->get();
    // }

    // public function setEstadoAttribute($value)
    // {
    //     $this->attributes['estado'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    // }

    // Respuestas por cada pregunta
    public function respuestasEncuesta($curso_id, $grupo, $enc_id, $pregunta_id)
    {
        $where_grupo = "u.grupo = '".$grupo."' ";
        if ($grupo == 'ALL') {
            $where_grupo = '1';
        }

        $usuario_ids = DB::table('poll_questions AS er')
                        ->join('usuarios AS u', 'u.id', '=', 'er.usuario_id')
                        ->select('u.id')
                        // ->where("u.grupo", $grupo)
                        ->where("er.curso_id", $curso_id)
                        ->where('er.encuesta_id', $enc_id)
                        ->whereRaw($where_grupo)
                        ->groupBy('er.usuario_id')
                        ->pluck('u.id')->toArray();

        $tot_usu_x_enc = count($usuario_ids);

        $rptas = DB::table('poll_questions')
                    ->select('*')
                    ->where("curso_id", $curso_id)
                    ->where('pregunta_id', $pregunta_id)
                    ->whereIn('usuario_id', $usuario_ids)
                    ->get();
        return $rptas;
    }


    /**
     * Searches a record according a criteria
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    protected function search($request)
    {
        $query = self::with('type');

        if ($request->q)
            $query->where('titulo', 'like', "%$request->q%");

        if ($request->poll)
            $query->where('poll_id', $request->poll);

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        // $sort = $request->sortDesc ? ($request->sortDesc == 'true' ? 'DESC' : 'ASC') : 'DESC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    public function formatOptions()
    {
        $opciones = json_decode($this->opciones);

        $data = [];

        foreach ($opciones as $key => $opcion) {
            $data[] = ['titulo' => $opcion];
        }

        return $data;
    }

    protected function setOptionsToStore($options = [])
    {
        $data = [];

        foreach ($options as $key => $option) {
            $data[$key + 1 ] = $option['titulo'];
        }

        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }

}
