<?php

namespace App\Console\Commands;

use App\Mail\EmailTemplate;
use App\Models\AssignedRole;
use App\Models\CriterionValue;
use App\Models\SegmentValue;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class UsersEmptyCriteria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'criteria:check-empty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds users with empty required (used in segmentation) criteria';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $workspaces = Workspace::whereNull('parent_id')->get();
        foreach ($workspaces as $workspace) {
            $this->checkAndNotifyEmptyCriteria($workspace->id);
        }

        return Command::SUCCESS;
    }

    /**
     * Count users with missing criteria values and notify
     * admin when this number is greater than zero
     *
     * @param $workspaceId
     * @return void
     */
    public function checkAndNotifyEmptyCriteria ($workspaceId): void
    {
        // Load workspace's criteria used in segmentation

        $criteriaIds = SegmentValue::loadWorkspaceSegmentationCriteriaIds($workspaceId);

        // Load users with missing criteria values

        $users =  CriterionValue::findUsersWithIncompleteCriteriaValues($workspaceId, $criteriaIds);
        $usersWithEmptyCriteria = count($users);

        if ($usersWithEmptyCriteria) {

            // Update users count in workspace

            $workspace = Workspace::find($workspaceId);
            $workspace->users_with_empty_criteria = $usersWithEmptyCriteria;
            $workspace->save();

            // Load admins email addresses

            $admins = AssignedRole::loadAllAdmins($workspaceId);
            $adminsEmails = $admins->pluck('email')->toArray();

            // Sends emails

            if (count($adminsEmails) > 0) {
                $data = [
                    'subject' => 'Reporte de criterios',
                    'reports-url' => env('APP_URL') . '/exportar/node',
                    'usersCount' => $usersWithEmptyCriteria,
                    'workspaceName' => $workspace->name
                ];

                Mail::to(['elvis@cursalab.io', 'kevin@cursalab.io'])
                    ->send(new EmailTemplate('emails.empty_criteria_notification', $data));
                exit;
            }
        }
    }
}
