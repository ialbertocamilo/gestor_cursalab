<?php

namespace App\Models;

use stdClass;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\TopicInPersonAppResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;

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
                    $sessions_in_person = CourseInPerson::listCoursesInPerson($request);
                    break;
                case 'live':
                    $sessions_live = Meeting::getListMeetingsByUser($request,'in-array');
                    break;
                case 'online':
                    break;
                case 'all':
                    $sessions_in_person = CourseInPerson::listCoursesInPerson($request);
                    $sessions_live = Meeting::getListMeetingsByUser($request,'in-array');
                    $sessions_course_live  = [];
                    break;
            }
        }else{
            $sessions_in_person = CourseInPerson::listCoursesInPerson($request);
            $sessions_live = Meeting::getListMeetingsByUser($request,'in-array');
            $sessions_course_live  = [];
        }
        return compact('sessions_in_person','sessions_live','sessions_course_live');
    }
    protected function listCoursesInPerson($request){
        $code = $request->code;
        $user = $request->user;
        
        $assigned_courses = $user->getCurrentCourses(withRelations: 'soft',only_ids_courses:true,modality_code:'in-person');
        
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
        $sessions_in_person = Topic::with(['course:id,modality_in_person_properties,imagen'])
                    ->select('id', 'name','course_id','modality_in_person_properties')
                    ->whereHas('course',function($q){
                        $q->where('active',1);
                    })
                    ->where(function($q) use($user,$assigned_courses){
                        $q->whereIn('course_id',$assigned_courses)->orWhere(DB::raw("modality_in_person_properties->'$.host_id'"), '=', $user->id);
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

   
    protected function listGuestsByCourse($course_id,$topic_id,$code){
        $topic =    Topic::select('id', 'name','course_id','modality_in_person_properties')
                        ->where('id',$topic_id)
                        ->with(['course.segments','course.segments.values'])
                        ->first();
        
        $users_segmented = $topic->course->usersSegmented($topic->course->segments,'get_records',[],['id','name','lastname','surname','document']);
        $users = [];
        $codes = [];
        $codes_taxonomy = Taxonomy::getDataForSelectAttrs(groupName:'course',typeName:'assistance',attributes:['id','code','name','color']);
        $_users_with_status = TopicAssistanceUser::listUserWithAssistance($users_segmented,$topic_id,$codes_taxonomy);
        switch ($code) {
            case 'all':
                $users = $_users_with_status;
                break;
            case 'pending':
                $users = $_users_with_status->where('status','absent')->values()->all();
                $codes = $codes_taxonomy
                            ->whereIn('code',['attended','late'])
                            ->values()->all();
                break;
            case 'present':
                $users = $_users_with_status->where('status','<>','absent')->values()->all();
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
        $topic = Topic::select('id','name','assessable','type_evaluation_id','modality_in_person_properties')
                    ->where('course_id',$course_id)
                    ->where('id',$topic_id)
                    ->with([
                        'medias:id,topic_id,title,value,type_id,embed,downloadable,position,created_at,updated_at,deleted_at',
                        'evaluation_type:id,code'
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
            'media_not_embed' => $media_not_embed,
            'media_topic_progress'=>$media_topic_progress,
            'review_all_duration_media' => boolval($topic->review_all_duration_media),
            'avaiable_to_show_resources'=> $avaiable_to_show_resources,
            'is_host' => $is_host
        ];
        return $topics_data;
    }

    protected function changeStatusEvaluation($data){
        $topic = Topic::select('id','modality_in_person_properties')->where('id',$data['topic_id'])->first();
        $action = $data['action'];
        $time = $data['time'];
        $message = '';
        $now = Carbon::now();
        switch ($action) {
            case 'start':
                $time_evaluation = Carbon::createFromFormat('H:i', $time);
                $minutes_duration = $time_evaluation->hour * 60 + $time_evaluation->minute;
                $finish_evaluation = $now->copy()->addMinutes($minutes_duration);

                // Obtener y decodificar las propiedades del tema
                $modality_in_person_properties = $topic->modality_in_person_properties;
                
                // Inicializar el campo evaluation si no existe
                if (!isset($modality_in_person_properties->evaluation)) {
                    $modality_in_person_properties->evaluation = [];
                }else{
                    $message = 'La evaluación ya esta inicializada.';
                    break;
                }
                // Actualizar las propiedades de evaluación
                $modality_in_person_properties->evaluation['date_init'] = $now->format('Y-m-d H:i');
                $modality_in_person_properties->evaluation['date_finish'] = $finish_evaluation->format('Y-m-d H:i');
                $modality_in_person_properties->evaluation['duration_in_minutes'] = $minutes_duration;
                $modality_in_person_properties->evaluation['time'] = $time;
                $modality_in_person_properties->evaluation['status'] = 'started';
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>$action];
                // Codificar nuevamente y guardar las propiedades actualizadas
                $topic->modality_in_person_properties = $modality_in_person_properties;
                $topic->save();
                $message = 'Se inició la evaluación.';
            break;
            case 'start-before-finished-time':
                $topic->modality_in_person_properties->evaluation['status'] = 'extra-time';
                $topic->save();
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>$action];
                $message = 'Se activó manualmente la evaluación.';
            break;
            case 'finish-early':
                $topic->modality_in_person_properties->evaluation['status'] = 'finished';
                $topic->save();
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>$action];
                $message = 'Se finalizó antes de terminar la evaluación.';
            break;
            case 'finish-in-time':
                $topic->modality_in_person_properties->evaluation['status'] = 'finished';
                $topic->save();
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>'finish-in-time'];
                $message = 'Se terminó a tiempo la evaluación.';
            break;
            case 'finish-manually':
                $modality_in_person_properties->evaluation['status'] = 'finished';
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>$action];
                $topic->save();
                $message = 'Se terminó manualmente la evaluación.';
            break;
        }
        return ['evaluation' => $topic->modality_in_person_properties->evaluation,'message'=>$message];
    }

    protected function getListMenu($topic_id){
        $user = auth()->user();
        $topic = Topic::select('id','poll_id','course_id','type_evaluation_id','modality_in_person_properties')
                    ->with(['course:id,modality_id,modality_in_person_properties','course.modality:id,code','course.polls:id'])
                    ->where('id',$topic_id)
                    ->first();

        $rol = $user->id == $topic->modality_in_person_properties->host_id ? 'host' : 'user';
        $menus = config('course-in-person.'.$rol);
        $last_session = Topic::select('id')->where('course_id',$topic->course_id)
                        ->where('active',ACTIVE)
                        ->orderBy(DB::raw("modality_in_person_properties->'$.start_date'"),'DESC')
                        ->first();
        //Si no tiene encuesta y ademas la última sesión es diferente a la sesión consultada. Se oculta el menú
        if(!$topic->course->polls->first() || $last_session->id != $topic->id){
            $menus = $this->modifyMenus($menus,'poll','unset');
        }
        if(!$topic->type_evaluation_id){
            $menus = $this->modifyMenus($menus,'evaluation');
        }
        if($rol == 'host' && $topic->course->modality->code == 'take-assistance'){
            $menus = $this->modifyMenus($menus,'evaluation');
        }
        if($last_session->id != $topic->id){
            $menus = $this->modifyMenus($menus,'certificate','unset');
        }
        $required_signature = false;
        if($topic->course->modality_in_person_properties?->required_signature){
            $hasSignature = TopicAssistanceUser::where('user_id',$user->id)->where('topic_id',$topic_id)->whereNotNull('signature')->first();
            $required_signature = boolval($hasSignature);
        }
        return ['menus'=> $menus , 'required_signature'=>$required_signature];
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
        $assistance = TopicAssistanceUser::userIsPresent($user->id,$topic_id);
        //Si no tiene la asistencia en late o absent no debería acceder ni a los recursos multimedia ni a la evaluación  ni a la encuesta
        if(!$assistance){
            return false;          
        }
        switch ($type) {
            case 'assistance':
                $resource = $assistance;
                break;
            case 'multimedias':
                $resource = Topic::select('id','name','assessable','type_evaluation_id','modality_in_person_properties')
                    ->where('id',$topic_id)
                    ->first()?->isAccessibleMultimedia();
                break;
            case 'evaluation':
                $resource = Topic::select('modality_in_person_properties')
                    ->where('id',$topic_id)
                    ->first()?->isAccessibleEvaluation();
                break;
            case 'poll':
                $resource =  Topic::select('poll_id','modality_in_person_properties')
                ->where('id',$topic_id)
                ->first()?->isAccessiblePoll();
            break;
        }
        $is_accessible = boolval($resource);
        return ['is_accessible'=>$is_accessible];
    }
    protected function startPoll($topic_id){
        $topic = Topic::select('id','poll_id','modality_in_person_properties')
                    ->where('id',$topic_id)
                    ->first();
        if(!$topic->poll_id){
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
        $topic = Topic::select('id','poll_id','modality_in_person_properties')
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
    //SUBFUNCTIONS
    private function modifyMenus($menus,$code,$action='change_status'){
        $pollIndex = array_search($code, array_column($menus, 'code'));
        if ($pollIndex !== false) {
            switch ($action) {
                case 'change_status':
                    $menus[$pollIndex]['show'] = false;
                    break;
                case 'unset':
                    unset($menus[$pollIndex]);
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
        $assigned_courses = $user->getCurrentCourses(withRelations: 'soft',only_ids_courses:true,modality_code:'in-person');
        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $query = Topic::select('id', 'name','course_id','modality_in_person_properties')
                    ->whereHas('course',function($q){
                        $q->where('active',1);
                    })
                    ->whereIn('course_id',$assigned_courses)
                    ->whereNotNull('modality_in_person_properties')
                    ->where('active',1);
        return [
            'count_today' => $query->where(DB::raw("modality_in_person_properties->'$.start_date'"), '=', $today)->count(),
            'count_scheduled' => $query->where(DB::raw("modality_in_person_properties->'$.start_date'"), '>=', $tomorrow)->count(),
            'count_finished' =>$query->where(DB::raw("modality_in_person_properties->'$.start_date'"), '<', $today)->count(),
        ];
    }
}
