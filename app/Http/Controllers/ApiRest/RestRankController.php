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
        foreach ($ranking as $rank) {
            $temp[] = [
                'usuario_id' => $rank->user_id,
                'nombre' => $rank->user->fullname,
                'rank' => $rank->score,
                'last_ev' => $rank->last_time_evaluated_at
            ];
        }

        $ranking_usuario = $this->cargar_position_user($user, $tipo, $data);

        return ['ranking' => $temp, 'ranking_usuario' => $ranking_usuario];
    }

    private function cargar_position_user($user, $tipo, $data)
    {
        $summary_user = SummaryUser::getCurrentRow($user);

        if ($tipo == 'general')
            $q_ranking = SummaryUser::whereRelation('user', 'subworkspace_id', $user->subworkspace_id);

        $ranks_before_user = $q_ranking->whereRelation('user', 'active', ACTIVE)
            ->where('score', '>=', $summary_user->score)
            ->orderBy('score', 'desc')
            ->orderBy('last_time_evaluated_at', 'asc')
            ->get();
        $position = 1;
        $nombre = 'Este usuario es de test (No entra en el ranking)';
        foreach ($ranks_before_user as $ranks) {
            if ($ranks->user_id == $user->id) {
                $nombre = $user->fullname;
                break;
            }
            $position++;
        }
        $rank = $summary_user->score;

        return [
            'usuario_id' => $user->id,
            'nombre' => $nombre,
            'rank' => $rank,
            'position' => $position
        ];
    }
}
