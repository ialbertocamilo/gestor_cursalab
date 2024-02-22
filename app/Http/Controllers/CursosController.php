<?php

namespace App\Http\Controllers;

use App\Models\Mongo\CourseInfoUsersM;
use App\Models\RegistroCapacitacionTrainer;
use DB;
use App\Models\Poll;
use App\Models\Ciclo;
use App\Models\Curso;

use App\Models\Media;
use App\Models\Topic;
use App\Models\Course;
use App\Models\Person;
use App\Models\Posteo;

// use App\Perfil;
// use App\Curso_perfil;
// use App\Posteo_perfil;
use App\Models\School;
use App\Models\Ability;
use App\Models\Carrera;
use App\Models\Abconfig;
use App\Models\Pregunta;
use App\Models\Taxonomy;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\Workspace;
use App\Models\Requirement;
use App\Models\CourseSchool;

use App\Models\SortingModel;

use Illuminate\Http\Request;
use App\Models\Curso_encuesta;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\CursoStoreRequest;
use App\Http\Requests\Curso\MoverCursoRequest;
use App\Exports\Course\CourseSegmentationExport;

use App\Http\Resources\Curso\CursoSearchResource;
use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Requests\Curso\CursoEncuestaStoreUpdate;
use App\Http\Requests\Curso\CursosStoreUpdateRequest;

class CursosController extends Controller
{

    public static $coursesUsersAssigned = [];

    public function search(School $school, Request $request)
    {
        $request->merge(['school_id' => $school->id ?? null]);
        $request->canChangePosition =   boolval(
            $request->school_id
            && !isset($request->active)
            && !isset($request->type)
            && !isset($request->q)
            && !isset($request->dates)
        ) || boolval(
            isset($request->segmented_module)
            && isset($request->schools)
            && count($request->schools) == 1
            && !isset($request->active)
            && !isset($request->type)
            && !isset($request->dates)
            && !isset($request->q)
        );
        //Set permission to edit/create project
        $request->hasHabilityToShowProjectButtons = false;
        $entity_id = Taxonomy::select('id')->where('group','gestor')->where('type','submenu')->where('code','projects')->first()?->id;
        if (auth()->user()->getAbilities()->where('entity_id',$entity_id)->first()) {
            $request->hasHabilityToShowProjectButtons = true;
        }
//        //Get data
        $paginatedCourses = Course::search($request);

        // Get users asigned to courses

        $courses = $paginatedCourses->toArray()['data'];
        $coursesIds = collect($courses)
            ->pluck('id')
            ->toArray();

        self::$coursesUsersAssigned = [];//Course::calculateUsersSegmentedCount($coursesIds);

        CursoSearchResource::collection($paginatedCourses);

        return $this->success($paginatedCourses);
    }

    public function getFormSelects(School $school, Course $course = null, $compactResponse = false)
    {
        $workspace = get_current_workspace();

        // $modules_id = $workspace->subworkspaces->pluck('id')->toArray();
        $modules_id = current_subworkspaces_id();

        $query = Course::whereRelation('workspaces', 'id', $workspace->id)
                    ->whereHas('schools', function($q) use ($modules_id) {
                        $q->whereRelationIn('subworkspaces', 'id', $modules_id);
                    })
                    ->where('active', ACTIVE);

        if ($course)
            $query->where('id', '!=', $course->id);

        $req_cursos = $query->get();

        $escuelas = School::with('subworkspaces:id,name,codigo_matricula')
            ->whereRelationIn('subworkspaces', 'id', $modules_id)
            ->select('id', 'name', 'active')
            ->get()
            ->map(function ($school, $key) {
                $school->setSelectSuffixes();
                return $school;
            });

        $requisitos = collect();

        $types = Taxonomy::getSelectData('course', 'type');

        foreach ($req_cursos as $req) {
            $requisitos->push((object)[
                'id' => $req->id,
                'name' => $req->name,
                'escuelas' => '---'
            ]);
        }

        $qualification_types = Taxonomy::getDataForSelect('system', 'qualification-type');

        $qualification_type = $workspace->qualification_type;
        $show_buttom_ia_description_generate = Ability::hasAbility('course','jarvis-descriptions');
        $has_DC3_functionality = boolval(get_current_workspace()->functionalities()->get()->where('code','dc3-dc4')->first());
        $instructors = [];
        $legal_representatives = [];
        $catalog_denominations = [];
        if($has_DC3_functionality){
            $instructors = Person::select('id','person_attributes')->where('workspace_id',$workspace->id)->where('type','dc3-instructor')->get();
            $legal_representatives = Person::select('id','person_attributes')->where('workspace_id',$workspace->id)->where('type','dc3-legal-representative')->get();
            $catalog_denominations = Taxonomy::where('group','course')->where('type','catalog-denomination-dc3')->select('id',DB::raw("CONCAT(code,' - ',name) as name"))->get();
        }

        $modalities = $workspace->getModalitiesCourseByWorkspace();
       
        $has_registro_capacitacion_functionality =   boolval(get_current_workspace()->functionalities()->get()->where('code','registro-capacitacion')->first());
        $registro_capacitacion_trainers = [];
        if ($has_registro_capacitacion_functionality) {
            $registro_capacitacion_trainers = RegistroCapacitacionTrainer::query()
                ->where('workspace_id', $workspace->id)
                ->get();
        }


        $response = compact(
            'escuelas', 'requisitos', 'types', 'qualification_types',
            'qualification_type','show_buttom_ia_description_generate','has_DC3_functionality',
            'instructors','legal_representatives','catalog_denominations','modalities',
            'has_registro_capacitacion_functionality', 'registro_capacitacion_trainers'
        );


        return $compactResponse ? $response : $this->success($response);
    }

    public function searchCurso(School $school, Course $course)
    {
        $course->load('qualification_type');
        // $scheduled_restarts = json_decode($course->scheduled_restarts);
        $scheduled_restarts = $course->scheduled_restarts;
        $course->scheduled_restarts_activado = $scheduled_restarts['activado'] ?? false;
        $course->scheduled_restarts_dias = $scheduled_restarts['reinicio_dias'] ?? 0;
        $course->scheduled_restarts_horas = $scheduled_restarts['reinicio_horas'] ?? 0;
        $course->scheduled_restarts_minutos = $scheduled_restarts['reinicio_minutos'] ?? 0;
        $form_selects = $this->getFormSelects($school, $course, true);
        $course->makeHidden('scheduled_restarts');

        $req_curso = Requirement::whereHasMorph('model', [Course::class], function ($query) use ($course) {
            $query->where('id', $course->id);
        })->first();

        $course->requisito_id = (!is_null($req_curso)) ? $req_curso->requirement_id : '';

        $schools = collect();
        foreach ($course->schools as $req) {
            $schools->push((object)[
                'id' => $req->id,
                'nombre' => $req->name,
            ]);
        }

        $course->lista_escuelas = $schools;
        $course->plantilla_diploma = $course->plantilla_diploma ? get_media_url($course->plantilla_diploma) : null;

        $mod_evaluaciones = $course->mod_evaluaciones;

        $course->mod_evaluaciones = $course->getModEvaluacionesConverted();
        // $course->mod_evaluaciones = []; // merge

        // if ($mod_evaluaciones && isset($mod_evaluaciones['nota_aprobatoria'])) {
        //     $nota_aprobatoria = calculateValueForQualification($mod_evaluaciones['nota_aprobatoria'], $course->qualification_type->position);
        //     $mod_evaluaciones['nota_aprobatoria'] = $nota_aprobatoria;
        //     $course->mod_evaluaciones = $mod_evaluaciones;
        // }
        $show_buttom_ia_description_generate = Ability::hasAbility('course','jarvis-descriptions');
        $workspace = get_current_workspace();
        $has_DC3_functionality = boolval($workspace->functionalities()->get()->where('code','dc3-dc4')->first());
        $instructors = [];
        $legal_representatives = [];
        $catalog_denominations = [];
        if($has_DC3_functionality){
            $instructors = Person::select('id','person_attributes')->where('workspace_id',$workspace->id)->where('type','dc3-instructor')->get();
            $legal_representatives = Person::select('id','person_attributes')->where('workspace_id',$workspace->id)->where('type','dc3-legal-representative')->get();
            $catalog_denominations = Taxonomy::where('group','course')->where('type','catalog-denomination-dc3')->select('id',DB::raw("CONCAT(code,' - ',name) as name"))->get();
        }
        return $this->success([
            'curso' => $course,
            'requisitos' => $form_selects['requisitos'],
            'escuelas' => $form_selects['escuelas'],
            'types' => $form_selects['types'],
            'modalities' => $form_selects['modalities'],
            'has_DC3_functionality' => $form_selects['has_DC3_functionality'],
            'has_registro_capacitacion_functionality' => $form_selects['has_registro_capacitacion_functionality'],
            'registro_capacitacion_trainers' => $form_selects['registro_capacitacion_trainers'],
            'qualification_types' => Taxonomy::getDataForSelect('system', 'qualification-type'),
            'show_buttom_ia_description_generate' => $show_buttom_ia_description_generate,
            'has_DC3_functionality' => $has_DC3_functionality,
            'instructors' => $instructors,
            'legal_representatives' => $legal_representatives,
            'catalog_denominations' => $catalog_denominations
        ]);
    }

    public function storeCurso(School $school, CursosStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data['school_id'] = ($school->exists) ? $school->id : null;
        $data['escuelas'] = $request->lista_escuelas;

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        Course::storeRequest($data);

        $response = [
            'msg' => 'Curso creado correctamente.',
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function updateCurso(School $school, Course $course, CursosStoreUpdateRequest $request)
    {
        $data = $request->validated();
//        info($data);

        if ($data['validateForm']):
            $validations = Course::validateBeforeUpdate($data, $school, $course);
            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error', 422);
        endif;

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $data['school_id'] = ($school->exists) ? $school->id : null;
        $data['escuelas'] = $request->lista_escuelas;
//        $data['active'] = ($data['active'] === 'true' or $data['active'] === true) ? 1 : 0;

        $response_curso = Course::storeRequest($data, $course);
        $response = [
            'curso' => $response_curso,
            'msg' => 'Curso actualizado correctamente.',
            'messages' => Course::getMessagesAfterUpdate($course, 'Curso actualizado correctamente.')
        ];

        return $this->success($response);
    }

    public function destroyCurso(School $school, Course $course, Request $request)
    {
        if ($request->validateForm):
            $validations = Course::validateBeforeDelete(['active' => true], $school, $course);
            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error', 422);
        endif;

        // TODO: Compatibles: Actualizar si se elimina el curso
        $course->updateOnModifyingCompatibility();

        SortingModel::deletePositionInPivotTable(CourseSchool::class,Course::class,[
            'course_id' => $course->id
        ]);

        $course->delete();
        $course->requirements()->delete();


        $response = [
            'curso' => $course,
            'msg' => 'Curso eliminado correctamente.',
            'messages' => Course::getMessagesAfterDelete($course)
        ];

        return $this->success($response);
    }

    public function updateStatus(School $school, Course $course, Request $request)
    {
        $update_status_to = !$course->active;

        if ($request->validateForm):
            $validations = Course::validateBeforeUpdate(['active' => $update_status_to], $school, $course);
            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error', 422);
        endif;

        $course->active = $update_status_to;
        $course->save();

        // TODO: Compatibles: Actualizar si se modifica el estado del curso
        if ($course->wasChanged('active')):

            $course->updateOnModifyingCompatibility();

        endif;

        $response = [
            'curso' => $course,
            'msg' => 'Estado actualizado con éxito.',
            'messages' => Course::getMessagesAfterUpdate($course, 'Curso actualizado correctamente.')
        ];

        return $this->success($response);
    }

    ////////////////////// CURSO ENCUESTA ////////////////////////////

    public function getEncuesta(School $school, Course $course)
    {
        $workspace = get_current_workspace();

        $encuestas = Poll::select('titulo as nombre', 'id')->whereRelation('type','code','xcurso')->where('workspace_id', $workspace->id)->get();
        $encuestas->prepend(['nombre' => 'Ninguno', 'id' => "ninguno"]);
        $course->encuesta_id = $course->polls->first()->id ?? "ninguno";

        return $this->success(compact('encuestas', 'course'));
    }

    public function storeUpdateEncuesta(School $school, Course $course, CursoEncuestaStoreUpdate $request)
    {
        $data = $request->validated();

        $polls = $data['encuesta_id'] === "ninguno" ? [] : $data['encuesta_id'];

        $course->polls()->sync($polls);

        cache_clear_model(Course::class);

        return $this->success(['msg' => 'Encuesta de curso actualizada.']);
    }

    public function create_CE(Curso $curso)
    {
        $encuestas_array = Poll::select('id', 'titulo')->pluck('titulo', 'id');
        $encuestas_array->prepend('NINGUNO', '');

        return view('cursos.create_encuesta', compact('encuestas_array', 'curso'));
    }

    public function store_CE(Curso $curso, Request $request)
    {

        $ce = new Curso_encuesta;
        $ce->curso_id = $request->curso_id;
        $ce->encuesta_id = $request->encuesta_id;

        if ($request->encuesta_id != "" || $request->encuesta_id != NULL) {
            $ce->save();
        }

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])
            ->with('info', 'Registro guardado con éxito');
    }

    public function edit_CE(Curso $curso, Curso_encuesta $ce)
    {

        $encuestas_array = Poll::select('id', 'titulo')->where('tipo', 'xcurso')->where('estado', 1)->pluck('titulo', 'id');
        $encuestas_array->prepend('NINGUNO', '');
        // return $encuestas_array;
        return view('cursos.edit_encuesta', compact('curso', 'ce', 'encuestas_array'));
    }

    public function update_CE(Curso $curso, Request $request, Curso_encuesta $ce)
    {

        $ce->curso_id = $request->curso_id;
        $ce->encuesta_id = $request->encuesta_id;
        if ($request->encuesta_id != "" || $request->encuesta_id != NULL) {
            $ce->save();
        } else {
            $ce->delete();
        }

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])
            ->with('info', 'Registro actualizado con éxito');
    }

    public function destroy_CE(Curso $curso, Curso_encuesta $ce)
    {
        $ce->delete();

        return redirect()->route('categorias.cursos', [$curso->categoria->config->id, $curso->categoria->id])->with('info', 'Eliminado Correctamente');
    }

    public function getCompatibilities(Course $course)
    {
        $workspace = get_current_workspace();
        $compatibilities = $course->getCompatibilities();
        $courses = Course::select('id', 'name')
                        ->whereRelation('workspaces', 'id', $workspace->id)
                        ->where('id', '<>', $course->id)
                        ->get();

        return $this->success(compact('compatibilities', 'courses'));
    }

    public function updateCompatibilities(Course $course, Request $request)
    {
        $courses = $request->compatibilities;

        // $data['compatibilities'] = array_column($courses, 'id');
        $data['compatibilities'] = $courses;

        Course::storeCompatibilityRequest($course, $data);

        $response = [
            // 'curso' => $course,
            'msg' => 'Compatibilidad de curso actualizado correctamente.',
        ];

        return $this->success($response);
    }

    // public function moverCurso(Abconfig $abconfig, Categoria $categoria, Curso $curso, MoverCursoRequest $request)
    // {
    //     $data = $request->validated();
    //     $validate = Curso::validateMoverCurso($curso);
    //     //        dd($validate);
    //     if (!$validate['validate'])
    //         return $this->success(compact('validate'), 422);

    //     $curso = Curso::moverCurso($curso, $data['escuela_id']);

    //     return $this->success(['msg' => 'El curso se movió correctamente.']);
    // }

    public function getSelects()
    {
        $workspace = get_current_workspace();

        $types = Taxonomy::getSelectData('course', 'type');
        $modalities = $workspace->getModalitiesCourseByWorkspace();

        return $this->success(compact('types','modalities'));
    }



    // ====================================== SEGMENTATION COURSE ===================================

    public function getFiltersSelects()
    {
        $workspace = get_current_workspace();

        $modules = Workspace::where('parent_id', $workspace->id)
            // ->select('criterion_value_id as id', 'name')
            ->select('id', 'name', 'codigo_matricula')
            ->whereIn('id', current_subworkspaces_id())
            ->get()
            ->map(function ($module, $key) {
                $module->name = $module->name . " [{$module->codigo_matricula}]";
                return $module;
            });

        $schools = [];
        $modalities = $workspace->getModalitiesCourseByWorkspace();
        return $this->success(compact('modules', 'schools','modalities'));
    }

    public function getEncuestaSegmentation(Course $course)
    {
        $school = $course->schools()->first();
        return $this->getEncuesta($school, $course);
    }

    public function storeUpdateEncuestaSegmentation(Course $course, CursoEncuestaStoreUpdate $request)
    {
        $school = $course->schools()->first();
        return $this->storeUpdateEncuesta($school, $course, $request);
    }

    public function searchCursoSegmentation(Course $course)
    {
        $school = $course->schools()->first();

        return $this->searchCurso($school, $course);
    }

    public function updateCursoSegmentation(Course $course, CursosStoreUpdateRequest $request)
    {
        $school = $course->schools()->first();

        return $this->updateCurso($school, $course, $request);
    }

    public function getSchoolsBySubworkspace(Workspace $subworkspace)
    {
        $schools = School::with('subworkspaces:id,name,codigo_matricula')
            ->whereRelation('subworkspaces', 'id', $subworkspace->id)
            ->select('id', 'name', 'active')
            ->get()
            ->map(function ($school, $key) {
                $school->setSelectSuffixes();
                return $school;
            });

        return $this->success(compact('schools'));
    }

    public function downloadCourseSegmentations()
    {
        $workspace = get_current_workspace();

        $code = $workspace->slug ?? "WRKSP[{$workspace->id}]";

        $datetime = date('Y-m-d_H:i:s');
        $filename = "Reporte-de-segmentación-[{$code}].xlsx";

        ob_end_clean();
        ob_start();

        return Excel::download(new CourseSegmentationExport($workspace), $filename);
    }
    
    public function listMediaTopics(Course $course)
    {
        $topics = $course->listMediaTopics();
        return $this->success(compact('topics'));
    }

    public function copy(School $school, Course $course)
    {
        $items = Course::getTopicsForTree($course);

        $items_destination = [];

        return $this->success(compact('school', 'items', 'items_destination'));
    }

    public function copyContent(Request $request, School $school, Course $course)
    {
        $selections = $request->selection_source;
        $workspace = get_current_workspace();

        // dd($selections, $school, $course);

        $data = $this->buildSourceTreeSelection($course, $selections);

        $_courses = Course::whereIn('id', [$course->id])->get();
        $_topics = Topic::with('questions', 'medias')->whereIn('id', $data['topic_ids'])->get();

        $prefix = '[COPIA] - ';

        Workspace::setCoursesDuplication($data['courses'], $_courses, $_topics, $school, $workspace, $prefix, true);

        return $this->success(['msg' => 'Contenido duplicado correctamente.']);
    }

    public function buildSourceTreeSelection($course, $selections)
    {
        $courses = [];
        $course_ids = [];
        $topic_ids = [];

        $prefix = 'course_' . $course->id . '-';

        foreach ($selections as $selection) {

            $sections = explode('-',  $prefix . $selection);
            $data = [];

            foreach ($sections as $section) {

                $part = explode('_', $section);

                $model = $part[0];
                $id = $part[1];

                ${$model.'_ids'}[$id] = $id;

                $row = [
                    'model' => $model,
                    'id' => $id,
                ];

                $data[] = $row; 
            }

            // $course_id = $data[0]['id'];
            $topic_id = $data[1]['id'] ?? NULL;

            if ($topic_id) {

                $courses[$course->id]['topics'][] = $topic_id;

            } else {
                
                $courses[$course->id]['topics'] = [];
            }

        }

        return compact('course_ids', 'topic_ids', 'courses');
    }
}
