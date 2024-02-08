<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EntrenadorUsuario;
use App\Models\SummaryUserChecklist;

class UpdateChecklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:checklist-summary-user';

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
        $this->updateSummayUser();
    }

    private function updateSummayUser(){

        $trainer_users = EntrenadorUsuario::select('trainer_user.user_id')
            ->leftJoin('summary_user_checklist as suc', 'suc.user_id', '=', 'trainer_user.user_id')
            ->where(function ($query) {
                $query->whereNull('suc.id')
                    ->orWhere('suc.assigned', '>', 0);
            })
        ->where('trainer_user.active', 1)
        ->whereHas('user', function($q) {
            $q->where('active', 1);
        })
        ->with(['user'])
        ->get();

        $bar = $this->output->createProgressBar($trainer_users->count());

        foreach ($trainer_users as $trainer_user) {
            SummaryUserChecklist::updateUserData($trainer_user->user);
            $bar->advance();
        }
        $bar->finish();
    }
}
