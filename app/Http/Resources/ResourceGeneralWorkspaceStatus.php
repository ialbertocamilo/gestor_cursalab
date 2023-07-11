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
        $users_count_inactives = $this->subworkspaces->sum('users_count_inactives');

        $users_count_limit = $this->limit_allowed_users ? $this->limit_allowed_users['quantity'] : 0;

        $size_medias_limit = $this->limit_allowed_storage ?? 0;
        $size_medias_storage = formatSize($this->medias_sum_size);
        $size_medias_storage_value = formatSize($this->medias_sum_size, parsed:false);

        $url_multimedia = url('/multimedia?module_data=multimedia');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => get_media_url($this->logo),

            'users_count_actives' => $users_count_actives,
            'users_count_inactives' => $users_count_inactives,
            'users_count_limit' => $users_count_limit,
            'users_count_porcent' => calculate_porcent($users_count_actives, $users_count_limit, 90),

            'size_medias_storage' => $size_medias_storage,
            'size_medias_limit' => $size_medias_limit,
            'size_medias_porcent' => calculate_porcent($size_medias_storage_value['size'], $size_medias_limit, 90),

            'route_multimedia' => $url_multimedia,
            'routes_redirects' => [
                // 'courses' => [
                //     'label' => 'Cursos',
                //     'url' => url('/cursos'),
                //     'filters' => NULL,
                // ],
                'pdf' => [
                    'label' =>'Pdf',
                    'url' => $url_multimedia,
                    'filters' =>'pdf',
                ],
                'scorm' => [
                    'label' =>'Scorm',
                    'url' => $url_multimedia,
                    'filters' =>'scorm',
                ],
                'videos' => [
                    'label' =>'Videos',
                    'url' => $url_multimedia,
                    'filters' =>'video',
                ],
                'images' => [
                    'label' =>'Imagenes',
                    'url' => $url_multimedia,
                    'filters' =>'image',
                ],
                'audio' => [
                    'label' =>'Audio',
                    'url' => $url_multimedia,
                    'filters' =>'audio',
                ],
            ],
            
            'route_user_actives' => [
                'url' => url('/usuarios?module_data=usuarios'),
                'filters' => [
                    'active' => 1
                ]
            ],
            'route_user_inactives' => [
                'url' => url('/usuarios?module_data=usuarios'),
                'filters' => [
                    'active' => 2
                ]
            ]
        ];

    }
}
