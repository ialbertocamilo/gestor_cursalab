<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encuestas_respuesta extends BaseModel
{
    protected $table = 'encuestas_respuestas';

    protected $fillable = [
    	'encuesta_id', 'curso_id', 'pregunta_id', 'usuario_id', 'respuestas', 'tipo_pregunta', 'created_at', 'updated_at'
    ];

    public function pregunta()
    {
        return $this->belongsTo(Encuestas_pregunta::class, 'pregunta_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
