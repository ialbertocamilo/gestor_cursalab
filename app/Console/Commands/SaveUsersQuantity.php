<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Workspace;
use App\Models\UserQuantity;
use Illuminate\Console\Command;

class SaveUsersQuantity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:users-quantity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commant to save users quantity by workspace and subworkspace';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->saveUsersQuantity();
    }

    private function saveUsersQuantity(){
        $workspaces = Workspace::with('subworkspaces')->WhereNull('parent_id')->get();
        $save_users_quantity=[];
        $date = now()->format('Y-m-d');
        foreach ($workspaces as $workspace) {
            $active_workspace_count = 0;
            $inactive_workspace_count = 0;
            $total_workspace_count = 0;
            foreach ($workspace->subworkspaces as $subworkspace) {
                $active_count = User::onlyClientUsers()
                    ->whereRelation('subworkspace', 'id', $subworkspace->id)
                    ->select('id', 'document', 'subworkspace_id', 'active')
                    ->where('active', ACTIVE)->count();

                $inactive_count = User::onlyClientUsers()
                    ->whereRelation('subworkspace', 'id', $subworkspace->id)
                    ->select('id', 'document', 'subworkspace_id', 'active')
                    ->where('active', INACTIVE)->count();

                $total_count = $active_count + $inactive_count;

                $active_workspace_count += $active_count;
                $inactive_workspace_count += $inactive_count;
                $total_workspace_count += $total_count;
                
                $save_users_quantity[] = [
                    'workspace_id'=>$workspace->id,
                    'subworkspace_id'=>$subworkspace->id,
                    'total'=>$total_count,
                    'active'=>$active_count,
                    'inactive'=>$inactive_count,
                    'date'=> $date
                ];
            }
            $save_users_quantity[] = [
                'workspace_id'=>$workspace->id,
                'subworkspace_id'=>null,
                'total'=>$total_workspace_count,
                'active'=>$active_workspace_count,
                'inactive'=>$inactive_workspace_count,
                'date'=> $date
            ];
        }
        $chunk_save_users_quantity = array_chunk($save_users_quantity,50);
        foreach ($chunk_save_users_quantity as $chunk) {
            UserQuantity::insert($chunk);
        }
    }
}
