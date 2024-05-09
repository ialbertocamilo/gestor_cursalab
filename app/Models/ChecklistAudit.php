<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function getDateAuditAttribute($value)
    {
        $date = Carbon::parse($value);
        return $date->format('d-m-Y');
    }
    public function model()
    {
        return $this->morphTo();
    }
    public function activity()
    {
        return $this->belongsTo(CheckListItem::class, 'checklist_activity_id');
    }
    public function qualification()
    {
        return $this->belongsTo(Taxonomy::class, 'qualification_id');
    }
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
            $photo = '';
            if($activity['file_photo']){
                // $activity = Media::requestUploadFile(data:$activity,field:'photo',return_media:true);
                $str_random = Str::random(5);
                $name_image = $activity['id'] . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random.'.png';
                $photo = 'checklist-photos/'.$checklist->id.'/'.$name_image;
                Media::uploadMediaBase64(name:'', path:$photo, base64:$activity['file_photo'],save_in_media:false,status:'private');
            }
            $_checklist_audit = [
                'identifier_request'=> $data['identifier_request'],
                'qualification_id'=> $activity['qualification_id'],
                'signature_supervisor' => $path_signature,
                'photo'=> $photo,
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

    protected function listProgress($checklist){
        $user = auth()->user();
        $checklist->loadMissing('modality:id,name,icon,alias');
        $audits = ChecklistAudit::select('id','qualification_id','photo','checklist_activity_id','model_type','model_id')
                                ->where('auditor_id',$user->id)
                                ->where('checklist_id',$checklist->id)
                                ->with([
                                    'activity:id,activity,extra_attributes,checklist_response_id',
                                    'activity.checklist_response:id,code',
                                    'model:id,value_text',
                                    'qualification:id,name',
                                ])
                                ->get();
        $entity_name = '';
        $entity_icon = '';
        $activities = [];
        foreach ($audits as $index => $audit) {
            if(!$entity_name){
                $entity_name =  $audit->model->value_text;
            }
            if(!$entity_icon){
                $entity_icon =  $audit->model_type == 'App\\Models\\CriterionValue' ? 'store' : 'user';
            }
            $activities[] = [
                "id" => $audit->checklist_activity_id,
                'name'=>'Actividad '.($index+1),
                "description" => $audit->activity?->activity,
                'can_comment'=> $audit->activity->extra_attributes['comment_activity'],
                'photo' => get_media_url($audit->photo),
                'type_system_calification'=>$audit->activity->checklist_response->code,
                'qualification' => $audit->qualification,
            ];
        }
        return [
            "checklist"=>[
                "id" => $checklist->id,
                "title"  => $checklist->title,
                "entity" => [
                    "name" => $entity_name,
                    "icon" => $entity_icon
                ],
                'activities' => $activities
            ]
        ];
    }
}