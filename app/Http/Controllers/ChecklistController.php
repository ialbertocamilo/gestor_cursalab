<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Taxonomy;
use App\Models\CheckList;
use App\Models\Criterion;
use Illuminate\Http\Request;
use App\Http\Requests\ChecklistStoreRequest;

class ChecklistController extends Controller
{
    public function getFormSelects(){
        $checklist_default_configuration = get_current_workspace()->checklist_configuration;
        $qualification_types = Taxonomy::getDataForSelect('system', 'qualification-type');
        $types_checklist = Taxonomy::getDataForSelect('checklist', 'type_checklist');

        $criteria = [];
        if(count($checklist_default_configuration->managers_criteria)){
            $criteria = Criterion::select('id','name')->whereIn('id',$checklist_default_configuration->managers_criteria)->get();
        }
        unset($checklist_default_configuration->managers_criteria);
        $data = compact(
            'checklist_default_configuration','qualification_types','criteria','types_checklist'
        );
        return $this->success($data);
    }
    public function editChecklist(CheckList $checklist){
        $checklist->load('modality:id,name,code,extra_attributes');
        $checklist->evaluation_types  = Taxonomy::select('id','name','code','extra_attributes')
                                                ->whereIn('id',$checklist->extra_attributes['evaluation_types_id'])
                                                ->get();
        return $this->success([
            'checklist'=>$checklist
        ]);
    }
    public function storeChecklist(ChecklistStoreRequest $request){
        $data = CheckList::storeRequest($request->validated());
        $data['msg'] = 'Checklist creado correctamente.';
        return $this->success($data);
    }
    public function updateChecklist(ChecklistStoreRequest $request,CheckList $checklist){
        $data = CheckList::storeRequest($request->validated(),$checklist);
        $data['msg'] = 'Checklist actualizado correctamente.';
        return $this->success($data);
    }
 
    public function searchChecklist(Request $request){
        $data = CheckList::gridCheckList($request->all());
        return $this->success($data);
    }

    public function formSelectsActivities(){
        $checklist_type_response = Taxonomy::getDataForSelect('checklist', 'type_response_activity');
        return $this->success(['checklist_type_response'=>$checklist_type_response]);
    }
    public function listActivitiesByChecklist(CheckList $checklist){
        $checklist->load('activities','activities.checklist_response:id,name','activities.custom_options');
        return $this->success([
            'activities'=>$checklist->activities
        ]);
    }
    public function saveActivitiesByChecklist(CheckList $checklist,Request $request){
        $activities = $request->all(); 
        $data = CheckList::saveActivities($checklist,$activities);
        $data['msg'] = 'Actividades creadas correctamente.';
        return $this->success($data);
    }

    public function getSegments(CheckList $checklist){
        $response = Checklist::getSegments($checklist);
        return $this->success($response);
    }
    public function supervisorSegmentation(CheckList $checklist){
        $supervisor_assigned_directly = [];
        if(count($checklist->supervisor_ids) > 0){
            $supervisor_assigned_directly = User::select('id','fullname','document', 'name', 'lastname', 'surname')
                                                ->whereIn('id',$checklist->supervisor_ids)
                                                ->get();
        }
        return $this->success([
            'supervisor_criteria'=>$checklist->supervisor_criteria,
            'supervisor_assigned_directly'=>$supervisor_assigned_directly
        ]);
    }
    public function saveSupervisorSegmentation(CheckList $checklist,Request $request){
        $data = CheckList::updateSupervisors($checklist,$request->all());
        $data['msg'] = 'Supervisores actualizados.';
        return $this->success($data);
    }
}
