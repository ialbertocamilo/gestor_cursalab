<?php

namespace App\Models;

class Workspace extends BaseModel
{
    protected $fillable = [
      'name', 'description', 'active'
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
