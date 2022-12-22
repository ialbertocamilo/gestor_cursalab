<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Services\FileService;

class SubWorkspaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $sub_workspace = $this;

        $active_users = $sub_workspace->active_users_count;
        $total_users = $sub_workspace->users_count;

        return [
            'id' => $sub_workspace->id,
//            'name' => $sub_workspace->etapa,
            'name' => $sub_workspace->name,
            'image' => space_url($sub_workspace->logo),
            'active' => $sub_workspace->active,

            // 'escuelas_count' => (string)$sub_workspace->categorias_count,
            'users_count' => (string)thousandsFormat($sub_workspace->users_count),
            'active_users' => "$active_users / $total_users",
            // 'carreras_count' => (string)thousandsFormat($sub_workspace->carreras_count),

            // 'escuelas_route' => route('escuelas.list', $sub_workspace->id),
            'users_route' => route('usuarios.list', ['subworkspace_id' => $sub_workspace->id]),
            // 'carreras_route' => route('carreras.index'),
        ];
    }
}
