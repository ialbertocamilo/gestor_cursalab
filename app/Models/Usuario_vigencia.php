<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario_vigencia extends Model
{
	protected $table = 'usuario_vigencia';
    protected $fillable = [
    	'usuario_id', 'fecha_inicio', 'fecha_fin'
    ];



    public function usuarioid()
    {
        return $this->belongsTo('App\Usuario', 'usuario_id');
    }
}
