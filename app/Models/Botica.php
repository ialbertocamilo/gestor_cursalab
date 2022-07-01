<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Botica extends Model
{
    protected $table = 'boticas';

    protected $fillable = [
        'id', 'nombre', 'criterio_id','config_id','codigo_local','updated_at','created_at', 'grupo_nombre'
    ];

    public function criterio()
    {
        return $this->belongsTo(Criterio::class,'criterio_id');
    }

    public function grupo()
    {
        return $this->belongsTo(Criterio::class, 'criterio_id');
    }

    public function config()
    {
        return $this->belongsTo(Abconfig::class,'config_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Abconfig::class, 'config_id');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'botica_id');
    }

    protected function search($request, $paginate = 15)
    {
        $q = $this->prepareSearchQuery($request, $paginate);

        return $q->paginate($paginate);
    }

    protected function prepareSearchQuery($request)
    {
        $query = self::query()->select('*')->with('criterio')
            ->addSelect(DB::raw(" CONCAT('[', codigo_local, ']', ' - ',nombre) as nombre"));

        if ($request->config_id)
            $query->where('config_id', $request->config_id);

        return $query;
    }

    protected function searchForGrid($request)
    {
        $query = self::with('criterio')->withCount('usuarios');

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        if ($request->modulo)
            $query->where('config_id', 'like', "%$request->modulo%");

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
            
        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }
}
