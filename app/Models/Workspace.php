<?php

namespace App\Models;

class Workspace extends BaseModel
{
    protected $fillable = [
        'parent_id', 'criterion_value_id',
        'name', 'description', 'active',
        'logo', 'plantilla_diploma', 'codigo_matricula', 'mod_evaluaciones', 'reinicios_programado'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => ['source' => 'name', 'onUpdate' => true, 'unique' => true]
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function schools()
    {
        return $this->belongsToMany(School::class);
    }

    protected static function search($request)
    {
        $query = self::where('id', $request->id)->withCount(['schools']);

        if ($request->q)
            $query->where('name', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'id';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }
}
