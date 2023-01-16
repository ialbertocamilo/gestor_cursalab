<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Workspace;
use Illuminate\Console\Command;

class SetCourseCompatiblesFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:set-file-compatibles {workspace}';

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

        $courses = Course::whereRelation('workspaces', 'id', $workspace_id)->get();
        $modules = Workspace::where('parent_id', $workspace_id)->get();

        foreach ($courses as $course) {
            
            foreach ($modules as $module) {

                $course->name = str_replace('Capacitación Farmacias Peruanas' . ' - ', '', $course->name);
                $course->name = str_replace('Capacitación ' . $module->name . ' - ', '', $course->name);
                $course->name = str_replace($module->name . ' - ', '', $course->name);
            }
        }

        $rows = config('compatible_uc_courses');

        $bar = $this->output->createProgressBar(count($rows));

        // info($courses->pluck('name')->toArray());

        foreach ($rows as $row) {

            $courses_a = $courses->where('name', $row[0]);
            $courses_b = $courses->where('name', $row[1]);

            if ($courses_a && $courses_b) {

                foreach ($courses_a as $course_a) {
                    
                    $compatibles = $course_a->getCompatibilities();
                    $compatibles_id = $compatibles->pluck('id')->toArray();

                    foreach ($courses_b as $course_b) {
                        if (!in_array($course_b->id, $compatibles_id)) {
                            $compatibles_id[] = $course_b->id;
                        }
                    }
                    
                    Course::storeCompatibilityRequest($course_a, ['compatibilities' => $compatibles_id]);
                }

            } else {
                
                info('Cursos no encontrados:');

                if (!$course_a) info($row[0]);
                if (!$course_b) info($row[1]);
            }

            
            $bar->advance();
        }

        $bar->finish();
    }
}
