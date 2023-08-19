<?php

namespace App\Http\Resources;

use App\Models\Workspace;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkspaceResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => FileService::generateUrl($this->logo),
            'logo_negativo' => FileService::generateUrl($this->logo_negativo),
            'modules_count' => $this->subworkspaces_count,
            'courses_count' => $this->courses_count,
            // 'modules_count' => Workspace::countModules($this->id),
            'users_count' => thousandsFormat(Workspace::countUsers($this->id)),
            'is_super_user' => auth()->user()->isAn('super-user'),
            'is_cursalab_super_user' => is_cursalab_superuser(),
            // 'is_super_user' => false,
            // 'users_count' => thousandsFormat($this->users_count),
            // 'schools_count' => Workspace::countUsers($this->id),
            // 'schools_count' => $this->schools_count,

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
