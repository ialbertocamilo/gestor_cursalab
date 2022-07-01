<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $table = 'matricula';
    // protected $fillable = [
    //     'curso_id', 'perfile_id'
    // ];

    public function curricula()
    {
        return $this->hasMany(Curricula::class, 'carrera_id','carrera_id');
    }
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }
    
    public function matricula_criterio(){
        return $this->hasMany(Matricula_criterio::class, 'matricula_id');
    }
}