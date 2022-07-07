<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;

class Encuesta extends BaseModel
{
    protected $table = 'polls';
    // protected $fillable = ['titulo', 'imagen', 'vigencia', 'post_id', 'estado', 'created_at', 'updated_at'];
    protected $fillable = ['titulo', 'imagen', 'estado', 'created_at', 'updated_at', 'type_id', 'anonima'];

    public function preguntas()
    {
        return $this->hasMany(Encuestas_pregunta::class, 'encuesta_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    protected function search($request)
    {
        $query = self::withCount('preguntas');

        if ($request->q)
            $query->where('titulo', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        
        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    }

    public function countCoursesRelated()
    {
        $total = DB::table('curso_encuesta')->where('encuesta_id', $this->id)->count();

        return $total;
    }
    
}
