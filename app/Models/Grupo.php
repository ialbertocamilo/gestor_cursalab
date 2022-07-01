<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = [
    	'nombre', 'estado'
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'grupo_id');
    }
}
