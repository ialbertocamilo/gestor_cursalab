<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Visita extends Model
{
    protected $fillable = [
    	'post_id', 'curso_id','usuario_id', 'sumatoria', 'descargas','estado_tema','tipo_tema'
    ];



    public function posteo()
    {
        return $this->belongsTo('App\Posteo', 'post_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'usuario_id');
    }

    protected function getVisitasPorUsuario($modulo_id = '')
    {
        $cache_name = 'visitas_usuarios_por_fecha-v2';

        $usuarios_id = Usuario::getUsuariosExcluidosDeGraficos();
        $usuarios_id = ($usuarios_id)
                        ? implode(',', $usuarios_id)
                        : '';


        $condition = '';

        if ($modulo_id)
        {
            $cursos = Curso::getIdsPorModulo($modulo_id);
            $cursos_id = ($cursos) ? implode(',', $cursos) : '';

            $condition = " AND p.curso_id IN (".$cursos_id.") ";

            $cache_name .= $modulo_id ? "-{$modulo_id}" : '';
        }

        $result = cache()->remember($cache_name, CACHE_SECONDS_DASHBOARD_GRAPHICS, function () use ($usuarios_id, $condition) {

            $data['time'] = now();

            $data['data'] = DB::select(
                DB::raw("SELECT DATE(p.created_at) AS fechita, count(*) as cant
                    FROM visitas p
                    WHERE p.usuario_id NOT IN (".$usuarios_id.")
                    ".$condition."
                    AND DATE(p.created_at) >= ( CURDATE() - INTERVAL 20 DAY )
                    GROUP BY fechita
                    ORDER BY fechita"
                )
            );
            return $data;
        });

        return $result;
    }
}
