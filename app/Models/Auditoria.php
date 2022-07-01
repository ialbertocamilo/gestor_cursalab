<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    
    protected $fillable = ['tipo_recurso', 'recurso_id', 'accion', 'descripcion', 'afecta', 'user_id'];
    
    protected $hidden = [
        'updated_at'
    ];
}
