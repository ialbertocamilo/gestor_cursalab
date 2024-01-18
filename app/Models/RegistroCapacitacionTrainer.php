<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroCapacitacionTrainer extends BaseModel
{
    protected $table = 'registro_capacitacion_trainers';

    protected $fillable = ['fullname','workspace_id'];
    use SoftDeletes;
}
