<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    // public $timestamps = false;
    protected $table = 'incidencias';
    protected $fillable = ['id', 'tipo', 'total', 'mensaje', 'afectados','comando', 'created_at', 'updated_at'];

    // protected function storeZoomIncidence($request)
    // {
    //     $data = [
    //         'tipo' => ,
    //         'mensaje' => ,
    //         'estado' => 1,
    //         'afectados' => $request->users(),
    //         // 'total' => 1,
    //         // 'comando' => 1,
    //     ];

    //     $incidencia = Incidencia::create($data);

    //     return $incidencia;
    // }

    protected function search($request)
    {
        $query = self::query();

        $query->where('tipo', '<>', 'ejecutando');
        $query->where('estado', 1);

        // $query->orderBy('orden','DESC');

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        // $sort = $request->sortDesc ? ($request->sortDesc == 'true' ? 'DESC' : 'ASC') : 'DESC';
        
        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }
}
