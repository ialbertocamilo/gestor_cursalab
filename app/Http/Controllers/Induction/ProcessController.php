<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Induction\ProcessStoreUpdateRequest;
use App\Models\Course;
use App\Models\Media;
use App\Models\Process;
use App\Models\Segment;
use App\Models\Supervisor;
use App\Models\User;
use App\Models\UserRelationship;
use Illuminate\Http\Request;

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

    public function store(ProcessStoreUpdateRequest $request)
    {
        // $data = [
        //     'title' => $request->title,
        //     'description' => $request->description,
        //     'limit_absences' => $request->limit_absences ?? false,
        //     'count_absences' => $request->count_absences ?? false,
        //     'absences' => $request->absences ?? null,
        // ];

        $process = Process::storeRequest($request->validated());

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

        $data = Media::requestUploadFile($data, 'background_mobile', false, 'background_mobile', 'png');
        $data = Media::requestUploadFile($data, 'background_web', false, 'background_web', 'png');
        $data = Media::requestUploadFile($data, 'logo', false, 'logo', 'png');
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
        $criteria_selected = $request->segments_supervisors;
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

        // Obtener los supervisores filtrados
        $workspace = get_current_workspace();
        $subworkspace = null;

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
        $supervisors = $supervisors_query->get();
        // Obtener los supervisores filtrados

        $supervisors->each(function($sup) use ($supervisors, $criteria_selected_list_data, $criteria_selected) {
            $sup->criterion_user->each(function($cri) use ($supervisors, $criteria_selected_list_data, $sup, $criteria_selected) {
                // $inArray = in_array($cri->criterion_value_id, $criteria_selected_list);

                // $keys = array_keys(array_column($criteria_selected_list, 'id'), 2);
                // $new_array = array_map(function($k) use ($criteria_selected_list){return $criteria_selected_list[$k];}, $keys);

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
                        // $sup_segments->push([
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
                    // dd($sup_segments->segments);
                    Segment::storeRequestData($sup_segments);
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


        return Segment::storeRequestData($request);
    }

}
