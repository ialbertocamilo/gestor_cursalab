<?php

namespace App\Http\Resources;

use App\Services\DashboardService;
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


        // === sizes by extensions ===
        $office_sizes = DashboardService::loadSizeByExtensionWorkspace($this->id, 'office');
        $office_sizes = formatSize($office_sizes);

        $pdf_sizes = DashboardService::loadSizeByExtensionWorkspace($this->id, 'pdf');
        $pdf_sizes = formatSize($pdf_sizes);

        $scorm_sizes = DashboardService::loadSizeByExtensionWorkspace($this->id, 'scorm');
        $scorm_sizes = formatSize($scorm_sizes);

        $video_sizes = DashboardService::loadSizeByExtensionWorkspace($this->id, 'video');
        $video_sizes = formatSize($video_sizes);

        $image_sizes = DashboardService::loadSizeByExtensionWorkspace($this->id, 'image');
        $image_sizes = formatSize($image_sizes);

        $audio_sizes = DashboardService::loadSizeByExtensionWorkspace($this->id, 'audio');
        $audio_sizes = formatSize($audio_sizes);
        // === sizes by extensions ===

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
                'office' => [
                    'label' =>'Office',
                    'url' => $url_multimedia,
                    'filters' =>'office',
                    'size' => $office_sizes
                ],
                'pdf' => [
                    'label' =>'Pdf',
                    'url' => $url_multimedia,
                    'filters' =>'pdf',
                    'size' => $pdf_sizes
                ],
                'scorm' => [
                    'label' =>'Scorm',
                    'url' => $url_multimedia,
                    'filters' =>'scorm',
                    'size' => $scorm_sizes
                ],
                'videos' => [
                    'label' =>'Videos',
                    'url' => $url_multimedia,
                    'filters' =>'video',
                    'size' => $video_sizes
                ],
                'images' => [
                    'label' =>'Imágenes',
                    'url' => $url_multimedia,
                    'filters' =>'image',
                    'size' => $image_sizes
                ],
                'audio' => [
                    'label' =>'Audio',
                    'url' => $url_multimedia,
                    'filters' =>'audio',
                    'size' => $audio_sizes
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
