<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class SetCourseCompatibles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:set-default-compatibles {workspace}';

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
        $workspace_id = $this->argument('workspace');

        $all = Course::whereRelation('workspaces', 'id', $workspace_id)
                    // ->where()
                    ->get();

        $grouped = $all->groupBy('name');

        $bar = $this->output->createProgressBar($grouped->count());

        foreach ($grouped as $courses) {
           
            if (!$courses->count()) {

                $bar->advance();

                continue;
            }

            $course = $courses->first();

            $courses_id = $courses->pluck('id')->toArray();

            Course::storeCompatibilityRequest($course, $courses_id);

            $bar->advance();

        }

        $bar->finish();
    }
}
