<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\TopicAssistanceUser;

class CourseInPersonDuplicateAssistanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'duplicate:assistance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duplicate assistances by session in courses in person';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->duplicateAssistance();
    }

    private function duplicateAssistance(){
        $today = now()->format('Y-m-d');
        $courses = Course::select('id','modality_in_person_properties')
                    ->whereHas('modality',function($q){
                        $q->where('code','in-person');
                    })
                    ->with('topics:id,modality_in_person_properties,course_id')
                    ->where(DB::raw("modality_in_person_properties->'$.assistance_type'"), '=', 'assistance-by-day')
                    ->wherehas('topics',function($q) use ($today){
                        $q->where('active',1)->where(DB::raw("modality_in_person_properties->'$.start_date'"), '=', $today);
                    })
                    ->whereHas('topics', function ($q) {
                        $q->where('active', 1);
                    }, '>', 1)
                    ->where('active',1)
                    ->get();
        foreach ($courses as $course) {
            $first_topic = $course->topics->map(function($topic) {
                                $topic->datetime_init = $topic->modality_in_person_properties->start_date . ' ' . $topic->modality_in_person_properties->start_time;
                                return $topic;
                            })->sortBy('datetime_init')->first();
            $assistance_first_topic = TopicAssistanceUser::select('user_id','topic_id','status_id','date_assistance','signature')->where('topic_id',$first_topic->id)->get();
            if(count($assistance_first_topic)==0){
                continue;
            }
            $duplicate_topics = $course->topics->where('id','<>',$first_topic->id)->all();
            foreach ($duplicate_topics as $topic) {
                $has_assistance =  TopicAssistanceUser::select('user_id')->where('topic_id',$topic->id)->get();
                $duplicate_assistances = count($has_assistance) > 0 
                                        ? $assistance_first_topic->whereNotIn('user_id',$has_assistance->pluck('user_id'))
                                        : $assistance_first_topic;
                $duplicate_assistances = $duplicate_assistances->map(function($da) use ($topic){
                    $da->topic_id = $topic->id;
                    return $da;
                })->toArray();    
                TopicAssistanceUser::insert($duplicate_assistances);   
            }
        }
    }
}
