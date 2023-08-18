<?php

namespace App\Console\Commands;

use App\Models\SummaryCourse;
use App\Models\SummaryUser;
use App\Models\User;
use Illuminate\Console\Command;

class restoreSummaryCourse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:calculate-summaries {documents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate summary courses and summary users from users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $documents = $this->argument('documents');
        $documents = explode(',', $documents);

        $this->restoreSummaryCourse($documents);

        return Command::SUCCESS;
    }

    public function restoreSummaryCourse($documents){

        User::select('id','subworkspace_id')
            ->whereIn('document', $documents)
            ->get()
            ->map(function($user){

                $courses = $user->getCurrentCourses();
                $_bar = $this->output->createProgressBar($courses->count());
                $_bar->start();
                foreach ($courses as $course) {
                    SummaryCourse::getCurrentRowOrCreate($course, $user);
                    SummaryCourse::updateUserData($course, $user, false,false);
                    $_bar->advance();
                }
                SummaryUser::updateUserData($user);
                $_bar->finish();
        });
    }
}
