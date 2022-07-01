<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CicloCompas extends Model
{
    protected $table = 'ciclos_compatibles';
    // protected $fillable = [
    // 	'config_id',
    // ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'id1');
    }
}
