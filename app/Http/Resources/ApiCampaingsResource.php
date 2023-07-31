<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiCampaingsResource extends JsonResource
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
            'description' => $this->description,
            'active' => $this->state,
            'color' => $this->color,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'file_image' => get_media_url($this->file_image),
            'file_banner' => get_media_url($this->file_banner),
            'coming_soon' => this->processUserCommingSoon($this->start_date, $this->end_date),
            'user_state' => $this->processUserStateStage(), // el estado actual del usuario
            // 'user_badges' => $this->processUserBadges(), // el estado actual del usuario
            'user_badges' => $this->badges,
            'question' => $this->question,
            'stage_id' => $this->stage_id,

            // stage campaña
            'stage_content' => $this->stage_content,
            'stage_postulate' => $this->stage_postulate,
            'stage_votation' => $this->stage_votation
        ];
        // return parent::toArray($request);
    }
}
