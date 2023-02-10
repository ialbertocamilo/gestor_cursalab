<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\Workspace;
use Illuminate\Console\Command;

class SetSchoolSubworkspace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schools:set-default-subworkspace {workspace}';

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

        $all = School::whereRelation('workspaces', 'id', $workspace_id)
                    ->get();

        $modules = Workspace::where('parent_id', $workspace_id)->get();

        $bar = $this->output->createProgressBar($all->count());

        $subworkspaces = [
            'IK' => 27,
            'MF' => 26,
            'FP' => 28,
        ];

        $shorts = [];

        foreach ($all as $school) {
            
            $string = str_replace('Capacitación Farmacias Peruanas - ', 'FP - ', $school->name);
            $string = str_replace('Capacitación Mifarma - ', 'MF - ', $string);
            $string = str_replace('Capacitación Inkafarma - ', 'IK - ', $string);

            $school->update(['name' => $string]);

            $parts = explode(' - ', $string);

            if (count($parts) > 1) {

                $code = $parts[0];
                $shorts[$code] = $code;

                $module_id = $subworkspaces[$code] ?? null;

                if (!$module_id) {
                    info($code . ' not found');
                    continue;
                }

                $module = $modules->where('id', $module_id)->first();

                $module->schools()->attach($school);

            } else {

                foreach ($modules as $key => $module) {
                    $module->schools()->attach($school);
                }
            }

            $bar->advance();
        }

        info($shorts);

        $bar->finish();
    }
}
