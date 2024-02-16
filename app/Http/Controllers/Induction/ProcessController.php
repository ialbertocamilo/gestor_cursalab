<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Induction\ProcessStoreUpdateRequest;
use App\Http\Resources\Induccion\ProcessAssistantsSearchResource;
use App\Models\Course;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\EntrenadorUsuario;
use App\Models\Media;
use App\Models\Process;
use App\Models\Segment;
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

        if(isset($data['file_background_mobile']))
            $data = Media::requestUploadFile($data, 'background_mobile', false, 'background_mobile', 'png');

        if(isset($data['file_background_web']))
            $data = Media::requestUploadFile($data, 'background_web', false, 'background_web', 'png');

        if(isset($data['file_logo']))
            $data = Media::requestUploadFile($data, 'logo', false, 'logo', 'png');

        if(isset($data['file_image_guia']))
            $data = Media::requestUploadFile($data, 'image_guia', false, 'image_guia', 'png');

        if(isset($data['file_icon_finished']))
            $data = Media::requestUploadFile($data, 'icon_finished', false, 'icon_finished', 'png');

        $process = Process::storeRequest($data);

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

        if(isset($data['file_background_mobile']))
            $data = Media::requestUploadFile($data, 'background_mobile', false, 'background_mobile', 'png');

        if(isset($data['file_background_web']))
            $data = Media::requestUploadFile($data, 'background_web', false, 'background_web', 'png');

        if(isset($data['file_logo']))
            $data = Media::requestUploadFile($data, 'logo', false, 'logo', 'png');

        if(isset($data['file_image_guia']))
            $data = Media::requestUploadFile($data, 'image_guia', false, 'image_guia', 'png');

        if(isset($data['file_icon_finished']))
            $data = Media::requestUploadFile($data, 'icon_finished', false, 'icon_finished', 'png');

        $process = Process::storeRequest($data, $process);

        $response = [
            'msg' => 'Proceso creado correctamente.',
            'process' => $process,
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
            $process->supervisor_criteria = json_encode(array_column($segments_supervisors_criteria, 'id'));
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

        if(count($users_segmented) > 0 && count($supervisors_segmented) > 0 ) {
            foreach ($users_segmented as $uss) {
                $this->asignar($uss, array_keys($save_supervisors));
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

    public function searchAssistants(Process $process, Request $request)
    {
        $workspace = get_current_workspace();
        // $request->mergeIfMissing(['workspace_id' => $workspace?->id]);
        $assistants = Process::getProcessAssistantsList($process);
        ProcessAssistantsSearchResource::collection($assistants);
        return $this->success($assistants);
    }
}
