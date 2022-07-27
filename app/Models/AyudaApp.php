<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyudaApp extends Model
{
    protected $table = 'ayuda_app';
    protected $fillable = [
        'nombre', 'orden','check_text_area'
    ];

    public function getCheckTextAreaAttribute($value){
        return boolval($value);
    }

    public function setCheckTextAreaAttribute($value)
    {
        $this->attributes['check_text_area'] = (
            $value ==='true' OR
            $value === true OR
            $value === 1 OR
            $value === '1'
        );
    }

    protected function search($request)
    {
        $query = self::query();

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }
}
