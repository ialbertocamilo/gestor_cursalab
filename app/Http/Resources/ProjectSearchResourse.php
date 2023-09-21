<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectSearchResourse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $modules = collect([]);
        $schools = $this->course?->schools ?? [];
        foreach ( $schools as $school) {
            $modules = $modules->merge($school->subworkspaces);
        }

        $all_modules = $modules->unique();
        return [
            'id'=>$this->id,
            'active' => $this->active,
            'course'=>  $this->course?->name ?? '',
            'school'=> $this->course?->schools?->pluck('name')->implode(', ') ?? '',
            'images' => $this->getModulesImages($all_modules),
            // 'subworkspaces'=> $this->course?->schools->subworkspaces?->pluck('name')->implode(', ') || '',
            // 'usuarios_count' => $this->usuario_cursos_count,
            'usuarios_count' => $this->users_count,
            'usuarios_route' => route('project_users.list', [$this->id ]),
        ];
    }
    public function getModulesImages($modules)
    {
        $data = [];

        foreach($modules AS $module)
        {
            $data[] = [
                'name' => $module->name,
                'image' => space_url($module->logo)
            ];
        }

        return $data;
    }
}
