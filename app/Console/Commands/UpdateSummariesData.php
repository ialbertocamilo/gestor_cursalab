<?php

namespace App\Console\Commands;

use App\Models\User;

use App\Models\Course;
use App\Models\Taxonomy;
use App\Models\SummaryUser;
use App\Models\SummaryCourse;
use Illuminate\Console\Command;

class UpdateSummariesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:update-data {subworkspace_id?}';

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
        //Actualizar summary courses que se quedaron con encuesta pendiente cuando ya no existe la encuesta:
        $this->updateStatusPendingPoll();
        $subworkspace_id = $this->argument('subworkspace_id');
        $users = User::disableCache()->select('id')
            ->where('is_updating', 0)
            ->where(function ($q) {
                $q->whereNotNull('summary_user_update')
                    ->orWhereNotNull('summary_course_update');
            })
            ->when($subworkspace_id, function($q) use ($subworkspace_id){
                $q->where('subworkspace_id', $subworkspace_id);
            })
            ->whereNotNull('subworkspace_id')
            ->limit(2500)
            ->get();
            
        User::whereIn('id', $users->pluck('id'))->update([
            'is_updating' => 1
        ]);

        $bar = $this->output->createProgressBar($users->count());
//        $this->info("INICIO : " . now());
//        $this->line("");
        $bar->start();
        info('Init command v1');
        foreach ($users as $key => $user) {
            try {
                $user = User::disableCache()->find($user->id);
                if ($user->summary_course_update) {
    
                    $course_ids = explode(',', $user->summary_course_data);
    
                    $courses = Course::disableCache()->whereIn('id', $course_ids)->get();
    
    //                $now = now();
    //                $this->newLine();
    //                $implode = implode(',', $courses->pluck('id')->toArray());
    //                $this->line("[{$now}] Updating courses => {$implode}");
                    foreach ($courses as $course) {
    //                    $now = now();
    //                    $this->line("[{$now}] Updating course => $course->name");
                        // SummaryCourse::getCurrentRowOrCreate($course, $user);
                        SummaryCourse::updateUserData($course, $user, false,false);
                    }
                }
    
                if ($user->summary_user_update) {
    //                $now = now();
    //                $this->line("[{$now}] [getCurrentRowOrCreate] Updating summary user => $user->document");
                    SummaryUser::getCurrentRowOrCreate($user, $user);
    
    //                $now = now();
    //                $this->line("[{$now}] [updateUserData] Updating summary user => $user->id - $user->document");
                    SummaryUser::updateUserData($user);
    
                }
                $user->update([
                    'summary_user_update' => NULL,
                    'summary_course_update' => NULL,
                    'summary_course_data' => NULL,
                    'is_updating' => 0,
                    // 'required_update_at' => NULL,
                    'last_summary_updated_at' => now()
                ]);
            } catch (\Throwable $ex) {
                info($ex);
                //El estado indica que el usuario tuvo un error en su actualización .. se coloca de esta manera para no interrumpir la actualización de los datos.
                $user->is_updating = 3;
                $user->save();
            }
            $bar->advance();
        }
        info('Finish command v1');
        $bar->finish();
//        $this->newLine(2);
//        $this->info("FIN : " . now());
    }

    public function updateStatusPendingPoll(){
        $users_id= collect();
        $taxonomy_enc_pend = Taxonomy::select('id')->where('group','course')->where('type','user-status')->where('code','enc_pend')->first();
        $summaries = SummaryCourse::doesntHave('course.polls')->with('course','user')->where('status_id',$taxonomy_enc_pend->id)->select('id','course_id','user_id')->get();
        $_bar = $this->output->createProgressBar($summaries->count());
        $_bar->start();
        foreach ($summaries as $summary) {
            try {
                SummaryCourse::updateUserData($summary->course, $summary->user, false);
                $users_id->push($summary->user_id);
            } catch (\Throwable $th) {
                info($summary);
            }
            $_bar->advance();
        }
        $_bar->finish();
        $summary_users = SummaryUser::whereIn('id',$users_id->unique())->with('user')->get();
        $count_summaries = $summary_users->count();
        $bar = $this->output->createProgressBar($count_summaries);
        $bar->start();
        foreach ($summary_users as $summary_user){
            try {
                SummaryUser::updateUserData($summary_user->user, false);
            } catch (\Throwable $th) {
                info($summary_user);
            }
            $bar->advance();
        }
        $_bar->finish();
    }
}
