<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\School;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Workspace;
use App\Models\SortingModel;
use Illuminate\Http\Request;
use App\Models\SchoolSubworkspace;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Escuela\EscuelaSearchResource;
use App\Http\Requests\Escuela\EscuelaStoreUpdateRequest;

class EscuelaController extends Controller
{
    public function search(Workspace $abconfig, Request $request)
    {
        // $workspace = session('workspace');
        // $workspace_id = (is_array($workspace)) ? $workspace['id'] : null;

        // $request->workspace_id = $workspace_id;
        $request->canChangePosition =   boolval(
                                        isset($request->modules) 
                                        && count($request->modules) == 1 
                                        && !isset($request->active)
                                        && !isset($request->dates)
                                    );
        $escuelas = School::search($request);
        EscuelaSearchResource::collection($escuelas);

        return $this->success($escuelas);
    }

    public function searchCategoria(School $school)
    {
        $reinicio_automatico = json_decode($school->scheduled_restarts);
        $school->reinicio_automatico = $reinicio_automatico->activado ?? false;
        $school->reinicio_automatico_dias = $reinicio_automatico->reinicio_dias ?? 0;
        $school->reinicio_automatico_horas = $reinicio_automatico->reinicio_horas ?? 0;
        $school->reinicio_automatico_minutos = $reinicio_automatico->reinicio_minutos ?? 0;
        // $nombre_ciclo_0 = DB::table('nombre_schools')->where('school_id', $school->id)->first();
        // $school->nombre_ciclo_0 = $nombre_ciclo_0->nombre ?? null;
        $school->makeHidden('scheduled_restarts');

        $school->load('subworkspaces');

        $workspace = get_current_workspace();

        $school->plantilla_diploma = $school->plantilla_diploma ? get_media_url($school->plantilla_diploma) : null;

        $modules = Workspace::where('parent_id', $workspace?->id)
            ->select('id', 'name')->get();

        return $this->success([
            'escuela' => $school,
            'modules' => $modules,
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        $workspace = get_current_workspace();

        $modules = Workspace::where('parent_id', $workspace?->id)
            ->whereIn('id', current_subworkspaces_id())
            ->select('id', 'name')->get();

        return $compactResponse ? [] : $this->success(compact('modules'));
    }

    public function store(EscuelaStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $escuela = School::storeRequest($data);

        $msg = 'Escuela creado correctamente.';
        return $this->success(compact('escuela', 'msg'));
    }

    public function update(EscuelaStoreUpdateRequest $request, School $school)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        School::storeRequest($data, $school);

        $msg = 'Escuela actualizada correctamente.';

        return $this->success(compact('school', 'msg'));
    }

    public function destroyEscuela(School $school)
    {
        // $validate = Categoria::validateEscuelaEliminar($school);

        if ($school->courses()->count() > 0)
            return $this->error('La escuela tiene cursos.', 422, [['Para eliminar la escuela no debe tener cursos.']]);

        SortingModel::deletePositionInPivotTable(SchoolSubworkspace::class,School::class,[
            'school_id' => $school->id
        ]);
        $school->delete();
        return $this->success(['msg' => 'Escuela eliminada correctamente.']);
    }

    // public function updateStatus(Abconfig $abconfig, Categoria $categoria)
    // {
    //     //        return $categoria;

    //     $estado = ($categoria->estado == 1) ? 0 : 1;
    //     $categoria->estado = $estado;
    //     $categoria->save();
    //     return $this->success(['info' => 'Estado actualizado con éxito.']);
    // }

    public function copy(School $school)
    {
        $items = School::getCoursesForTree($school->courses);

        $items_destination = School::getAvailableForTree($school);

        return $this->success(compact('school', 'items', 'items_destination'));
    }

    public function copyContent(Request $request, School $school)
    {
        $selections = $request->selection_source;
        $destinations = $request->selection_destination;

        $workspace = get_current_workspace();

        $subworkspace_ids = [];

        foreach ($destinations as $destination) {

            $part = explode('_', $destination);
            $subworkspace_ids[] = $part[1]; 
        }

        $data = $this->buildSourceTreeSelection($selections);

        return $data;

        $subworkspaces = Workspace::whereIn('id', $subworkspace_ids)->get();
        // $_schools = School::whereIn('id', $data['school_ids'])->get();
        $_courses = Course::whereIn('id', $data['course_ids'])->get();
        $_topics = Topic::with('questions', 'medias')->whereIn('id', $data['topic_ids'])->get();

        // foreach ($data['courses'] as $course_id => $course_ids) {

        //     $_course = $_courses->where('id', $course_id)->first();

        //     $course_data = $_course->toArray();
        //     $course_data['external_id'] = $_course->id;

        //     foreach ($subworkspaces as $subworkspace) {

        //         $school = $subworkspace->schools()->where('name', $_school->name)->first();

        //         if ( ! $school ) {

        //             $school_position = ['position' => $subworkspace->schools()->count() + 1];

        //             $school = $subworkspace->schools()->create($school_data, $school_position);
        //         }

        //         foreach ($course_ids['courses'] as $course_id => $topic_ids) {

        //             $_course = $_courses->where('id', $course_id)->first();

        //             $course_data = $_course->toArray();
        //             $course_data['external_id'] = $_course->id;

        //             $course = $school->courses()->create($course_data);

        //             $workspace->courses()->attach($course);

        //             foreach ($topic_ids['topics'] as $topic_id) {

        //                 $_topic = $_topics->where('id', $topic_id)->first();

        //                 $topic_data = $_topic->toArray();
        //                 $topic_data['external_id'] = $_topic->id;

        //                 $topic = $course->topics()->create($topic_data);

        //                 $topic->medias()->createMany($_topic->medias->toArray());
        //                 $topic->questions()->createMany($_topic->questions->toArray());
        //             }
        //         }
        //     }
        // }

        return $this->success(['msg' => 'Contenido duplicado correctamente.']);
    }

    public function buildSourceTreeSelection($selections)
    {
        $courses = [];
        $course_ids = [];
        $topic_ids = [];

        foreach ($selections as $index => $selection) {

            $sections = explode('-', $selection);
            $data = [];

            foreach ($sections as $position => $section) {

                $part = explode('_', $section);

                $model = $part[0];
                $id = $part[1];

                ${$model.'_ids'}[$id] = $id;

                $row = [
                    'model' => $model,
                    'id' => $id,
                ];

                $data[] = $row; 

                // $courses[$index][$position] = $row;  
            }

            $course_id = $data[0]['id'];
            $topic_id = $data[1]['id'] ?? NULL;

            if ($topic_id) {

                $courses[$course_id]['topics'][] = $topic_id;

            } else {
                
                $courses[$course_id]['topics'] = [];
            }

        }

        return compact('course_ids', 'topic_ids', 'courses');
    }

}
