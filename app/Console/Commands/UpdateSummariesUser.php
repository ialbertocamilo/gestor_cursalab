<?php

namespace App\Console\Commands;

use App\Models\SummaryCourse;
use App\Models\SummaryUser;
use Illuminate\Console\Command;

class UpdateSummariesUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary-user:update {documents?} {--without-courses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecutar actulizacion a todos los usuarios que tengan summary_user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());
        $documents = $this->argument("documents");
        $without_courses = $this->option("without-courses");
        $summary_users = SummaryUser::when($documents, function ($q) use($documents){
            $q->whereHas('user',function($q2)use($documents){
               $q2->whereIn('document',explode(',',$documents));
                // $q2->whereIn('subworkspace_id',[27,29])->where('active',1);
        });
        })->with('user')->get();
//        $summary_users = SummaryUser::with('user')
//            ->where('user_id', 27660)->get();
        $count_summaries = $summary_users->count();

        $bar = $this->output->createProgressBar($count_summaries);
        $bar->start();

        foreach ($summary_users as $summary_user){

            $user = $summary_user->user;

            if (!$without_courses) {

                $courses = $user->getCurrentCourses();
                // $summaries_courses = SummaryCourse::withWhereHas('course')->where('user_id', $user->id)->get();

                foreach ($courses as $course){
                    // SummaryCourse::getCurrentRowOrCreate($course, $user);
                    SummaryCourse::updateUserData($course, $user, false, false);
                }
            }

            SummaryUser::updateUserData($summary_user->user, false);

            $bar->advance();
        }
        $bar->finish();


        $this->info("\n Fin: " . now());
        info(" \n Fin: " . now());
    }
}
