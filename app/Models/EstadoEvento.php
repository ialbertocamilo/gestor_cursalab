<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoEvento extends Model
{
    protected $table = 'estado_evento';
    
    protected $fillable = ['estado'];
    
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
