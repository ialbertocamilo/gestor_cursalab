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
        $topic = Topic::select('id','name','course_id','assessable','type_evaluation_id')
                    ->where('course_id',$course_id)
                    ->where('id',$topic_id)
                    ->with(['medias','course:id,mod_evaluaciones','evaluation_type:id,code'])
                    ->first();
        $statuses_topic = Taxonomy::where('type', 'user-status')->where('group', 'topic')->where('type', 'user-status')
                        ->whereIn('code', ['aprobado', 'realizado', 'revisado'])->get()
                            ->pluck('id')->toArray();
        $summary_topic =  SummaryTopic::where('topic_id',$topic_id)->where('user_id',$user->id)->first();
        $max_attempts = $topic->course->mod_evaluaciones['nro_intentos'];
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


        $topic_status = $topic->getTopicStatusByUser($topic, $user, $max_attempts,$statuses_topic);
        
        $topics_data = [
            'id' => $topic->id,
            'nombre' => $topic->name,
            'imagen' => $topic->imagen,
            'contenido' => $topic->content,
            'media' => $media_embed,
            'media_not_embed' => $media_not_embed,
            'media_topic_progress'=>$media_topic_progress,
            'evaluable' => $topic->assessable ? 'si' : 'no',
            'tipo_ev' => $topic->evaluation_type->code ?? NULL,
            'nota' => $topic_status['grade'],
            'nota_sistema_nombre' => $topic_status['system_grade_name'] ?? NULL,
            'nota_sistema_valor' => $topic_status['system_grade_value'] ?? NULL,
            'disponible' => $topic_status['available'],
            'intentos_restantes' => $topic_status['remaining_attempts'],
            'estado_tema' => $topic_status['status'],
            //                    'estado_tema_str' => $topic_status['status'],
            'estado_tema_str' => $topic_status_arr[$topic_status['status']],
            'mod_evaluaciones' => $topic->course->getModEvaluacionesConverted($topic),
            'review_all_duration_media' => boolval($topic->review_all_duration_media)
        ];
        return $topics_data;
    }
}
