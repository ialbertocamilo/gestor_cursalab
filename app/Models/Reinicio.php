<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Reinicio extends Model
{
    

    // protected $fillable = [
    // 	'nombre', 'estado'
    // ];

    public function save_reset_user($prueba,$admin_id,$tipo)
    {
        $posteos = Posteo::where('curso_id',$prueba->curso_id)->select('id')->get();
        if(count($posteos)==1){
            $res = Resumen_x_curso::where('usuario_id',$prueba->usuario_id)->where('curso_id',$prueba->curso_id)->update([
                'estado' => 'desarrollo',
            ]);
        }
        $res = Reinicio::where('usuario_id', $prueba->usuario_id)->where('posteo_id', $prueba->posteo_id)->first();
        if (!$res) {
            $dip = new Reinicio;
            $dip->tipo = $tipo;
            $dip->usuario_id = $prueba->usuario_id;
            $dip->posteo_id =  $prueba->posteo_id;
            $dip->curso_id = $prueba->curso_id;
            $dip->admin_id =  $admin_id;
            $dip->acumulado =  1;
            $dip->save();
        }
        else{
            $res->acumulado = $res->acumulado + 1;
            $res->save();
        }
    }
}
