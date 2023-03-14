<?php

namespace App\Console\Commands;

use App\Models\Mongo\ViewsM;
use Illuminate\Console\Command;
use App\Models\Mongo\CourseInfoUsersM;

class SaveDataMongo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mongo:save-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to save historic data in mongo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = date('Y-m-d');
        //save users topic'c views in mongodb 
        // $this->saveViewsTopics($date);
        //save course information about progress user in mongo
        $this->saveCourseInformationUser($date);
    }

    private function saveViewsTopics($date){
        ViewsM::saveViews($date);
    }

    private function saveCourseInformationUser($date){
        CourseInfoUsersM::saveCourseData($date);
    }
}
