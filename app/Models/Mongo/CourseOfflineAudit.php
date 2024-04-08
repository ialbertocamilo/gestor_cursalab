<?php

namespace App\Models\Mongo;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\MediaTema;
use Jenssegers\Mongodb\Eloquent\Model;

class CourseOfflineAudit extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'course_offline_audits';
    
    protected function saveAudit($data){
        $user = auth()->user();
        $course = Course::select('id','is_offline')->where('id',$data['course_id'])->first();
        $course_offline_data = MediaTema::dataSizeCourse(course:$course,has_offline:$course->is_offline);
        $startsAt = Carbon::parse($data['starts_at']);
        $finhesAt = Carbon::parse($data['finishes_at']);
        // Calcular la diferencia en minutos
        $diffInSeconds = $startsAt->diffInSeconds($finhesAt);
        self::insert([
            'user'=>$user->id,
            'course_id'=>$data['course_id'],
            'diff_in_seconds'=>$diffInSeconds,
            'course_data_offline' => $course_offline_data,
            'type'=>$data['type'],
            'starts_at' =>$data['starts_at'],
            'finishes_at' =>$data['finishes_at'],
        ]);
    }

}
