<?php

namespace App\Models;

use stdClass;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\MeetingAppResource;
use App\Http\Resources\TopicInPersonAppResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseInPerson extends Model
{
    use HasFactory;

    protected function listCoursesByUser($request){
        $user = auth()->user();
        $request->user = $user;
        $type_session = $request->type_session;
        $sessions_in_person = [];
        $sessions_live = [];
        $sessions_course_live = [];
        if ($type_session) {
            switch ($type_session) {
                case 'in-person':
                    $sessions_in_person = CourseInPerson::listCoursesByTypeCode($request,'in-person');
                    break;
                case 'live':
                    $request->include_topic_meetings = false;
                    $sessions_live = Meeting::getListMeetingsByUser($request,'in-array');
                    break;
                case 'online':
                    $sessions_course_live  = CourseInPerson::listCoursesByTypeCode($request,'virtual');
                    break;
                case 'all':
                    $sessions_in_person = CourseInPerson::listCoursesByTypeCode($request,'in-person');
                    $sessions_live = Meeting::getListMeetingsByUser($request,'in-array');
                    $sessions_course_live  = CourseInPerson::listCoursesByTypeCode($request,'virtual');
                    break;
            }
        }else{
            $sessions_in_person = CourseInPerson::listCoursesByTypeCode($request,'in-person');
            $sessions_live = Meeting::getListMeetingsByUser($request,'in-array');
            $sessions_course_live  = CourseInPerson::listCoursesByTypeCode($request,'virtual');
        }
        return compact('sessions_in_person','sessions_live','sessions_course_live');
    }
    protected function listCoursesByTypeCode($request,$modality_code){
        $code = $request->code;
        $user = $request->user;
        
        $assigned_courses = $user->getCurrentCourses(withRelations: 'soft',only_ids_courses:true,modality_code:$modality_code);
        $operator = '';
        switch ($code) {
            case 'today':
                $date = Carbon::today()->format('Y-m-d');
                $operator = '=';
                break;
            case 'scheduled':
                $date = Carbon::tomorrow()->format('Y-m-d');
                $operator = '>=';
                break;
            case 'finished':
                $date = Carbon::today()->format('Y-m-d');
                $operator = '<';
                break;
            default:
                $date = Carbon::today()->format('Y-m-d');
                $operator = '=';
                break;
        }
        $months = config('data.months');
        $days = config('data.days');
        $sessions_in_person = Topic::with([
                        'course:id,modality_in_person_properties,name,imagen,modality_id',
                        'course.modality:id,code',
                        'course.schools' => function ($query) {
                            $query->select('id')->where('active', ACTIVE);
                        }
                    ])
                    ->select('id', 'name','course_id','modality_in_person_properties')
                    ->whereHas('course',function($q) use ($modality_code){
                        $q->where('active',1)->whereRelation('modality','code',$modality_code);
                    })
                    ->where(function($q) use($user,$assigned_courses){
                        $q->whereIn('course_id',$assigned_courses)
                        ->orWhere(DB::raw("modality_in_person_properties->'$.host_id'"), '=', $user->id)
                        ->orWhere(DB::raw("modality_in_person_properties->'$.cohost_id'"), '=', $user->id);
                    })
                    // ->whereIn('course_id',$assigned_courses)
                    ->whereNotNull('modality_in_person_properties')
                    ->where('active',1)
                    ->where(DB::raw("modality_in_person_properties->'$.start_date'"), $operator, $date)
                    ->get();
        if(count($sessions_in_person) == 0){
            return [];
        }
        $sessions_in_person = TopicInPersonAppResource::collection($sessions_in_person);
        $sessions_group_by_date = json_decode($sessions_in_person->toJson(), true);
        $sessions_group_by_date = collect($sessions_group_by_date)->groupBy('key')->all();
        if (count($sessions_group_by_date) === 0) $sessions_group_by_date = new stdClass();
        return $sessions_group_by_date;

    }

    protected function getData($request){
        $user = auth()->user();
        $sessions_live = $this->getCountLiveSession($user,$request);
        $sessions_in_person = $this->getCountCourseInPerson($user);
        $data = [
            'today' => [
                'code' => 'today',
                'title' => 'Hoy',
                'total' => $sessions_live['count_today'] + $sessions_in_person['count_today'],
            ],
            'scheduled' => [
                'code' => 'scheduled',
                'title' => 'Próximas',
                'total' => $sessions_live['count_scheduled'] + $sessions_in_person['count_scheduled'],
            ],
            'finished' => [
                'code' => 'finished',
                'title' => 'Historial',
                'total' => $sessions_live['count_finished'] + $sessions_in_person['count_finished'],
            ],

            'current_server_time' => [
                'timestamp' => (int) (now()->timestamp . '000'),
                'value' => now(),
            ],
            'recommendations' => config('meetings.recommendations'),
            'filters'=> config('course-in-person.filters')
        ];
        return $data;
    }

   
    protected function listUsersBySession($course_id,$topic_id,$code,$search_user,$maskDocument=true,$signature=false){
        $topic =    Topic::select('id', 'name','course_id','modality_in_person_properties')
                        ->where('id',$topic_id)
                        ->with(['course.segments','course.segments.values'])
                        ->first();
        $filters = [];
        if($search_user){
            $filters = [
                ['statement'=>'filterText','value'=>$search_user]
            ];
        }
        $users_segmented = $topic->course->usersSegmented($topic->course->segments,'get_records',$filters,['id','name','lastname','surname','document']);
        $users = [];
        $codes = [];
        $codes_taxonomy = Taxonomy::getDataForSelectAttrs(groupName:'course',typeName:'assistance',attributes:['id','code','name','color']);
        $_users_with_status = TopicAssistanceUser::listUserWithAssistance($users_segmented,$topic_id,$codes_taxonomy,$maskDocument,$signature);
        switch ($code) {
            case 'all':
                $users = $_users_with_status;
                break;
            case 'pending':
                $users = $_users_with_status->whereIn('status',['absent',null])->values()->all();
                $codes = $codes_taxonomy
                            ->whereIn('code',['attended','late'])
                            ->values()->all();
                break;
            case 'present':
                $users = $_users_with_status->whereIn('status',['attended','late'])->values()->all();
                $codes = $codes_taxonomy
                            ->whereIn('code',['attended','late','absent'])
                            ->values()->all();
                break;
        }
        return compact('users','codes');
    }

    protected function listResources($course_id,$topic_id){
        $topic_status_arr = config('topics.status');
        $user = auth()->user();
        $topic = Topic::select('id','name','assessable','type_evaluation_id','modality_in_person_properties','review_all_duration_media')
                    ->where('course_id',$course_id)
                    ->where('id',$topic_id)
                    ->with([
                        'medias:id,topic_id,title,value,type_id,embed,downloadable,position,created_at,updated_at,deleted_at',
                        'evaluation_type:id,code',
                        'tags.taxonomy:id,name,type,description'
                    ])
                    ->first();
        $summary_topic =  SummaryTopic::where('topic_id',$topic_id)->where('user_id',$user->id)->first();
        $media_topics = $topic->medias;

        $media_progress = !is_null($summary_topic?->media_progress) ? json_decode($summary_topic?->media_progress) : null;
        foreach ($media_topics as $media) {
            unset($media->created_at, $media->updated_at, $media->deleted_at);
            $media->full_path = !in_array($media->type_id, ['youtube', 'vimeo', 'scorm', 'link'])
                ? route('media.download.media_topic', [$media->id]) : null;

            $media->status_progress = 'por-iniciar';
            $media->last_media_duration = null;

            if(!is_null($media_progress)){
                foreach($media_progress as $medp){
                    if($medp->media_topic_id == $media->id){
                        $media->status_progress = $medp->status;
                        $media->last_media_duration = $medp->last_media_duration;
                        break;
                    }
                }
            }
        }

        $media_embed = array();
        $media_not_embed = array();
        foreach ($media_topics as $med) {
            if($med->embed)
                array_push($media_embed, $med);
            else
                array_push($media_not_embed, $med);
        }


        $last_media_access = $summary_topic?->last_media_access;
        $last_media_duration = $summary_topic?->last_media_duration;

        $media_topic_progress = array('media_progress' => $media_progress,
                                    'last_media_access' => $last_media_access,
                                    'last_media_duration' => $last_media_duration);

        $is_host = $user->id == $topic->modality_in_person_properties->host_id;
        if(isset($topic->modality_in_person_properties?->cohost_id)){
            $is_host = $user->id == $topic->modality_in_person_properties?->cohost_id;
        }
        $avaiable_to_show_resources = $topic->modality_in_person_properties->show_medias_since_start_course;
        if(!$avaiable_to_show_resources){
            $current_time = Carbon::now();
            $datetime = Carbon::parse($topic->modality_in_person_properties->start_date.' '.$topic->modality_in_person_properties->finish_time);
            $avaiable_to_show_resources = $current_time>=$datetime;
        }
        $topics_data = [
            'id' => $topic->id,
            'nombre' => $topic->name,
            'imagen' => $topic->imagen,
            'contenido' => $topic->content,
            'media' => $media_embed,
            'tags' => $topic->tags->map( fn($t) => $t->taxonomy),
            'media_not_embed' => $media_not_embed,
            'media_topic_progress'=>$media_topic_progress,
            'review_all_duration_media' => boolval($topic->review_all_duration_media),
            'avaiable_to_show_resources'=> $avaiable_to_show_resources,
            'is_host' => $is_host,
        ];
        return $topics_data;
    }

    protected function changeStatusEvaluation($data){
        $topic = Topic::select('id','modality_in_person_properties')->where('id',$data['topic_id'])->first();
        $action = $data['action'];
        $time = $data['time'];
        $message = '';
        $now = Carbon::now();
        $modality_in_person_properties = $topic->modality_in_person_properties;
        $modality_in_person_properties = json_decode(json_encode($modality_in_person_properties), false);
         // unset($modality_in_person_properties->evaluation);
        // $topic->modality_in_person_properties = $modality_in_person_properties;
        // $topic->save();
        // dd();
        switch ($action) {
            case 'start':
                $time_evaluation = Carbon::createFromFormat('H:i', $time);
                $minutes_duration = $time_evaluation->hour * 60 + $time_evaluation->minute;
                $finish_evaluation = $now->copy()->addMinutes($minutes_duration);
                // Inicializar el campo evaluation si no existe
                if (!isset($modality_in_person_properties->evaluation)) {
                    $modality_in_person_properties->evaluation = [];
                }else{
                    $message = 'La evaluación ya esta inicializada.';
                    break;
                }
                // Actualizar las propiedades de evaluación
                $modality_in_person_properties->evaluation['date_init'] = $now->format('Y-m-d H:i:s');
                $modality_in_person_properties->evaluation['date_finish'] = $finish_evaluation->format('Y-m-d H:i:s');
                $modality_in_person_properties->evaluation['duration_in_minutes'] = $minutes_duration;
                $modality_in_person_properties->evaluation['time'] = $time;
                $modality_in_person_properties->evaluation['status'] = 'started';
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now->format('Y-m-d H:i:s'),'action'=>$action];
                $message = 'Se inició la evaluación.';
            break;
            case 'start-before-finished-time':
                $modality_in_person_properties->evaluation->status = 'extra-time';
                $modality_in_person_properties->evaluation->time = $now->format('Y-m-d H:i:s');
                $modality_in_person_properties->evaluation->historic_status[] = ['time'=>$now->format('Y-m-d H:i:s'),'action'=>$action];
                $message = 'Se activó manualmente la evaluación.';
            break;
            case 'finish-early':
                $modality_in_person_properties->evaluation->status = 'finished';
                $modality_in_person_properties->evaluation->historic_status[] = ['time'=>$now->format('Y-m-d H:i:s'),'action'=>$action];
                $message = 'Se finalizó antes de terminar la evaluación.';
            break;
            case 'finish-in-time':
                $modality_in_person_properties->evaluation->status = 'finished';
                $modality_in_person_properties->evaluation->historic_status[] = ['time'=>$now->format('Y-m-d H:i:s'),'action'=>'finish-in-time'];
                $message = 'Se terminó a tiempo la evaluación.';
            break;
            case 'finish-manually':
                $modality_in_person_properties->evaluation->status = 'finished';
                $modality_in_person_properties->evaluation->historic_status[] = ['time'=>$now->format('Y-m-d H:i:s'),'action'=>$action];
                $message = 'Se terminó manualmente la evaluación.';
            break;
        }
        $topic->modality_in_person_properties = $modality_in_person_properties;
        $topic->save();
        return ['evaluation' => $topic->modality_in_person_properties->evaluation,'message'=>$message];
    }
    protected function verifyEvaluationTime($topic_id){
        $user = auth()->user();
        $topic = Topic::select('id','modality_in_person_properties')->where('id',$topic_id)->first();
        $modality_in_person_properties = $topic->modality_in_person_properties;
        $is_host = $user->id == $modality_in_person_properties->host_id || $user->id == $modality_in_person_properties?->cohost_id;
        // unset($modality_in_person_properties->evaluation);
        // $topic->modality_in_person_properties = $modality_in_person_properties;
        // $topic->save();
        // dd();
        if(!$is_host){
            return [
                'message' => 'No eres el host de la sesión.'
            ];
        }
        $is_evaluation_started = true; 
        if (!isset($modality_in_person_properties->evaluation)) {
            $is_evaluation_started = false;
            return [
                'is_evaluation_started' => $is_evaluation_started,
                'evaluation' => []
            ];
        }
        $is_evaluation_started = $modality_in_person_properties->evaluation->status == 'started';
        unset($modality_in_person_properties->evaluation->historic_status);
        $current_time = Carbon::now();
        if($is_evaluation_started){
            $finish_time = Carbon::createFromFormat('Y-m-d H:i:s',$modality_in_person_properties->evaluation->date_finish);
            $diff = $finish_time->diff($current_time);
            $modality_in_person_properties->evaluation->current_time = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
        }
        if( $modality_in_person_properties->evaluation->status == 'extra-time'){
            $init_extra_time = Carbon::createFromFormat('Y-m-d H:i:s',$modality_in_person_properties->evaluation->time);
            $diff = $current_time->diff($init_extra_time);
            $modality_in_person_properties->evaluation->time = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
            $is_evaluation_started = true;
        }
        return [
            'is_evaluation_started' => $is_evaluation_started,
            'evaluation' => $modality_in_person_properties->evaluation
        ]; 
    }
    protected function getListMenu($topic_id){
        $user = auth()->user();
        $topic = Topic::select('id','course_id','type_evaluation_id','modality_in_person_properties')
                    ->with([
                        'course:id,modality_id,modality_in_person_properties,registro_capacitacion,mod_evaluaciones,show_certification_to_user',
                        'course.modality:id,code','course.polls:id'
                    ])
                    ->where('id',$topic_id)
                    ->first();

        $rol = $user->id == $topic->modality_in_person_properties->host_id ? 'host' : 'user';
        if(isset($topic->modality_in_person_properties->cohost_id ) && $user->id == $topic->modality_in_person_properties->cohost_id){
            $rol = 'host';
        }
        $data = [];
        switch ($rol) {
            case 'user':
                $data = $this->listUserMenu($topic,$user);
                break;
            case 'host':
                $data = $this->listHostMenu($topic,$user);
                break;
            case 'cohost':
                $data = $this->listHostMenu($topic,$user);
                break;
        }
        $data['current_server_time'] = [
            'timestamp' => (int) (now()->timestamp . '000'),
            'value' => now(),
        ];
        return $data;
    }

    

    protected function takeAssistance($topic_id,$data){
        $user_ids = $data['user_ids'];
        $action = $data['action'];
        $assistance_users = TopicAssistanceUser::assistance($topic_id,$user_ids);

        $assistance = Taxonomy::select('id','name','code')
                        ->where('group','course')
                        ->where('type','assistance')
                        ->where('code',$data['action'])
                        ->first();

        $users_to_create = [];
        $users_to_update = [];
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $historic_assistance = [
            'status_id' => $assistance->id,
            'status_code' => $assistance->code,
            'date_assistance' => $now
        ];
        foreach ($user_ids as $user_id) {
            $user_has_assistance = $assistance_users->where('user_id',$user_id)->first()?->toArray();
            if($user_has_assistance && $user_has_assistance['status_id'] != $assistance->id){
                $user_has_assistance['status_id'] = $assistance->id;
                $user_has_assistance['date_assistance'] = $now;
                $user_has_assistance['historic_assistance'][] = $historic_assistance;
                $user_has_assistance['historic_assistance'] = json_encode($user_has_assistance['historic_assistance']);
                $users_to_update[] = $user_has_assistance;
            }
            if(is_null($user_has_assistance)){
                $users_to_create[] = [
                    'user_id' => $user_id,
                    'topic_id' => $topic_id,
                    'status_id'=> $assistance->id,
                    'date_assistance'=>$now,
                    'historic_assistance' =>json_encode([$historic_assistance])
                ];
            }
        }
        TopicAssistanceUser::insertUpdateMassive($users_to_create,'insert');
        TopicAssistanceUser::insertUpdateMassive($users_to_update,'update');
        
        return ['message' => 'Se ha asignado la asistencia correctamente.'];
    }

    protected function uploadSignature($signature,$topic_id){
        $user = auth()->user();
        // workspace creation reference
        $str_random = Str::random(5);
        $name_image = $user->subworkspace_id . '-' . Str::random(4) . '-' . date('YmdHis') . '-' . $str_random.'.png';
        // Ruta donde se guardará la imagen en el servidor
        $path = 'course-in-person-signatures/'.$topic_id.'/'.$name_image;
        Media::uploadMediaBase64(name:'', path:$path, base64:$signature,save_in_media:false,status:'private');
        TopicAssistanceUser::updateOrCreate(
            ['user_id'=>$user->id,'topic_id'=>$topic_id],
            ['user_id'=>$user->id,'topic_id'=>$topic_id,'signature'=>$path]
        );
        $user_attended = TopicAssistanceUser::userIsPresent($user->id,$topic_id);
        return [
            'message'=> 'Se ha guardado la firma.',
            'has_assistance'=> boolval($user_attended)
        ];
    }
    protected function validateResource($type,$topic_id){
        $resource = null;
        $user = auth()->user();
        //Si no tiene la asistencia en late o absent no debería acceder ni a los recursos multimedia ni a la evaluación  ni a la encuesta
        $is_done = false;
        $has_attempts_evaluation = false;
        switch ($type) {
            case 'assistance':
                $assistance = TopicAssistanceUser::userIsPresent($user->id,$topic_id);
                $topic = Topic::select('id','course_id','modality_in_person_properties')
                    ->with(['course:id,modality_id,modality_in_person_properties','course.modality:id,code'])
                    ->where('id',$topic_id)
                    ->first();
                // only validate assistance in courses in-person
                $resource = ($topic->course->modality->code == 'in-person') ? $assistance : true;
                if($topic->course->modality->code == 'in-person'){
                    $is_session_finished = now() > Carbon::parse($topic->modality_in_person_properties->start_date.' '.$topic->modality_in_person_properties->finish_time);
                    if($is_session_finished && $topic->course->modality_in_person_properties->visualization_type =='all-users'){
                        $resource = true;
                    }
                }
                break;
            case 'multimedias':
                $resource = Topic::select('id','name','assessable','type_evaluation_id','modality_in_person_properties')
                    ->where('id',$topic_id)
                    ->first()?->isAccessibleMultimedia();
                break;
            case 'evaluation':
                $topic = Topic::select('modality_in_person_properties','course_id','type_evaluation_id','id')
                    ->with(['evaluation_type', 'course'])
                    ->where('id',$topic_id)
                    ->first();
                $resource = $topic?->isAccessibleEvaluation();
                if($resource){
                    $row = SummaryTopic::where('topic_id',$topic_id)->where('user_id',$user->id)->first();
                    $is_done = !is_null($row?->last_time_evaluated_at);
                    $is_qualified = $topic->evaluation_type->code == 'qualified';
                    $has_attempts_evaluation = !($row?->hasNoAttemptsLeft(null,$topic->course) && $is_qualified);
                }
                break;
            case 'poll':
                $topic =  Topic::select('course_id','modality_in_person_properties')
                ->where('id',$topic_id)
                ->first();
                $resource = $topic->isAccessiblePoll();
                if($resource){
                    $has_poll = PollQuestionAnswer::select('course_id')
                    ->where('course_id', $topic->course_id)
                    ->where('user_id', $user->id)
                    ->first();
                    $is_accessible = !boolval($has_poll);
                    $is_done = boolval($has_poll);
                }
            break;
        }
        $is_accessible = boolval($resource);
        return ['is_accessible'=>$is_accessible,'is_done' => $is_done,'has_attempts_evaluation'=>$has_attempts_evaluation];
    }
    protected function startPoll($topic_id){
        $topic = Topic::select('id','course_id','modality_in_person_properties')
                    ->with('course:id')
                    ->where('id',$topic_id)
                    ->first();
        $poll = $topic->course->polls->first();
        if(!$poll){
            return ['message'=>'Esta sesión no tiene una encuesta asignada'];
        }          
        $modality_in_person_properties = $topic->modality_in_person_properties;
        if($topic && isset($modality_in_person_properties->poll_started)){
            return ['message'=>'La encuesta ya ha sido iniciada.'];
        }
        $modality_in_person_properties->poll_started = true;
        $topic->modality_in_person_properties = $modality_in_person_properties;
        $topic->save();
        return ['message'=>'Se inició la encuesta.'];
    }
    protected function loadPoll($topic_id){
        $topic = Topic::select('id','modality_in_person_properties')
                    ->where('id',$topic_id)
                    ->with([
                        'poll:id,titulo,imagen,anonima',
                        'poll.questions' => function ($q) {
                            $q->with('type:id,code')
                                ->where('active', ACTIVE)
                                ->select('id', 'poll_id', 'titulo', 'type_id', 'opciones');
                        }
                    ])
                    ->where('active', ACTIVE)
                    ->first();

        $is_accessible = $topic->isAccessiblePoll();
        
        if($is_accessible){
            $poll = $topic->poll;
        }
        return ['is_accessible'=>$is_accessible,'poll'=>$topic->poll];
    }
    protected function usersInitData($topic_id){
        $topic = Topic::select('id','course_id','modality_in_person_properties','path_qr')
                    ->where('id',$topic_id)
                    ->with([
                        'course:id,modality_in_person_properties',
                    ])
                    ->where('active', ACTIVE)
                    ->first();

        $required_signature = $topic->course->modality_in_person_properties?->required_signature;
        $show_modal_double_assistance = false;
        if($topic->course->modality_in_person_properties?->assistance_type == 'assistance-by-day'){
            $today = Carbon::today()->format('Y-m-d');
            $first_session_of_day = Topic::select('id')
                                    ->where('course_id',$topic->course_id)
                                    ->where('active',ACTIVE)
                                    ->where(DB::raw("modality_in_person_properties->'$.start_date'"), '=', $today)
                                    ->orderBy(DB::raw("modality_in_person_properties->'$.start_date'"),'ASC')
                                    ->first();
            if($first_session_of_day){
                $show_modal_double_assistance = $first_session_of_day?->id != $topic->id;
            }
        }
        return [
            'qr'=>$topic->path_qr,
            // 'link'=>config('app.web_url').'/sesiones',
            'show_modal_double_assistance' => $show_modal_double_assistance, 
            'nota'=> $required_signature ? 'Ingresa al QR recuerda firmar; esta firma se colocará en el reporte de asistencias.' : '',
        ];
    }
    //SUBFUNCTIONS
    private function modifyMenus($menus,$code,$action='change_status',$value=null){
        $pollIndex = array_search($code, array_column($menus, 'code'));
        if ($pollIndex !== false) {
            switch ($action) {
                case 'change_status':
                    $menus[$pollIndex]['show'] = false;
                    break;
                case 'unset':
                    unset($menus[$pollIndex]);
                    break;
                case 'change_status_code':
                    $menus[$pollIndex]['status'] = $value;
                    break;
                default:
                    throw new InvalidArgumentException('Acción no válida especificada');
            }
        }
        return array_values($menus);
    }

    private function getCountLiveSession($user,$request){
        $subworkspace = $user->subworkspace;
        $request->merge(['workspace_id' => $subworkspace->parent_id]);
        $status_meeting = Taxonomy::getDataForSelectAttrs(groupName:'meeting',typeName:'status',attributes:['id','code','name','color']);
        $scheduled = $status_meeting->where('code','scheduled')->first();
        $started = $status_meeting->where('code','in-progress')->first();
        $finished = $status_meeting->where('code','finished')->first();
        $overdue = $status_meeting->where('code','overdue')->first();
        $cancelled = $status_meeting->where('code','cancelled')->first();

        $filters_today = new Request([
            'usuario_id' => $user->id,
            'statuses' => [$scheduled->id, $started->id],
            'date' => Carbon::today(),
        ]);

        $filters_scheduled = new Request([
            'usuario_id' => $user->id,
            'statuses' => [$scheduled->id],
            'date_start' => Carbon::tomorrow(),
        ]);

        $filters_finished = new Request([
            'usuario_id' => $user->id,
            'statuses' => [$finished->id, $overdue->id, $cancelled->id],
        ]);

        return [
            'count_today' => Meeting::search($filters_today, 'count'),
            'count_scheduled' => Meeting::search($filters_scheduled, 'count'),
            'count_finished' => Meeting::search($filters_finished, 'count'),
        ];
    }
    private function getCountCourseInPerson($user){

        $assigned_courses = $user->getCurrentCourses(withRelations: 'soft',only_ids_courses:true);
        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $count_today =  $this->returnQuery($user,$assigned_courses,'=',$today);
        $count_scheduled = $this->returnQuery($user,$assigned_courses,'>=',$tomorrow);
        $count_finished = $this->returnQuery($user,$assigned_courses,'<',$today);
        return compact('count_today','count_scheduled','count_finished');
    }
    private function returnQuery($user,$assigned_courses,$operator,$date){
        return Topic::select('id', 'name','course_id','modality_in_person_properties')
        ->whereHas('course',function($q){
            $q->where('active',1);
        })
        ->where(function($q) use($user,$assigned_courses){
            $q->whereIn('course_id',$assigned_courses)
            ->orWhere(DB::raw("modality_in_person_properties->'$.host_id'"), '=', $user->id)
            ->orWhere(DB::raw("modality_in_person_properties->'$.cohost_id'"), '=', $user->id);
        })
        ->whereNotNull('modality_in_person_properties')
        ->where('active',1)->where(DB::raw("modality_in_person_properties->'$.start_date'"), $operator, $date)->count();
    }
    private function listUserMenu($topic,$user){
        $menus = config('course-in-person.user');
        $last_session = Topic::select('id')->where('course_id',$topic->course_id)
                        ->where('active',ACTIVE)
                        ->orderBy(DB::raw("CONCAT(modality_in_person_properties->'$.start_date', ' ', modality_in_person_properties->'$.start_time')"), 'DESC')
                        ->first();
        //Si es virtual añade el card de zoom
        $zoom = null;
        $zoom_status = ['name'=>'Agendado','code'=>'scheduled'];
        $meeting = null;
        $is_on_time = false;
        if($topic->course->modality->code == 'virtual'){
            $meeting = Meeting::where('model_type','App\\Models\\Topic')->with('status')->where('model_id',$topic->id)->first();
            if($meeting){
                $is_on_time = $meeting->isOnTime();
                $zoom_status = ['name'=>$meeting->status->name,'code'=>$meeting->status->code];
                $zoom = MeetingAppResource::collection([$meeting]);
            }
            $menus = $this->modifyMenus($menus,'assistance','unset');
            //Si es virtual añade el card de zoom
            if(in_array($zoom_status['code'],['scheduled','reserved']) && $meeting){
                $zoom_status['name'] = $zoom_status['name'].': '.Carbon::parse($meeting->starts_at)->format('Y-m-d H:i');
            }
            array_unshift($menus,  [
                'title' => 'Iniciar sesión zoom',
                'code' => 'zoom',
                'description' => 'Ingresa a la sesión zoom asignada a este tema.',
                'show' => $is_on_time,
                'status' => $zoom_status
            ]);
        }

        //Si no tiene encuesta y ademas la última sesión es diferente a la sesión consultada. Se oculta el menú
        $unset_poll = false;
        if(!$topic->course->polls->first() || $last_session->id !== $topic->id){
            $menus = $this->modifyMenus($menus,'poll','unset');
            $unset_poll = true;
        }
        //Si no tiene evaluación y ademas la última sesión es diferente a la sesión consultada. Se oculta el menú
        $unset_evaluation = false;
        if(!$topic->type_evaluation_id){
            $menus = $this->modifyMenus($menus,'evaluation','unset');
            $unset_evaluation = true;
        }
         //Si no es el último tema o no tiene activa mostrar el ceritificado, no se muestra el certificado
        $show_certification_to_user = $topic->course->show_certification_to_user;
        if($last_session->id != $topic->id || !$show_certification_to_user){
            $menus = $this->modifyMenus($menus,'certificate','unset');
        }
        //Obtener 
        $action_button = null;
        //Si tiene evaluación, verificar el estado
        // $is_accessible_evaluation = $topic->isAccessibleEvaluation();
        if($topic->type_evaluation_id){
            $summary = SummaryTopic::with('status:id,name,code')->select('attempts','passed','status_id')->where('user_id',$user->id)->where('topic_id',$topic->id)->first();
            $attemps_limit = $topic->course->getAttemptsLimit();
            if($summary){
                $evaluation_status_name = $summary?->status?->name.' ('.$summary->attempts.'/'.$attemps_limit.')';
                $menus = $this->modifyMenus(
                            $menus,
                            'evaluation',
                            'change_status_code',
                            [
                                'code'=> $summary?->status?->code,
                                'name'=> $evaluation_status_name,
                            ]
                        );
                if($summary?->attempts == $attemps_limit || !$topic->isAccessibleEvaluation()){
                    $menus = $this->modifyMenus(
                        $menus,
                        'evaluation'
                    );
                }
            }
            if(
                !$summary || (in_array($summary?->status?->code,['desaprobado','por-iniciar']) && $summary->attempts < $attemps_limit)
                && !$unset_evaluation
            ){
                $action_button = [
                    'code' => 'evaluation',
                    'name' => 'Realizar evaluación'
                ];
            }
        }
        //Si tiene encuesta, verificar el estado
        if($topic->isAccessiblePoll()){
            $hasPoll = PollQuestionAnswer::select('id')->where('user_id',$user->id)->where('course_id',$topic->course_id)->first();
            if($hasPoll){
                $menus = $this->modifyMenus(
                    $menus,
                    'poll',
                    'change_status_code',
                    [
                        'code'=> 'realizado',
                        'name'=> 'Realizado',
                    ]
                );
            }
        }
        if(is_null($action_button) && $topic->course->polls->first() && !$unset_poll){
            $action_button = [
                'code' => 'poll',
                'name' => 'Realizar encuesta'
            ];
        }
        if(is_null($action_button) && $zoom && $is_on_time){
            $action_button = [
                'code' => 'zoom',
                'name' => 'Iniciar reunión zoom'
            ];
        }
        // if(is_null($action_button)){
        //     $action_button = [
        //         'code' => 'multimedias',
        //         'name' => 'Consulta el material del curso'
        //     ];
        // }
        //Si es presencial mandar el estado de la asistencia
        if($topic->course->modality->code == 'in-person'){
            $has_assistance = TopicAssistanceUser::select('id','status_id')->with('status:id,code,name')
                                ->where('user_id',$user->id)
                                ->whereNotNull('status_id')
                                ->where('topic_id',$topic->id)
                                ->first();
            if($has_assistance){
                $menus = $this->modifyMenus(
                    $menus,
                    'assistance',
                    'change_status_code',
                    [
                        'code'=> $has_assistance->status->code,
                        'name'=> $has_assistance->status->name,
                    ]
                );
            }
        }
        if($last_session->id != $topic->id){
            $menus = $this->modifyMenus($menus,'certificate','unset');
        }
        $required_signature = false;
        if($topic->course->modality_in_person_properties?->required_signature){
            $hasSignature = TopicAssistanceUser::where('user_id',$user->id)->where('topic_id',$topic->id)->whereNotNull('signature')->first();
            $required_signature = !boolval($hasSignature);
        }
        $show_modal_signature_registro_capacitación = false;
        //REGISTRO DE CAPACITACIÓN
        $registroCapacitacionIsActive = $topic->course->registroCapacitacionIsActive();
        if($registroCapacitacionIsActive){
            $summary = SummaryCourse::select('registro_capacitacion_path','advanced_percentage')->where('user_id',$user->id)->where('course_id', $topic->course_id)->first();
            $registroCapacitacionPath = null;
            if ($summary) {
                $registroCapacitacionPath = $summary->registro_capacitacion_path;
                $registroCapacitacionUrl = $registroCapacitacionPath
                    ? Course::generateRegistroCapacitacionURL($registroCapacitacionPath)
                    : null;
            }
            $show_modal_signature_registro_capacitación = !boolval($registroCapacitacionPath) && $summary?->advanced_percentage == 100;
        }
        
        $has_media = boolval($topic->medias()->first());
        return compact('menus','required_signature','show_modal_signature_registro_capacitación','zoom','has_media','action_button');
    }

    private function listHostMenu($topic,$user){
        $menus = config('course-in-person.host');
        
        $last_session = Topic::select('id')->where('course_id',$topic->course_id)
                        ->where('active',ACTIVE)
                        ->orderBy(DB::raw("CONCAT(modality_in_person_properties->'$.start_date', ' ', modality_in_person_properties->'$.start_time')"), 'DESC')
                        ->first();
        $zoom = null;
        $is_on_time = false;
        $zoom_status = ['name'=>'Agendado','code'=>'scheduled'];
        $meeting = null;
        $unset_take_assistance = false;
        if($topic->course->modality->code == 'virtual'){
            $meeting = Meeting::where('model_type','App\\Models\\Topic')->with('status')->where('model_id',$topic->id)->first();
            if($meeting){
                $is_on_time = $meeting->isOnTime();
                $zoom_status = ['name'=>$meeting->status->name,'code'=>$meeting->status->code];
                $zoom = MeetingAppResource::collection([$meeting]);
            }
            //Si es virtual se quita el card de tomar asistencia, la asistencia la toma zoom
            $menus = $this->modifyMenus($menus,'take-assistance','unset');
            $unset_take_assistance = true;
            //Si es virtual añade el card de zoom
            if(in_array($zoom_status['code'],['scheduled','reserved']) && $meeting){
                $zoom_status['name'] = $zoom_status['name'].': '.Carbon::parse($meeting->starts_at)->format('Y-m-d H:i');
            }
            array_unshift($menus,  [
                'title' => 'Iniciar sesión zoom',
                'code' => 'zoom',
                'description' => 'Ingresa a la sesión zoom asignada a este tema.',
                'show' => $is_on_time,
                'status' => $zoom_status
            ]);
        }
        //Si no tiene encuesta y ademas la última sesión es diferente a la sesión consultada. Se oculta el menú
        $unset_poll = false;
        if(!$topic->course->polls->first() || $last_session->id != $topic->id){
            $menus = $this->modifyMenus($menus,'poll','unset');
            $unset_poll = true;
        }
        //Si no tiene evaluación y ademas la última sesión es diferente a la sesión consultada. Se oculta el menú
        $unset_evaluation = false;
        if(!$topic->type_evaluation_id){
            $menus = $this->modifyMenus($menus,'evaluation','unset');
            $unset_evaluation = true;
        }
        
        /*************************************************************VERIFICAR LOS ESTADOS*****************************/
        $action_button = null;
        if($topic->course->modality->code == 'in-person'){
            $has_one_assistance = TopicAssistanceUser::select('id')->whereNotNull('status_id')->where('topic_id',$topic->id)->first();
            if($has_one_assistance){
                $menus = $this->modifyMenus(
                    $menus,
                    'take-assistance',
                    'change_status_code',
                    [
                        'code'=> 'realizado',
                        'name'=> 'Realizado',
                    ]
                );
            }else{
                if(!$unset_take_assistance){
                    $action_button = [
                        'code' => 'take-assistance',
                        'name' => 'Tomar asistencia'
                    ];
                    $menus = $this->modifyMenus(
                        $menus,
                        'evaluation'
                    );
                    $menus = $this->modifyMenus(
                        $menus,
                        'poll'
                    );
                }
            }

        }
        if(is_null($action_button) && !isset($topic->modality_in_person_properties->evaluation) && !$unset_evaluation){
            $action_button = [
                'code' => 'evaluation',
                'name' => 'Iniciar evaluación'
            ];
        }
        if($topic->type_evaluation_id && isset($topic->modality_in_person_properties->evaluation)){
            $status_evaluation = $topic->modality_in_person_properties?->evaluation?->status;
            $menus = $this->modifyMenus(
                $menus,
                'evaluation',
                'change_status_code',
                [
                    'code'=> $status_evaluation,
                    'name'=> $status_evaluation == 'finished' ? 'Finalizado' : 'Iniciado',
                ]
            );
            if(in_array($status_evaluation,['extra-time','started']) && !$unset_evaluation){
                $action_button = [
                    'code' => 'evaluation',
                    'name' => 'Continuar evaluación'
                ];
            }
        }
        
        if($topic->course->polls->first() && $last_session->id == $topic->id){
            $is_accessible_poll = $topic->isAccessiblePoll();
            $menus = $this->modifyMenus(
                $menus,
                'poll',
                'change_status_code',
                [
                    'code'=> $is_accessible_poll ? 'realizado' : 'pending',
                    'name'=> $is_accessible_poll ? 'Realizado' : 'Pendiente',
                ]
            );
            if(is_null($action_button) && !$is_accessible_poll && !$unset_poll){
                $action_button = [
                    'code' => 'poll',
                    'name' => 'Iniciar encuesta'
                ];
            }
        }
        if(is_null($action_button) && $zoom && $is_on_time){
            $action_button = [
                'code' => 'zoom',
                'name' => 'Iniciar reunión zoom'
            ];
        }
        /*-------------------------------------------------------------------FINALIZAR CAMBIAR ESTADO----------------------------------------------- */
        $show_modal_signature_registro_capacitación = false;
        $required_signature = false;
        
        $has_media = boolval($topic->medias()->first());
        return compact('menus','required_signature','show_modal_signature_registro_capacitación','zoom','has_media','action_button');
    }
}
