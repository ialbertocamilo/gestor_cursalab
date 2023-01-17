<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Workspace;
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
                    ->get();

        $modules = Workspace::where('parent_id', $workspace_id)->get();

        foreach ($all as $course) {
            
            $string = str_replace('Capacitación Farmacias Peruanas' . ' - ', '', $course->name);

            foreach ($modules as $module) {
                $string = str_replace('Capacitación ' . $module->name . ' - ', '', $string);
                $string = str_replace($module->name . ' - ', '', $string);
            }
            
            $course->name = mb_strtolower(trim($string));
        }

        info($all->pluck('name')->toArray());

        $grouped = $all->groupBy('name');

        $bar = $this->output->createProgressBar($grouped->count());

        foreach ($grouped as $courses) {

            if ($courses->count() == 1) {

                $bar->advance();

                continue;
            }


            foreach ($courses as $course) {
                
                $data['compatibilities'] = $courses->where('id', '<>', $course->id)->pluck('id')->toArray();

                Course::storeCompatibilityRequest($course, $data);
            }

            // $course = $courses->shift();

            // $data['compatibilities'] = $courses->pluck('id')->toArray();

            // Course::storeCompatibilityRequest($course, $data);

            $bar->advance();
        }

        $bar->finish();
    }
}
