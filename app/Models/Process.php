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
        'block_stages',
        'migrate_users',
        'corporate_process',
        'alert_user_deleted',
        'message_user_deleted',
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
        'qualification_type_id',
        'position'
    ];

    protected $casts = [
        'active' => 'boolean',
        'count_absences' => 'boolean',
        'limit_absences' => 'boolean',
        'config_completed' => 'boolean',
        'block_stages' => 'boolean',
        'migrate_users' => 'boolean',
        'corporate_process' => 'boolean',
        'alert_user_deleted' => 'boolean'
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
        return $this->hasMany(Instruction::class, 'process_id', 'id')->orderBy('position');
    }

    public function stages()
    {
        return $this->hasMany(Stage::class, 'process_id', 'id')->orderBy('position');
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

    public function subworkspaces()
    {
        return $this->belongsToMany(Workspace::class, 'process_subworkspace', 'process_id', 'subworkspace_id');
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
        $modules_id = $data['modules'] ?? current_subworkspaces_id();

        $repository_media_process = $this->getRepositoryMediaProcess();
        $processes_query = Process::with(['instructions', 'segments', 'stages', 'subworkspaces','instructors_direct' => function($i){
                                        $i->select('id','fullname','document', 'name', 'lastname', 'surname');
                                    }])
                                    ->whereHas('subworkspaces', function ($j) use ($modules_id) {
                                        $j->whereIn('subworkspace_id', $modules_id);
                                    });
                                    // ->where('workspace_id', $workspace->id);

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
                if(isset($data['subworkspaces']))
                {
                    foreach ($data['subworkspaces'] as  $subworkspace) {
                        SortingModel::setLastPositionInPivotTable(ProcessSubworkspace::class, Process::class, [
                            'subworkspace_id'=>$subworkspace,
                            'process_id' => $process->id
                        ],[
                            'subworkspace_id'=>$subworkspace,
                        ]);
                    }
                }
            endif;

            if(isset($data['subworkspaces']))
            {
                $process->subworkspaces()->sync($data['subworkspaces'] ?? []);
            }


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

        cache_clear_model(Instruction::class);
        cache_clear_model(Process::class);

        return $process;
    }

    protected function getProcessAssistantsList(Process $process, bool $is_paginated = true, bool $absences = false,$by_supervisor=null)
    {
        session()->put('platform', 'induccion');
        $course = new Course();
        $process->load('segments');
        $values_criterio_user = [];
        if($by_supervisor){
            $supervisor_criteria = json_decode($process->supervisor_criteria);
            if($supervisor_criteria && count($supervisor_criteria)){
                $values_criterio_user =  $by_supervisor->criterion_values
                                        ->whereIn('criterion_id',$supervisor_criteria)
                                        ->map(fn($c)=>['criterion_id'=>$c->criterion_id,'criterion_value_id'=>$c->id])->values()->toArray();
            }
            // dd($supervisor_criteria,$by_supervisor->criterion_values);
            $by_supervisor->criterion_user;
        }
        $segmentados_id = $course->usersSegmented(course_segments:$process->segments, type:'users_id',only_criterian_values_by_criterion:$values_criterio_user);
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
    protected function syncEnrolledDateProcess($user){
        $processes = Process::getProcessesAssigned($user);
        foreach ($processes as $process_id) {
            ProcessSummaryUser::enrolledProcess($user->id,$process_id);
        }
    }
    protected function setUsersToUpdateBackground($process_id){
        $course = new Course();
        $process = Process::find($process_id);
        $users_id =$course->usersSegmented($process->segments,'users_id');
        if(count($users_id)>0){
            Summary::updateUsersByCourse($process,$users_id,false,false,'segmented',send_notification:false);
        }
    }
    protected function getProcessesAssigned( $user, $user_aprendizaje = false )
    {
        $type_employee_onboarding = Taxonomy::getFirstData('user','type', 'employee_onboarding');
        $processes_assigned = [];

        if($user->type_id == $type_employee_onboarding?->id)
        {
            $supervisor = count($user->processes);
            if($supervisor)
                $processes_assigned = $user->processes()->get()->pluck('id')->toArray();
            else
                $processes_assigned = array_column($user->getSegmentedByModelType(Process::class),'id');
        }
        if($user_aprendizaje) {
            $processes_assigned = $user->processes()->get()->pluck('id')->toArray();
        }
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

        $process = Process::select('id','absences','supervisor_criteria','limit_absences', 'count_absences', 'title', 'description', 'active', 'starts_at', 'finishes_at')
                    ->where('id', $process_id)
                    ->first();
        if($process)
        {
            $user->load('summary_process');
            // if(count($user->processes)) {
                $participants = $this->getProcessAssistantsList(process:$process,by_supervisor:$user);
                $process->participants = $participants->total() ?? 0;
                $process->students = $participants;
                $param_resource = [
                    'process_id' => $process->id,
                    'limit_absences' => $process->limit_absences,
                    'count_absences' => $process->count_absences,
                    'absences' => $process->absences,
                ];
                ProcessAssistantsSearchResource::customCollection($process->students, $param_resource);
            // }
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
            $process->participants = $participants->total() ?? 0;

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
        $current_date = now()->startOfDay();

        $process = Process::where('id', $process_id)
                    ->select('id', 'limit_absences', 'absences', 'count_absences', 'color', 'icon_finished', 'starts_at', 'finishes_at', 'color_map_even', 'color_map_odd','certificate_template_id', 'block_stages', 'position', 'alert_user_deleted', 'message_user_deleted')
                    ->active()
                    ->first();
        if($process)
        {
            $user_summary_process = $user->summary_process()->where('process_id', $process->id)->select('completed_instruction', 'status_id', 'enrolled_date', 'absences')->first();
            $process->completed_instruction = $user_summary_process?->completed_instruction ?? false;
            $total_activities = 0;
            $stages_list = $process->stages()
                                    ->select('id', 'duration', 'position', 'title')
                                    ->active()
                                    ->get();
            $days_stages = 0;
            if($stages_list->count() > 0) {
                foreach ($stages_list as $index => $stage) {
                    // segun el copy, si block_stages es 1 entonces siempre se debe mostrar
                    if($process->block_stages) {
                        $stage->status = 'progress';
                    }
                    else {
                        $stage->status = 'locked';
                        if($user_summary_process?->enrolled_date){
                            $enrolled_date = Carbon::create($user_summary_process->enrolled_date);
                            $days_stages = $days_stages + $stage->duration;
                            $finish_days_stage = $enrolled_date->addDay($days_stages)->startOfDay();
                            $diff_days = $current_date->diffInDays($finish_days_stage);
                            if($diff_days <= $stage->duration || $finish_days_stage <= $current_date){
                                $stage->status = 'progress';
                            }
                        }
                    }
                    $stage->duration = $stage->duration ? ($stage->duration == 1 ? $stage->duration .' día' : $stage->duration .' días') : $stage->duration;
                    $activities_list = $stage->activities()
                                                ->with(['type', 'requirement' => function($r){
                                                    $r->select('id', 'model_id', 'type_id', 'title', 'description')
                                                    ->with(['type']);
                                                }])
                                                ->select('id', 'stage_id', 'title', 'description', 'type_id', 'activity_requirement_id', 'model_id', 'position')
                                                ->active()
                                                ->get();
                    if($activities_list->count() > 0) {
                        foreach ($activities_list as $activity) {
                            $total_activities++;
                            $activity->progress = 0;
                            $activity->status = 'pending';
                            $exist = ProcessSummaryActivity::where('user_id', $user->id)->where('activity_id', $activity->id)->first();
                            if($exist) {
                                $activity->status = $exist->status?->code;
                                $activity->progress = $exist->progress ? round($exist->progress) : $exist->progress;
                            }
                            unset($activity->type_id);
                            unset($activity->activity_requirement_id);
                        }
                    }
                    $stage->activities = $activities_list;
                }
            }
            $process->finishes_at = $process->finishes_at ? date('d-m-Y', strtotime($process->finishes_at)) : null;
            $process->starts_at = $process->starts_at ? date('d-m-Y', strtotime($process->starts_at)) : null;

            $process->percentage = 0;

            if($process->count_absences) {
                $count_absences = $user_summary_process?->absences ?? 0;
                if($process->limit_absences) {
                    $process->user_absences = $count_absences;
                }
                else {
                    $absences = $process->absences ?? 0;
                    $process->user_absences = $count_absences.'/'.$absences;
                }
            }
            else {
                $process->user_absences = null;
            }

            $status_finished = Taxonomy::getFirstData('user-activity', 'status', 'finished')->id;
            $user_activities = ProcessSummaryActivity::where('user_id', $user->id)
                                ->whereHas('activity', function($a) use ($process) {
                                    $stages_ids = Stage::select('id')->where('process_id', $process->id)->pluck('id')->toArray();
                                    $a->whereIn('stage_id', $stages_ids);
                                })
                                ->where('status_id', $status_finished)
                                ->count();
            $process->user_activities_progress = $user_activities;
            $process->user_activities_total = $total_activities;


            $process->user_activities_progressbar = $user_activities > 0 && $total_activities > 0 ? round(((($user_activities * 100 / $total_activities) * 100) / 100)) : 0;
            $process->stages = $stages_list;

            $process->certificate = [
                'enabled' => false,
                'message' => null,
                'url' => null,
                'url_download' => null,
                'login_aprendizaje' => false
            ];

            $tax_user_process_finished = Taxonomy::getFirstData('user-process', 'status', 'finished');
            // if($user_summary_process?->status_id == $tax_user_process_finished?->id)
            if($process->user_activities_progressbar >= 100)
            {
                $process->certificate = [
                    'enabled' => true,
                    'message' => '¡Gracias por realizar este proceso con nosotros!',
                    'url' => "tools/induccion/ver_diploma/{$user->id}/{$process->id}",
                    'url_download' => "tools/induccion/dnc/{$user->id}/{$process->id}",
                    'login_aprendizaje' => false
                ];
            }

            $tax_user_process_removed_x_disapproval = Taxonomy::getFirstData('user-process', 'status', 'removed_x_disapproval');

            $process->reprobate_user_alert = [
                'show_alert' => false,
                'message' => null
            ];
            if($user_summary_process?->status_id == $tax_user_process_removed_x_disapproval?->id) {
                if($process->alert_user_deleted){
                    $process->reprobate_user_alert = [
                        'show_alert' => true,
                        'message' => $process->message_user_deleted
                    ];
                }
            }

            unset($process->limit_absences);
            unset($process->absences);
            // unset($process->count_absences);
            unset($process->certificate_template_id);
            unset($process->block_stages);
            unset($process->alert_user_deleted);
            unset($process->message_user_deleted);
        }

        return $process;
    }

    protected function getUserProcessOnlyInstructionsApi( $process_id )
    {
        $process = Process::with(['instructions'])
                    ->where('id', $process_id)
                    ->select('id', 'title' , 'description', 'logo', 'background_web', 'background_mobile', 'color', 'image_guia')
                    ->first();

        return $process;
    }

}
