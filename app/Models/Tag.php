<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
    	'nombre', 'color'
    ];

    public function relationships()
    {
        return $this->hasMany(Posteo::class, 'element_id');
    }

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
