<?php

namespace App\Http\Resources\Curso;

use Carbon\Carbon;
use App\Models\MediaTema;
use App\Services\FileService;
use App\Http\Controllers\CursosController;
use Illuminate\Http\Resources\Json\JsonResource;

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


        // Set assigned users to every course

//        $usersCount = collect(CursosController::$coursesUsersAssigned)
//            ->where('course_id', $this->id)
//            ->first();
//
//        $assignedUsers = $usersCount
//            ? $usersCount['count']
//            : 0;
        $assignedUsers = 1;

        $modality_code = $this->modality->code;
        $data_offline = MediaTema::dataSizeCourse($this,$request->has_offline,$request->size_limit_offline);
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

            'active_topics_count' => $this->active_topics_count,
            'inactive_topics_count' => $this->inactive_topics_count,
            'active_inactive_topics_count' => $this->active_topics_count . '/' . $this->topics_count,
            'activate_at' => $this->activate_at,
            'deactivate_at' => $this->deactivate_at,
            'assigned_users' => $assignedUsers,

            'encuesta_count' => $this->polls_count,
            'segments_count' => $this->segments_count,
            'active' => $this->active,
            'config_id' => '',
            'type' => $this->type->name ?? 'No definido',
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : '-',

            'custom_curso_nombre' => $this->getCourseTypes(),
            'curso_nombre_escuela' => [
                'curso' => $this->name,
                'escuela' => implode(', ', $schools),
            ],

            'curso_estado' => $this->getCourseStatus(),
            'modality_code' => $modality_code,
            'actualizaciones' => '',
            'is_cursalab_super_user'=> is_cursalab_superuser(),
            'is_super_user'=>auth()->user()->isAn('super-user'),

            'edit_route' => $route_edit,
            'temas_route' => $route_topics,

            'compatibilities_count' => $this->compatibilities_a_count + $this->compatibilities_b_count,
            // 'compatibilities_count' => 1,
            'compatibility_available' => get_current_workspace()->id == 25,
            'is_super_user'=>auth()->user()->isAn('super-user'),
            'has_space_offline' => $data_offline['has_space']
        ];
        if($request->hasHabilityToShowProjectButtons){
            //relation projects
            $create_project = !isset($this->project->id);
            $edit_project = !$create_project;
            $project_id = isset($this->project->id) ? $this->project->id : 0;
            $_course['create_project'] = $create_project && $modality_code == 'asynchronous';
            $_course['edit_project'] = $edit_project && $modality_code == 'asynchronous';
            $_course['project_users'] = ($edit_project && $this->active) && $modality_code == 'asynchronous';
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

    public function getCourseStatus()
    {
        $subtitles = [];

        $subtitles[] = [
            'name' => 'Temas activos: ' . $this->active_topics_count,
            'class' => $this->active_topics_count == 0 ? 'text-red text-bold' : 'text-primary',
        ];

        $subtitles[] = [
            'name' => $this->segments_count ? 'Segmentado' : 'No segmentado',
            'class' => $this->segments_count == 0 ? 'text-red text-bold' : 'text-primary',
        ];

        $active_schools_count = $this->schools->where('active', ACTIVE)->count();

        $subtitles[] = [
            'name' => 'Escuelas activas: ' . $active_schools_count,
            'class' => $active_schools_count == 0 ? 'text-red text-bold' : 'text-primary',
        ];

        $visible = ($this->active_topics_count && $this->segments_count && $active_schools_count && $this->active);

        $estado = $visible ? 'Curso visible para usuarios' : 'Curso no visible para usuarios';

        if (!$this->active) {
            $estado .= ' [Inactivo]';
        }
        // $icon_data = [];
        $data = [
            'estado' => $estado,
            'subtitles' => $subtitles,
            'icon' => $icon_data ?? NULL,
        ];

        if ($this->activate_at || $this->deactivate_at) {

            $icon_title = '';

            $activate_at = $this->activate_at ? Carbon::parse($this->activate_at)->format('d/m/Y H:i a') : 'Indefinido';
            $deactivate_at = $this->deactivate_at ? Carbon::parse($this->deactivate_at)->format('d/m/Y H:i a') : 'Indefinido';

            if ($activate_at) $icon_title .= $activate_at;
            if ($deactivate_at) $icon_title .= ' - ' . $deactivate_at;

            $data['icon'] = [
                'name' => 'mdi-calendar',
                'title' => $icon_title,
            ];

            // $estado .= ' [Programado]';
        }

        return $data;
    }

    public function getCourseTypes()
    {
        $subtitles = [];

        $subtitles[] = [
            'name' => 'Tipo: ' . ($this->type->name ?? 'No definido'),
            // 'class' => $this->active_topics_count == 0 ? 'text-red text-bold' : 'text-primary',
        ];

        $subtitles[] = [
            'name' => ($this->qualification_type->name ?? 'Sistema no definido'),
            // 'class' => $this->segments_count == 0 ? 'text-red text-bold' : 'text-primary',
        ];

        if ($this->created_at) {

            $subtitles[] = [
                'name' => $this->created_at->format('d/m/Y'),
                'title' => 'Creado: ' . $this->created_at->format('d/m/Y H:i:s'),
                // 'class' => $this->segments_count == 0 ? 'text-red text-bold' : 'text-primary',
            ];
        }


        // $active_schools_count = $this->schools->where('active', ACTIVE)->count();

        // $subtitles[] = [
        //     'name' => 'Escuelas activas: ' . $active_schools_count,
        //     'class' => $active_schools_count == 0 ? 'text-red text-bold' : 'text-primary',
        // ];

        $data = [
            'nombre' => $this->name,
            'subtitles' => $subtitles,
        ];

        return $data;
    }

}
