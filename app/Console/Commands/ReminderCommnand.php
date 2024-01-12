<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\Workspace;
use App\Models\SummaryCourse;
use App\Models\Mongo\EmailLog;
use Illuminate\Console\Command;

class ReminderCommnand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $workspaces_allow_reminders = Workspace::select('id')->whereNull('parent_id')->wherehas('functionalities',function($q){
            $q->where('code','reminder-course-inactivate');
        })->get()->pluck('id');
        //Enviar correo antes de 
        $after_days_inactivate_course = 4;
        $diff_day = Carbon::now()->addDays($after_days_inactivate_course)->toDateString();
        $coursesToSentEmail = Course::with(['workspaces:id,name'])
                            ->whereHas('workspaces',function($q) use ($workspaces_allow_reminders){
                                $q->whereIn('id',$workspaces_allow_reminders);
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

        foreach ($coursesToSentEmail as $course) {
            $course->load('segments');
            /* Obtener los usuarios segmentados que tengan correo*/ 
            $users_segmented = $course->usersSegmented($course->segments,'get_records',$filters,['email','subworkspace_id']);
            // $users_id_chunked = array_chunk($users_segmented,500);
            $users_id_chunked = $users_segmented->chunk(500)->all();
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
                        ];
                    }
                }
            }
        }
        if(count($users_to_send_email)){
            $users_to_send_email = $this->formatToSaveEmail(
                                        users:$users_to_send_email,
                                        subject:'Recordatorio de inactivación',
                                        template:'emails.reminder_inactivate_course',
                                        type_email:'reminder_inactivate_course',
                                    ); 
            $chunk_insert = array_chunk($users_to_send_email,10);
            foreach ($chunk_insert as $key => $insert) {
                EmailLog::insert($insert);
            }
        }
    }
    private function formatToSaveEmail($users,$subject,$template,$type_email,$status='programmed'){
        //programar emails
        $init_time = Carbon::today()->startOfDay()->addHours(6); // 6am
        $total_minutes = 720; // 6am to 6pm in minutes
        $count_users = count($users); 
        $bloks_to_sent = (int) round($count_users /match (true) {
            $count_users < 20 =>  $count_users,
            $count_users > 20 && $count_users <= 100 => 10,
            $count_users > 100 && $count_users <= 500 => 20,
            $count_users > 500 => 40,
            default => 20,
        });
        $range_in_minutes = (int) round($total_minutes/$bloks_to_sent);
        $chunk_users = array_chunk($users,$bloks_to_sent);
        $_users=[];
        $firstElement = true;
        $web_url = config('app.web_url');
        foreach ($chunk_users as $chunk_user) {
            //first block send at 6:00 am
            $add_minutes = $firstElement ? 0 : $range_in_minutes;
            $init_time = $init_time->addMinutes($add_minutes);
            foreach ($chunk_user as $user) {
                $_users[]=[
                    'user_email' => $user['email'],
                    'email_data' => [
                        'subject' => $subject,
                        'courses' => $user['courses'],
                        'user_id' => $user['user_id'],
                        'workspace_id' => $user['workspace_id'],
                        'subworkspace_id' => $user['workspace_id'],
                        'web_url'=> $web_url
                    ],
                    'time' => $init_time->format('Y-m-d H:i'),
                    'email_subject' =>  $subject,
                    'template' => $template,
                    'type_email'=> $type_email,
                    'status' => $status
                ];
            }
            $firstElement = false;
        }
        return $_users;
    }
}
