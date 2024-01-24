<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Induction\ActivityCursosStoreUpdateRequest;
use App\Http\Requests\Induction\ActivityMeetingRequest;
use App\Http\Requests\Induction\ActivityTemaStoreUpdateRequest;
use App\Http\Requests\MeetingRequest;
use App\Http\Requests\PollStoreRequest;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Resources\Posteo\PosteoPreguntasResource;
use App\Models\Activity;
use App\Models\CheckList;
use App\Models\CheckListItem;
use App\Models\Course;
use App\Models\Meeting;
use App\Models\Poll;
use App\Models\Process;
use App\Models\Project;
use App\Models\Question;
use App\Models\School;
use App\Models\Stage;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\Usuario;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    // public function search(Process $process, Request $request)
    // {
    //     $workspace = get_current_workspace();
    //     $request->mergeIfMissing([
    //         'workspace_id' => $workspace?->id,
    //         'process_id' => $process?->id
    //     ]);

    //     $data = Stage::getStagesList($request->all());

    //     return $this->success($data);
    // }

    public function TareasStore(Process $process, Stage $stage, ProjectStoreRequest $request)
    {
        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'tareas');
        // $new_request = [];
        // foreach ($request->request as $req) {
        //     $req['ss'] ='aa';
        // }
        // $request->request->add(['project.key' =>'value']);
        $data = Project::storeUpdateRequest($request);

        // // $request->request->project['model_id'] = $stage?->id ?? null;
        // // $request->model_type = Activity::class;

        cache_clear_model(Project::class);

        $data_activity = [
            'title' => $request->project['title'],
            'stage_id' => $request->project['model_id'],
            'model_id' => $data['project'] ?? null,
            'model_type' => Project::class,
            'type_id' => $type_activity?->id ?? null
        ];

        $activity = Activity::storeRequest($data_activity);
        cache_clear_model(Activity::class);

        // $activity = '';

        // $stage = Stage::storeRequest($data);


        $response = [
            'msg' => 'Actividad creada correctamente.',
            'activity' => $activity,
            'still_has_storage'=> $data['still_has_storage'] ?? true,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
        // $req_project = new ProjectStoreRequest();
        // $req_project->request->add(['project' => $data_activity]);
        // dd($req_project);
        // return $this->success($data);
    }



    public function SesionesStore(Process $process, Stage $stage, ActivityMeetingRequest $request)
    {
        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'sesion_online');
        $type_meeting = Taxonomy::getFirstData('meeting', 'type', 'room');
        $request->request->add([
            'type_id' => $type_meeting?->id ?? null
        ]);
        $meeting = Meeting::storeRequest($request->all());

        cache_clear_model(Meeting::class);

        $data_activity = [
            'title' => $request->name,
            'stage_id' => $request->model_id,
            'model_id' => $meeting?->id ?? null,
            'model_type' => Meeting::class,
            'type_id' => $type_activity?->id ?? null
        ];

        $activity = Activity::storeRequest($data_activity);
        cache_clear_model(Activity::class);

        $response = [
            'msg' => 'Actividad creada correctamente.',
            'activity' => $activity,
            'messages' => ['list' => []]
        ];

        return $this->success($response);
    }

    public function SesionesGetFormSelects()
    {

        // $default_meeting_type = Taxonomy::getFirstData('meeting', 'type', 'room');
        $user_types = Taxonomy::getSelectData('meeting', 'user');
        // $types = Taxonomy::getSelectData('meeting', 'type');
        // $default_meeting_type = Taxonomy::getFirstData('meeting', 'type', 'room');
        // //don't include benefit type if you don't have a benefit activated
        // if(
        //     !Benefit::where('workspace_id',get_current_workspace()->id)->where('active',ACTIVE)
        //     ->whereHas('type', fn($q) => $q->whereIn('code', ['sesion_online','sesion_hibrida']))
        //     ->first()
        // ){
        //     $types = collect($types)->filter(function ($type) {
        //         return $type['code'] != 'benefits';
        //     });
        // }
        $hosts = Usuario::getCurrentHosts();

        // $response = compact('types', 'hosts', 'user_types', 'default_meeting_type');
        $response = compact('hosts', 'user_types');

        return $this->success($response);
    }

    public function TemasStore(Process $process, Stage $stage,  ActivityTemaStoreUpdateRequest $request)
    {
        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'temas');
        // $data = $request->validated();
        // $data = Media::requestUploadFile($data, 'imagen');

        // $request->request->add([
        //     'type_id' => $type_meeting?->id ?? null
        // ]);
        // $meeting = Meeting::storeRequest($request->all());

        $data_course = [
            'name' => $request['name'],
            'active' => 1,
            'escuelas' => $request['school_id'] ? [ School::where('id', $request['school_id'])->first()?->id] : [],
            'reinicios_programado' => null,
            'requisito_id' => null
        ];

        $course = Course::storeRequest($data_course);

        $request->request->add([
            'course_id' => $course?->id ?? null
        ]);

        $tema = Topic::storeRequest($request->all());

        cache_clear_model(Topic::class);

        $data_activity = [
            'title' => $request->name,
            'stage_id' => $request->model_id,
            'model_id' => $tema?->id ?? null,
            'model_type' => Topic::class,
            'type_id' => $type_activity?->id ?? null
        ];

        $activity = Activity::storeRequest($data_activity);
        cache_clear_model(Activity::class);

        $response = [
            'tema' => $tema,
            'msg' => ' Tema creado correctamente.',
            'activity' => $activity,
            'messages' => [
                'list' => [],
                'title' => null,
                'type' => 'validations-after-update'
            ],
        ];

        return $this->success($response);
    }


    public function TemasGetFormSelects(Process $process, Stage $stage)
    {
        $q_requisitos = Activity::select('id as code', 'title as name')->where('stage_id', $stage?->id);
        // if ($topic)
        //     $q_requisitos->whereNotIn('id', [$topic->id]);

        $requirements = $q_requisitos->orderBy('position')->get();

        // $qualification_type = $course->qualification_type;
        $qualification_type = [];
        $media_url = get_media_root_url();

        $response = compact( 'requirements', 'qualification_type', 'media_url');

        return $this->success($response);
    }

    public function TemasSearchPreguntas(Process $process, Stage $stage, Topic $topic, Request $request)
    {
        $request->merge(['tema_id' => $topic->id, 'current_qualification_value' => $topic->qualification_type->position]);

        $preguntas = Topic::search_preguntas($request, $topic);

        PosteoPreguntasResource::collection($preguntas);

        return $this->success($preguntas);
    }

    public function guardarChecklist(Request $request)
    {
        $workspace = get_current_workspace();
        $data = $request->all();

        $data['id'] = $data['id'] ?? null;

        if(isset($data['duplicado']) && $data['duplicado'])
            $data['id'] = null;

        $type_checklist = Taxonomy::getFirstData('checklist', 'type_checklist', 'libre');
        $starts_at = (isset($data['starts_at']) && $data['starts_at']) ? $data['starts_at'] : null;
        $finishes_at = (isset($data['finishes_at']) && $data['finishes_at']) ? $data['finishes_at'] : null;

        //checklist
        $checklist = CheckList::updateOrCreate(
            ['id' => $data['id']],
            [
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'active' => $data['active'],
                'workspace_id' => $workspace->id,
                'type_id' => !is_null($type_checklist) ? $type_checklist->id : null,
                'starts_at' => $starts_at,
                'finishes_at' => $finishes_at
            ]
        );

        //actividades
        if($data['checklist_actividades'])
        {
            $data['checklist_actividades'] = json_encode($data['checklist_actividades']);
            if(is_array($data['checklist_actividades']))
            {
                foreach ($data['checklist_actividades'] as $key => $checklist_actividad) {
                    $type = Taxonomy::where('group', 'checklist')
                        ->where('type', 'type')
                        ->where('code', $checklist_actividad['type_name'])
                        ->first();
                    CheckListItem::updateOrCreate(
                        ['id' => is_null($data['id']) ? null : $checklist_actividad['id']],
                        [
                            'activity' => $checklist_actividad['activity'],
                            'type_id' => !is_null($type) ? $type->id : null,
                            'active' => $checklist_actividad['active'],
                            'checklist_id' => $checklist->id,
                            'position' => $key + 1,
                        ]
                    );
                }
            }
        }
        return $checklist;
    }

    public function ChecklistStore(Process $process, Stage $stage,  Request $request)
    {
        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'checklist');

        $checklist = $this->guardarChecklist($request);

        $data_activity = [
            'title' => $request->title,
            'stage_id' => $request->model_id,
            'model_id' => $checklist?->id ?? null,
            'model_type' => CheckList::class,
            'type_id' => $type_activity?->id ?? null
        ];

        $activity = Activity::storeRequest($data_activity);
        cache_clear_model(Activity::class);

        $response = [
            'msg' => ' Checklist creado correctamente.',
            'activity' => $activity,
            'messages' => [
                'list' => [],
                'title' => null,
                'type' => 'validations-after-update'
            ],
        ];

        return $this->success($response);
    }

    public function EncuestasStore(Process $process, Stage $stage, PollStoreRequest $request)
    {
        $data = $request->validated();

        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'encuesta');

        $type_poll = Taxonomy::getFirstData('poll', 'tipo', 'activity');
        $data['type_id'] = $type_poll?->id ?? null;

        $session = $request->session()->all();
        $workspace = $session['workspace'];
        $data['workspace_id'] = $workspace->id;

        $poll = Poll::create($data);

        cache_clear_model(Poll::class);

        $data_activity = [
            'title' => $request->titulo,
            'stage_id' => $request->model_id,
            'model_id' => $poll?->id ?? null,
            'model_type' => Poll::class,
            'type_id' => $type_activity?->id ?? null
        ];

        $activity = Activity::storeRequest($data_activity);
        cache_clear_model(Activity::class);

        $response = [
            'msg' => 'Actividad creada correctamente.',
            'activity' => $activity,
            'encuesta_id' => $poll?->id ?? 0,
            'messages' => ['list' => []]
        ];

        return $this->success($response);
    }

    public function EvaluacionesStore(Process $process, Stage $stage, ActivityCursosStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data['requisito_id'] = null;
        $data['escuelas'] = null;
        $data['assessable'] = 1;
        $data['active'] = 1;


        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'evaluacion');
        $type_evaluation = Taxonomy::getFirstData('topic', 'evaluation-type', 'qualified');

        // $activity_topic = Activity::where('id', $data['topic'])->first();
        // $topic = $activity_topic ? Topic::where('id', $data['topic'])->first() : null;
        $topic = Topic::where('id', $data['topic'])->first();

        if($topic)
        {
            $topic->type_evaluation_id = $type_evaluation?->id ?? null;
            $topic->qualification_type_id = $data['qualification_type_id'] ?? null;
            $topic->assessable = true;
            $topic->save();
        }
        $verify_evaluation = Question::verifyEvaluation($topic);
        cache_clear_model(Topic::class);

        $course = $topic ? Course::where('id', $topic?->course_id)->first() : null;
        $course_update = Course::storeRequest($data, $course);

        cache_clear_model(Course::class);

        $data_activity = [
            'title' => $request->titulo,
            'stage_id' => $request->model_id,
            'model_id' => $topic?->id ?? null,
            'model_type' => Topic::class,
            'type_id' => $type_activity?->id ?? null
        ];

        $activity = Activity::storeRequest($data_activity);
        cache_clear_model(Activity::class);

        $response = [
            'msg' => 'Actividad creada correctamente.',
            'activity' => $activity,
            'course' => $course_update?->id ?? null,
            'messages' => ['list' => []]
        ];

        return $this->success($response);
    }

    public function ActivitiesGetFormSelects(Process $process, Stage $stage)
    {
        $q_requisitos = Activity::select('id as code', 'title as name')->where('stage_id', $stage?->id);
        // if ($topic)
        //     $q_requisitos->whereNotIn('id', [$topic->id]);

        $requisitos = $q_requisitos->orderBy('position')->get();

        $response = compact('requisitos');

        return $this->success($response);
    }

    public function EvaluacionesGetFormSelects(Process $process, Stage $stage)
    {
        $q_requisitos = Activity::select('id as code', 'title as name')->where('stage_id', $stage?->id);
        // if ($topic)
        //     $q_requisitos->whereNotIn('id', [$topic->id]);

        $requirements = $q_requisitos->orderBy('position')->get();

        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'temas');

        $topics = Activity::select('model_id as code', 'title as name')
                            ->where('stage_id', $stage?->id)
                            ->where('type_id', $type_activity?->id)
                            ->orderBy('position')
                            ->get();

        $qualification_types = Taxonomy::getDataForSelect('system', 'qualification-type');

        $response = compact('requirements', 'topics', 'qualification_types');

        return $this->success($response);
    }

    public function ChecklistGetFormSelects(Process $process, Stage $stage)
    {
        $q_requisitos = Activity::select('id as code', 'title as name')->where('stage_id', $stage?->id);
        // if ($topic)
        //     $q_requisitos->whereNotIn('id', [$topic->id]);

        $requirements = $q_requisitos->orderBy('position')->get();

        $response = compact('requirements');

        return $this->success($response);
    }

    // public function store(ProjectStoreRequest $request){
    //     $data = Project::storeUpdateRequest($request);
    //     return $this->success($data);
    // }

    // public function getData(Stage $stage)
    // {
    //     $response = Stage::getData($stage);

    //     return $this->success($response);
    // }
    /**
     * Activity request to toggle value of active status (1 or 0)
     *
     * @param Activity $activity
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Process $process, Stage $stage, Activity $activity, Request $request)
    {
        $activity->update(['active' => !$activity->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Activity request to delete activity record
     *
     * @param Activity $activity
     * @return JsonResponse
     */
    public function destroy(Process $process, Stage $stage, Activity $activity)
    {
        $activity->delete();

        return $this->success(['msg' => 'Actividad eliminado correctamente.']);
    }


    public function update(Process $process, Stage $stage, Activity $activity, Request $request)
    {
        $data = $request->all();

        $activity = Activity::storeRequest($data, $activity);

        $response = [
            'msg' => 'La actividad se actualizó correctamente',
            'process' => $activity,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }
}
