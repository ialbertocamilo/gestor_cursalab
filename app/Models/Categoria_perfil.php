<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria_perfil extends Model
{
    protected $table = 'categoria_perfil';
    protected $fillable = [
        'categoria_id', 'perfil_id'
    ];
    public $timestamps = false;


    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

}