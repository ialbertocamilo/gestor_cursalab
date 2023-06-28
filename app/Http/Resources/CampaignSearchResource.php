<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CampaignSearchResource extends JsonResource
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
            'title' => $this->title,
            'active' => $this->state,
            'inicio_fin' => $this->getCampaingInicioFinFecha(),
            'etapa' => $this->getCampaingStage(),
            'modulos' => $this->modules->pluck('module_id')->toArray(),
            'stages' => [ 
                          'stage_content' => $this->stage_content,
                          'stage_postulate' => $this->stage_postulate,
                          'stage_votation'=> $this->stage_votation 
                        ],
            'edit_route' => route('votacion.edit', [$this->id]),
            'detail_route' => route('votacion.detail', [$this->id]),
            'is_super_user'=> auth()->user()->isAn('super-user')
        ];

        // return parent::toArray($request);
    }
}
