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
        'signature_supervised',
        'checklist_id',
        'percent_progress',
        'checklist_finished',
        'activities_assigned',
        'activities_reviewved',
        'action_plan',
        'auditor_id',
        'model_type',
        'model_id',
        'date_audit',
        'starts_at',
        'finishes_at'
    ];

    protected $casts = [
        'comments'=>'array',
        'date_audit' => 'timestamp',
    ];

    public function audit_activities()
    {
        return $this->hasMany(ChecklistActivityAudit::class, 'checklist_audit_id');
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
    protected function saveDataDummy(){
        $users = User::whereIn('id',[59386,59387,86190])->get();
        $checklist = CheckList::where('id',994)->with(['type','modality','activities'])->first();
        $qualification_ids = $checklist->extra_attributes['evaluation_types_id'];
        $activies = $checklist->activities;
        foreach ($activies as $activity) {
            foreach ($users as $user) {
                $number_rand = rand(0,count($qualification_ids)-1);
                $data['activity_id'] = $activity->id;
                $data['qualification_id'] = $qualification_ids[$number_rand];
                $data['entity_id'] = 180853;
                $request = [
                    'entity_id' => 180853
                ];
                self::saveActivity($checklist,$data,'qualification',(object) $request ,$user);
            }
        }
    }
    protected function saveActivitiesAudits($checklist,$data,$request){
        $user = auth()->user();
        $checklist->load('type:id,name,code');
        $checklist->load('modality:id,name,code');
        //FINALIZAR Y SUBIR FIRMA
        $path_signature = '';
        if($checklist->extra_attributes['required_signature_supervisor']){
            $str_random = Str::random(5);
            $name_image = $user->subworkspace_id . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random.'.png';
            // Ruta donde se guardará la imagen en el servidor
            $path_signature = 'checklist-signatures/'.$checklist->id.'/'.$name_image;
            Media::uploadMediaBase64(name:'', path:$path_signature, base64:$data['signature'],save_in_media:false,status:'private');
        }

        $checklist_audit = collect();
        if ($checklist->modality->code != 'qualify_user') {
            if(isset($data['entity_id'])){
                $model_id = $data['entity_id'];
            }else{
                $criterion_value_user_entity = ChecklistAudit::getCriterionValueUserEntity($checklist, $user);
                $model_id = $checklist->modality->code === 'qualify_entity' ? $criterion_value_user_entity->id : $user->id;
            }
            $model_type = $checklist->modality->code === 'qualify_entity' ? CriterionValue::class : User::class;
            $checklist_audit =  ChecklistAudit::getCurrentChecklistAudit($checklist,$model_type,$model_id,$user);
            $checklist_audit->signature_supervisor = $path_signature;
            $checklist_audit->checklist_finished = true;
            $checklist_audit->finishes_at = now()->format('Y-m-d H:i:s');
            $checklist_audit->save();
        }else{
            $model_id = $data['user_ids'];
            $model_type = User::class;
            $checklist_audit =  ChecklistAudit::getCurrentChecklistAudit($checklist,$model_type,$model_id,$user);
            foreach ($checklist_audit as $key => $audit) {
                $audit->signature_supervisor = $path_signature;
                $audit->checklist_finished = true;
                $audit->finishes_at = now()->format('Y-m-d H:i:s');
                $audit->save();
            }
        }
        return [
            'message' => 'Checklist finalizado.',
        ];
    }
    protected function saveActivity(Checklist $checklist, array $data,$action_request,$request,$_user=null): array
    {
        $user = $_user ?? auth()->user();
        $checklist->load(['type:id,name,code', 'modality:id,name,code','activities:id,checklist_id']);
        $criterionValueUserEntity = $this->getCriterionValueUserEntity($checklist, $user);

        $modelType = $checklist->modality->code === 'qualify_entity' ? CriterionValue::class : User::class;
        $dateAudit = now();
        if($checklist->modality->code =='qualify_entity' && $request->entity_id || $data['entity_id']){
            $modelId = $request->entity_id ?? $data['entity_id'];
        }else{
            $modelId = $checklist->modality->code === 'qualify_entity' ? $criterionValueUserEntity->id : $user->id;
        }
        
        $checklistActivityAuditToUpdate = [];
        $checklistActivityAuditToCreate = [];
        $assigned = 0;
        $reviewved = 0;
        $percent_progress = 0;
        $photos = [];
        $comments = [];
        $qualification_id = null;
        if ($checklist->modality->code === 'qualify_user') {
            // if(isset($data['users_id'])){
                foreach ($data['user_ids'] as $userId) {
                    $this->processAudit(
                        $action_request,$checklist, $data, $user, $modelType, $userId, 
                        $dateAudit, $checklistActivityAuditToCreate, $checklistActivityAuditToUpdate,
                        $assigned,$reviewved,$percent_progress,$photos,$qualification_id,$comments
                    );
                }
            // }
        } else {
            $this->processAudit($action_request,
                $checklist, $data, $user, $modelType, 
                $modelId, $dateAudit, $checklistActivityAuditToCreate, $checklistActivityAuditToUpdate,
                $assigned,$reviewved,$percent_progress,$photos,$qualification_id,$comments
            );
        }
        ChecklistActivityAudit::insertUpdateMassive($checklistActivityAuditToCreate,'insert');
        ChecklistActivityAudit::insertUpdateMassive($checklistActivityAuditToUpdate,'update');
        $list_photos = [];
        if($photos && count($photos) > 0){
            foreach ($photos as $photo) {
                $photo['url'] = reportsSignedUrl($photo['url']);
                $list_photos[] = $photo; 
            }
        }

        if(!is_array($comments)){
            $comments = [];
        }
        return [
            'message' => 'Actividad actualizada.',
            'percent_progress' => $percent_progress,
            'activities_assigned' => $assigned,
            'activities_reviewved' =>  $reviewved,
            'list_photos' => $list_photos,
            'qualification_id' => $qualification_id,
            'principal_comment' =>  count($comments) > 0  ? collect($comments)?->where('user_id',$user->id)->first() : null,
            'comments' =>  count($comments) > 0  ? collect($comments)?->where('user_id',$user->id)->values() : [],
        ];
    }

    protected function getCriterionValueUserEntity(Checklist $checklist, User $user): ?CriterionValue
    {
        if ($checklist->modality->code !== 'qualify_entity') {
            return null;
        }

        $workspaceEntityCriteria = Workspace::select('checklist_configuration')
            ->where('id', $user->subworkspace->parent->id)
            ->first()?->checklist_configuration?->entities_criteria;

        return $user->criterion_values->whereIn('criterion_id', $workspaceEntityCriteria)->first();
    }

    // protected function processAudit(
    //     $action_request,Checklist $checklist, array $data, User $user, string $modelType, int $modelId, \Illuminate\Support\Carbon $dateAudit, 
    //     array &$checklistActivityAuditToCreate, array &$checklistActivityAuditToUpdate,
    //     &$assigned,&$reviewved,&$percent_progress,&$photos,&$qualification_id
    // ): void
    // {
    //     $dateAudit = $dateAudit->format('Y-m-d H:i:s');
    //     $checklist_audit =  self::getCurrentChecklistAudit($checklist,$modelType,$modelId,$user,true);
    //     $activities_reviewved = $checklist_audit?->audit_activities ? count($checklist_audit?->audit_activities) : 0;
    //     if(!$checklist_audit){
    //         $checklist_audit = ChecklistAudit::create([
    //             'checklist_id' => $checklist->id,
    //             'auditor_id' => $user->id,
    //             'date_audit' => $dateAudit,
    //             'model_type' => $modelType,
    //             'model_id' => $modelId,
    //             'starts_at' => $dateAudit
    //         ]);
    //     }
    //     $activities_assigned = count($checklist->activities);
    //     $checklistActivityAudit = ChecklistActivityAudit::where([
    //         'checklist_audit_id' => $checklist_audit->id,
    //         'checklist_id' => $checklist->id,
    //         'checklist_activity_id' => $data['activity_id'],
    //     ])->first();
        
    //     if ($checklistActivityAudit) {
    //         $checklistActivityAudit = $checklistActivityAudit->toArray();
    //         $checklist_activity_update = [
    //             'id' => $checklistActivityAudit['id'],
    //             'date_audit' => $dateAudit,
    //         ];
    //         switch ($action_request) {
    //             case 'qualification':
    //                 $photos = $checklistActivityAudit['photo'];
    //                 $historicQualification = [
    //                     'qualification_id' => $data['qualification_id'],
    //                     'date_audit' => $dateAudit
    //                 ];
    //                 /*qualification to response type write_option*/
    //                 if(is_string($data['qualification_response'])){
    //                     $qualification_response = Taxonomy::updateOrCreate(
    //                         ['id' => $data['qualification_id'],'group'=>'checklist','type' => 'response_write_user'],
    //                         [
    //                             'group' => 'checklist',
    //                             'type' => 'response_write_user',
    //                             "name" => $data['qualification_response'],
    //                         ]
    //                     );
    //                     $data['qualification_id'] = $qualification_response->id;
    //                 }
    //                 $qualification_id = $data['qualification_id'];
    //                 $checklist_activity_update['qualification_id'] = $data['qualification_id'];
    //                 $checklist_activity_update['historic_qualification'][] = $historicQualification;
    //                 $checklist_activity_update['historic_qualification'] = json_encode($checklistActivityAudit['historic_qualification']);
    //             break;
    //             case 'comments':
    //                 $photos = $checklistActivityAudit['photo'];
    //                 $qualification_id = $checklistActivityAudit['qualification_id'];

    //                 $historicComments = [
    //                     "user_id"=> $user->id,
    //                     "comment" => $data['comment'],
    //                     "date_time" => $dateAudit
    //                 ];
    //                 if(is_array($checklistActivityAudit['comments'])){
    //                     $checklist_activity_update['comments'][] = $historicComments;
    //                 }else{
    //                     $checklist_activity_update['comments'] = [];
    //                     $checklist_activity_update['comments'][] = $historicComments;
    //                 }
    //                 $checklist_activity_update['comments'] = json_encode($checklist_activity_update['comments']);
    //             break;
    //             case 'photo':
    //                 $checklist_activity_update['photo'] = $checklistActivityAudit['photo'];
    //                 $qualification_id = $checklistActivityAudit['qualification_id'];

    //                 if(isset($data['action']) && $data['action'] == 'insert'){
    //                     if(isset($data['file_photo'])){
    //                         $str_random = Str::random(5);
    //                         $name_image = $data['activity_id'] . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random.'.png';
    //                         $photo = 'checklist-photos/'.$checklist->id.'/'.$name_image;
    //                         Media::uploadMediaBase64(name:'', path:$photo, base64:$data['file_photo'],save_in_media:false,status:'private');
    //                         if(is_array($checklist_activity_update['photo'])){
    //                             $checklist_activity_update['photo'][] = [
    //                                 'url'=>$photo,
    //                                 'datetime' => $dateAudit
    //                             ];
    //                         }else{
    //                             $checklist_activity_update['photo'] = [];
    //                             $checklist_activity_update['photo'][] = [
    //                                 'url'=>$photo,
    //                                 'datetime' => $dateAudit
    //                             ];
    //                         }
    //                     }
    //                 }
    //                 if(isset($data['action']) && $data['action'] == 'delete'){
    //                     if (isset($data['photo'])) {
    //                         $photoIndex = $data['photo'];
    //                         if (isset($checklist_activity_update['photo'][$photoIndex])) {
    //                             unset($checklist_activity_update['photo'][$photoIndex]);
    //                         }
    //                         // Reindexar el array para evitar problemas con índices no consecutivos
    //                         $checklist_activity_update['photo'] = isset($checklist_activity_update['photo'])
    //                                                                 ? array_values($checklist_activity_update['photo']) 
    //                                                                 : [];
    //                     }
    //                 }
    //                 $photos = $checklist_activity_update['photo'];
    //                 $checklist_activity_update['photo'] = json_encode($checklist_activity_update['photo']);
    //             break;
    //         }
    //         $checklistActivityAuditToUpdate[] = $checklist_activity_update;
    //     } else {
    //         $photos = [];
    //         $comment = [];
    //         if(isset($data['comment'])){
    //             $comment[] = [
    //                 "user_id"=> $user->id,
    //                 "comment" => $data['comment'],
    //                 "date_time" => $dateAudit
    //             ];
    //         }
    //         if(isset($data['file_photo'])){
    //             // $activity = Media::requestUploadFile(data:$activity,field:'photo',return_media:true);
    //             $str_random = Str::random(5);
    //             $name_image = $data['activity_id'] . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random.'.png';
    //             $photo = 'checklist-photos/'.$checklist->id.'/'.$name_image;
    //             Media::uploadMediaBase64(name:'', path:$photo, base64:$data['file_photo'],save_in_media:false,status:'private');
    //             $photos[] = [
    //                 'url'=>$photo,
    //                 'datetime' => $dateAudit
    //             ];
    //         }
    //         /*qualification to response type write_option*/
    //         if(isset($data['qualification_response']) && is_string($data['qualification_response'])){
    //             $qualification_response = Taxonomy::updateOrCreate(
    //                 ['id' => $data['qualification_id'],'group'=>'checklist','type' => 'response_write_user'],
    //                 [
    //                     'group' => 'checklist',
    //                     'type' => 'response_write_user',
    //                     "name" => $data['qualification_response'],
    //                 ]
    //             );
    //             $data['qualification_id'] = $qualification_response->id;
    //         }
    //         $activities_reviewved = $activities_reviewved + 1;
    //         $checklistActivityAuditToCreate[] = [
    //             'checklist_audit_id' => $checklist_audit->id,
    //             'qualification_id' => $data['qualification_id'] ?? null,
    //             'qualification_id' => $data['qualification_id'] ?? null,
    //             'photo' => json_encode($photos),
    //             'checklist_id' => $checklist->id,
    //             'checklist_activity_id' => $data['activity_id'],
    //             'auditor_id' => $user->id,
    //             'date_audit' => $dateAudit,
    //             'historic_qualification' => isset($data['qualification_id']) ? json_encode([[
    //                 'qualification_id' => $data['qualification_id'],
    //                 'date_audit' => $dateAudit
    //             ]]) : '[]',
    //             'comments' => json_encode($comment)
    //         ];
    //     }
    //     //update progress
        
    //     $checklist_audit->activities_assigned  = $activities_assigned;
    //     $checklist_audit->activities_reviewved  = $activities_reviewved;
    //     $checklist_audit->percent_progress  = round(($activities_reviewved/$activities_assigned),2) * 100;
    //     // $checklist_audit->checklist_finished  = ($activities_assigned == $activities_reviewved);
    //     // dd($checklist_audit->percent_progress,$activities_reviewved/$activities_assigned,$activities_assigned,$activities_reviewved);
    //     $checklist_audit->save();
    //     $assigned = $checklist_audit->activities_assigned;
    //     $reviewved = $checklist_audit->activities_reviewved;
    //     $percent_progress = $checklist_audit->percent_progress;
    // }
    protected function processAudit(
        $action_request, Checklist $checklist, array $data, User $user, string $modelType, int $modelId, \Illuminate\Support\Carbon $dateAudit,
        array &$checklistActivityAuditToCreate, array &$checklistActivityAuditToUpdate,
        &$assigned, &$reviewved, &$percent_progress, &$photos, &$qualification_id,&$comments
    ): void
    {
        $dateAuditFormatted = $dateAudit->format('Y-m-d H:i:s');
        $checklist_audit = self::getCurrentChecklistAudit($checklist, $modelType, $modelId, $user, true);
        $activities_reviewved = $checklist_audit?->audit_activities ? count($checklist_audit->audit_activities) : 0;
        // Create a new checklist audit if it doesn't exist
        if (!$checklist_audit) {
            $checklist_audit = ChecklistAudit::create([
                'checklist_id' => $checklist->id,
                'auditor_id' => $user->id,
                'date_audit' => $dateAuditFormatted,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'starts_at' => $dateAuditFormatted
            ]);
        }
        $activities_assigned = count($checklist->activities);
        // Fetch or initialize the ChecklistActivityAudit
        $checklistActivityAudit = ChecklistActivityAudit::where([
            'checklist_audit_id' => $checklist_audit->id,
            'checklist_id' => $checklist->id,
            'checklist_activity_id' => $data['activity_id'],
        ])->first();
        if ($checklistActivityAudit) {
            $checklistActivityAudit = $checklistActivityAudit->toArray();
            $checklist_activity_update = [
                'id' => $checklistActivityAudit['id'],
                'date_audit' => $dateAuditFormatted,
            ];
            switch ($action_request) {
                case 'qualification':
                    $this->handleQualification($data, $checklistActivityAudit, $checklist_activity_update, $dateAuditFormatted, $photos, $qualification_id,$comments);
                    break;
                case 'comments':
                    $this->handleComments($data, $checklistActivityAudit, $checklist_activity_update, $user, $dateAuditFormatted, $photos, $qualification_id,$comments);
                    break;
                case 'photo':
                    $this->handlePhoto($data, $checklistActivityAudit, $checklist_activity_update, $checklist, $dateAuditFormatted, $photos, $qualification_id,$comments);
                    break;
            }
    
            $checklistActivityAuditToUpdate[] = $checklist_activity_update;
        } else {
            $this->handleNewActivity($checklist_audit,$data, $checklist, $user, $dateAuditFormatted, 
                $checklistActivityAuditToCreate, $activities_reviewved, 
                $photos, $qualification_id,$comments
            );
        }
    
        // Update progress
        $checklist_audit->activities_assigned = $activities_assigned;
        $checklist_audit->activities_reviewved = $activities_reviewved;
        $checklist_audit->percent_progress = round(($activities_reviewved / $activities_assigned), 2) * 100;
        $checklist_audit->save();
    
        $assigned = $checklist_audit->activities_assigned;
        $reviewved = $checklist_audit->activities_reviewved;
        $percent_progress = $checklist_audit->percent_progress;
    }
    
    private function handleQualification(array $data, array $checklistActivityAudit, array &$checklist_activity_update, string $dateAuditFormatted, 
    &$photos, &$qualification_id,&$comments)
    {
        $photos = $checklistActivityAudit['photo'];
        $comments = $checklistActivityAudit['comments'];

        $historicQualification = [
            'qualification_id' => $data['qualification_id'],
            'date_audit' => $dateAuditFormatted
        ];
    
        if (isset($data['qualification_response']) && is_string($data['qualification_response'])) {
            $qualification_response = Taxonomy::updateOrCreate(
                ['id' => $data['qualification_id'], 'group' => 'checklist', 'type' => 'response_write_user'],
                [
                    'group' => 'checklist',
                    'type' => 'response_write_user',
                    'name' => $data['qualification_response'],
                ]
            );
            $data['qualification_id'] = $qualification_response->id;
        }
    
        $qualification_id = $data['qualification_id'];
        $checklist_activity_update['qualification_id'] = $data['qualification_id'];
        $checklist_activity_update['historic_qualification'][] = $historicQualification;
        $checklist_activity_update['historic_qualification'] = json_encode($checklistActivityAudit['historic_qualification']);
    }
    // $checklist_activity_update['comments'] = isset($checklistActivityAudit['comments']) ? $checklistActivityAudit['comments'] : [];
        // $historicComments['principal'] = count($checklist_activity_update['comments']) == 0;
        // if (is_array($checklist_activity_update['comments'])) {
        //     $checklist_activity_update['comments'][] = $historicComments;
        // } else {
        //     $checklist_activity_update['comments'] = [];
        //     $checklist_activity_update['comments'][] = $historicComments;
        // }
    private function handleComments(
        array $data, array $checklistActivityAudit, array &$checklist_activity_update, User $user, string $dateAuditFormatted,
         &$photos, &$qualification_id,&$comments
    )
    {
        $photos = $checklistActivityAudit['photo'];
        $qualification_id = $checklistActivityAudit['qualification_id'];
    
        $historicComments = [
            'user_id' => $user->id,
            'name' => $user->fullname,
            'comment' => $data['comment'],
            'date_time' => $dateAuditFormatted
        ];

        if (isset($checklistActivityAudit['comments'])) {
            $commentsCollection = collect($checklistActivityAudit['comments']);
            $userHasComment = $commentsCollection->firstWhere('user_id', $user->id);
            if ($userHasComment) {
                $userHasComment['comment'] = $data['comment'];
                $commentsCollection = $commentsCollection->map(function ($comment) use ($userHasComment) {
                    return $comment['user_id'] === $userHasComment['user_id'] ? $userHasComment : $comment;
                });
            } else {
                $commentsCollection->push($historicComments);
            }
            $checklist_activity_update['comments'] = $commentsCollection->toArray();
        } else {
            $checklist_activity_update['comments'] = [$historicComments];
        }

        $comments = $checklist_activity_update['comments'];
        $checklist_activity_update['comments'] = json_encode($checklist_activity_update['comments']);
    }
    
    private function handlePhoto(array $data, array $checklistActivityAudit, array &$checklist_activity_update, Checklist $checklist, string $dateAuditFormatted,
     &$photos, &$qualification_id,&$comments)
    {
        $checklist_activity_update['photo'] = $checklistActivityAudit['photo'];
        $comments = $checklistActivityAudit['comments'];

        $qualification_id = $checklistActivityAudit['qualification_id'];
    
        if (isset($data['action']) && $data['action'] == 'insert' && isset($data['file_photo'])) {
            $str_random = Str::random(5);
            $name_image = $data['activity_id'] . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random . '.png';
            $photo = 'checklist-photos/' . $checklist->id . '/' . $name_image;
            Media::uploadMediaBase64(name: '', path: $photo, base64: $data['file_photo'], save_in_media: false, status: 'private');
    
            $checklist_activity_update['photo'][] = [
                'url' => $photo,
                'datetime' => $dateAuditFormatted
            ];
        }
    
        if (isset($data['action']) && $data['action'] == 'delete' && isset($data['photo'])) {
            $photoIndex = $data['photo'];
            if (isset($checklist_activity_update['photo'][$photoIndex])) {
                unset($checklist_activity_update['photo'][$photoIndex]);
                $checklist_activity_update['photo'] = array_values($checklist_activity_update['photo']);
            }
        }
    
        $photos = $checklist_activity_update['photo'];
        $checklist_activity_update['photo'] = json_encode($checklist_activity_update['photo']);
    }
    
    private function handleNewActivity(
        $checklist_audit,array $data, Checklist $checklist, User $user, string $dateAuditFormatted, array &$checklistActivityAuditToCreate, 
        int &$activities_reviewved, &$photos, &$qualification_id,&$comments
    )
    {
        $photos = [];
        $comments = [];
    
        if (isset($data['comment'])) {
            $comments[] = [
                'user_id' => $user->id,
                'comment' => $data['comment'],
                'date_time' => $dateAuditFormatted,
                'principal' => true,
            ];
        }
    
        if (isset($data['file_photo'])) {
            $str_random = Str::random(5);
            $name_image = $data['activity_id'] . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random . '.png';
            $photo = 'checklist-photos/' . $checklist->id . '/' . $name_image;
            Media::uploadMediaBase64(name: '', path: $photo, base64: $data['file_photo'], save_in_media: false, status: 'private');
            $photos[] = [
                'url' => $photo,
                'datetime' => $dateAuditFormatted
            ];
        }
    
        if (isset($data['qualification_response']) && is_string($data['qualification_response'])) {
            $qualification_response = Taxonomy::updateOrCreate(
                ['id' => $data['qualification_id'], 'group' => 'checklist', 'type' => 'response_write_user'],
                [
                    'group' => 'checklist',
                    'type' => 'response_write_user',
                    'name' => $data['qualification_response'],
                ]
            );
            $data['qualification_id'] = $qualification_response->id;
        }
    
        $activities_reviewved += 1;
        $qualification_id = isset($data['qualification_id']) ? $data['qualification_id'] : null;
        $checklistActivityAuditToCreate[] = [
            'checklist_audit_id' => $checklist_audit->id,
            'qualification_id' => $data['qualification_id'] ?? null,
            'photo' => json_encode($photos),
            'checklist_id' => $checklist->id,
            'checklist_activity_id' => $data['activity_id'],
            'auditor_id' => $user->id,
            'date_audit' => $dateAuditFormatted,
            'historic_qualification' => isset($data['qualification_id']) ? json_encode([[
                'qualification_id' => $data['qualification_id'],
                'date_audit' => $dateAuditFormatted
            ]]) : '[]',
            'comments' => json_encode($comments)
        ];
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
    protected function saveActionPlan($checklist,$data,$request){
        $user = auth()->user();
        if ($checklist->modality->code != 'qualify_user') {
            if($request->entity_id){
                $model_id = $request->entity_id;
            }else{
                $criterion_value_user_entity = ChecklistAudit::getCriterionValueUserEntity($checklist, $user);
                $model_id = $checklist->modality->code === 'qualify_entity' ? $criterion_value_user_entity->id : $user->id;
            }

            $model_type = $checklist->modality->code === 'qualify_entity' ? CriterionValue::class : User::class;
            $checklist_audit =  ChecklistAudit::getCurrentChecklistAudit($checklist,$model_type,$model_id,$user,false,true);
            $checklist_audit->action_plan=$data['action_plan'];
            $checklist_audit->save();
        }else{
            $model_ids = $data['user_ids'];
            $model_type = User::class;
            foreach ($model_ids as $key => $model_id) {
                $checklist_audit =  ChecklistAudit::getCurrentChecklistAudit($checklist,$model_type,$model_id,$user,false,true);
                $checklist_audit->action_plan=$data['action_plan'];
                $checklist_audit->save();
            }
        }
        return [
            'message' => 'Plan de acción guardado correctamente.'
        ];
    }
    protected function saveSignatureSupervised($checklist,$data,$request){
        $user = auth()->user();
        if ($checklist->modality->code != 'qualify_user') {
            if($request->entity_id){
                $model_id = $request->entity_id;
            }else{
                $criterion_value_user_entity = ChecklistAudit::getCriterionValueUserEntity($checklist, $user);
                $model_id = $checklist->modality->code === 'qualify_entity' ? $criterion_value_user_entity->id : $user->id;
            }
            $model_type = $checklist->modality->code === 'qualify_entity' ? CriterionValue::class : User::class;
        }else{
            $model_id = $request->user_id;
            $model_type = User::class;
        }
        $checklist_audit =  ChecklistAudit::getCurrentChecklistAudit($checklist,$model_type,$model_id,$user,false,true);
        $str_random = Str::random(5);
        $name_image = $user->subworkspace_id . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random.'_supervised.png';
        // Ruta donde se guardará la imagen en el servidor
        $path_signature = 'checklist-signatures/'.$checklist->id.'/'.$name_image;
        Media::uploadMediaBase64(name:'', path:$path_signature, base64:$data['signature'],save_in_media:false,status:'private');
        $checklist_audit->signature_supervised = json_encode([
            'path_signature' => $path_signature,
            'supervised_id' => isset($data['user_id']) ? $data['user_id'] : $request->user_id ?? $request->supervised_id
        ]);
        $checklist_audit->save();
        return [
            'message' => 'Plan de acción guardado correctamente.'
        ];
    }
    protected function getCurrentChecklistAudit($checklist,$model_type,$model_id,$user,$with_audit_activities=false,$last_finished=false,$filters=[]){
        //Falta añadir cuando es replicable el checklist
        $_query = ChecklistAudit::where('checklist_id',$checklist->id)
                                ->where('model_type',$model_type)
                                // ->where('model_id',$model_id)
                                ->when($model_id, function($query) use($model_id){
                                    if (is_array($model_id)) {
                                        $query->whereIn('model_id', $model_id);
                                    } else {
                                        $query->where('model_id', $model_id);
                                    }
                                })
                                ->when($checklist->extra_attributes['view_360'], function($q) use($user){
                                    $q->where('auditor_id',$user->id);
                                })
                                ->when($with_audit_activities, function($q){
                                    $q->with('audit_activities:id,checklist_audit_id,checklist_activity_id,qualification_id,photo,comments');
                                })
                                ->when($model_id, function($query) use($model_id){
                                    if (is_array($model_id)) {
                                        $query->get();
                                    } else {
                                        $query->first('model_id', $model_id);
                                    }
                                })
                                ->where(function ($query) use ($last_finished) {
                                    if($last_finished){
                                        $query->whereNotNull('finishes_at')->orderBy('finishes_at','desc');
                                    }else{
                                        $query->whereNull('finishes_at');
                                    }
                                });
        if(count($filters)){
            $_query = addExtraFilters($_query,$filters);
        }                   
        return is_array($model_id) ? $_query->get() : $_query->first();
    }

    
}