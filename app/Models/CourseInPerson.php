<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseInPerson extends Model
{
    use HasFactory;

    protected function listCoursesByUser($user,$code){
        
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
                # code...
                break;
            default:
                $date = Carbon::today()->format('Y-m-d');
                $operator = '=';
                break;
        }
        $months = config('data.months');
        $days = config('data.days');
        return Topic::with(['course:id,modality_in_person_properties'])->select('id', 'name','course_id','modality_in_person_properties')
                    ->whereIn('course_id',$assigned_courses)
                    ->whereNotNull('modality_in_person_properties')
                    ->where('active',1)
                    ->where(DB::raw("modality_in_person_properties->'$.start_date'"), $operator, $date)
                    ->get()->map(function($topic) use ($user){
                        $format_day =  Carbon::parse($topic->modality_in_person_properties->start_date)->format('l, j \d\e F');
                        $start_datetime = Carbon::parse($topic->modality_in_person_properties->start_date.' '.$topic->modality_in_person_properties->start_time);
                        $finish_datetime = Carbon::parse($topic->modality_in_person_properties->start_date.' '.$topic->modality_in_person_properties->finish_time);
                        // $start_time = Carbon::parse($start_datetime);
                        // $finish_time = Carbon::parse($finish_datetime);
                        $diff_in_minutes = $start_datetime->diffInMinutes($finish_datetime);
                        if($diff_in_minutes < 1440){
                            $duration = $start_datetime->format('h:i a').' a '.$finish_datetime->format('h:i a');
                            $duration = $diff_in_minutes > 60 
                                        ? $duration .' ('.round($diff_in_minutes / 60,1).' horas)'
                                        : $duration .' ('.$diff_in_minutes.' minutos)';
                        }else{
                            $duration = $start_datetime->format('h:i a').' a '.$finish_datetime->format('h:i a');
                            $duration = $duration .' ('.round($diff_in_minutes / 1440,1).' días)';
                            $format_day =  Carbon::parse($topic->modality_in_person_properties->start_date)->format('l, j \d\e F');
                        }
                        return [
                            'name' => $topic->name,
                            'course_id' => $topic->course_id,
                            'topic_id' => $topic->id,
                            'reference' => $topic->modality_in_person_properties->reference,
                            'location'=> $topic->modality_in_person_properties->ubicacion,
                            'show_medias_since_start_course'=>  $topic->modality_in_person_properties->show_medias_since_start_course,
                            'is_host' => $user->id == $topic->modality_in_person_properties->host_id,
                            'duration' => $duration,
                            'url_maps' => $topic->modality_in_person_properties->url,
                            'format_day' => fechaCastellanoV2($format_day),
                            'required_signature'=>$topic->course->modality_in_person_properties->required_signature
                        ];
                    });
    }

    protected function listGuestsByCourse($course_id){
        $course = Course::with(['segments','segments.values'])->where('id',$course_id)->select('id')->first();
        $filters = [
            ['statement'=>'where','field'=>'active','operator'=>'=','value'=>1]
        ];
        $users_segmented = $course->usersSegmented($course->segments,'get_records',$filters,['id','name','lastname','surname','document']);
        foreach ($users_segmented as $user) {
            $user['status'] = 'attended';
        }
        return $users_segmented;
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

    protected function startEvaluation($data){
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
                    $message = 'La evaluación ya esta iniciadalizada';
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
                $message = 'Se inicio la evaluación';
            break;
            case 'start-before-finished-time':
                $topic->modality_in_person_properties->evaluation['status'] = 'extra-time';
                $topic->save();
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>$action];
                $message = 'Se activo manualmente la evaluación';
            break;
            case 'finish-early':
                $topic->modality_in_person_properties->evaluation['status'] = 'finished';
                $topic->save();
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>$action];
                $message = 'Se activo manualmente la evaluación';
            break;
            case 'finish-in-time':
                $topic->modality_in_person_properties->evaluation['status'] = 'finished';
                $topic->save();
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>'finish-in-time'];
                $message = 'Se activo manualmente la evaluación';
            break;
            case 'finish-manually':
                $modality_in_person_properties->evaluation['status'] = 'finished';
                $modality_in_person_properties->evaluation['historic_status'][] = ['time'=>$now,'action'=>$action];
                $topic->save();
                $message = 'Se terminó manualmente la evaluación';
            break;
        }
        return ['evaluation' => $topic->modality_in_person_properties->evaluation,'message'=>$message];
    }
}
