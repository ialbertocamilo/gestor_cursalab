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
use App\Models\Segment;
use App\Models\Stage;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\Usuario;
use Exception;
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

    public function TareasUpdate(Process $process, Stage $stage, Activity $activity, ProjectStoreRequest $request)
    {
        $course_id = $request->project['course_id'];
        $project_id = $request->project['id'] ?? null;
        $project = Project::where('id', $project_id)->first();

        if($course_id) {
            $course = Course::where('id', $course_id)->first();
            if($course) {
                $course->name = 'Inducción - Tareas - '. $request->project['title'];
                $course->save();

                $request->request->add([
                    'course_id' => $course?->id
                ]);
                $data = Project::storeUpdateRequest($request, $project);
                cache_clear_model(Project::class);
            }
        }

        $activity->title = $request->project['title'];
        $activity->save();
        cache_clear_model(Activity::class);

        $response = [
            'msg' => 'Actividad creada correctamente.',
            'activity' => $activity,
            'still_has_storage'=> $data['still_has_storage'] ?? true,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function TareasStore(Process $process, Stage $stage, ProjectStoreRequest $request)
    {
        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'tareas');
        $platform_onboarding = Taxonomy::getFirstData('project', 'platform', 'onboarding');

        $data_course = [
            'name' => 'Inducción - Tareas - '. $request->project['title'],
            'active' => 1,
            'escuelas' => $request['school_id'] ? [ School::where('id', $request['school_id'])->first()?->id] : [],
            'reinicios_programado' => null,
            'requisito_id' => null,
            'platform_id' => $platform_onboarding?->id
        ];

        $course = Course::storeRequest($data_course);

        $request->request->add([
            'platform_id' => $platform_onboarding?->id,
            'course_id' => $course?->id,
            'active' => 1,
        ]);
        $data = Project::storeUpdateRequest($request);

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

        $response = [
            'msg' => 'Actividad creada correctamente.',
            'activity' => $activity,
            'still_has_storage'=> $data['still_has_storage'] ?? true,
            'messages' => ['list' => []]
        ];

        return $this->success($response);
    }

    public function editActivityTareas(Process $process, Stage $stage, Activity $activity)
    {
        $project = Project::where('id', $activity?->model_id)->first();
        $response = Project::editProject($project);
        $response['title'] = $activity->title;
        $response['activity_id'] = $activity->id;
        // $response = [];

        return $this->success($response);
    }



    public function SesionesStore(Process $process, Stage $stage, ActivityMeetingRequest $request)
    {
        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'sesion_online');
        $type_meeting = Taxonomy::getFirstData('meeting', 'type', 'room');
        $platform_onboarding = Taxonomy::getFirstData('project', 'platform', 'onboarding');

        $request->request->add([
            'type_id' => $type_meeting?->id ?? null,
            'platform_id' => $platform_onboarding?->id
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

    public function SesionesGetFormSelects(Process $process, Stage $stage)
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

        $q_requisitos = Activity::select('id as code', 'title as name')->where('stage_id', $stage?->id);
        $requirements = $q_requisitos->orderBy('position')->get();

        // $response = compact('types', 'hosts', 'user_types', 'default_meeting_type');
        $response = compact('hosts', 'user_types', 'requirements');

        return $this->success($response);
    }

    public function TemasStore(Process $process, Stage $stage,  ActivityTemaStoreUpdateRequest $request)
    {
        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'temas');
        $platform_onboarding = Taxonomy::getFirstData('project', 'platform', 'onboarding');

        $data_course = [
            'name' => 'Inducción - Temas - '. $request['name'],
            'active' => 1,
            'escuelas' => $request['school_id'] ? [ School::where('id', $request['school_id'])->first()?->id] : [],
            'reinicios_programado' => null,
            'requisito_id' => null,
            'platform_id' => $platform_onboarding?->id
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
        $platform_onboarding = Taxonomy::getFirstData('project', 'platform', 'onboarding');

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
                'finishes_at' => $finishes_at,
                'platform_id' => $platform_onboarding?->id
            ]
        );

        //actividades
        if($data['checklist_actividades'])
        {
            $data['checklist_actividades'] = json_decode($data['checklist_actividades']);
            if(is_array($data['checklist_actividades']))
            {
                foreach ($data['checklist_actividades'] as $key => $checklist_actividad) {
                    $type = Taxonomy::where('group', 'checklist')
                        ->where('type', 'type')
                        ->where('code', $checklist_actividad->type_name)
                        ->first();
                    CheckListItem::updateOrCreate(
                        ['id' => is_null($data['id']) ? null : $checklist_actividad['id']],
                        [
                            'activity' => $checklist_actividad->activity,
                            'type_id' => !is_null($type) ? $type->id : null,
                            'active' => $checklist_actividad->active,
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

        if($process && $checklist) {

            $criteria = Segment::getCriteriaByWorkspace(get_current_workspace());
            $segments = Segment::getSegmentsByModel($criteria, Process::class, $process->id);

            $data['segments'] = $segments->toArray();

            if (is_array($segments)) {
                $segments = collect($segments);
            }

            $segmentation_by_document_list = [];
            $segmentation_by_document = $segments->map(function ($item) {
                return ['segmentation_by_document'=> $item->segmentation_by_document];
            });

            foreach ($segmentation_by_document as $seg) {
                foreach ($seg['segmentation_by_document'] as $value) {
                    array_push($segmentation_by_document_list, $value);
                }
            }
            $data['segment_by_document'] = ['criteria_selected'=> $segmentation_by_document_list];
            $data['model_type'] = Checklist::class;
            $data['model_id'] = $checklist->id;
            $data['code'] = null;

            // Segmentación directa
            if(isset($data['segments']) && count($data['segments']) > 0)
            {
                $list_segments_temp = [];
                foreach($data['segments'] as $seg) {
                    $seg['id'] = 'new-segment-'.strtotime(date('Y-m-d'));
                    unset($seg['model_type']);
                    unset($seg['model_id']);
                    if($seg['type_code'] === 'direct-segmentation')
                        array_push($list_segments_temp, $seg);
                }
                $data['segments'] = $list_segments_temp;

                $list_segments = (object) $data;

                (new Segment)->storeDirectSegmentation($list_segments);
            }
            // Segmentación por documento
            if(isset($data['segment_by_document']) && isset($data['segment_by_document']['criteria_selected']))
            {
                $list_segments = $data;

                (new Segment)->storeSegmentationByDocument($list_segments);
            }
        }

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

        $platform_onboarding = Taxonomy::getFirstData('project', 'platform', 'onboarding');
        $data['platform_id'] = $platform_onboarding?->id;

        $session = $request->session()->all();
        $workspace = $session['workspace'];
        $data['workspace_id'] = $workspace->id;

        $poll = Poll::create($data);

        cache_clear_model(Poll::class);

        $data_activity = [
            'title' => $request->titulo,
            'description' => $request->description,
            'stage_id' => $request->model_id,
            'model_id' => $poll?->id ?? null,
            'model_type' => Poll::class,
            'type_id' => $type_activity?->id ?? null,
            'active' => false
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

    public function editActivityEncuestas(Process $process, Stage $stage, Activity $activity)
    {
        $poll = Poll::where('id', $activity?->model_id)->first();
        $response['poll'] = $poll;
        $response['activity'] = $activity;
        // $response = [];

        return $this->success($response);
    }

    public function editActivityTemas(Process $process, Stage $stage, Activity $activity)
    {
        $temas = Topic::where('id', $activity?->model_id)->first();
        $response['temas'] = $temas;

        return $this->success($response);
    }

    public function editActivityChecklist(Process $process, Stage $stage, Activity $activity)
    {
        $checklist = Checklist::where('id', $activity?->model_id)->first();
        $response['checklist'] = $checklist;

        return $this->success($response);
    }

    public function editActivitySesiones(Process $process, Stage $stage, Activity $activity)
    {
        $response = [];

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

        $tax_evaluation_type = Taxonomy::find($topic->type_evaluation_id);

        $response = [
            'msg' => 'Actividad creada correctamente.',
            'activity' => $activity,
            'course' => $course_update?->id ?? null,
            'topic' => ['type_evaluation_id' => $topic?->type_evaluation_id, 'evaluation_type' => $tax_evaluation_type?->code],
            'verify_evaluation' => $verify_evaluation,
            'messages' => ['list' => []]
        ];

        return $this->success($response);
    }

    public function ActivitiesGetFormSelects(Process $process, Stage $stage)
    {
        $q_requisitos = Activity::select('id as code', 'title as name')->where('stage_id', $stage?->id);
        // if ($topic)
        //     $q_requisitos->whereNotIn('id', [$topic->id]);

        $requirements = $q_requisitos->orderBy('position')->get();

        $response = compact('requirements');

        return $this->success($response);
    }

    public function EvaluacionesGetFormSelects(Process $process, Stage $stage)
    {
        $workspace = get_current_workspace();

        $type_activity = Taxonomy::getFirstData('processes', 'activity_type', 'temas');
        $qualification_types = Taxonomy::getDataForSelect('system', 'qualification-type');

        $q_requisitos = Activity::select('id as code', 'title as name')->where('stage_id', $stage?->id);
        $requirements = $q_requisitos->orderBy('position')->get();


        $topics = Activity::select('model_id as code', 'title as name')
                            ->where('stage_id', $stage?->id)
                            ->where('type_id', $type_activity?->id)
                            ->orderBy('position')
                            ->get();

        $qualification_type = $workspace->qualification_type;

        $response = compact('requirements', 'topics', 'qualification_types', 'qualification_type');

        return $this->success($response);
    }

    public function getDataTopicByAssessmentsActivity( Process $process, Stage $stage, Topic $topic )
    {
        $taxonomy = Taxonomy::find($topic->type_evaluation_id);

        $topic->evaluation_type = $taxonomy->code ?? '';

        $response = compact('topic');
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
