<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Update_usuarios extends Model
{
    protected $table = 'update_usuarios';
    
    protected $fillable = ['id','usuarios_id','config_id','categoria_id','curso_id','accion','tipo','total','extra_info','estado'];
}
