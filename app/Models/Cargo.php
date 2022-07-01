<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table = 'cargos';

    protected $fillable = [
        'id', 'nombre'
    ];

    protected function search($request)
    {
        $query = self::query();

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
            
        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }
}
