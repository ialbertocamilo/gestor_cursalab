<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSearchResource extends JsonResource
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
            'name' => $this->fullname,
            'email_gestor' => $this->email_gestor,
            'document' => $this->document ?? 'Sin documento',
            'active' => $this->active,
     
            // 'reporte_route' => route('exportar.node', ['dni' => $this->document]),
            'is_super_user' => auth()->user()->isAn('super-user'),
            // 'is_super_user'=> true,

        ];
    }
}
