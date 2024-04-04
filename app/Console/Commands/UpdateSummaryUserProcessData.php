<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Process;
use App\Models\Taxonomy;
use Illuminate\Console\Command;

class UpdateSummaryUserProcessData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:summary-user-process {subworkspace_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza la fecha en la que se añadió un usuario al proceso inductivo.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return $this->summaryUsers();
    }

    private function summaryUsers(){
        $type_user_id = Taxonomy::getFirstData('user', 'type', 'employee_onboarding')->id;
        $subworkspace_id = $this->argument('subworkspace_id');

        $users = User::disableCache()->select('id','subworkspace_id')
            ->where('type_id',$type_user_id)
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
        $bar->start();
        foreach ($users as $user) {
            try {
                Process::syncEnrolledDateProcess($user);
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
               $user->update([
                    'is_updating' => 3,
                ]);
            }
            $bar->advance();
        }
    }
}
