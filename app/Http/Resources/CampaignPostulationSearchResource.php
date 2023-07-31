<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CampaignPostulationSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'fullname' => $this->user->getFullnameAttribute(),
            'criterio_value' => $this->getCriterioValueUserCampaign(), 
            'document' => $this->user->document,
            
            'user' => $this->user, // usuario data en partes
            'in_date' => $this->in_date,
            'answer' => $this->answer,
            'candidate_state' => $this->candidate_state,
            'state_sustent' => $this->state_sustent,
            
            'total' => $this->total,
            'approveds' => $this->approveds,
            'pendings' => $this->pendings
        ];
        // return parent::toArray($request);
    }
}
