<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\SummaryCourse;
use App\Models\SummaryUser;
use App\Models\Course;
use App\Models\User;

class UpdateSummariesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:update-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar datos de resumenes de usuarios y cursos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::disableCache()->select('id')
        ->where('is_updating',0)
        ->where(function($q){
            $q->whereNotNull('summary_user_update')
            ->orWhereNotNull('summary_course_update');
        })
        ->limit(3000)
        ->get();

        User::whereIn('id',$users->pluck('id'))->update([
            'is_updating'=>1
        ]);

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        foreach ($users as $key => $user) {

            $user = User::disableCache()->find($user->id);

            if ($user->summary_course_update) {

                $course_ids = explode(',', $user->summary_course_data);

                $courses = Course::disableCache()->whereIn('id', $course_ids)->get();

                foreach ($courses as $course) {
                    SummaryCourse::getCurrentRowOrCreate($course, $user);
                    SummaryCourse::updateUserData($course, $user, false);
                }
            } 

            if ($user->summary_user_update) {
                SummaryUser::getCurrentRowOrCreate($user, $user);
                SummaryUser::updateUserData($user);
            }

            $user->update([
                'summary_user_update' => NULL,
                'summary_course_update' => NULL,
                'summary_course_data' => NULL,
                'is_updating' => 0,
                // 'required_update_at' => NULL,
                'last_summary_updated_at' => now(),
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
