<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'service' => $this->service->name ?? 'No definido',
            'plan' => $this->plan->name ?? 'No definido',
            'type' => $this->type->name ?? 'No definido',
            'email' => $this->email,
            'active' => $this->active ? true : false,
            'is_super_user' => auth()->user()->isAn('super-user'),

            // 'orden' => $this->orden,

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
