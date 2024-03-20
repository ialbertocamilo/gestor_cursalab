<?php

namespace App\Models;

use App\Http\Resources\Induccion\ProcessAssistantsSearchResource;
use App\Http\Resources\Induccion\SupervisorProcessesSupervisorsResource;
use App\Http\Resources\Multimedia\MultimediaSearchResource;
use App\Models\BaseModel;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class Process extends BaseModel
{
    protected $table = 'processes';

    protected $fillable = [
        'workspace_id',
        'title',
        'description',
        'count_absences',
        'limit_absences',
        'absences',
        'active',
        'logo',
        'background_web',
        'background_mobile',
        'color',
        'icon_finished',
        'icon_finished_name',
        'config_completed',
        'certificate_template_id',
        'starts_at',
        'finishes_at',
        'color_map_even',
        'color_map_odd',
        'image_guia',
        'image_guide_name',
        'supervisor_criteria',
        'qualification_type_id'
    ];

    protected $casts = [
        'active' => 'boolean',
        'count_absences' => 'boolean',
        'limit_absences' => 'boolean',
        'config_completed' => 'boolean',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    public function instructions()
    {
        return $this->hasMany(Instruction::class, 'process_id', 'id');
    }

    public function stages()
    {
        return $this->hasMany(Stage::class, 'process_id', 'id');
    }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function instructors()
    {
        return $this->belongsToMany(User::class, 'process_instructors', 'process_id', 'user_id');
    }

    public function instructors_direct()
    {
        return $this->instructors()->where('type','direct');
    }

    protected function getRepositoryMediaProcess() {

        $session = session()->all();
        $workspace = $session['workspace'];

        $list_icon_final = Media::query()
                      ->where('workspace_id', $workspace->id)
                      ->where('type', 'icon_final')
                      ->get();
        $list_guide = Media::query()
                    ->where('workspace_id', $workspace->id)
                    ->where('type', 'guide')
                    ->get();

        $list_icon_final->each(function($item){
            $item->formattedSize = FileService::formatSize($item->size);
            $item->tipo = $item->getMediaType($item->ext);
            $item->url = FileService::generateUrl($item->file);
            $item->image = FileService::generateUrl($item->getPreview());
        });

        $list_guide->each(function($item){
            $item->formattedSize = FileService::formatSize($item->size);
            $item->tipo = $item->getMediaType($item->ext);
            $item->url = FileService::generateUrl($item->file);
            $item->image = FileService::generateUrl($item->getPreview());
        });

        return compact('list_icon_final', 'list_guide');
    }

    protected function getProcessesList( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $workspace = get_current_workspace();

        $repository_media_process = $this->getRepositoryMediaProcess();
        $processes_query = Process::with(['instructions', 'segments', 'stages','instructors_direct' => function($i){
                                        $i->select('id','fullname','document', 'name', 'lastname', 'surname');
                                    }])
                                    ->where('workspace_id', $workspace->id);

        if(request()->sortBy){
            if (request()->sortBy == 'title_process')
                $field = 'title';
            else
                request()->sortBy;
        }
        else {
            $field = 'created_at';
        }
        $sort = !is_null(request()->sortDesc) ? (request()->sortDesc == 'true' ? 'DESC' : 'ASC') : 'DESC';

        $processes_query->orderBy('active', $sort);
        $processes_query->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $processes_query->where(function ($query) use ($filtro) {
                $query->where('title', 'like', "%$filtro%");
                $query->orWhere('description', 'like', "%$filtro%");
            });
        }
        if(request()->all_data){
            $response['data'] = $processes_query->get();
            return $response;
        }
        $processes = $processes_query->paginate(request('paginate', 15));

        $processes_items = $processes->items();
        foreach($processes_items as $item) {
            $item->title_process = $item->title;

            $assistans_route = route('process.assistants.index', [$item->id]);
            $stages_route = route('stages.index', [$item->id]);
            $certificate_route = ($item->certificate_template_id) ?
                                        route('process.diploma.edit', [$item->id, $item->certificate_template_id]) :
                                        route('process.diploma.create', [$item->id]);

            $item->assistans_route = $assistans_route;
            $item->stages_route = $stages_route;
            $item->certificate_route = $certificate_route;
            $item->assigned_users = $item->segments->count();
            if($item->segments->count())
                $item->progress_process = 0;
            else
                $item->progress_process = null;
            $item->stages_count = $item->stages->count();

            $item->starts_at = $item->starts_at ? date('Y-m-d', strtotime($item->starts_at)) : null;
            $item->finishes_at = $item->finishes_at ? date('Y-m-d', strtotime($item->finishes_at)) : null;


            $item->logo = $item->logo ? FileService::generateUrl($item->logo) : $item->logo;
            $item->background_mobile = $item->background_mobile ? FileService::generateUrl($item->background_mobile) : $item->background_mobile;
            $item->background_web = $item->background_web ? FileService::generateUrl($item->background_web) : $item->background_web;
            $item->repository = $repository_media_process;

            $item->image_guia = $item->image_guia ? FileService::generateUrl($item->image_guia) : $item->image_guia;

            $item->config_process = [
                'edit' => $item->config_completed,
                'activities' => $item->stages->count() ? true : false,
                'segments' => $item->segments->count() ? true : false,
                'certificate' => $item->certificate_template_id ? true : false
            ];

            $item->supervisor_criteria = $item->supervisor_criteria ? json_decode($item->supervisor_criteria) : [];
            $item->supervisor_assigned_directly = $item->instructors_direct ?? [];
        }

        $response['data'] = $processes->items();
        $response['lastPage'] = $processes->lastPage();
        $response['current_page'] = $processes->currentPage();
        $response['first_page_url'] = $processes->url(1);
        $response['from'] = $processes->firstItem();
        $response['last_page'] = $processes->lastPage();
        $response['last_page_url'] = $processes->url($processes->lastPage());
        $response['next_page_url'] = $processes->nextPageUrl();
        $response['path'] = $processes->getOptions()['path'];
        $response['per_page'] = $processes->perPage();
        $response['prev_page_url'] = $processes->previousPageUrl();
        $response['to'] = $processes->lastItem();
        $response['total'] = $processes->total();

        return $response;
    }
    protected function storeRequest($data, $process = null)
    {
        try {
            $workspace = get_current_workspace();
            $data['workspace_id'] = $workspace?->id;
            $data['config_completed'] = isset($data['config_completed']) && $data['config_completed'] ? $data['config_completed'] : false;

            DB::beginTransaction();


            if ($process) :
                $process->update($data);
            else:
                $process = self::create($data);
            endif;


            //instructions
            if(isset($data['instructions']) && $data['instructions'])
            {
                $instructions = json_decode($data['instructions']);
                if(is_array($instructions))
                {
                    foreach ($instructions as $key => $instruction) {
                        Instruction::updateOrCreate(
                            ['id' => str_contains($instruction->id, 'n-') ? null : $instruction->id],
                            [
                                'description' => $instruction->description,
                                'process_id' => $process->id,
                                'position' => $key + 1,
                            ]
                        );
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }

        cache_clear_model(Process::class);

        return $process;
    }

    protected function getProcessAssistantsList(Process $process, bool $is_paginated = true, bool $absences = false)
    {
        $course = new Course();

        $process->load('segments');

        $segmentados_id = $course->usersSegmented($process->segments, 'users_id');
        $segmentados_id = array_unique($segmentados_id);

        $segmentados = User::FilterByPlatform()->with(['subworkspace', 'summary_process'])
                            ->whereIn('id',$segmentados_id);
        if($absences){
            $segmentados = $segmentados->whereHas('summary_process', function($s) {
                $s->where('absences','>',0);
            });
        }

        if($is_paginated)
            $segmentados = $segmentados->paginate(request('paginate', 15));
        else
            $segmentados = $segmentados->get();
        return $segmentados;
    }

    // Api
    protected function getProcessesAssigned( $user )
    {
        $supervisor = $user->isSupervisor();
        if($supervisor)
            $processes_assigned = $user->processes()->get()->pluck('id')->toArray();
        else
            $processes_assigned = array_column($user->getSegmentedByModelType(Process::class),'id');
        return $processes_assigned;
    }

    protected function getSupervisorProcessesApi( $data )
    {
        $user = $data['user'];

        $response['data'] = null;

        // $benefits_asigned = array_column($user->getSegmentedByModelType(Process::class),'id');

        $processes_assigned = $user->processes()->get()->pluck('id')->toArray();

        $field = 'created_at';
        $sort = 'DESC';

        $processes_query = Process::select('id', 'workspace_id', 'title', 'active', 'starts_at', 'finishes_at')
                                    ->whereIn('id', $processes_assigned)
                                    ->orderBy($field, $sort)
                                    ->paginate(10);

        return $processes_query;
    }

    protected function getSupervisorProcessOnlyStudentsApi( $data )
    {
        $response['data'] = null;
        $user = $data['user'];

        $process_id = $data['process'];

        $process = Process::select('id','absences', 'limit_absences', 'count_absences', 'title', 'description', 'active', 'starts_at', 'finishes_at')
                    ->where('id', $process_id)
                    ->first();

        if($process)
        {
            $user->load('summary_process');
            if(count($user->processes)) {
                $participants = $this->getProcessAssistantsList($process);
                $process->participants = $participants->count() ?? 0;
                $process->students = $participants;
                $param_resource = [
                    'process_id' => $process->id,
                    'limit_absences' => $process->limit_absences,
                    'count_absences' => $process->count_absences,
                    'absences' => $process->absences,
                ];
                ProcessAssistantsSearchResource::customCollection($process->students, $param_resource);
            }
            $process->finishes_at = $process->finishes_at ? date('d-m-Y', strtotime($process->finishes_at)) : null;
            $process->starts_at = $process->starts_at ? date('d-m-Y', strtotime($process->starts_at)) : null;
            $process->percentage = 0;

            unset($process->segments);
            unset($process->absences);
            unset($process->limit_absences);
            unset($process->count_absences);
        }

        return ['data'=> $process];
    }

    protected function getSupervisorProcessOnlySupervisorsApi( $data )
    {
        $response['data'] = null;
        $user = $data['user'];

        $process_id = $data['process'];

        $process = Process::select('id', 'title', 'description', 'active')
                    ->where('id', $process_id)
                    ->with(['instructors' => function($q){
                        $q->select('id', 'fullname', 'name', 'lastname', 'surname', 'document', 'active');
                    }])
                    ->first();

        return ['data'=> $process];
    }

    protected function getSupervisorProcessApi( $data )
    {
        $response['data'] = null;
        $user = $data['user'];

        $process_id = $data['process'];

        $process = Process::select('id', 'starts_at', 'finishes_at', 'title', 'description', 'workspace_id', 'absences', 'workspace_id')
                    ->where('id', $process_id)
                    ->first();
        if($process)
        {
            $criteria_assigned = [];
            foreach ($process->segments as $segment) {

                $segment_class = new Segment();
                $segment->type_code = $segment->type?->code;

                $criteria = Segment::getCriteriaByWorkspace(Workspace::find($process->workspace_id));
                $criteria_selected = ($segment->type_code == 'direct-segmentation') ? $segment_class->setDataDirectSegmentation($criteria, $segment, true) : [];

                foreach($criteria_selected as $criteria)
                {
                    $segment_values = SegmentValue::whereIn('id', array_column($criteria['values_selected'], 'segment_value_id'))->get()->toArray();
                    $criterion_values = CriterionValue::whereIn('id', array_column($segment_values, 'criterion_value_id'))->get()->toArray();
                    foreach($criterion_values as $val) {
                        array_push($criteria_assigned, $criteria['name'].'('.$val['value_text'].')');
                    }
                }
            }
            $process->criteria_assigned = implode(', ',$criteria_assigned);
            $participants = $this->getProcessAssistantsList($process);
            $process->participants = $participants->count() ?? 0;

            $process_duration = 0;
            foreach($process->stages as $stage) {
                $process_duration += intval($stage->duration ?? 0);
            }
            $process->duration = $process_duration ? ($process_duration == 1 ? $process_duration .' día' : $process_duration .' días') : $process_duration;

            $process->count_stages = $process->stages()->count();

            $process->finishes_at = $process->finishes_at ? date('d-m-Y', strtotime($process->finishes_at)) : null;
            $process->starts_at = $process->starts_at ? date('d-m-Y', strtotime($process->starts_at)) : null;

            $process->percentage = 0;
            unset($process->stages);
            unset($process->segments);
        }

        return ['data'=> $process];
    }

    protected function getUserProcessApi( $data )
    {
        $response['data'] = null;
        $user = $data['user'];

        $process_id = $data['process'];
        // $summary_user_activities = ProcessSummaryActivity::whereHas('topic.course', function ($q) use ($user_courses) {
        //     $q->whereIn('id', $user_courses->pluck('id'))->where('active', ACTIVE)->orderBy('position');
        // })
        // ->with('status:id,code')
        // ->where('user_id', $user->id)
        // ->get();
        $user->load('summary_process');

        $process = Process::with(['instructions','stages.activities.type','stages.activities.requirement'])
                    ->where('id', $process_id)
                    ->first();
        // if($process)
        // {
        //     $exist = ProcessSummaryActivity::where('user_id', $user->id)->where()->first();
        // }

        if($process)
        {
            $total_activities = 0;
            foreach ($process->stages as $index => $stage) {
                $stage->status = $index == 0 ? 'progress' : 'locked';
                $stage->duration = $stage->duration ? ($stage->duration == 1 ? $stage->duration .' día' : $stage->duration .' días') : $stage->duration;
                foreach ($stage->activities as $activity) {
                    $total_activities++;
                    $activity->progress = 0;
                    $activity->status = 'pending';
                    $exist = ProcessSummaryActivity::where('user_id', $user->id)->where('activity_id', $activity->id)->first();
                    if($exist) {
                        $activity->status = $exist->status->code;
                        $activity->progress = $exist->progress ? round($exist->progress) : $exist->progress;
                    }
                }
            }

            $process->finishes_at = $process->finishes_at ? date('d-m-Y', strtotime($process->finishes_at)) : null;
            $process->starts_at = $process->starts_at ? date('d-m-Y', strtotime($process->starts_at)) : null;

            $process->percentage = 0;

            $count_absences = $user->summary_process()->where('process_id', $process->id)->first()?->absences ?? 0;
            $process->user_absences = $process->absences ? $count_absences.'/'.$process->absences : '-';

            $status_finished = Taxonomy::getFirstData('user-activity', 'status', 'finished')->id;
            $user_activities = ProcessSummaryActivity::where('user_id', $user->id)->where('status_id', $status_finished)->count();
            $process->user_activities_progress = $user_activities;
            $process->user_activities_total = $total_activities;


            $process->user_activities_progressbar = $user_activities > 0 && $total_activities > 0 ? round(((($user_activities * 100 / $total_activities) * 100) / 100)) : 0;
        }

        return ['data'=> $process];
    }

}
