<?php

namespace App\Http\Controllers;

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
        // $checklist->evaluation_types  = 
        return $this->success([
            'checklist'=>$checklist
        ]);
    }
    public function storeChecklist(ChecklistStoreRequest $request){
        $data = $request->validated();
        $cheklist = CheckList::storeRequest($data);
        return $this->success([
            'msg'=>'checklist creado correctamente.',
            'checklist' => [
                'id'=>$cheklist->id,
                'title'=>$cheklist->title
            ]
        ]);
    }
    public function updateChecklist(ChecklistStoreRequest $request,CheckList $checklist){
        $data = $request->validated();
        CheckList::storeRequest($data,$checklist);
        return $this->success([
            'msg'=>'checklist actualizado correctamente.'
        ]);
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
        // $checklist = $checklist->
        return $this->success([
            'activities'=>$checklist->activities
        ]);
    }
    public function saveActivitiesByChecklist(CheckList $checklist,Request $request){
        $activities = $request->all(); 
        CheckList::saveActivities($checklist,$activities);
        return $this->success([
            'msg'=>'Actividades creadas correctamente.'
        ]);
    }

    public function getSegments(CheckList $checklist){
        $response = Checklist::getSegments($checklist);
        return $this->success($response);
    }
}
