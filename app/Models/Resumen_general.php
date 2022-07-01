<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resumen_general extends Model
{
    protected $table = 'resumen_general';

    protected $fillable = [
        'usuario_id', 'cur_asignados', 'intentos', 'rank','tot_completados','nota_prom','porcentaje','actualizado','last_ev'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
