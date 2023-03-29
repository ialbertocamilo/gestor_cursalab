<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\Workspace;
use Illuminate\Console\Command;

class RemoveSchoolCoursesPrefix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schools:remove-prefixes {workspace}';

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

        $all = School::with('courses')
                    ->whereRelation('subworkspaces.parent', 'id', $workspace_id)
                    ->get();

        $modules = Workspace::where('parent_id', $workspace_id)->get();

        $bar = $this->output->createProgressBar($all->count());

        $values = [
            [
                'current' => 'Capacitación Farmacias Peruanas - ',
                'new' => 'FP - ',
            ],
            [
                'current' => 'Capacitación Mifarma - ', 
                'new' => 'MF - ',
            ],
            [
                'current' => 'Capacitación Inkafarma - ',
                'new' => 'IK - ',
            ],
        ];

        $shorts = [];

        foreach ($all as $school) {
            
            $s_name = $school->name;

            foreach ($values as $key => $value) {
                $s_name = str_replace($value['current'], $value['new'], $s_name);
            }
            
            $school->update(['name' => $s_name]);

            foreach ($school->courses as $key => $course) {
                 $c_name = $course->name;

                foreach ($values as $key => $value) {
                    $c_name = str_replace($value['current'], $value['new'], $c_name);
                }
                
                $course->update(['name' => $c_name]);
            }

            $bar->advance();
        }

        $bar->finish();
    }
}
