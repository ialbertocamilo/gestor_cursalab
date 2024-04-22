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
        return $this->success([
            'checklist'=>$checklist
        ]);
    }
    public function storeChecklist(ChecklistStoreRequest $request){
        $data = $request->validated();
        CheckList::storeRequest($data);
        return $this->success([
            'msg'=>'checklist creado correctamente.'
        ]);
    }
    public function updateChecklist(ChecklistStoreRequest $request,CheckList $checklist){
        $data = $request->validated();
        CheckList::storeRequest($data,$checklist);
        return $this->success([
            'msg'=>'checklist actualizado correctamente.'
        ]);
    }
    
}
