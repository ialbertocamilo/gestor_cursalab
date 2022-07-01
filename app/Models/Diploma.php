<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diploma extends Model
{
    protected $table = 'diplomas';
    protected $fillable = ['id', 'usuario_id', 'curso_id', 'posteo_id', 'categoria_id', 'fecha_emision', 'updated_at', 'created_at'];

    public static function generar_diploma_x_curso_y_escuela($cursos_asignados,$curso,$usuario_id)
    {
        Diploma::firstOrCreate(['usuario_id' => $usuario_id, 'curso_id' => $curso->id],
        ['usuario_id' => $usuario_id, 'curso_id' => $curso->id, 'fecha_emision' => date('Y-m-d')]);
        // Cant cursos asignados al usuario por escuela
        $asignados_x_escuela = Curso::select('id')->whereIn('id', $cursos_asignados)->where('categoria_id', $curso->categoria_id)->count();
        // Cant diplomas del curso con la misma escuela
        $cant_completados_x_escuela = Resumen_x_curso::select('id')->where('usuario_id', $usuario_id)->where('categoria_id', $curso->categoria_id)->count();
        if ($asignados_x_escuela == $cant_completados_x_escuela) {
            Diploma::firstOrCreate(['usuario_id' => $usuario_id, 'categoria_id' => $curso->categoria_id],
            ['usuario_id' => $usuario_id, 'categoria_id' => $curso->categoria_id, 'fecha_emision' => date('Y-m-d')]);
        }
    }
}