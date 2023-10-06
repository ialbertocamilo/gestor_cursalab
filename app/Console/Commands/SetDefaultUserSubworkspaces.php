<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Workspace;
use App\Models\User;

class SetDefaultUserSubworkspaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workspaces:set-default-user-subworkspaces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::whereRelation('roles', function ($query) {
                    $query->where('name', '<>', 'super-user');
                })->get();

        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {

            $roles = $user->assigned_roles()->get();

            $subworkspaces = Workspace::whereIn('parent_id', $roles->pluck('scope'))
                            ->get();
            // info('workspaces');
            // info($workspaces);

            $subworkspaces_id = $subworkspaces->pluck('id')->toArray();
            
            info('subworkspaces_id');
            info($subworkspaces_id);

            $user->subworkspaces()->sync($subworkspaces_id);

            $bar->advance();
        }

        $bar->finish();

        return Command::SUCCESS;
    }
}
