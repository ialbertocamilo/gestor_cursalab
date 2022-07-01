<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso_perfil extends Model
{
    protected $table = 'curso_perfil';
    
    protected $fillable = [
        'curso_id', 'perfile_id'
    ];

    public $timestamps = false;


    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

}