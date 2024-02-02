<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;

class Prueba extends Model
{
    protected $fillable = [
    	'posteo_id', 'usuario_id','curso_id','intentos', 'rptas_ok', 'rptas_fail', 'nota', 'resultado', 'historico','last_ev','fuente','created_at', 'updated_at',
    ];

    public function posteo()
    {
        return $this->belongsTo('App\Posteo', 'posteo_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'usuario_id');
    }

    public function curso()
    {
        return $this->belongsTo('App\Curso', 'curso_id');
    }

    protected function getTopBoticas($modulo_id = NULL)
    {
        $cache_name = 'pruebas_top_boticas-v2';
        $cache_name .= $modulo_id ? "-modulo-{$modulo_id}" : '';

        $excluir_usuarios = Usuario::getUsuariosExcluidosDeGraficos();

        $result = cache()->remember($cache_name, CACHE_SECONDS_DASHBOARD_GRAPHICS, function () use ($modulo_id, $excluir_usuarios) {

            $data['time'] = now();

            $data['data'] = DB::table('pruebas')
                                ->join('usuarios', 'usuarios.id', '=', 'pruebas.usuario_id')
                                ->whereNotIn('usuario_id', $excluir_usuarios)
                                ->when($modulo_id, function($q) use ($modulo_id){
                                    $q->where('usuarios.config_id', $modulo_id);
                                })
                                ->selectRaw('COUNT(usuario_id) as total_usuarios, usuarios.botica AS botica')
                                ->where('resultado', 1)
                                ->where('usuarios.estado', 1)
                                ->groupBy('usuarios.botica')
                                ->orderBy('total_usuarios', 'DESC')
                                ->limit(10)
                                ->get(['total_usuarios', 'botica']);

            return $data;
        });

        return $result;
    }

    protected function getEvaluacionesPorfecha($modulo_id = '')
    {
        $cache_name = 'evaluaciones_por_fecha-v2';

        $usuarios_id = Usuario::getUsuariosExcluidosDeGraficos();
        $usuarios_id = ($usuarios_id) ? implode(',', $usuarios_id) : '';

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
                DB::raw(
                    "SELECT DATE(p.created_at) AS fechita, count(*) as cant
                    FROM pruebas p
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

    public static function reinicioIntentosMasivos($posteosIds, $usuario_id)
    {
        Prueba::whereIn('posteo_id', $posteosIds)
                ->where('usuario_id', $usuario_id)
                ->update([
                        'intentos' => 0,
                        'fuente' => 'resetm'
                        // 'rptas_ok' => NULL,
                        // 'rptas_fail' => NULL,
                        // 'nota' => NULL,
                        // 'resultado' => 0,
                        // 'usu_rptas' => NULL,
                    ]);
    }
}

