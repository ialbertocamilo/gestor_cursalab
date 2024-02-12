<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Course;
use App\Models\Workspace;
use App\Models\SummaryCourse;
use App\Models\Mongo\EmailLog;
use Illuminate\Console\Command;

class ReminderInactivateCourseCommnand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:reminder-inactivate-course';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command scheduled emails with course inactive in 4 days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->reminderInactivateCourse();
    }

    private function reminderInactivateCourse(){
        // $workspaces_allow_reminders = Workspace::select('id')->whereNull('parent_id')->wherehas('functionalities',function($q){
        //     $q->where('code','reminder-course');
        // })->get()->pluck('id');
        //Enviar correo antes de 
        Workspace::select('id','parent_id','reminders_configuration')->whereNull('parent_id')->wherehas('functionalities',function($q){
            $q->where('code','reminder-course');
        })->whereNotNull('reminders_configuration')->with(['subworkspaces'])->get()->map(function($workspace){
            $after_days_inactivate_course = 4;
            $diff_day = Carbon::now()->addDays($after_days_inactivate_course)->toDateString();
            $coursesToSentEmail = Course::with(['workspaces:id,name,logo'])
                                ->whereHas('workspaces',function($q) use ($workspace){
                                    $q->where('id',$workspace->id);
                                })
                                ->select('id','name')
                                ->where('active',1)
                                ->whereDate('deactivate_at', '=',$diff_day)
                                ->get();
            
            $users_to_send_email = [];
            $filters = [
                ['statement'=>'whereNotNull','field'=>'email'],
                ['statement'=>'where','field'=>'email','operator'=>'<>','value'=>' ']
            ];
            $reminders_configuration = $workspace->reminders_configuration;
            
            foreach ($coursesToSentEmail as $course) {
                $course->load('segments');
                /* Obtener los usuarios segmentados que tengan correo*/ 
                $users_segmented = $course->usersSegmented($course->segments,'get_records',$filters,['email','subworkspace_id']);
                
                // $users_id_chunked = array_chunk($users_segmented,500);
                $users_id_chunked = $users_segmented->chunk(200)->all();
                $_course =  [ 'id' => $course->id,'name' => $course->name];
                
                foreach ($users_id_chunked as $_users) {
                    $users_to_completed_course = SummaryCourse::select('user_id')
                                                ->whereHas('user',function($q){
                                                    $q->whereNotNull('email');
                                                })
                                                ->with('user:id,email')
                                                ->whereIn('user_id',$_users->pluck('id'))
                                                ->where('course_id',$course->id)
                                                ->where('advanced_percentage','100')
                                                ->pluck('user_id')->toArray();
    
                    $user_not_complete_course = $_users->whereNotIn('id',$users_to_completed_course)->all();
                    foreach ($user_not_complete_course as $diff_user_id) {
                        
                        $key = array_search($diff_user_id->id, array_column($users_to_send_email, 'user_id'));
                        if ($key !== false) {
                            $users_to_send_email[$key]['courses'][] = $_course;
                        } else {
                            $users_to_send_email[] = [
                                'user_id' => $diff_user_id->id,
                                'email' => $diff_user_id->email,
                                'subworkspace_id' => $diff_user_id->subworkspace_id,
                                'workspace_id' => $course->workspaces->first()->id,
                                'courses' => [$_course],
                                'image_logo'=> get_media_url($course->workspaces->first()->logo,'s3')
                            ];
                        }
                    }
                }
            }
           
            if(count($users_to_send_email)){
                
                $users_to_send_email = EmailLog::formatToSaveEmail(
                                            users:$users_to_send_email,
                                            subject:'Recordatorio de inactivación',
                                            template:'emails.reminder_inactivate_course',
                                            type_email:'reminder_inactivate_course',
                                            reminders_configuration:$reminders_configuration,
                                            status:'programmed',
                                        ); 
                
                $chunk_insert = array_chunk($users_to_send_email,10);
                foreach ($chunk_insert as $key => $insert) {
                    EmailLog::insert($insert);
                }
            }
        });
    }
}
