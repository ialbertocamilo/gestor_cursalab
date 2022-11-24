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
    protected $signature = 'summary-user:update {documents?}';

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
        $summary_users = SummaryUser::when($documents, function ($q) use($documents){
            $q->whereHas('user',function($q2)use($documents){
               $q2->whereIn('document',explode(',',$documents));
            });
        })->with('user')->get();
//        $summary_users = SummaryUser::with('user')
//            ->where('user_id', 27660)->get();
        $count_summaries = $summary_users->count();

        $bar = $this->output->createProgressBar($count_summaries);
        $bar->start();


        foreach ($summary_users as $summary_user){

            $user = $summary_user->user;

            $summaries_courses = SummaryCourse::withWhereHas('course')->where('user_id', $user->id)->get();

            foreach ($summaries_courses as $summary_course)
                SummaryCourse::getCurrentRowOrCreate($summary_course->course, $user);
                SummaryCourse::updateUserData($summary_course->course, $user, update_attempts: false);

            SummaryUser::updateUserData($summary_user->user);

            $bar->advance();
        }
        $bar->finish();


        $this->info("\n Fin: " . now());
        info(" \n Fin: " . now());
    }
}
