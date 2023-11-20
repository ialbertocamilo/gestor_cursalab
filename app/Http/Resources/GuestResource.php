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
        $status = 'No disponible';
        $active = true;
        if($this->user){
            $status = ($this->user->active) ? 'Activo' : 'Inactivo';
            $active = boolval($this->user->active);
        }
        $color = ($active) ? '#5458ea' : '#e0e0e0' ;
        return [
            'id' =>             $this->id,
            'email' =>          $this->email,
            'user_id' =>        $this->user?->id,
            // 'status' =>         $status,
            'status' => [
                'text' => $status,
                'color' => $color,
            ],
            'active' =>         $active,
            'user_name' =>      $this->user ? $this->user?->name.' '.$this->user?->lastname.' '.$this->user?->surname : 'No disponible',
            'state_name' =>     $this->status?->name ?? 'Pendiente' ,
            'state_enabled'=>   'Registrado',
            'date_invitation'=> $this->date_invitation ? Carbon::parse($this->date_invitation)->format('d/m/Y g:i a') : 'No definido',
            'show_edit' =>      boolval($this->user?->id),
            'show_status' =>      boolval($this->user?->id),
        ];
    }
}
