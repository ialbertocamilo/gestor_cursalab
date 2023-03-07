<?php

namespace App\Console\Commands;

use App\Models\CriterionValue;
use App\Models\SegmentValue;
use Illuminate\Console\Command;

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

        dd(count($users));

        //$this->info();
        return Command::SUCCESS;
    }
}
