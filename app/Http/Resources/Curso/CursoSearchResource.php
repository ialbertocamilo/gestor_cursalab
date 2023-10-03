<?php

namespace App\Http\Resources\Curso;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\FileService;

class CursoSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
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
        $position = null;
        $pivot_id_selected = null;
        if($request->canChangePosition){
            $position = $this->course_position;
            $pivot_id_selected = $request->school_id  ?? $request->schools[0];
        }

        $_course = [
            'id' => $this->id,
            'name' => $this->name,
            // 'orden' => $this->position,
            // 'position' => $this->position,
            'pivot_id_selected'=> $pivot_id_selected,
            'position' => $position,
            'canChangePosition' => $request->canChangePosition,
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
            'is_super_user'=>auth()->user()->isAn('super-user'),
        ];
        if($request->hasHabilityToShowProjectButtons){
            //relation projects
            $create_project = !isset($this->project->id);
            $edit_project = !$create_project;
            $project_id = isset($this->project->id) ? $this->project->id : 0;
            $_course['create_project'] = $create_project;
            $_course['edit_project'] = $edit_project;
            $_course['project_users'] = ($edit_project && $this->active);
            $_course['project_users_route'] = route('project_users.list', [$project_id]);
            $_course['project'] = $this->project;
        }
        return $_course;
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
