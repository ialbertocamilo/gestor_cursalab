<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Pregunta_frecuente extends Model
{
    protected $table = 'preguntas_frec';

    protected $fillable = [
    	'pregunta', 'respuesta', 'estado', 'orden'
    ];

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    }

    protected function search($request)
    {
        $query = self::query();

        if ($request->q)
            $query->where('pregunta', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        
        $query->orderBy($field, $sort);
        
        // $query->latest();

        return $query->paginate($request->paginate);
    }
}
