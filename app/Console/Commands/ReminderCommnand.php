<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\SummaryCourse;
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
        $diff_day = Carbon::now()->addDays(4)->toDateString();
        $coursesToSentEmail = Course::select('id','name')->where('active',1)->whereDate('deactivate_at', '=',$diff_day )->get();
        $users_to_send_email = [];
        $filters = [
            ['statement'=>'whereNotNull','field'=>'email'],
            ['statement'=>'where','field'=>'email','operator'=>'<>','value'=>' '],
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
                                            ->whereIn('user_id',$users_id)
                                            ->where('course_id',$course->id)
                                            ->where('advanced_percentage','100')
                                            ->pluck('user_id')->toArray();
                $diff_users_id = array_diff($users_id,$users_to_completed_course);
                foreach ($diff_users_id as $diff_user_id) {
                    $key = array_search($diff_user_id, array_column($users_to_send_email, 'user_id'));
                    info($key);
                    if ($key !== false) {
                        dd($users_to_send_email);
                        $users_to_send_email[$key]['courses_id'][] = $course->id;
                    } else {
                        $users_to_send_email[] = [
                            'user_id' => $diff_user_id,
                            'courses_id' => [$course->id]
                        ];
                    }
                }
            }
        }
        info($users_to_send_email);
        dd($users_to_send_email);
        dd($coursesToSentEmail);
    }
}
