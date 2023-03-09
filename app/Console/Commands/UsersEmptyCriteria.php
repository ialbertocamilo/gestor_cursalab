<?php

namespace App\Console\Commands;

use App\Mail\EmailTemplate;
use App\Models\CriterionValue;
use App\Models\SegmentValue;
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
        $criteriaIds = SegmentValue::loadWorkspaceSegmentationCriteriaIds(25);
        $users =  CriterionValue::findUsersWithIncompleteCriteriaValues(25, $criteriaIds);
        $data = [
          'subject' => 'This is a test',
          'usersCount' => count($users)
        ];

        Mail::to('elvis@cursalab.io')
            ->send(new EmailTemplate('emails.empty_criteria_notification', $data));

        return Command::SUCCESS;
    }
}
