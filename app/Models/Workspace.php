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

}
