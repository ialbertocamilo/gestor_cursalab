<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Usuario;
use App\Models\Resumen_general;

use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class RestRankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.jwt');
        return auth()->shouldUse('api');
        Carbon::setLocale('es');
    }
    /***********************************REDISEÑO******************* */
    public function ranking()
    {
        /**
         * Retornar los 3 rankings en una sola API
         */
        $apiResponse = [];
        $app_user = auth()->user();
        $apiResponse['ranking_botica'] = $this->cargarRankingBotica($app_user->id, $app_user->botica);
        $apiResponse['ranking_zona'] = $this->cargarRankingZona($app_user->id, $app_user->grupo);
        $apiResponse['ranking_global'] = $this->cargarRankingGeneral($app_user->id, $app_user->config_id);

        return response()->json($apiResponse, 200);
    }
    /***********************************REDISEÑO******************* */
    //CARGAR RANKINGS
    public function cargarRankingBotica($user_id = null, $botica)
    {
        return $this->cargar_ranking($user_id, 'usuarios.botica', $botica);
    }

    public function cargarRankingZona($user_id = null, $grupo)
    {
        return $this->cargar_ranking($user_id, 'usuarios.grupo', $grupo);
    }

    public function cargarRankingGeneral($user_id)
    {
        $usuario = Usuario::select("config_id", "nombre")->find($user_id);
        return $this->cargar_ranking($user_id, 'usuarios.config_id', $usuario->config_id);
    }
    /*--------------------------------------------------------SUBFUNCIONES----------------------------------------------------------------*/
    private function cargar_ranking($user_id, $tipo, $data)
    {
        //Tipo -> general,zonal,botica
        $ranking = Resumen_general::join('usuarios', 'usuarios.id', '=', 'resumen_general.usuario_id')
            ->select('resumen_general.usuario_id', 'usuarios.nombre', 'resumen_general.rank', 'resumen_general.last_ev')
            ->where($tipo, $data)
            ->where('usuarios.rol','default')
            ->where('usuarios.estado',1)
            ->orderBy('resumen_general.rank', 'desc')
            ->orderBy('resumen_general.last_ev', 'asc')
            ->take(10)
            ->get();
        $ranking_usuario = $this->cargar_position_user($user_id, $tipo, $data);
        return compact('ranking', 'ranking_usuario');
    }

    private function cargar_position_user($usuario_id, $tipo, $data)
    {
        $ranking = Resumen_general::select('resumen_general.rank', 'resumen_general.last_ev')
            ->where('resumen_general.usuario_id', $usuario_id)
            ->first();

        $ranks_before_user = Resumen_general::join('usuarios', 'usuarios.id', '=', 'resumen_general.usuario_id')
            ->select('resumen_general.usuario_id', 'usuarios.nombre', 'resumen_general.rank', 'resumen_general.last_ev')
            ->where($tipo, $data)
            ->where('usuarios.rol','default')
            ->where('usuarios.estado',1)
            ->where('resumen_general.rank', '>=', $ranking->rank)
            ->orderBy('resumen_general.rank', 'desc')
            ->orderBy('resumen_general.last_ev', 'asc')
            ->get();
        $position = 1;
        $nombre = 'Este usuario es de test (No entra en el ranking)';
        foreach ($ranks_before_user as $ranks) {
            if ($ranks->usuario_id == $usuario_id) {
                $nombre = $ranks->nombre;
                break;
            }
            $position++;
        }
        $rank = $ranking->rank;
        return compact('usuario_id', 'nombre', 'rank', 'position');
    }
}
