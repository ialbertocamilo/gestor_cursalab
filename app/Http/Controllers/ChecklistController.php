<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Taxonomy;
use App\Models\CheckList;
use App\Models\Criterion;
use Illuminate\Http\Request;
use App\Models\CheckListItem;
use App\Http\Requests\ChecklistStoreRequest;
use App\Http\Resources\ChecklistSearchResource;

class ChecklistController extends Controller
{
    public function getFormSelects(){
        $checklist_default_configuration = get_current_workspace()->checklist_configuration;
        $qualification_types = Taxonomy::getDataForSelect('system', 'qualification-type');
        $types_checklist = Taxonomy::getDataForSelect('checklist', 'type_checklist');
        $is_checklist_premium = boolval(get_current_workspace()->functionalities()->where('code','checklist-premium')->first());

        $criteria = [];
        if(count($checklist_default_configuration->managers_criteria)){
            $criteria = Criterion::select('id','name')->whereIn('id',$checklist_default_configuration->managers_criteria)->get();
        }
        unset($checklist_default_configuration->managers_criteria);
        $data = compact(
            'checklist_default_configuration','qualification_types','criteria','types_checklist','is_checklist_premium'
        );
        return $this->success($data);
    }
    public function editChecklist(CheckList $checklist){
        $checklist->load('modality:id,name,code,extra_attributes');
        $checklist->load('type:id,name,code,extra_attributes');
        $checklist->load('course:id,name');
        $checklist->evaluation_types  = isset($checklist->extra_attributes['evaluation_types_id']) ? Taxonomy::select('id','name','code','extra_attributes')
                                                ->whereIn('id',$checklist->extra_attributes['evaluation_types_id'])
                                                ->get() : [];
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
 
    public function listChecklists(Request $request){
        $paginatedChecklist = CheckList::listChecklists($request->all());
        ChecklistSearchResource::collection($paginatedChecklist);
        return $this->success($paginatedChecklist);
    }

    public function formSelectsActivities(CheckList $checklist){
        $data = CheckListItem::formSelectsActivities($checklist);
        return $this->success($data);
    }
    public function listActivitiesByChecklist(CheckList $checklist){
        $checklist->load('activities','activities.checklist_response:id,name,code','activities.custom_options:id,group,type,name,code');
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
    public function saveActivityByChecklist(CheckList $checklist,Request $request){
        $activity = $request->get('activity'); 
        $data = CheckListItem::saveActivity($checklist,$activity);
        $data['msg'] = 'Actividad actualizada correctamente.';
        return $this->success($data);
    }
    public function deleteActivity(CheckList $checklist,CheckListItem $activity){
        $activity->delete();
        return $this->success(['msg'=>'Actividad eliminada correctamente.']);
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

    public function verifyNextStep(CheckList $checklist){
        $next_step = CheckList::nextStep($checklist);
        return $this->success([
            'next_step' => $next_step,
            'checklist' => $checklist
        ]);
    }
    
    public function status(CheckList $checklist, Request $request){
        $checklist->update(['active' => !$checklist->active]);
        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function searchCourses(Request $request){
        $courses = Checklist::searchCourses($request);
        return $this->success(['courses'=>$courses]);
    }

    public function uploadMassive(Checklist $checklist,Request $request){
        $activities = Checklist::uploadMassive($checklist,$request);
        return $this->success(['activities'=>$activities]);
    }

    public function listAreas(){
        $areas = Taxonomy::getDataForSelectWorkspace('checklist','areas');
        return $this->success(['areas'=>$areas]);
    }

    public function saveArea(Request $request){
        CheckListItem::saveArea($request->all());
        return $this->success(['msg' => 'Área creada correctamente.']);
    }

    public function editArea(Request $request){
        CheckListItem::editArea($request->all());
        return $this->success(['msg' => 'Área actualizada correctamente.']);
    }
    public function chengePositionActivities(Checklist $checklist,Request $request){
        CheckListItem::chengePositionActivities($checklist,$request->get('activities'));
        return $this->success(['msg' => 'Posición actualizado correctamente.']);
    }
    public function activitiesByArea(Checklist $checklist){
        $data = CheckListItem::groupByAreas($checklist);
        return $this->success($data);
    }
    public function saveTematica(Checklist $checklist,Request $request){
        $area_id = $request->get('area_id');
        $workspace = get_current_workspace();
        $data = CheckListItem::saveTematica($checklist->id,$area_id,$workspace);
        return $this->success(['msg'=>'Se creó temática correctamente']);
    }

    public function editTematica(Checklist $checklist,Request $request){
        $tematica = $request->get('tematica');
        $data = CheckListItem::editTematica($tematica);
        return $this->success(['msg'=>'Se edito la temática correctamente']);
    }

    public function changeAgrupation(Checklist $checklist){
        CheckListItem::changeAgrupation($checklist);
        return $this->success(['msg'=>'Se cambió el tipo de agrupación']);
    }
    public function deleteTematica(Checklist $checklist,Taxonomy $taxonomy){
        CheckListItem::deleteTematica($taxonomy);
        return $this->success(['msg'=>'Se elimino la temática correctamente.']);
    }
}
