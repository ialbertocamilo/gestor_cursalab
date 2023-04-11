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
        if (is_null($request->school_id)) {
//            $route_edit = route('curso.editCurso', [$this->id]);
//            $route_topics = route('tema.list', [$this->id]);
            $route_edit = route('cursos.editCurso', [$first_school->id, $this->id]);
            $route_topics = route('temas.list', [$first_school->id, $this->id]);
        } else {
            $route_edit = route('cursos.editCurso', [$request->school_id, $this->id]);
            $route_topics = route('temas.list', [$request->school_id, $this->id]);
        }

        $schools = $this->schools->pluck('name')->toArray();


        $modules = [];

        foreach ($this->segments as $segment) {

            foreach ($segment->values as $segment_value) {
                if ($segment_value?->criterion_value?->value_text)
                    $modules[] = $segment_value->criterion_value->value_text;

            }
        }

        $modules = array_unique($modules);


        return [
            'id' => $this->id,
            'name' => $this->name,
            'orden' => $this->position,
            // 'position' => $this->position,
            'nombre' => $this->name,
            'schools' => implode(',', $schools),
            'modules' => implode(',', $modules),
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
}
