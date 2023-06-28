<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CampaignVotationSearchResource extends JsonResource
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
            'votes' => $this->votes
        ];
        // return parent::toArray($request);
    }
}
