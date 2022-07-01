<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    protected $table = 'supervisores';

    protected $fillable = [
       'usuario_id', 'criterio_id'
    ];
}