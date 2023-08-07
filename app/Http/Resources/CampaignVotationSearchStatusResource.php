<?php

namespace App\Http\Resources;

use App\Models\CampaignRequirements;
use App\Models\CampaignSummoneds;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignVotationSearchStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $users_criterio = CampaignRequirements::usersCheckCriterioDate_And_CriterioValue(
                                $request->data, 
                                $request->workspace_id, 
                                $request->sub_workspaces,
                                $request->criterio_id_fecha_inicio_reconocimiento,
                                $this->id
                            )->count();

        $user_emiting_votes = CampaignSummoneds::candidates_votes_criterio(
                                $request->campaign_id,
                                $request->criterio_id, 
                                $this->id
                            );

        $criterio_porcent = ($users_criterio > 0) ? 
                            ($user_emiting_votes * 100) / $users_criterio : 0;

        return [
            'id' => $this->id,
            'criterio' => $this->name,
            'users_criterio' => $users_criterio, // total de usuarios por criterio
            'users_waiting_votes' => ($users_criterio * 3), // votos esperados
            'users_emiting_votes' => $user_emiting_votes, // votos emitidos
            'criterio_porcent' => round($criterio_porcent), // porcentaje
            'porcent_state' => $criterio_porcent > $request->porcent, // porcentaje
        ];
    }
}
