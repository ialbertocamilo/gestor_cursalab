<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistAudit extends BaseModel
{
    protected $table = 'checklist_audits';

    protected $fillable = [
        'id',
        'identifier_request',
        'signature_supervisor',
        'qualification_id',
        'photo',
        'checklist_id',
        'checklist_activity_id',
        'auditor_id',
        'model_type',
        'model_id',
        'date_audit'
    ];

    protected $casts = [
        'comments'=>'array',
        'date_audit' => 'timestamp',
    ];


    protected function saveActivitiesAudits($checklist,$data){
        $user = auth()->user();
        $checklist->load('type:id,name,code');
        $checklist->load('modality:id,name,code');
        $activities = $data['activities'];
        $criterion_value_user_entity = null;
        if($checklist->modality->code == 'qualify_entity'){
            $workspace_entity_criteria = Workspace::select('checklist_configuration')
                ->where('id', $user->subworkspace->parent->id)
                ->first()?->checklist_configuration?->entities_criteria;
            $criterion_value_user_entity = $user->criterion_values->whereIn('criterion_id', $workspace_entity_criteria)->first();
        }
        $path_signature = '';
        if($checklist->extra_attributes['required_signature_supervisor']){
            $str_random = Str::random(5);
            $name_image = $user->subworkspace_id . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random.'.png';
            // Ruta donde se guardará la imagen en el servidor
            $path_signature = 'checklist-signatures/'.$checklist->id.'/'.$name_image;
            Media::uploadMediaBase64(name:'', path:$path_signature, base64:$data['signature'],save_in_media:false,status:'private');
        }

        foreach ($activities as $activity) {
            $model_type =  ($checklist->modality->code == 'qualify_entity') ? 'App\\Models\\CriterionValue'  : 'App\\Models\\User';
            $model_id = ($checklist->modality->code == 'qualify_entity') ? $criterion_value_user_entity->id : $user->id;
            $activity = Media::requestUploadFile(data:$activity,field:'photo',return_media:true);
            $_checklist_audit = [
                'identifier_request'=> $data['identifier_request'],
                'qualification_id'=> $activity['qualification_id'],
                'signature_supervisor' => $path_signature,
                'photo'=> $activity['photo']?->file,
                'checklist_id'=>$checklist->id,
                'checklist_activity_id'=>$activity['id'],
                'auditor_id' => $user->id,
                'model_type' => $model_type,
                'model_id' => $model_id,
                'date_audit' => now(),
            ];
            ChecklistAudit::insert($_checklist_audit);
        } 
    }
}