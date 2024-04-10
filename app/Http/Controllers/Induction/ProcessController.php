<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Induction\ProcessStoreUpdateRequest;
use App\Http\Resources\Induccion\ProcessAssistantsSearchResource;
use App\Models\Activity;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\EntrenadorUsuario;
use App\Models\Instruction;
use App\Models\Media;
use App\Models\Poll;
use App\Models\PollQuestion;
use App\Models\Process;
use App\Models\ProcessSubworkspace;
use App\Models\School;
use App\Models\Segment;
use App\Models\SortingModel;
use App\Models\Stage;
use App\Models\Supervisor;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\UserRelationship;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessController extends Controller
{

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing(['workspace_id' => $workspace?->id]);
        $data = Process::getProcessesList($request->all());

        return $this->success($data);
    }
    public function getFormSelectsProcess()
    {
        $workspace = get_current_workspace();

        $modules = Workspace::where('parent_id', $workspace?->id)
            ->whereIn('id', current_subworkspaces_id())
            ->select('id', 'name')->get();

        return $this->success(compact('modules'));
    }
    public function storeInline(Request $request)
    {
        $data = [ 'title' => $request->title ];

        $process = Process::storeRequest($data);

        $response = [
            'msg' => 'Proceso creado correctamente.',
            'process' => $process,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function store(Process $process, ProcessStoreUpdateRequest $request)
    {
        $data = $request->validated();

        $type_icon_finished = isset($data['icon_finished_name']) && str_contains($data['icon_finished_name'], 'img_onb_') ? 'icon_final_default' : 'icon_final';

        $type_image_guia = isset($data['image_guide_name']) && str_contains($data['image_guide_name'], 'img_guide_') ? 'guide_default' : 'guide';

        if(isset($data['file_background_mobile']))
            $data = Media::requestUploadFile($data, 'background_mobile', false, 'background_mobile', 'png');

        if(isset($data['file_background_web']))
            $data = Media::requestUploadFile($data, 'background_web', false, 'background_web', 'png');

        if(isset($data['file_logo']))
            $data = Media::requestUploadFile($data, 'logo', false, 'logo', 'png');

        if(isset($data['file_image_guia']))
            $data = Media::requestUploadFile($data, 'image_guia', false, 'image_guia', 'png', $type_image_guia);

        if(isset($data['file_icon_finished']))
            $data = Media::requestUploadFile($data, 'icon_finished', false, 'icon_finished', 'png', $type_icon_finished);

        $process = Process::storeRequest($data);

        if( $process )
        {
            $assistans_route = route('process.assistants.index', [$process->id]);
            $stages_route = route('stages.index', [$process->id]);
            $certificate_route = ($process->certificate_template_id) ?
                                        route('process.diploma.edit', [$process->id, $process->certificate_template_id]) :
                                        route('process.diploma.create', [$process->id]);

            $process->assistans_route = $assistans_route;
            $process->stages_route = $stages_route;
            $process->certificate_route = $certificate_route;
        }

        $response = [
            'msg' => 'Proceso creado correctamente.',
            'process' => $process,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function update(Process $process, ProcessStoreUpdateRequest $request)
    {

        $data = $request->validated();

        $type_icon_finished = isset($data['icon_finished_name']) && str_contains($data['icon_finished_name'], 'img_onb_') ? 'icon_final_default' : 'icon_final';

        $type_image_guia = isset($data['image_guide_name']) && str_contains($data['image_guide_name'], 'img_guide_') ? 'guide_default' : 'guide';

        if(isset($data['file_background_mobile']))
            $data = Media::requestUploadFile($data, 'background_mobile', false, 'background_mobile', 'png');

        if(isset($data['file_background_web']))
            $data = Media::requestUploadFile($data, 'background_web', false, 'background_web', 'png');

        if(isset($data['file_logo']))
            $data = Media::requestUploadFile($data, 'logo', false, 'logo', 'png');

        if(isset($data['file_image_guia']))
            $data = Media::requestUploadFile($data, 'image_guia', false, 'image_guia', 'png', $type_image_guia);

        if(isset($data['file_icon_finished']))
            $data = Media::requestUploadFile($data, 'icon_finished', false, 'icon_finished', 'png', $type_icon_finished);

        $process = Process::storeRequest($data, $process);

        $response = [
            'msg' => 'Proceso creado correctamente.',
            'process' => $process,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function updateQualificationStages( Process $process, Request $request )
    {
        $stages = json_decode($request->stages_json);
        $qualification_type = $request->qualification_type;

        $process->qualification_type_id = $qualification_type;
        $process->save();
        if(is_array($stages) && count($stages)>0) {
            foreach ($stages as $st) {
                $stage = Stage::where('id', $st->id)->first();
                if($stage) {
                    $stage->qualification_percentage = $st->qualification_percentage;
                    $stage->qualification_equivalent = $st->qualification_equivalent;
                    $stage->save();
                }
            }
        }
        $response = [
            'msg' => 'Sistema de calificación actualizado',
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function getData(Process $process)
    {
        $response = Process::getData($process);

        return $this->success($response);
    }
    /**
     * Process request to toggle value of active status (1 or 0)
     *
     * @param Process $process
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Process $process, Request $request)
    {
        if(!$process->active){
            Process::setUsersToUpdateBackground($process->id);
        }
        $process->update(['active' => !$process->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Process request to delete process record
     *
     * @param Process $process
     * @return JsonResponse
     */
    public function destroy(Process $process)
    {
        $process->delete();

        return $this->success(['msg' => 'Proceso eliminado correctamente.']);
    }

    public function supervisorsUsers(Request $request)
    {
        dd($request->all());
        return $this->success(['msg' => 'Proceso eliminado correctamente.']);
    }

    public function storeSegments(Request $request)
    {
        $segments_supervisors_direct = $request->segments_supervisors_direct;
        $segments_supervisors_criteria = $request->segments_supervisors_criteria;
        $save_supervisors = [];

        $process = Process::where('id', $request->model_id)->first();
        $supervisors = $this->selectedSupervisors($request);
        if(count($segments_supervisors_criteria) > 0) {
            $process->supervisor_criteria = json_encode(array_unique(array_column($segments_supervisors_criteria, 'id')));
            $process->save();

            $selected_supervisors = $supervisors->pluck('id')->toArray();
            if(count($selected_supervisors) > 0) {
                $pivotData = array_fill(0, count($selected_supervisors), ['type' => 'criteria']);
                $syncData  = array_combine($selected_supervisors, $pivotData);
                $save_supervisors += $syncData;
            }
        }

        if(count($segments_supervisors_direct) > 0) {
            $selected_supervisors = array_column($segments_supervisors_direct, 'id');
            if(count($selected_supervisors) > 0) {
                $pivotData = array_fill(0, count($selected_supervisors), ['type' => 'direct']);
                $syncData  = array_combine($selected_supervisors, $pivotData);
                $save_supervisors += $syncData;
            }
        }

        $process?->instructors()->sync($save_supervisors);

        // $this->saveSegmentInstructors($request, $supervisors);

        $save_segmented = Segment::storeRequestData($request);

        $users_segmented = Process::getProcessAssistantsList($process, false)->pluck('id')->toArray();
        $supervisors_segmented = array_keys($save_supervisors);

        $key_save_supervisors = array_keys($save_supervisors);
        $users_segmented_filter = array_diff_key($users_segmented, $key_save_supervisors);
        $asigned_errors = [];
        if(count($users_segmented_filter) > 0 && count($supervisors_segmented) > 0 ) {
            foreach ($users_segmented_filter as $uss) {
                $asigned_errors[] = $this->asignar($uss, $key_save_supervisors);
            }
        }

        return $save_segmented;
    }

    public function asignar($user, $trainers)
    {
        $errors = [];
        foreach ($trainers as $key => $trainer) {
            $temp = [
                'trainer_id' => $trainer,
                'user_id' => $user,
                'active' => 1
            ];
            $asignar_msg = EntrenadorUsuario::asignar($temp, false);
            if($asignar_msg['error'])
                array_push($errors, $asignar_msg['msg']);
        }
        $apiResponse['errors'] = $errors;
        cache_clear_model(EntrenadorUsuario::class);
        cache_clear_model(User::class);

        return $apiResponse;
    }
    private function criteriaSelected(Request $request) {

        $criteria_selected = $request->segments_supervisors_criteria;

        $values = array_column($criteria_selected, 'values');
        $values_selected = array_column($criteria_selected, 'values_selected');

        $criteria_list = [];
        $criteria_selected_list = [];
        $criteria_selected_list_data = [];

        foreach($values_selected as $val) {
            foreach($val as $item) {
                array_push($criteria_selected_list, $item);
            }
        }
        foreach($values as $val) {
            foreach($val as $item) {
                array_push($criteria_list, $item);
            }
        }
        $criteria_selected_id = array_column($criteria_selected_list, 'id');

        foreach($criteria_selected_id as $id) {
            $ff = array_filter($criteria_list, function ($var) use ($id) {
                return ($var['id'] == $id);
            });
            if(count($ff)){
                foreach($ff as $f){
                    array_push($criteria_selected_list_data, $f);
                }
            }
        }
        return compact('criteria_selected_list_data', 'criteria_selected_id');
    }

    private function selectedSupervisors(Request $request)
    {
        $workspace = get_current_workspace();

        $criterion_module_id = Criterion::with('field_type')->where('code', 'module')->first('id');
        $criteria_selected_list_data = $this->criteriaSelected($request)['criteria_selected_list_data'];
        $criteria_selected_id = $this->criteriaSelected($request)['criteria_selected_id'];

        $subworkspace_cv_id = [];

        foreach($criteria_selected_list_data as $csld) {
            if( $csld['criterion_id'] == $criterion_module_id?->id) {
                array_push($subworkspace_cv_id, $csld['id']);
            }
        }

        $subworkspace = Workspace::whereIn('criterion_value_id', $subworkspace_cv_id)->get()->pluck('id')->toArray() ?? [];

        // Obtener los supervisores filtrados

        $supervisors_query = User::query()
            ->FilterByPlatform()
            ->whereHas('segments')
            ->withCount([
                'segments' => function ($q) {
                    $q->
                    whereRelation('code', 'code', 'user-supervise');
                },
            ])
            ->whereRelation('segments.code', 'code', 'user-supervise');

        $supervisors_query->withWhereHas('subworkspace', function ($query) use ($workspace, $subworkspace) {
            if ($subworkspace)
                $query->whereIn('id', $subworkspace);
            else
                $query->where('parent_id', $workspace?->id);
        });
        $supervisors_query->with(['segments','criterion_user' => function ($q) use ($criteria_selected_id) {
                                $q->select('*');
                                $q->whereIn('criterion_value_id',$criteria_selected_id);
                            }])
                            ->whereHas('criterion_user', function($q) use ($criteria_selected_id) {
                                $q->whereIn('criterion_value_id',$criteria_selected_id);
                            });

        return $supervisors_query->get();
    }

    public function saveSegmentInstructors(Request $request, $supervisors)
    {
        $criteria_selected = $request->segments_supervisors_criteria;
        $criteria_selected_list_data = $this->criteriaSelected($request)['criteria_selected_list_data'];

        $supervisors->each(function($sup) use ($supervisors, $criteria_selected_list_data, $criteria_selected) {
            $sup->criterion_user->each(function($cri) use ($supervisors, $criteria_selected_list_data, $sup, $criteria_selected) {

                $criterion_value_id = $cri->criterion_value_id;
                $inArray = array_filter($criteria_selected_list_data, function ($var) use ($criterion_value_id) {
                    return ($var['id'] == $criterion_value_id);
                });
                if(count($inArray)) {
                    $criterion_id = array_column($inArray, 'criterion_id');
                    if(count($criterion_id) == 0){
                        dd($inArray,$criterion_id);
                    }
                    $criteria_selected_data = array_filter($criteria_selected, function ($var) use ($criterion_id) {
                        return ($var['id'] == $criterion_id[0]);
                    });
                    $criteria_selected_data_save = [];
                    foreach($criteria_selected_data as $csd) {
                        unset($csd['values_selected']);
                        $csd['values_selected'] = $inArray;
                        $criteria_selected_data_save = $csd;
                    }
                    $sup_segments = new Request();
                    $sup->segments->each(function($seg) use ($criteria_selected_data_save, $sup, $sup_segments){
                        $sup_segments->replace([
                            'model_id' => $sup->id,
                            'model_type' => User::class,
                            'code' => 'user-supervise',
                            'segments' => [[
                                'id' => $seg->id,
                                'criteria_selected' => [$criteria_selected_data_save]
                            ]],
                            'segment_by_document' => [
                                'criteria_selected' => []
                            ]
                            ]);
                    });
                    // dd($sup_segments);

                    // $segments_id = array_column($sup_segments->segments, 'id');
                    // Segment::where('model_type', $sup_segments->model_type)->where('model_id', $sup_segments->model_id)
                    //     ->whereRelation('type', 'code', 'direct-segmentation')
                    //     ->whereNotIn('id', $segments_id)->delete();

                    $this->segmentStoreRequestData($sup_segments);
                }
            });
        });

        // $user_documents = array_column($data, 'document');
        // $user_documents = [$supervisor->document];
        // $users = User::with('subworkspace.module_criterion_value')->whereIn('document', $user_documents)->get();
        // $users_segments = UserRelationship::setUsersAsSupervisor($users);

        // if(!$checklist->segments){
        //     $workspace_id = $checklist->workspace_id;
        //     $checklist->segments = Checklist::getChecklistsWorkspace(checklist_id:$checklist_id, with_segments:true, select : 'id');
        // }
        // $users_assigned = Course::usersSegmented($checklist->segments, $type = 'users_id');
    }

    private function segmentStoreRequestData($data)
    {
        $segment_class = new Segment();
        try {

            DB::beginTransaction();

            $direct_segmentation = Taxonomy::getFirstData('segment', 'type', 'direct-segmentation');
            $code_segmentation = Taxonomy::getFirstData('segment', 'code', $data->code);


            foreach ($data->segments as $key => $segment_row) {
                if (count($segment_row['criteria_selected']) == 0) continue;

                $segment_data = [
                    'type_id' => $direct_segmentation->id,
                    'code_id' => $code_segmentation?->id,
                    'model_type' => $data->model_type,
                    'model_id' => $data->model_id,
                    'name' => 'Nuevo segmento',
                    'active' => ACTIVE,
                ];

                $segment = str_contains($segment_row['id'], "new-segment-") ?
                    Segment::create($segment_data)
                    : Segment::find($segment_row['id']);

                $values = [];

                foreach ($segment_row['criteria_selected'] ?? [] as $criterion) {

                    $temp_values = match ($criterion['field_type']['code']) {
                        'default' => $segment_class->prepareDefaultValues($criterion),
                        'date' => $segment_class->prepareDateRangeValues($criterion),
                        default => [],
                    };

                    $values = array_merge($values, $temp_values);
                }

                if ($segment)
                    $segment->values()->sync($values);
            }

            DB::commit();
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();

            return $this->error($e->getMessage());
        }

        $message = "Segmentación actualizada correctamente.";

        return $this->success(['msg' => $message], $message);
    }

    // Assistants

    public function loadInfoAssistants(Process $process)
    {
        $response['users'] = Process::getProcessAssistantsList($process)->count() ?? 0;
        $response['absences'] = Process::getProcessAssistantsList($process, true, true)->count() ?? 0;
        return $this->success($response);
    }
    public function searchAssistants(Process $process, Request $request)
    {
        $assistants = Process::getProcessAssistantsList($process);
        $param_resource = [
            'process_id' => $process->id,
            'limit_absences' => $process->limit_absences,
            'count_absences' => $process->count_absences,
            'absences' => $process->absences,
        ];
        ProcessAssistantsSearchResource::customCollection($assistants, $param_resource)
                                        ->map(function($i) use ($process) {
                                            $i->process = $process?->id;
                                            $i->limit_absences = $process?->absences ?? 0;
                                        });
        return $this->success($assistants);
    }

    // Duplicar procesos
    

    public function duplicate(Request $request)
    {
        $response['msg'] = 'No se encontró el proceso, o el valor ingresado no es válido.';
        if($request->process_id) {
            $process = Process::where('id', $request->process_id)->first();
            if($process) {
                $new_process = $this->forDuplicateCreateProcess($process);
                if($new_process) {
                    $process_certificate = Certificate::where('id', $process->certificate_template_id)->first();
                    if($process_certificate) {
                        $new_certificate = Certificate::create([
                            'media_id' => $process_certificate->media_id,
                            'title' => $process_certificate->title,
                            'path_image' => $process_certificate->path_image,
                            'info_bg' => $process_certificate->info_bg,
                            'd_objects' => $process_certificate->d_objects,
                            's_objects' => $process_certificate->s_objects,
                            'active' => $process_certificate->active
                        ]);
                        if($new_certificate) {
                            $new_process->certificate_template_id = $new_certificate->id;
                            $new_process->save();
                        }
                    }
                    if($process->stages) {
                        foreach($process->stages as $stage) {
                            $new_school = null;
                            if($stage->school_id) {
                                $find_school = School::where('id', $stage->school_id)->first();
                                if($find_school) {
                                    $new_school = School::storeRequest([
                                        'name' => $find_school->name,
                                        'description' => $find_school->description,
                                        'imagen' => $find_school->imagen,
                                        'plantilla_diploma' => $find_school->plantilla_diploma,
                                        'scheduled_restarts' => $find_school->scheduled_restarts,
                                        'active' => $find_school->active,
                                        'subworkspaces' => $find_school->subworkspaces,
                                        'platform_id' => $find_school->platform_id,
                                    ]);
                                }
                            }
                            $new_stage = Stage::create([
                                'process_id' => $new_process->id,
                                'school_id' => $new_school?->id,
                                'title' => $stage->title,
                                'duration' => $stage->duration,
                                'position' => $stage->position,
                                'active' => false,
                                'qualification_percentage' => $stage->qualification_percentage,
                                'qualification_equivalent' => $stage->qualification_equivalent
                            ]);
                            if($new_stage) {
                                $stage_certificate = Certificate::where('id', $stage->certificate_template_id)->first();
                                if($stage_certificate) {
                                    $new_certificate = Certificate::create([
                                        'media_id' => $stage_certificate->media_id,
                                        'title' => $stage_certificate->title,
                                        'path_image' => $stage_certificate->path_image,
                                        'info_bg' => $stage_certificate->info_bg,
                                        'd_objects' => $stage_certificate->d_objects,
                                        's_objects' => $stage_certificate->s_objects,
                                        'active' => $stage_certificate->active
                                    ]);
                                    if($new_certificate) {
                                        $new_stage->certificate_template_id = $new_certificate->id;
                                        $new_stage->save();
                                    }
                                }
                                // Crear actividades
                                $this->forDuplicateCreateActivities($stage->activities, $new_stage);
                            }
                        }
                    }
                }
            }
            $response['msg'] = 'Proceso duplicado correctamente.';
        }
        return $this->success($response);
    }

    private function forDuplicateCreateProcess($process)
    {
        $new_process = Process::create([
                'workspace_id' => $process->workspace_id,
                'description' => $process->description,
                'block_stages' => $process->block_stages,
                'migrate_users' => $process->migrate_users,
                'alert_user_deleted' => $process->alert_user_deleted,
                'message_user_deleted' => $process->message_user_deleted,
                'count_absences' => $process->count_absences,
                'limit_absences' => $process->limit_absences,
                'absences' => $process->absences,
                'logo' => $process->logo,
                'background_web' => $process->background_web,
                'background_mobile' => $process->background_mobile,
                'color' => $process->color,
                'icon_finished' => $process->icon_finished,
                'icon_finished_name' => $process->icon_finished_name,
                'starts_at' => $process->starts_at,
                'finishes_at' => $process->finishes_at,
                'color_map_even' => $process->color_map_even,
                'color_map_odd' => $process->color_map_odd,
                'image_guia' => $process->image_guia,
                'image_guide_name' => $process->image_guide_name,
                'qualification_type_id' => $process->qualification_type_id,
                'position' => $process->position,
                'title' => '(Duplicado) '.$process->title,
                'active' => false,
                'config_completed' => false
            ]);

        // subworkspaces
        if($process->subworkspaces)
        {
            foreach ($process->subworkspaces as  $subworkspace) {
                SortingModel::setLastPositionInPivotTable(ProcessSubworkspace::class, Process::class, [
                    'subworkspace_id'=>$subworkspace,
                    'process_id' => $new_process->id
                ],[
                    'subworkspace_id'=>$subworkspace,
                ]);
            }
            $new_process->subworkspaces()->sync($process->subworkspaces ?? []);
        }
        
        //instructions
        if($process->instructions)
        {
            foreach ($process->instructions as $key => $instruction) {
                Instruction::create(
                    [
                        'description' => $instruction->description,
                        'process_id' => $new_process->id,
                        'position' => $key + 1,
                    ]
                );
            }
        }
        return $new_process;
    }

    private function forDuplicateCreateActivities($activities, $new_stage)
    {
        if($activities)
        {
            foreach($activities as $activity)
            {
                $model_id = null;
                if($activity->model_type == Poll::class)
                {
                    $poll = Poll::where('id', $activity?->model_id)->first();
                    if($poll) {
                        $new_poll = Poll::create([
                            'workspace_id' => $poll->workspace_id,
                            'type_id' => $poll->type_id,
                            'anonima' => $poll->anonima,
                            'titulo' => $poll->titulo,
                            'imagen' => $poll->imagen,
                            'position' => $poll->position,
                            'active' => $poll->active,
                            'platform_id' => $poll->platform_id
                        ]);
                        if($new_poll) {
                            $model_id = $new_poll->id;
                            if($poll->questions) {
                                foreach($poll->questions as $question) {
                                    PollQuestion::create([
                                        "poll_id" => $new_poll->id,
                                        "type_id" => $question->type_id,
                                        "titulo" => $question->titulo,
                                        "opciones" => $question->opciones,
                                        "active" => $question->active
                                    ]);
                                }
                            }
                        }
                    }
                }
                    
                $new_activity = Activity::create([
                    'stage_id' => $new_stage->id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'position' => $activity->position,
                    'active' => false,
                    'qualified' => $activity->qualified,
                    'required' => $activity->required,
                    'type_id' => $activity->type_id,
                    'model_id' => $model_id,
                    'model_type' => $activity->model_type,
                    'activity_requirement_id' => null,
                    'percentage_ev' => $activity->percentage_ev
                ]);
                
            }
        }
        
    }
}
