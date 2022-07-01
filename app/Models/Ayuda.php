<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ayuda extends Model
{
	// protected $table = 'ayuda';
    protected $fillable = [
        'imagen', 'estado', 'orden'
    ];

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    }

    protected function search($request)
    {
        $query = self::query();

        // if ($request->q)
        //     $query->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        
        $query->orderBy($field, $sort);
        // $query->orderBy('orden', 'DESC');

        return $query->paginate($request->paginate);
    }
}
