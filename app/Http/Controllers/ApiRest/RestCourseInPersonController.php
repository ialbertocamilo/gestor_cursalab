<?php

namespace App\Http\Controllers\ApiRest;

use Carbon\Carbon;
use App\Models\Topic;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RestCourseInPersonController extends Controller
{
    public function listUserCourses(Request $request){
        $code = $request->code;
        if(!$code){
            return $this->error('Es necesario el código.');
        }
        $user = auth()->user();
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
        $topics = Topic::with(['course:id,modality_in_person_properties'])->select('id', 'name','course_id','modality_in_person_properties')
                    ->whereIn('course_id',$assigned_courses)
                    ->whereNotNull('modality_in_person_properties')
                    ->where(DB::raw("modality_in_person_properties->'$.start_date'"), $operator, $date)
                    ->get()->map(function($topic){
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
                            'reference' => $topic->modality_in_person_properties->reference,
                            'location'=> $topic->modality_in_person_properties->ubicacion,
                            'show_medias_since_start_course'=>  $topic->modality_in_person_properties->show_medias_since_start_course,
                            'duration' => $duration,
                            'url_maps' => $topic->modality_in_person_properties->url,
                            'format_day' => fechaCastellanoV2($format_day),
                            'required_signature'=>$topic->course->modality_in_person_properties->required_signature
                        ];
                    });

        return $this->success($topics);
    }
}
