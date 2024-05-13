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
        'checklist_id',
        'action_plan',
        'auditor_id',
        'model_type',
        'model_id',
        'date_audit',
    ];

    protected $casts = [
        'comments'=>'array',
        'date_audit' => 'timestamp',
    ];

    public function audit_activities()
    {
        return $this->hasMany(CheckListActivityAudit::class, 'checklist_audit_id');
    }
    public function getDateAuditAttribute($value)
    {
        $date = Carbon::parse($value);
        return $date->format('d-m-Y');
    }
    public function model()
    {
        return $this->morphTo();
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

        $model_type =  ($checklist->modality->code == 'qualify_entity') ? 'App\\Models\\CriterionValue'  : 'App\\Models\\User';
        $model_id = ($checklist->modality->code == 'qualify_entity') ? $criterion_value_user_entity->id : $user->id;
        $date_audit = now();
        $checklist_audit = ChecklistAudit::create([
            'identifier_request' => $data['identifier_request'],
            'signature_supervisor' => $path_signature,
            'checklist_id' => $checklist->id,
            'model_type' => $model_type,
            'model_id' => $model_id,
            'action_plan' => $data['action_plan'] ?? null,
            'auditor_id' => $user->id,
            'date_audit' => $date_audit,
        ]);

        foreach ($activities as $activity) {
            $photo = '';
            if($activity['file_photo']){
                // $activity = Media::requestUploadFile(data:$activity,field:'photo',return_media:true);
                $str_random = Str::random(5);
                $name_image = $activity['id'] . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random.'.png';
                $photo = 'checklist-photos/'.$checklist->id.'/'.$name_image;
                Media::uploadMediaBase64(name:'', path:$photo, base64:$activity['file_photo'],save_in_media:false,status:'private');
            }
            $_checklist_audit = [
                'checklist_audit_id' => $checklist_audit->id,
                'identifier_request'=> $data['identifier_request'],
                'qualification_id'=> $activity['qualification_id'],
                'photo'=> $photo,
                'checklist_id'=>$checklist->id,
                'checklist_activity_id'=>$activity['id'],
                'auditor_id' => $user->id,
                'date_audit' => $date_audit,
            ];
            ChecklistActivityAudit::insert($_checklist_audit);
        } 
    }

    protected function listProgress($checklist){
        $user = auth()->user();
        $checklist->loadMissing('modality:id,name,icon,alias');
        $checklist_audit = ChecklistAudit::select('id','model_type','model_id')
                                ->where('auditor_id',$user->id)
                                ->where('checklist_id',$checklist->id)
                                ->when($checklist->extra_attributes['replicate'],function($q){
                                    $now = now()->format('Y-m-d');
                                    $q->whereDate('date_audit','=',$now);
                                })
                                ->with([
                                    'audit_activities.activity:id,activity,extra_attributes,checklist_response_id',
                                    'audit_activities.activity.checklist_response:id,code',
                                    'audit_activities.qualification:id,name',
                                    'model:id,value_text',
                                ])
                                ->first();
        $entity_name =  $checklist_audit->model?->value_text;
        $entity_icon = $checklist_audit->model_type == 'App\\Models\\CriterionValue' ? 'store' : 'user';
        $activities = [];
        foreach ($checklist_audit->audit_activities as $index => $audit_activity) {
            $activities[] = [
                "id" => $audit_activity->checklist_activity_id,
                'name'=>'Actividad '.($index+1),
                "description" => $audit_activity->activity?->activity,
                'can_comment'=> $audit_activity->activity->extra_attributes['comment_activity'],
                'photo' => get_media_url($audit_activity->photo),
                'type_system_calification'=>$audit_activity->activity->checklist_response->code,
                'qualification' => $audit_activity->qualification,
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