<?php

namespace App\Models;

use App\Resumen_x_curso;
use Illuminate\Database\Eloquent\Model;


class Curso_encuesta extends Model
{
    protected $table = 'curso_encuesta';

    protected $fillable = [
        'curso_id', 'encuesta_id'
    ];

    public $timestamps = false;

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function encuesta()
    {
        return $this->belongsTo(Poll::class, 'encuesta_id');
    }

    public static function getEncuestaPendiente($usuario_id,$curso_id)
    {
         $estado = null;
         $rxc= Resumen_x_curso::where('usuario_id',$usuario_id)->where('estado','enc_pend')->where('curso_id',$curso_id)->select('estado')->first();
         if($rxc){
            $curso_encuesta = Curso_encuesta::where('curso_id',$curso_id)->first();
            $estado =  $curso_encuesta ? $curso_encuesta->encuesta_id : null;
         }
         return $estado;
    }
}
