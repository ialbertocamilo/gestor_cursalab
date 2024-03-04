<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Mongo\EmailLog;
use Illuminate\Console\Command;

class ReminderProgressCourseCommnand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:reminder-progress-course';

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
        $this->reminderCourseProgress();
    }

    public function reminderCourseProgress(){
        // 7 days before current time.
        $subworkspaces_allow_reminders = Workspace::select('id','parent_id','reminders_configuration')->whereNull('parent_id')->wherehas('functionalities',function($q){
            $q->where('code','reminder-course');
        })->whereNotNull('reminders_configuration')->with(['subworkspaces'])->get()->map(function($workspace){
            $subworkspaces_id = $workspace->subworkspaces->pluck('id');
            $reminders_configuration = $workspace->reminders_configuration;
            $init_time = Carbon::today()->subDays(7)->startOfDay()->format('Y-m-d H:i');
            if(count($subworkspaces_id) == 0){
                return;
            }
            $users_to_send_email = collect();
            User::select('id','subworkspace_id','email')
                    ->with(['subworkspace:id,parent_id','subworkspace.parent:id,logo'])
                    ->where('active',1)
                    ->whereHas('summary', function ($q)  {
                        $q->where('advanced_percentage','<',100);
                    })->whereDate('last_login','<',$init_time)
                    ->whereNotNull('email')
                    ->whereIn('subworkspace_id',$subworkspaces_id)
                    ->whereNull('email_gestor')
                    ->chunkById(500, function ($users) use ($users_to_send_email){
                        $_bar = $this->output->createProgressBar(count($users));
                        $_bar->start();
                        foreach ($users as $key => $user) {
                            $user_courses = $user->getCurrentCourses(withRelations:'soft');
                            $filteredCourses = $user_courses->filter(function ($course) {
                                return count($course->summaries) == 0 || (count($course->summaries) > 0 && $course->summaries[0]->advanced_percentage < 100);
                            })->map( fn ($c) =>  [ 'id' => $c->id,'name' => $c->name])->toArray();
                            if(count($filteredCourses)>0){
                                $users_to_send_email->push([
                                    'user_id' => $user->id,
                                    'email' => $user->email,
                                    'subworkspace_id' => $user->subworkspace_id,
                                    'workspace_id' => $user->subworkspace->parent->id,
                                    'courses' => $filteredCourses,
                                    'image_logo'=> get_media_url($user->subworkspace->parent->logo,'s3')
                                ]);
                            }
                            $_bar->advance();
                    }
                    $_bar->finish();
                }
            );
            if(count($users_to_send_email)){
                $users_to_send_email = $users_to_send_email->toArray();
                $users_to_send_email = EmailLog::formatToSaveEmail(
                                            users:$users_to_send_email,
                                            subject:'Recordatorio de progreso',
                                            template:'emails.reminder_progress_courses',
                                            type_email:'reminder_progress_courses',
                                            reminders_configuration:  $reminders_configuration
                                        ); 
                $chunk_insert = array_chunk($users_to_send_email,10);
                foreach ($chunk_insert as $key => $insert) {
                    EmailLog::insert($insert);
                }
            }
        });
    }
}
