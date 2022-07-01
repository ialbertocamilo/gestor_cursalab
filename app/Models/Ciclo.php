<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Ciclo extends Model
{
    protected $fillable = [
    	'carrera_id', 'nombre', 'duracion', 'estado', 'secuencia'
    ];

    public function curriculas()
    {
        return $this->hasMany(Curricula::class, 'ciclo_id');
    }


    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    //

    public function compatibles()
    {
        return $this->hasMany(CicloCompas::class, 'ciclo_id_1');
    }

}
