<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario_nivel extends Model
{
	protected $table = 'usuario_nivel';
    
    protected $fillable = [
    	'user_padre_id', 'user_hijo_id', 'relacion','created_at','updated_at'
    ];

    public function padre()
    {
        return $this->belongsTo(Usuario::class, 'user_padre_id');
    }


    public function hijo()
    {
        return $this->belongsTo(Usuario::class, 'user_hijo_id');
    }

}