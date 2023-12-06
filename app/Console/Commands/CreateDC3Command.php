<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Taxonomy;
use App\Models\Workspace;
use App\Models\SummaryCourse;
use Illuminate\Console\Command;

class CreateDC3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:dc3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create pdf dc3';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->createDC3Command();
    }

    private function createDC3Command(){
        $workspaces = Workspace::wherehas('functionalities',function($q){
            $q->where('code','dc3-dc4');
        })->select('id','dc3_configuration','name')->whereNotNull('dc3_configuration')->get();
        $user_status_approved = Taxonomy::where('group','course')->where('type','user-status')->where('code','aprobado')->select('id')->first();
        foreach ($workspaces as $workspace) {
            $courses = Course::whereHas('workspaces',function($q) use ($workspace){
                $q->where('id',$workspace->id);
            })->where('can_create_certificate_dc3_dc4',1)->select('id','dc3_configuration','name')->get();
            $criterion_position = $workspace->dc3_configuration->criterion_position;
            $subwokspace_data = collect($workspace->dc3_configuration->subwokspace_data);
            foreach ($courses as $course) {
                # code...
                SummaryCourse::select('id','course_id','dc3_path','user_id')
                    ->where('course_id',$course->id)
                    ->where('status_id',$user_status_approved->id)
                    ->whereNull('dc3_path')
                    ->with([
                        'user:id,name,lastname,surname,national_occupation_id,subworkspace_id',
                        'user.national_occupation:id,code',
                        'user.criterion_values'=>function($q) use ($criterion_position){
                            $q->where('criterion_id',$criterion_position)->select('id','value_text');
                        }
                    ])
                    ->chunkById(500, function ($summaries) use ($subwokspace_data){
                        $_bar = $this->output->createProgressBar($summaries->count());
                        $_bar->start();
                        foreach ($summaries as $summary) {
                            $user = $summary->user;
                            $lastname = ($user->lastname) ?? '';
                            $surname = ($user->surname) ?? '';
                            $subworkspace = $subwokspace_data->where('subworkspace_id',$user->subworkspace_id)->first();
                            $data = [
                                'user' => [
                                    'name' => $user->name.' '.$lastname.' '.$surname,
                                    'curp' => $user->curp,
                                    'occupation'=> $user?->national_occupation?->code,
                                    'position' => $user->criterion_values->first()?->value_text
                                ],
                                'subworkspace' => [
                                    
                                ]
                            ];
                            dd();
                        }
                        $_bar->finish();
                });
            }
        }
    }
}
