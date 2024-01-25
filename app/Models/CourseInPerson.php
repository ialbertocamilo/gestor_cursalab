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
                        $finish_datetime = Carbon::parse($topic->modality_in_person_properties->finish_date.' '.$topic->modality_in_person_properties->finish_time);
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
        $users_segmented = $course->usersSegmented($course->segments,'get_records',$filters,['id','name','lastname','document']);
        return $usersSegmented;
    }

}
