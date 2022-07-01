<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mallas extends Model
{
	protected $table = 'perfil_malla';
    protected $fillable = [
    	'perfil_id','config_id','archivo'
    ];

    public function config()
    {
        return $this->belongsTo(Abconfig::class, 'config_id');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

}
