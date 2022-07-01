<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCriterio extends Model
{
    protected $table = 'tipo_criterios';

    protected $fillable = ['nombre', 'nombre_plural', 'data_type', 'tipo_criterio_superior_id', 'estado','id', 'orden','general', 'obligatorio'];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function criterios()
    {
        return $this->hasMany(Criterio::class, 'tipo_criterio_id');
    }

    public function setObligatorioAttribute($value)
    {
        $this->attributes['obligatorio'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    }

    protected function search($request)
    {
        $query = self::withCount('criterios');

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        // if ($request->module)
        //     $query->where('config_id', 'like', "%$request->module%");

        // $query->orderBy('orden','DESC');

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
            
        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    protected function getForSelect()
    {
        return TipoCriterio::select('id', 'nombre')
                        ->get();
    }
}
