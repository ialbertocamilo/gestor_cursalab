<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResourceGeneralSubWorkspaceStatus extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $users_count_limit = ($this->parent->limit_allowed_users) ? $this->parent->limit_allowed_users['quantity'] : 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => get_media_url($this->logo),
            
            // 'users_count' => $this->users_count,
            'users_count_actives' => $this->users_count_actives,
            'users_count_limit' => $users_count_limit,
            'users_count_porcent' => calculate_porcent($this->users_count_actives, $users_count_limit, 90), // segun modulo
        ];
    
    }
}
