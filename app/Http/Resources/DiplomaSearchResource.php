<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiplomaSearchResource extends JsonResource
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
            'id' => $this->id,
            'image' => get_media_url($this->media->file),
            'title' => $this->title,
            'active' => $this->active,
            'd_objects' => json_decode($this->d_objects),
            's_objects' => json_decode($this->s_objects),
            'edit_route' => route('diploma.edit', [$this->id]),
            'is_super_user'=> auth()->user()->isAn('super-user')
        ];
    }
}

    /*
    public function toArray($request)
    {
        $first_school = $this->schools->first();

        $school_id = $request->school_id ?? $first_school->id ?? NULL;

        $route_edit = route('cursos.editCurso', [$school_id, $this->id]);
        $route_topics = route('temas.list', [$school_id, $this->id]);

        $schools = $this->schools->pluck('name')->toArray();

        $modules = collect([]);

        foreach ($this->schools as $school) {
            $modules = $modules->merge($school->subworkspaces);
        }

        $all_modules = $modules->unique();

        $modules = array_unique($modules->pluck('name')->toArray());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'orden' => $this->position,
            // 'position' => $this->position,
            'nombre' => $this->name,
            'schools' => implode(', ', $schools),
            'modules' => implode(', ', $modules),
            'images' => $this->getModulesImages($all_modules),
            'first_school_id' => $first_school,
            'image' => FileService::generateUrl($this->imagen),
            // 'medium_image' => FileService::generateUrl($this->imagen),
            'temas_count' => $this->topics_count,
            'encuesta_count' => $this->polls_count,
            'segments_count' => $this->segments_count,
            'active' => $this->active,
            'config_id' => '',
            'type' => $this->type->name ?? 'No definido',
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : '-',

            'custom_curso_nombre' => '',

            'actualizaciones' => '',

            'edit_route' => $route_edit,
            'temas_route' => $route_topics,

            'compatibilities_count' => $this->compatibilities_a_count + $this->compatibilities_b_count,
            // 'compatibilities_count' => 1,
            'compatibility_available' => get_current_workspace()->id == 25,
            'is_super_user'=>auth()->user()->isAn('super-user')
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
    }*/
