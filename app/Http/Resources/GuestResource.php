<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
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
            'id' =>             $this->id,
            'email' =>          $this->email,
            'user_id' =>        $this->user?->id,
            'state' =>          $this->user?->active ?? 'No disponible',
            'user_name' =>      $this->user?->nombre ?? 'No disponible',
            'state_name' =>     $this->status?->name ?? 'Pendiente' ,
            'state_enabled'=>   'Registrado',
            'date_invitation'=> $this->date_invitation ? Carbon::parse($this->date_invitation)->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
