<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\Person;
use App\Models\Taxonomy;
use App\Models\Workspace;
use Illuminate\Support\Str;
use App\Models\SummaryCourse;
use Illuminate\Console\Command;
use App\Http\Controllers\Dc3Controller;

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
        })->with('subworkspaces:id,parent_id,name,logo')->select('id','dc3_configuration','name')->whereNotNull('dc3_configuration')->get();
        $user_status = Taxonomy::where('group','course')->where('type','user-status')->whereIn('code',['aprobado','enc_pend'])->select('id')->first();
        $dc3_controller = new Dc3Controller();

        foreach ($workspaces as $workspace) {
            $courses = Course::whereHas('workspaces',function($q) use ($workspace){
                $q->where('id',$workspace->id);
            })->where('can_create_certificate_dc3_dc4',1)->whereNotNull('dc3_configuration')->select('id','dc3_configuration','name','duration')->get();
            $criterion_position = $workspace->dc3_configuration->criterion_position;
            $subwokspace_data = collect($workspace->dc3_configuration->subwokspace_data);
            foreach ($courses as $course) {
                # code...
                SummaryCourse::select('id','course_id','dc3_path','user_id')
                    ->where('course_id',$course->id)
                    ->whereIn('status_id',$user_status->pluck('id'))
                    ->whereNull('dc3_path')
                    ->with([
                        'user:id,document,name,lastname,surname,national_occupation_id,subworkspace_id',
                        'user.national_occupation:id,code',
                        'user.criterion_values'=>function($q) use ($criterion_position){
                            $q->where('criterion_id',$criterion_position)->select('id','value_text');
                        }
                    ])
                    ->chunkById(500, function ($summaries) use ($subwokspace_data,$course,$workspace,$dc3_controller){
                        $_bar = $this->output->createProgressBar($summaries->count());
                        $_bar->start();
                        foreach ($summaries as $summary) {
                            $user = $summary->user;
                            $lastname = ($user->lastname) ?? '';
                            $surname = ($user->surname) ?? '';
                            $subworkspace = $subwokspace_data->where('subworkspace_id',$user->subworkspace_id)->first();
                            $subworkspace_logo = $workspace->subworkspaces->where('id',$user->subworkspace_id)->first()?->logo;
                            $instructor = Person::where('id',$course->dc3_configuration->instructor)->select('person_attributes')->first();
                            $legal_representative = Person::where('id',$course->dc3_configuration->legal_representative)->select('person_attributes')->first();
                            $catalog_denomination_dc3 = Taxonomy::select('code')->where('id',$course->dc3_configuration->catalog_denomination_dc3_id)->first();
                            $date_range = $course->dc3_configuration->date_range;
                            $init_date_course = ($date_range[0] >= $date_range[1]) ? $date_range[1] : $date_range[0];
                            $init_date_course_parse = Carbon::parse($init_date_course);
                            $final_date_course = ($date_range[0] >= $date_range[1]) ? $date_range[0] : $date_range[1];
                            $final_date_course_parse = Carbon::parse($final_date_course);
                            $data = [
                                'user' => [
                                    'name' => Str::title($user->name.' '.$lastname.' '.$surname),
                                    'curp' => $user->curp,
                                    'document'=> $user->document,
                                    'occupation'=> $user?->national_occupation?->code,
                                    'position' => $user->criterion_values->first()?->value_text
                                ],
                                'subworkspace' => [
                                    'id' => $subworkspace->subworkspace_id,
                                    'name_or_social_reason' => $subworkspace->name_or_social_reason,
                                    'shcp' => $subworkspace->shcp,
                                    'subworkspace_logo'=>$subworkspace_logo
                                ],
                                'course'=>[
                                    'id'=>$course->id,
                                    'name'=>$course->name,
                                    'duration' => $course->duration,
                                    'instructor' => $instructor->person_attributes['name'],
                                    'instructor_signature' => $instructor->person_attributes['signature_file'],
                                    'legal_representative' => $legal_representative->person_attributes['name'],
                                    'legal_representative_signature' => $legal_representative->person_attributes['signature_file'],
                                    'catalog_denomination_dc3' => $catalog_denomination_dc3->code,
                                    'init_date_course_year' => $init_date_course_parse->year,
                                    'init_date_course_month' => $init_date_course_parse->month,
                                    'init_date_course_day' => $init_date_course_parse->day,
                                    'final_date_course_year' => $final_date_course_parse->year,
                                    'final_date_course_month' => $final_date_course_parse->month,
                                    'final_date_course_day' => $final_date_course_parse->day,
                                ]
                            ];
                            $summary->dc3_path = $dc3_controller->generatePDF($data);
                            $summary->save();
                            dd($summary);
                        }
                        $_bar->finish();
                });
            }
        }
    }
}
