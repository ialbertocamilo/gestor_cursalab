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
        $users = User::whereNotNull('summary_user_update')->orWhereNotNull('summary_course_update')->get();
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        foreach ($users as $key => $user) {

            // $user = User::find($user->id);

            if ($user->summary_course_update) {

                $course_ids = explode(',', $user->summary_course_data);

                $courses = Course::whereIn('id', $course_ids)->get();

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
                // 'required_update_at' => NULL,
                'last_summary_updated_at' => now(),
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
