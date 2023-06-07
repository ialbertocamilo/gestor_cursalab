<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Course;
use App\Models\SummaryUser;
use App\Models\SummaryCourse;
use Illuminate\Console\Command;

class UpdateSummariesDataV2 extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:update-data-v2';

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
            ->where('is_updating', 0)
            ->where(function ($q) {
                $q->whereNotNull('summary_user_update')
                    ->orWhereNotNull('summary_course_update');
            })
            ->whereNotNull('subworkspace_id')
            ->limit(1000)
            ->get();

        User::whereIn('id', $users->pluck('id'))->update([
            'is_updating' => 1
        ]);

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        $chunkUserUpdate = [];
        $chunkSummaryCourseUpdate = [];
        $chunkSummaryUserUpdate = [];
        info('Init command v2');
        foreach ($users as $key => $user) {

            $user = User::disableCache()->find($user->id);
            if ($user->summary_course_update) {
                $course_ids = explode(',', $user->summary_course_data);
                $courses = Course::disableCache()->whereIn('id', $course_ids)->get();
                foreach ($courses as $course) {
                    $chunkSummaryCourseUpdate[] = SummaryCourse::updateUserData($course, $user, false,false,notSaveData:true);
                }
            }
            if ($user->summary_user_update) {
                SummaryUser::getCurrentRowOrCreate($user, $user);
                $chunkSummaryUserUpdate[] = SummaryUser::updateUserData($user,notSaveData:true);
            }
            $chunkUserUpdate[] = [
                    'id' => $user->id,
                    'summary_user_update' => NULL,
                    'summary_course_update' => NULL,
                    'summary_course_data' => NULL,
                    'is_updating' => 0,
                    'last_summary_updated_at' => now(),
            ];
            $bar->advance();
        }
        batch()->update(new SummaryCourse, $chunkSummaryCourseUpdate, 'id');
        batch()->update(new SummaryUser, $chunkSummaryUserUpdate, 'id');
        batch()->update(new User, $chunkUserUpdate, 'id');
        info('Finish command v2');
        $bar->finish();
//        $this->newLine(2);
//        $this->info("FIN : " . now());
    }
}
