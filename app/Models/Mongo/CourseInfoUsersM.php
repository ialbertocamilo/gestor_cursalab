<?php

namespace App\Models\Mongo;

use App\Models\Course;
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
        ])->withCount(['summaries'=>function ($q) {
            $q->whereRelation('user','active',ACTIVE)->where('passed', true);
        }])->chunkById(200, function ($courses) use ($date){
            foreach ($courses as $course) {
                $user_active_having_course = $course->usersSegmented($course->segments,'');
                self::insert([
                    'course_id'=>$course->id,
                    'total_user_completed' =>$course->summaries_count,
                    'total_user_assignment' => $user_active_having_course,
                    'generated_at' => $date
                ]);
            }
        });
    }
}
