<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resumen_x_curso extends Model
{
    protected $table = 'resumen_x_curso';

    protected $fillable = [
        'id','usuario_id', 'curso_id', 'categoria_id','last_ev','asignados','aprobados','realizados','revisados','desaprobados','nota_prom','intentos' ,'visitas','porcentaje','libre','estado','estado_rxc'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
