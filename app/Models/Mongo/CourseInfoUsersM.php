<?php

namespace App\Models\Mongo;

use App\Models\Course;
use App\Models\SummaryCourse;
use Jenssegers\Mongodb\Eloquent\Model;

class CourseInfoUsersM extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'course_info_users';
    
    public static function saveCourseData($date){
        // $date = date('Y-m-d');
        Course::select('id')->with([
            'segments:id,model_id',
            'segments.values:id,segment_id,criterion_id,criterion_value_id,type_id,starts_at,finishes_at',
            'segments.values.criterion:id,field_id',
            'segments.values.criterion.field_type:id,code',
        ])
        // ->withCount(['summaries'=>function ($q) {
        //     $q->whereRelation('user','active',ACTIVE)->where('passed', true);
        // }])
        ->chunkById(200, function ($courses) use ($date){
            foreach ($courses as $course) {
                $users_having_course = $course->usersSegmented($course->segments,'get_records');
                $summaries_count = 0;
                if(count($users_having_course)>0){
                    $summaries_count = SummaryCourse::where('course_id',$course->id)
                                        ->whereIn('user_id',$users_having_course->pluck('id'))
                                        ->where('advanced_percentage', 100)
                                        ->count();
                }
                self::insert([
                    'course_id'=>$course->id,
                    'total_user_completed' =>$summaries_count,
                    'total_user_assignment' => $users_having_course->count(),
                    'generated_at' => $date
                ]);
            }
        });
    }
}
