<?php

namespace App\Models;

use App\Resumen_x_curso;
use Illuminate\Database\Eloquent\Model;


class Curso_encuesta extends Model
{
    protected $table = 'course_poll';

    protected $fillable = [
        'course_id', 'poll_id'
    ];

    public $timestamps = false;

    public function curso()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function encuesta()
    {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    public static function getEncuestaPendiente($usuario_id, $course_id)
    {
        $estado = null;
        $rxc = Resumen_x_curso::where('usuario_id', $usuario_id)->where('estado', 'enc_pend')->where('course_id', $course_id)->select('estado')->first();
        if ($rxc) {
            $curso_encuesta = Curso_encuesta::where('course_id', $course_id)->first();
            $estado =  $curso_encuesta ? $curso_encuesta->poll_id : null;
        }
        return $estado;
    }
}
