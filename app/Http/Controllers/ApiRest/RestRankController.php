<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\SummaryUser;
use App\Models\User;
use App\Models\Usuario;
use App\Models\Resumen_general;

use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class RestRankController extends Controller
{
    /***********************************REDISEÑO******************* */
    public function ranking()
    {
        /**
         * Retornar los 3 rankings en una sola API
         */
        $apiResponse = [];
        $user = auth()->user();
        $apiResponse['ranking_global'] = $this->cargarRankingGeneral($user);
//        $apiResponse['ranking_botica'] = $this->cargarRankingBotica($user->id, $app_user->botica);
//        $apiResponse['ranking_zona'] = $this->cargarRankingZona($user->id, $app_user->grupo);

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

    public function cargarRankingGeneral($user)
    {

        return $this->cargar_ranking($user, 'general');
    }

    /*--------------------------------------------------------SUBFUNCIONES----------------------------------------------------------------*/
    private function cargar_ranking($user, $tipo, $data = null)
    {
        $ranking_usuario = $this->cargar_position_user($user, $tipo, $data);

        //Tipo -> general,zonal,botica
        if ($tipo == 'general')
            $q_ranking = SummaryUser::withWhereHas('user', function ($q) use ($user) {
                $q->select('id', 'name', 'lastname', 'surname')
                    ->where('subworkspace_id', $user->subworkspace_id);
            });


        $ranking = $q_ranking->whereRelation('user', 'active', ACTIVE)
            ->whereNotNull('last_time_evaluated_at')
            ->orderBy('score', 'desc')
            ->orderBy('last_time_evaluated_at', 'asc')
            ->take(10)
            ->get();

        $temp = [];
        $i = 0;
        $current = false;
        foreach ($ranking as $rank) {
            $i++;
            $current = $i === $ranking_usuario['position'];
            $temp[] = [
                'usuario_id' => $rank->user_id,
                'nombre' => $rank->user->fullname,
                'rank' => $rank->score,
                'current' => $current,
                'last_ev' => $rank->last_time_evaluated_at
            ];
        }

        if (!$current && $ranking_usuario['position'] && count($temp) === 10) $temp[] = [
            'usuario_id' => $ranking_usuario['usuario_id'],
            'nombre' => $ranking_usuario['nombre'],
            'rank' => $ranking_usuario['rank'],
            'last_ev' => $ranking_usuario['last_ev']
        ];

        return ['ranking' => $temp,/* 'ranking_usuario' => $ranking_usuario*/];
    }

    private function cargar_position_user($user, $tipo, $data)
    {

        $summary_user = SummaryUser::getCurrentRow($user);
        if (!$summary_user) return ['position' => null];

        if ($tipo == 'general')
            $q_ranking = SummaryUser::whereRelation('user', 'subworkspace_id', $user->subworkspace_id);

        $ranks_before_user = $q_ranking->whereRelation('user', 'active', ACTIVE)
            ->whereNotNull('last_time_evaluated_at')
            ->where('score', '>=', $summary_user->score ?? 0)
            ->orderBy('score', 'desc')
            ->orderBy('last_time_evaluated_at', 'asc')
            ->get();

        $position = 1;
        $nombre = 'Este usuario es de test (No entra en el ranking)';
        $last_ev = null;
        foreach ($ranks_before_user as $ranks) {
            if ($ranks->user_id == $user->id) {
                $nombre = $user->fullname;
                $last_ev = $ranks->last_time_evaluated_at;
                break;
            }
            $position++;
        }
        $rank = $summary_user?->score;

        return [
            'usuario_id' => $user->id,
            'last_ev' => $last_ev,
            'nombre' => $nombre,
            'rank' => $rank,
            'position' => $position
        ];
    }
}
