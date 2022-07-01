<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post_electivo extends Model
{
    protected $fillable = [
    	'config_id','nombre', 'resumen', 'contenido', 'cod_video', 'imagen', 'visitas', 'likes', 'archivo', 'estado', 'created_at', 'updated_at', 'evaluable', 'orden'
    ];
}