<?php

namespace App\Http\Resources\Escuela;

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class EscuelaSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $modalidades = config('constantes.modalidad');

        $modules = $this->subworkspaces->pluck('name')->toArray();
        // info($this->orden);
        $position = null;
        if($request->canChangePosition){
            // $position = DB::table('school_subworkspace')->select('position')->where('subworkspace_id',$request->modules[0])->where('school_id',$this->id)->first()?->position;
            $position = $this->school_position;
        }
        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'name' => $this->name,
            'escuela_nombre' => [
                'name' => $this->name,
            ],
            'image' => FileService::generateUrl($this->imagen),
            'images' => $this->getModulesImages(),
            'modules' => implode(', ', $modules),
            'pivot_id_selected'=> $request->canChangePosition ? $request->modules[0] : null,
            'active' => $this->active,
            // 'orden' => $position,
            'canChangePosition' => $request->canChangePosition,
            'position' => $position,

            'modalidad' => $modalidades[$this->modalidad] ?? '',

            'edit_route' =>  route('escuelas.edit', [$this->id]),
            'cursos_count' => $this->courses_count,
            'has_no_courses' => $this->courses_count == 0,
            // 'has_no_courses' => true,

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : '-',

            'cursos_route' => route('cursos.list', [$this->id]),
            'is_super_user'=>auth()->user()->isAn('super-user'),
            'is_cursalab_super_user'=> is_cursalab_superuser(),
        ];
    }

    public function getModulesImages()
    {
        $data = [];

        foreach($this->subworkspaces AS $module)
        {
            $data[] = [
                'name' => $module->name,
                'image' => space_url($module->logo)
            ];
        }

        return $data;
    }
}
