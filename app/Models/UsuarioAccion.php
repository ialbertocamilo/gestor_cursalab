<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class UsuarioAccion extends Model
{
    use SoftDeletes;

    protected $table = 'usuario_acciones';

    protected $fillable = [
        'user_id', 'type_id', 'model_type', 'model_id', 'score'
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];

    // Relationships
  
    // public function modulos()
    // {
    //     return $this->belongsToMany(Abconfig::class, 'vademecum_modulo', 'vademecum_id', 'modulo_id');
    // }

    // public function categoria()
    // {
    //     return $this->belongsTo(Taxonomia::class, 'categoria_id');
    // }

    // public function media()
    // {
    //     return $this->belongsTo(Media::class, 'media_id');
    // }

}
