<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    protected $fillable = [
    	'nombre', 'resumen', 'contenido', 'cod_vieo', 'imagen', '', '', 'archivo', 'estado', 'created_at', 'updated_at', 'orden'
    ];
}