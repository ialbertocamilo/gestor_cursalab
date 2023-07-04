<?php

namespace App\Http\Resources;

use App\Services\DashboardService;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceListGeneralWorkspacesStatus extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // == subworkspaces
        $this->subworkspaces->each(function ($subworkspace) {
            $subworkspace->logo = get_media_url($subworkspace->logo); // media_url
        });
        // == subworkspaces

        $workspace_size_medias = (int) $request->workspaces_storage
                                               ->firstWhere('id', $this->id)
                                               ->medias_sum_size; // en KB
        
        $users_count = $this->subworkspaces->sum('users_count');
        $users_count_limit = $this->limit_allowed_users ? $this->limit_allowed_users['quantity'] : null;
        
        $size_medias_limit = $this->limit_allowed_storage ?? null;
        $size_medias_storage = formatSize($workspace_size_medias);
        $size_medias_storage_value = formatSize($workspace_size_medias, parsed:false);
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => get_media_url($this->logo),
            
            'users_count' => $users_count,
            'users_count_limit' => $users_count_limit,
            'users_count_porcent' => calculate_porcent($users_count, $users_count_limit, 90),

            'size_medias_storage' => $size_medias_storage,
            'size_medias_limit' => $size_medias_limit,
            'size_medias_porcent' => calculate_porcent($size_medias_storage_value['size'], $size_medias_limit, 90),
        ];
    }
}
