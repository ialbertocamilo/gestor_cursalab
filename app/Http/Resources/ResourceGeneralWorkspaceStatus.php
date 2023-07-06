<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResourceGeneralWorkspaceStatus extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $users_count_actives = $this->subworkspaces->sum('users_count_actives');
        $users_count_limit = $this->limit_allowed_users ? $this->limit_allowed_users['quantity'] : 0;
        
        $size_medias_limit = $this->limit_allowed_storage ?? 0;
        $size_medias_storage = formatSize($this->medias_sum_size);
        $size_medias_storage_value = formatSize($this->medias_sum_size, parsed:false);
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => get_media_url($this->logo),
            
            'users_count_actives' => $users_count_actives,
            'users_count_limit' => $users_count_limit,
            'users_count_porcent' => calculate_porcent($users_count_actives, $users_count_limit, 90),

            'size_medias_storage' => $size_medias_storage,
            'size_medias_limit' => $size_medias_limit,
            'size_medias_porcent' => calculate_porcent($size_medias_storage_value['size'], $size_medias_limit, 90),

            'routes_redirects' => [
                'courses' => 'url_courses',
                'topics' => 'url_courses',
                'pdf' => 'url_courses',
                'scorm' => 'url_courses',
                'office' => 'url_courses',
                'video' => 'url_courses',
                'Imagenes' => 'url_courses',
                'Audio' => 'url_courses',
            ]
        ];
    
    }
}