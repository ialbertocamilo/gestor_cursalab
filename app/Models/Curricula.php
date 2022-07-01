<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Curricula extends Model
{
    protected $table = 'curricula';

    protected $fillable = [
        'carrera_id', 'curso_id', 'ciclo_id', 'id', 'all_criterios'
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function curricula_criterio()
    {
        return $this->hasMany(Curricula_criterio::class, 'curricula_id');
    }
}
