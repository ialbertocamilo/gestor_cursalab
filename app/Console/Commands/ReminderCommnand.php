<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Course;
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
        
        //Enviar correo antes de 
        $after_days_inactivate_course = 3;
        $diff_day = Carbon::now()->addDays($after_days_inactivate_course)->toDateString();
        $coursesToSentEmail = Course::select('id','name')->where('active',1)->whereDate('deactivate_at', '=',$diff_day )->get();
        $users_to_send_email = [];
        $filters = [
            ['statement'=>'whereNotNull','field'=>'email'],
            ['statement'=>'where','field'=>'email','operator'=>'<>','value'=>' ']
        ];

        foreach ($coursesToSentEmail as $course) {
            $course->load('segments');
            /* Obtener los usuarios segmentados que tengan correo*/ 
            $users_segmented = $course->usersSegmented($course->segments, $type = 'users_id',$filters);
            $users_id_chunked = array_chunk($users_segmented,500);
            foreach ($users_id_chunked as $users_id) {

                $users_to_completed_course = SummaryCourse::select('user_id')
                                            ->whereHas('user',function($q){
                                                $q->whereNotNull('email');
                                            })
                                            ->with('user:id,email')
                                            ->whereIn('user_id',$users_id)
                                            ->where('course_id',$course->id)
                                            ->where('advanced_percentage','100')
                                            ->pluck('user_id')->toArray();

                $diff_users_id = array_diff($users_id,$users_to_completed_course);
                
                foreach ($diff_users_id as $diff_user_id) {
                    $key = array_search($diff_user_id, array_column($users_to_send_email, 'user_id'));
                    if ($key !== false) {
                        $users_to_send_email[$key]['courses'][] = $course;
                    } else {
                        $users_to_send_email[] = [
                            'user_id' => $diff_user_id,
                            'email' => $diff_user_id->user->email,
                            'courses' => [$course],
                        ];
                    }
                }
            }
        }
        
        $users_to_send_email = $this->formatToSaveEmail(
                                    users:$users_to_send_email,
                                    subject:'Recordatorio de inactivación',
                                    template:'emails.reminder_inactivate_course',
                                    type_email:'reminder_inactivate_course',
                                ); 
        $chunk_insert = array_chunk($users_to_send_email);
        
        foreach ($chunk_insert as $key => $insert) {
            EmailLog::insert($insert);
            dd('insert');
        }
    }
    private function formatToSaveEmail($users,$subject,$template,$type_email,$status='programmed'){
        //programar emails
        $init_time = Carbon::today()->startOfDay()->addHours(6); // 6am
        $total_minutes = 720; // 6am to 6pm in minutes
        $count_users = count($users); 
        $bloks_to_sent = (int) round($count_users /match (true) {
            $count_users > 0 && $count_users <= 100 => 10,
            $count_users > 100 && $count_users <= 500 => 20,
            $count_users > 500 => 40,
            default => 20,
        });
        $range_in_minutes = (int) round($total_minutes/$bloks_to_sent);
        // dd($init_time,$bloks_to_sent,$range_in_minutes,$count_users);
        $chunk_users = array_chunk($users,$bloks_to_sent);
        $_users=[];
        $firstElement = true;
        foreach ($chunk_users as $chunk_user) {
            //first block send at 6:00 am
            $add_minutes = $firstElement ? 0 : $range_in_minutes;
            $init_time = $init_time->addMinutes($add_minutes);
            foreach ($chunk_user as $user) {
                $_user[]=[
                    'user_email' => $user['email'],
                    'email_data' => [
                        'subject' => $subject,
                        'courses' => $user['courses'],
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
