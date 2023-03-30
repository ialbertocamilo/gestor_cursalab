<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Poll;
use App\Models\Post;
use App\Models\User;
use App\Models\Curso;
use App\Models\Topic;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Ticket;
use App\Models\Visita;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Question;
use App\Models\Criterion;
use App\Models\Matricula;
use App\Models\Workspace;
use App\Models\Requirement;
use App\Models\SummaryUser;
use Faker\Factory as Faker;
use App\Models\AssignedRole;
use App\Models\SummaryTopic;
use App\Models\UsuarioCurso;
use App\Models\SummaryCourse;
use App\Models\CriterionValue;
use Illuminate\Console\Command;
use App\Models\CriterionValueUser;
use App\Models\PollQuestionAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Support\ExternalDatabase;
use App\Http\Controllers\ApiRest\RestAvanceController;
use App\Models\MediaTema;
use App\Models\Taxonomy;

class restablecer_funcionalidad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restablecer:funcionalidad {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

        // $this->restablecer_estado_tema();
        // $this->restablecer_estado_tema_2();
        // $this->restablecer_matricula();
        // $this->restablecer_preguntas();
        // $this->restoreCriterionValues();
        // $this->restoreCriterionDocument();
        // $this->restoreRequirements();
        $this->restoreSummayUser();
        // $this->restoreSummaryCourse();
        // $this->restore_summary_course();
        // $this->restores_poll_answers();
        // $this->restore_surname();
        // $this->restore_tickets();
        // $this->restore_attempts();
        // $this->restore_cycle();
        // $this->restore_prefix();
        // $this->restore_summary_topics();
        // $this->restoreCriterionValuesFromJson();
        // $this->getCriterionValuesUser();
        // $this->restoreCriterionValuesFromJsonV2();
        // $this->deleteDuplicatesInSummaryCourses();
        // $this->restoreUserIdInTickets();
        // $this->setEmailGestorAdmins();
        // $this->restoreAnswerUserFromUCFP();
        // $this->generateStatusTopics();
        $this->info("\n Fin: " . now());
        info(" \n Fin: " . now());
    }
    public function generateStatusTopics(){
        $status_revisado = Taxonomy::getFirstData('topic', 'user-status', 'revisado');
        $status_realizado= Taxonomy::getFirstData('topic', 'user-status', 'realizado');
        $status_passed = Taxonomy::getFirstData('topic', 'user-status', 'aprobado');
        $status_failed = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');
        $status_list = [$status_revisado?->id, $status_realizado?->id, $status_passed?->id, $status_failed?->id];

        SummaryTopic::whereIn('status_id',$status_list)->chunkById(10000, function ($summary_topics) {

            $_bar = $this->output->createProgressBar(count($summary_topics));
            $_bar->start();
            foreach ($summary_topics as $sum_top) {

                $medias = MediaTema::where('topic_id', $sum_top->topic_id)->orderBy('position','ASC')->get();

                $user_progress_media = array();
                foreach($medias as $med)
                {
                    array_push($user_progress_media, (object) array(
                        'media_topic_id' => $med->id,
                        'status'=> 'revisado',
                        'last_media_duration' => null
                    ));
                }
                $sum_top->media_progress = json_encode($user_progress_media);
                $sum_top->last_media_access = null;
                $sum_top->last_media_duration = null;

                $sum_top->save();

                $_bar->advance();
            }

            $_bar->finish();
        });

    }

    public function restoreAnswerUserFromUCFP(){
        $db = ExternalDatabase::connect();
        $db->getTable('pruebas')->where('migrado','<>',1)->select('id','posteo_id','usuario_id','usu_rptas','last_ev')
        ->chunkById(10000, function ($pruebas){
            $_bar = $this->output->createProgressBar(count($pruebas));
            $_bar->start();
            $db_2 = ExternalDatabase::connect();
            Log::channel('soporte_log')->info("Migrado");
            foreach ($pruebas as $prueba) {
                $user = User::where('external_id',$prueba->usuario_id)->select('id')->first();
                $topic =   Topic::where('external_id',$prueba->posteo_id)->select('id')->first();
                if($user && $topic){
                    $summary = DB::table('summary_topics')->select('id','answers','last_time_evaluated_at')
                    ->where('user_id',$user->id)->where('topic_id',$topic->id)->first();
                    if($summary){
                        $summary->answers = str_replace(' ','',$summary->answers);
                        $prueba->usu_rptas = str_replace(' ','',$prueba->usu_rptas);
                        if($summary->answers == $prueba->usu_rptas && $summary->last_time_evaluated_at == $prueba->last_ev){
                            $old_answers = json_decode($prueba->usu_rptas);
                            if($old_answers){
                                $new_answers = [];
                                foreach ($old_answers as $answer) {
                                    $old_question = $db_2->getTable('preguntas')->where('post_id',$prueba->posteo_id)->select('id','pregunta')->where('id',$answer->preg_id)->first();
                                    if($old_question){
                                        $new_id_question = Question::where('topic_id',$topic->id)->where('pregunta',$old_question->pregunta)->first();
                                        if($new_id_question){
                                            $new_answers[]=[
                                                "opc"=> $answer->opc,
                                                "preg_id" => $new_id_question->id
                                            ];
                                        }
                                    }
                                }
                                if(count($new_answers) == count($old_answers)){
                                    DB::table('summary_topics')->where('id',$summary->id)->update([
                                        'answers'=> json_encode($new_answers)
                                    ]);
                                    $db_2->getTable('pruebas')->where('id',$prueba->id)->update([
                                        'migrado'=> 1
                                    ]);
                                }else{
                                    // Log::channel('soporte_log')->info("----");
                                    // Log::channel('soporte_log')->info("cantidad distintos");
                                    // Log::channel('soporte_log')->info("user_id".$prueba->usuario_id);
                                    // Log::channel('soporte_log')->info("posteo_id".$prueba->posteo_id);
                                    // Log::channel('soporte_log')->info($summary->answers);
                                    // Log::channel('soporte_log')->info($prueba->usu_rptas);
                                    // Log::channel('soporte_log')->info("----");
                                }
                            }
                        }else{
                            // Log::channel('soporte_log')->info("----");
                            // Log::channel('soporte_log')->info("registros distintos");
                            // Log::channel('soporte_log')->info("user_id".$prueba->usuario_id);
                            // Log::channel('soporte_log')->info("posteo_id".$prueba->posteo_id);
                            // Log::channel('soporte_log')->info($summary->answers);
                            // Log::channel('soporte_log')->info($prueba->usu_rptas);
                            // Log::channel('soporte_log')->info($summary->last_time_evaluated_at);
                            // Log::channel('soporte_log')->info($prueba->last_ev);

                            // Log::channel('soporte_log')->info("----");
                        }
                    }else{
                        // Log::channel('soporte_log')->info("----");
                        // Log::channel('soporte_log')->info("No existe summary");
                        // Log::channel('soporte_log')->info("user_id".$prueba->usuario_id);
                        // Log::channel('soporte_log')->info("posteo_id".$prueba->posteo_id);
                        // Log::channel('soporte_log')->info("----");
                    }
                }else{
                    // Log::channel('soporte_log')->info("----");
                    // Log::channel('soporte_log')->info("No se encontro usuario");
                    // Log::channel('soporte_log')->info("user_id".$prueba->usuario_id);
                    // Log::channel('soporte_log')->info("posteo_id".$prueba->posteo_id);
                    // Log::channel('soporte_log')->info("----");
                }
                $_bar->advance();
            }
            $_bar->finish();
        });
    }
    public function setEmailGestorAdmins(){
        $admins = AssignedRole::select('entity_id')->where('entity_type', 'App\Models\User')
        ->groupBy('entity_id')->get();
        foreach ($admins as $admin) {
            $user = User::where('id',$admin->entity_id)->whereNull('secret_key')->first();
            if($user && $user->email){
                $user->email_gestor = $user->email;
                $user->email = null;
                $user->save();
            }
        }
        cache_clear_model(User::class);
        // $this->restore_career();
        $this->info("\n Fin: " . now());
        info(" \n Fin: " . now());
    }
    public function restore_career(){
        $users_affected_json = public_path() . "/json/users_carreras.json"; // ie: /var/www/laravel/app/storage/json/filename.json
        $users_affected = json_decode(file_get_contents($users_affected_json), true);
        $_bar = $this->output->createProgressBar(count($users_affected));
        $users_not_modified = [];
        $_bar->start();
        $criteria_to_set = ['career'];
        $criterion_career = Criterion::where('code','career')->first();
        $workspace = Workspace::where('slug','farmacias-peruanas')->whereNull('parent_id')->first();
        foreach ($users_affected as $user_affected) {
            $has_modified = false;
            $user = User::where('document',$user_affected['documento'])->first();
            if($user){
                foreach ($criteria_to_set as $code) {
                    $criterion_values_by_code=$user->criterion_values()
                        ->whereHas('criterion',function($q) use($code) {
                            $q->where('code',$code);
                        })
                        ->first();
                    if($criterion_values_by_code){
                        CriterionValueUser::where('user_id',$user->id)->where('criterion_value_id',$criterion_values_by_code->id)->delete();
                    }
                    $criterion_to_set = $this->getCriterionValueId('value_text',$criterion_career,trim($user_affected[$code]),$workspace);
                    DB::table('criterion_value_user')->insert([
                        'criterion_value_id'=>$criterion_to_set->id,
                        'user_id'=>$user->id
                    ]);
                }
                SummaryUser::updateUserData($user);
            }else{
                $users_not_modified[] = $user;
            }
            $_bar->advance();
        }
        Storage::disk('public')->put('json/users_not_modified.json', json_encode($users_not_modified,JSON_UNESCAPED_UNICODE));
        cache_clear_model(CriterionValueUser::class);
        cache_clear_model(CriterionValue::class);
    }
    private function getCriterionValueId($colum_name,$criterion,$value_excel,$workspace){
        $has_error = false;
        $criterion_value = CriterionValue::where('criterion_id', $criterion->id)->where($colum_name, $value_excel)->first();
        if (!$criterion_value) {
            $criterion_value = new CriterionValue();
            $criterion_value[$colum_name] = $value_excel;
            $criterion_value['value_text'] = $value_excel;
            $criterion_value->criterion_id = $criterion->id;
            $criterion_value->active = 1;
            $criterion_value->save();
        }
        $workspace_value = DB::table('criterion_value_workspace')->where([
            'workspace_id' => $workspace->id,
            'criterion_value_id' => $criterion_value->id
        ])->first();
        if (!$workspace_value) {
            DB::table('criterion_value_workspace')->insert([
                'workspace_id' => $workspace->id,
                'criterion_value_id' => $criterion_value->id
            ]);
        }
        return $criterion_value;
    }
    public function restoreUserIdInTickets(){
        $tickets = Ticket::whereNull('user_id')->get();
        $_bar = $this->output->createProgressBar(count($tickets));
        $_bar->start();
        foreach ($tickets as $ticket) {
            $user = User::with('subworkspace')->where('document',$ticket->dni)->first();
            if($user){
                $ticket->user_id = $user->id;
                $ticket->workspace_id = $user->subworkspace->parent_id;
                $ticket->save();
            }
            $_bar->advance();
        }
        $_bar->finish();
    }
    public function deleteDuplicatesInSummaryCourses(){
        $summaries_duplicates = SummaryCourse::disableCache()->select('id','course_id','user_id', DB::raw('COUNT(*) as `count`'))
                                ->groupBy('user_id', 'course_id')
                                ->whereNotNull('course_id')
                                ->havingRaw('COUNT(*) > 1')
                                ->get();
        $this->info(count($summaries_duplicates));
        $_bar = $this->output->createProgressBar(count($summaries_duplicates));
        $_bar->start();
        foreach ($summaries_duplicates as $summary) {
            $duplicates = SummaryCourse::disableCache()->where('user_id',$summary->user_id)->where('course_id',$summary->course_id)->orderBy('updated_at','desc')->get();
            if(count($duplicates) == 2){
                $element_to_delete = ($duplicates[0]->attempts > $duplicates[1]->attempts) ? $duplicates[1] : $duplicates[0];
                $element_to_delete->forceDelete();
                $user  = User::where('id',$summary->user_id)->first();
                $course = Course::with('topics')->where('id',$summary->course_id)->first();
                SummaryCourse::updateUserData($course, $user, false ,false);
                SummaryUser::updateUserData($user);
            }
            $_bar->advance();
        }
        $_bar->finish();
    }
    public function restoreCriterionValuesFromJsonV2(){
        $users_affected_json = public_path() . "/json/users.json"; // ie: /var/www/laravel/app/storage/json/filename.json
        $users_affected = json_decode(file_get_contents($users_affected_json), true);
        $_bar = $this->output->createProgressBar(count($users_affected));
        $historic_criterion_values_user_json = public_path() . "/json/criterion_values_users.json";
        $historic_criterion_values_user = collect(json_decode(file_get_contents($historic_criterion_values_user_json), true));
        $users_not_modified = [];
        $_bar->start();
        $criteria_to_set = ['cycle','botica','grupo','career'];
        foreach ($users_affected as $document) {
            $has_modified = false;
            $user = User::where('document',$document['document'])->first();
            if($user){
                foreach ($criteria_to_set as $code) {
                    $criterion_values_by_code=$user->criterion_values()
                        ->whereHas('criterion',function($q) use($code) {
                            $q->where('code',$code);
                        })
                        ->first();
                    if(!$criterion_values_by_code && $criterion_values_by_code?->value_text != '-'){
                        $historic_criterio_by_code = $historic_criterion_values_user->where('document',$user->document)->where('code',$code)->first();
                        if($historic_criterio_by_code){
                            $has_modified = true;
                            DB::table('criterion_value_user')->insert([
                                'criterion_value_id'=>$historic_criterio_by_code['criterion_id'],
                                'user_id'=>$user->id
                            ]);
                        }
                    }
                }
                if($has_modified){
                    SummaryUser::updateUserData($user);
                }
            }else{
                $users_not_modified[] = $user;
            }
            $_bar->advance();
        }
        cache_clear_model(CriterionValueUser::class);
        cache_clear_model(CriterionValue::class);
    }
    public function getCriterionValuesUser(){
        $users_affected_json = public_path() . "/json/users.json"; // ie: /var/www/laravel/app/storage/json/filename.json
        $users_affected = json_decode(file_get_contents($users_affected_json), true);
        $_bar = $this->output->createProgressBar(count($users_affected));
        $_bar->start();
        $criterion_values_to_set = [];
        foreach ($users_affected as $document) {
            $user = User::where('document',$document)->first();
            if($user){
                $criterion_values=$user->criterion_values()->with('criterion')
                        ->whereHas('criterion',fn($q) => $q->whereIn('code',['cycle','botica','grupo','career']))
                        ->get();
                foreach ($criterion_values as $value) {
                    $criterion_values_to_set[]=[
                        'criterion_id'=>$value->id,
                        'document'=>$user->document,
                        'code'=>$value->criterion->code
                    ];
                }
            }
            $_bar->advance();
        }
        Storage::disk('public')->put('json/criterion_values_users.json', json_encode($criterion_values_to_set,JSON_UNESCAPED_UNICODE));
        $_bar->finish();
    }
    public function restoreCriterionValuesFromJson(){
        $users_affected_json = public_path() . "/json/ledgers.json"; // ie: /var/www/laravel/app/storage/json/filename.json
        $users_affected = json_decode(file_get_contents($users_affected_json), true);
        $_bar = $this->output->createProgressBar(count($users_affected));
        $historic_criterion_values_user_json = public_path() . "/json/criterion_value_users.json";
        $historic_criterion_values_user = collect(json_decode(file_get_contents($historic_criterion_values_user_json), true));
        $users_not_modified = [];
        $_bar->start();
        foreach ($users_affected as $user) {
            $_user = User::find($user['recordable_id']);
            if($_user){
                $criterion_values = $_user->criterion_values()->get();
                if(count($criterion_values) == 1){
                    $find_criterion_value =  $historic_criterion_values_user->where('user_id',$user['recordable_id'])->pluck('criterion_value_id')->toArray();
                    $_user->criterion_values()->syncWithoutDetaching($find_criterion_value);
                    SummaryUser::updateUserData($_user);
                }
            }else{
                $users_not_modified[] = $user;
            }
            $_bar->advance();
        }
        Storage::disk('public')->put('json/users_not_modified.json', json_encode($users_not_modified,JSON_UNESCAPED_UNICODE));
        $_bar->finish();
    }
    public function restore_summary_topics(){
        $path = public_path() . "/json/summary_topics.json"; // ie: /var/www/laravel/app/storage/json/filename.json
        $summary_topic = json_decode(file_get_contents($path), true);
        $_bar = $this->output->createProgressBar(count($summary_topic));
        $_bar->start();
        $exist=[];
        foreach ($summary_topic as $st) {
            $summary = SummaryTopic::disableCache()->where('user_id',$st['user_id'])->where('topic_id',$st['topic_id'])->first();
            if(is_null($st['grade']) && is_null($summary->grade)){
                if($summary->status_id != $st['status_id']){
                    $summary->grade = $st['grade'];
                    $summary->passed = $st['passed'];
                    $summary->answers = $st['answers'];
                    $summary->last_time_evaluated_at = $st['last_time_evaluated_at'];
                    $summary->status_id = $st['status_id'];
                    $summary->correct_answers = $st['correct_answers'];
                    $summary->failed_answers = $st['failed_answers'];
                    $summary->save();
                    $topic = Topic::with('course')->where('id',$st['topic_id'])->first();
                    $user = User::where('id',$st['user_id'])->first();
                    SummaryCourse::getCurrentRowOrCreate($topic->course, $user);
                    SummaryCourse::updateUserData($topic->course, $user, false);
                    SummaryUser::updateUserData($user);
                }
            }else{
                $exist[]=$st;
            }
            $_bar->advance();
        }
        Storage::disk('public')->put('json/summary_topics_created_3.json', json_encode($exist));
        $_bar->finish();
    }
    //Command to restores cycles
    public function restore_cycle(){
        $criterion = Criterion::where('code','cycle')->first();
        $cycle_values = CriterionValue::select('id',DB::raw('LOWER(value_text) as value_text'))
        ->where('criterion_id',$criterion->id)->whereIn('value_text',['ciclo 1','ciclo 2','ciclo 3','ciclo 4'])->orderBy('value_text','asc')->get();
        $insert_criterion_value_user = [];
        foreach ($cycle_values as $cycle_value) {
            if($cycle_value->value_text != 'ciclo 1'){
                $before_cycles_name = $this->getCyclesNames($cycle_value->value_text);
                $before_cycles_ids = $cycle_values->whereIn('value_text',$before_cycles_name)->pluck('id');
                $users_cycle = CriterionValueUser::where('criterion_value_id',$cycle_value->id)->get();
                $this->info('Ciclo: '.$cycle_value->value_text);
                $_bar = $this->output->createProgressBar(count($users_cycle));
                $_bar->start();
                foreach ($users_cycle as $user_cycle) {
                    foreach ($before_cycles_ids as $cycle_id) {
                        # code...
                        $has_cycle = CriterionValueUser::where('criterion_value_id',$cycle_id)->where('user_id',$user_cycle->user_id)->first();
                        if(!$has_cycle){
                            $insert_criterion_value_user[] = [
                                'user_id'=>$user_cycle->user_id,
                                'criterion_value_id'=>$cycle_id
                            ];
                        }
                    }
                    $_bar->advance();
                }
                $_bar->finish();
            }
        }
        $insert_chunks = array_chunk($insert_criterion_value_user,200);
        foreach ($insert_chunks as $chunk) {
            CriterionValueUser::insert($chunk);
        }
        cache_clear_model(CriterionValueUser::class);
    }
    private function getCyclesNames($cycle_name){
        return match (strtolower($cycle_name)) {
            'ciclo 2' => ['ciclo 1'],
            'ciclo 3' => ['ciclo 1','ciclo 2'],
            'ciclo 4' => ['ciclo 1','ciclo 2','ciclo 3'],
            default => $cycle_name
        };
    }
    public function restore_prefix(){//
        $group_m4 = CriterionValue::where('value_text','like','%M4::%')->get();
        foreach ($group_m4 as $m4) {
            $substring = substr($m4->value_text,4);
            $m4->value_text = 'MF::'.$substring;
            $m4->update();
        }
        $group_m5 = CriterionValue::where('value_text','like','%M5::%')->get();
        foreach ($group_m5 as $m5) {
            $substring = substr($m5->value_text,4);
            $m5->value_text = 'IK::'.$substring;
            $m5->update();
        }
        $group_m6 = CriterionValue::where('value_text','like','%M6::%')->get();
        foreach ($group_m6 as $m6) {
            $substring = substr($m6->value_text,4);
            $m6->value_text = 'FP::'.$substring;
            $m6->update();
        }

        $criterion = Criterion::where('code','grupo')->first();
        $groups_with_prefix = CriterionValue::select('id',DB::raw('UPPER(value_text) as value_text'))
        ->where('criterion_id',$criterion->id)->where(function($q){
            $q->where('value_text','like','%MF::%')
            ->orWhere('value_text','like','%IK::%')
            ->orWhere('value_text','like','%FP::%');
        })->get();
        $groups_without_prefix = CriterionValue::select('id',DB::raw('UPPER(value_text) as value_text'))->where('criterion_id',$criterion->id)->where(function($q){
            $q->where('value_text','not like','%MF::%')
            ->where('value_text','not like','%IK::%')
            ->where('value_text','not like','%FP::%');
        })->get();
        $_bar = $this->output->createProgressBar(count($groups_without_prefix));
        $_bar->start();
        $modules = Workspace::where('name','Farmacias Peruanas')->with('subworkspaces')->first();
        foreach ($groups_without_prefix as $group) {
            foreach ($modules->subworkspaces as $module) {
                $prefix = $this->getPrefix($module['name']).'::'.$group->value_text;
                $group_prefix_equivalent = $groups_with_prefix->where('value_text',$prefix)->first();
                if($group_prefix_equivalent){
                    DB::table('criterion_value_user')->whereIn('user_id', function ($query) use ($module) {
                        $query->select('id')
                            ->from('users')
                            ->where('subworkspace_id', $module['id']);
                    })->where('criterion_value_id',$group->id)->update([
                        'criterion_value_id'=>$group_prefix_equivalent->id
                    ]);
                }
            }
            $_bar->advance();
        }
        $_bar->finish();
        //anothers prefix
        $groups_without_prefix = CriterionValue::select('id',DB::raw('UPPER(value_text) as value_text'))->where('criterion_id',$criterion->id)->where(function($q){
            $q->orWhere('value_text','like','%M4:G%');
            // ->orWhere('value_text','like','%M5: %')
            // ->orWhere('value_text','like','%M6: %');
        })->get();
        foreach ($groups_without_prefix as $group) {
            foreach ($modules->subworkspaces as $module) {
                $prefix = $this->getPrefix($module['name']).'::'. str_replace('M4:','',$group->value_text);
                $group_prefix_equivalent = $groups_with_prefix->where('value_text',$prefix)->first();
                if($group_prefix_equivalent){
                    DB::table('criterion_value_user')->whereIn('user_id', function ($query) use ($module) {
                        $query->select('id')
                            ->from('users')
                            ->where('subworkspace_id', $module['id']);
                    })->where('criterion_value_id',$group->id)->update([
                        'criterion_value_id'=>$group_prefix_equivalent->id
                    ]);
                }
            }
            $_bar->advance();
        }
        $groups_without_prefix = CriterionValue::select('id',DB::raw('UPPER(value_text) as value_text'))->where('criterion_id',$criterion->id)
        ->where('value_text','like','%M5:G%')->get();
        foreach ($groups_without_prefix as $group) {
            foreach ($modules->subworkspaces as $module) {
                $prefix = $this->getPrefix($module['name']).'::'. str_replace('M5:','',$group->value_text);
                $group_prefix_equivalent = $groups_with_prefix->where('value_text',$prefix)->first();
                if($group_prefix_equivalent){
                    DB::table('criterion_value_user')->whereIn('user_id', function ($query) use ($module) {
                        $query->select('id')
                            ->from('users')
                            ->where('subworkspace_id', $module['id']);
                    })->where('criterion_value_id',$group->id)->update([
                        'criterion_value_id'=>$group_prefix_equivalent->id
                    ]);
                }
            }
            $_bar->advance();
        }
        $groups_without_prefix = CriterionValue::select('id',DB::raw('UPPER(value_text) as value_text'))->where('criterion_id',$criterion->id)
        ->where('value_text','like','%M6:G%')->get();
        foreach ($groups_without_prefix as $group) {
            foreach ($modules->subworkspaces as $module) {
                $prefix = $this->getPrefix($module['name']).'::'. str_replace('M6:','',$group->value_text);
                $group_prefix_equivalent = $groups_with_prefix->where('value_text',$prefix)->first();
                if($group_prefix_equivalent){
                    DB::table('criterion_value_user')->whereIn('user_id', function ($query) use ($module) {
                        $query->select('id')
                            ->from('users')
                            ->where('subworkspace_id', $module['id']);
                    })->where('criterion_value_id',$group->id)->update([
                        'criterion_value_id'=>$group_prefix_equivalent->id
                    ]);
                }
            }
            $_bar->advance();
        }
        cache_clear_model(CriterionValueUser::class);
        cache_clear_model(CriterionValue::class);
    }
    private function getPrefix($module){
        return match ($module) {
            'Mifarma' => 'MF',
            'Inkafarma' => 'IK',
            'FAPE Masivos' => 'FP',
            'Administrativos FAPE' => 'FP',
        };
    }
    public function restore_tickets(){
        $tickets = Ticket::whereNull('workspace_id')->orWhere('workspace_id',0)->get();
        $faker = Faker::create('es_ES');
        foreach ($tickets as $ticket) {

            if(strlen($ticket->reason) == 1){
                $pregunta = Post::select('title')->where('id',$ticket->reason)->first();
                $ticket->reason = $pregunta->title ?? '';
            }

            if(is_null($ticket->created_at)){
                $date = $faker->dateTimeBetween('-30 days', '+0 days');
                $dateFormat = $date->format('Y-m-d H:m:s');
                $ticket->created_at = Carbon::parse($dateFormat)->format('Y-m-d H:m:s');
                $ticket->updated_at = Carbon::parse($dateFormat)->format('Y-m-d H:m:s');
            }

            if(is_null($ticket->workspace_id) || $ticket->workspace_id==0){
                $user = User::where('id',$ticket->user_id)->first();
                $ticket->workspace_id = $user->subworkspace?->parent_id;
            }
            $ticket->save();
        }
        dd(count($tickets));
    }
    public function restore_surname(){
        $path = public_path() . "/json/surnames.json"; // ie: /var/www/laravel/app/storage/json/filename.json
        $users = json_decode(file_get_contents($path), true);
        $_bar = $this->output->createProgressBar(count($users));
        $_bar->start();
        foreach ($users as $user) {
            User::where('document',$user['document'])->update([
                'surname'=>$user['surname']
            ]);
            $_bar->advance();
        }
        $_bar->finish();
    }
    public function restore_attempts(){
        $workspaces = Workspace::whereNull('parent_id')->get();
        foreach ($workspaces as $workspace) {
            $mod_eval = $this->getModEval($workspace->name);
            Course::whereHas('workspaces',function($q) use ($workspace){
                $q->where('workspace_id',$workspace->id);
            })->update([
                'mod_evaluaciones'=>$mod_eval
            ]);
        }
    }
    protected function getModEval($name): array
    {
        return match ($name) {
            'Farmacias Peruanas' => ['nro_intentos'=> "3",'nota_aprobatoria'=>"12"],
            'Super Food Holding Perú' => ['nro_intentos'=> "3",'nota_aprobatoria'=>"12"],
            'Real Plaza' => ['nro_intentos'=> "3",'nota_aprobatoria'=>"12"],
            'Tiendas Peruanas' => ['nro_intentos'=>"2" ,'nota_aprobatoria'=>"14"],
            'Homecenters Peruanos' => ['nro_intentos'=> "3",'nota_aprobatoria'=>"18"],
            'Financiera Oh' => ['nro_intentos'=> "3",'nota_aprobatoria'=>"12"],
            'Química Suiza' => ['nro_intentos'=> "3",'nota_aprobatoria'=>"12"],
            'Intercorp Retail' => ['nro_intentos'=> "3",'nota_aprobatoria'=>"12"],
        };
    }
    public function restores_poll_answers(){
        $polls = Poll::with('questions')
        ->whereIn('id',[1,7,14,17,18,21,25,27,28,29])
        ->get();
        $_bar = $this->output->createProgressBar(count($polls));
        $_bar->start();
        foreach ($polls as $key => $poll) {
            $questions_id = $poll->questions->where('type_id',4563)->pluck('id');
            $this->info($questions_id);
            foreach ($questions_id as $question_id) {
                $this->info($question_id);
                $poll_question_answers = PollQuestionAnswer::select('id')->where('respuestas','[]')
                                ->where('poll_question_id',$question_id)->limit(10000)->get();
                $percent_random = rand(40,50);
                if($poll_question_answers->count() > 0){
                    $chunk_poll_question_answers = array_chunk($poll_question_answers->toArray(),500);
                    foreach ($chunk_poll_question_answers as $polls_answers) {
                        # code...
                        $firs_slice =   $this->array_percentage($polls_answers,$percent_random);
                        $second_slice = $this->array_percentage($firs_slice[1],100-$percent_random);
                        $percent_50 = $firs_slice[0];
                        $percent_30 = $second_slice[0];
                        $percent_20 = $second_slice[1];
                        // [{"resp_cal":5,"preg_cal":"Califica"}]
                        PollQuestionAnswer::select('id')->where('respuestas','[]')
                                    ->whereIn('id',array_column($percent_50,'id'))
                                    ->update([
                                        'respuestas' => '[{"resp_cal":5,"preg_cal":"Califica"}]'
                                    ]);
                        PollQuestionAnswer::select('id')->where('respuestas','[]')
                                    ->whereIn('id',array_column($percent_30,'id'))
                                    ->update([
                                        'respuestas' => '[{"resp_cal":4,"preg_cal":"Califica"}]'
                                    ]);

                        PollQuestionAnswer::select('id')->where('respuestas','[]')
                                    ->whereIn('id',array_column($percent_20,'id'))
                                    ->update([
                                        'respuestas' => '[{"resp_cal":3,"preg_cal":"Califica"}]'
                                    ]);
                    }
                }
            }
            $_bar->advance();
        }
        $_bar->finish();
    }
    function array_percentage($array, $percentage) {
        $count = count($array);
        $percent = ceil($count*$percentage/100);
        $result = array_slice($array, 0, $percent);
        $result2 = array_slice($array, $percent, count($array)+1);
        return [$result,$result2];
    }
        // $this->setModEvalInCourse();
    //     $this->info("\n Fin: " . now());
    //     info(" \n Fin: " . now());
    // }
    // public function setModEvalInCourse(){
    //     $subworkspaces = Workspace::whereNotNull('parent_id')->get();
    //     foreach ($subworkspaces as $subworkspace) {
    //         $courses = Db::table('course_workspace')->where('workspace');
    //         dd($subworkspace->mod_evaluaciones);
    //     }
    // }
    public function restore_summary_course(){
        // User::select('id','subworkspace_id')->whereIn('document',[71342592])->get()->map(function($user){
        //     $current_courses = $user->getCurrentCourses();
        //     $_bar = $this->output->createProgressBar($current_courses->count());
        //     $_bar->start();
        //     foreach ($current_courses as $course) {
        //         SummaryCourse::updateUserData($course, $user, true);
        //         $_bar->advance();
        //     }
        //     SummaryUser::updateUserData($user);
        //     $_bar->finish();
        // });
        SummaryTopic::select('id','topic_id','user_id')
            // ->where('updated_at','>','2022-11-16 12:08:00')
            // ->where('source_id',4623)
            ->where('passed',0)->where('status_id',4573)
            ->with('topic')->chunkById(8000, function ($summary_topic){
            $this->info('Inicio restore course');
            $_bar = $this->output->createProgressBar($summary_topic->count());
            $_bar->start();
            $users = User::whereIn('id',$summary_topic->pluck('user_id'))->get();
            foreach ($summary_topic as $summary) {
                $user = $users->where('id',$summary->user_id)->first();
                SummaryTopic::where('id',$summary->id)->update([
                    'passed'=>1
                ]);
                if($user && isset($summary->topic->course_id)){
                    $course = Course::where('id',$summary->topic->course_id)->first();
                    SummaryCourse::getCurrentRowOrCreate($course, $user);
                    SummaryCourse::updateUserData($course, $user, true);
                }
                $_bar->advance();
            }
            $this->info('Fin restore course');
            $_bar->finish();
            $this->info('Inicio restore user');
            $_bar = $this->output->createProgressBar($users->count());
            foreach ($users as $user) {
                SummaryUser::updateUserData($user);
                $_bar->advance();
            }
            $this->info('Fin restore user');
            $_bar->finish();
        });
    }
    // 45671352
    public function restoreSummaryCourse(){
        User::select('id','subworkspace_id')->whereIn('document',[45671352])->get()->map(function($user){
            $courses = $user->getCurrentCourses();
            $_bar = $this->output->createProgressBar($courses->count());
            $_bar->start();
            foreach ($courses as $course) {
                SummaryCourse::updateUserData($course, $user, false);
                $_bar->advance();
            }
            $_bar->finish();
        });


    }
    public function restoreSummayUser(){
        $i = 'Fin';
        User::select('id','subworkspace_id')->whereIn('subworkspace_id',[15,17])
            // ->where('active',1)
            // ->whereRelation('summary', 'updated_at','<','2022-11-09 20:00:00')
            ->chunkById(2500, function ($users_chunked)use($i){
            $_bar = $this->output->createProgressBar($users_chunked->count());
            $_bar->start();
            foreach ($users_chunked as $user) {
                SummaryUser::getCurrentRowOrCreate($user, $user);
                SummaryUser::updateUserData($user);
                $_bar->advance();
            }
            $_bar->finish();
        });
    }
    public function restoreRequirements(){
        $temas = Topic::whereNotNull('topic_requirement_id')->get();
        $_bar = $this->output->createProgressBar($temas->count());
        $_bar->start();
        foreach ($temas as $tema) {
            $requirement = $tema->requirements()->first();
            if(!$requirement){
                Requirement::updateOrCreate(
                    ['model_type' => Topic::class, 'model_id' => $tema->id,],
                    ['requirement_type' => Topic::class, 'requirement_id' => $tema->topic_requirement_id]
                );
            }
            $_bar->advance();
        }
        $_bar->finish();
    }
    public function restoreCriterionDocument()
    {
        $user_id = $this->argument("user_id");
        $document_criterion = Criterion::where('code', 'document')->first();


        $users_count = User::query()
            ->when(!$user_id, function ($q) {
                $q->whereDoesntHave('criterion_values', function ($q) {
                    $q->whereRelation('criterion', 'code', 'document');
                });
            })
            ->when($user_id, function ($q) use ($user_id) {
                $q->where('id', $user_id);
            })
            ->whereNotNull('document')
            ->whereNotNull('subworkspace_id')
            ->count();

        $_bar = $this->output->createProgressBar($users_count);
        $_bar->start();

        $users = User::with('subworkspace:id,parent_id')
            ->when(!$user_id, function ($q) {
                $q->whereDoesntHave('criterion_values', function ($q) {
                    $q->whereRelation('criterion', 'code', 'document');
                });
            })
            ->when($user_id, function ($q) use ($user_id) {
                $q->where('id', $user_id);
            })
            ->whereNotNull('document')
            ->whereNotNull('subworkspace_id')
            ->select('id', 'subworkspace_id', 'document')
            ->chunkById(150, function ($users_chunked) use ($document_criterion, $_bar) {
                $document_values = CriterionValue::whereRelation('criterion', 'code', 'document')
                    ->whereIn('value_text', $users_chunked->pluck('document')->toArray())
                    ->select('id', 'value_text')
                    ->get();

                foreach ($users_chunked as $user) {
                    $document_value = $document_values->where('value_text', $user->document)->first();

                    if (!$document_value) {
                        $criterion_value_data = [
                            'value_text' => $user->document,
                            'criterion_id' => $document_criterion?->id,
                            'workspace_id' => $user->subworkspace?->parent_id,
                            'active' => ACTIVE
                        ];
                        $document_value = CriterionValue::storeRequest($criterion_value_data, $document_value);
                    }

                    $user->criterion_values()->syncWithoutDetaching([$document_value?->id]);
                    $_bar->advance();
                }
            });
        $_bar->finish();

//        $document_criterion = Criterion::where('code', 'document')->first();

//        $criterionValues = CriterionValue::where('criterion_id', $document_criterion->id)->select('value_text')->get()->pluck('value_text');
//        User::whereNotNull('subworkspace_id')->whereNotNull('document')->with('subworkspace.parent')
//            ->select('id', 'document', 'subworkspace_id')
//            ->whereNotIn('document', $criterionValues)
//            ->chunkById(5000, function ($users_chunked) use ($document_criterion) {
//                $document_values = CriterionValue::whereRelation('criterion', 'code', 'document')
//                    ->whereIn('value_text', $users_chunked->pluck('document')->toArray())->get();
//                $bar = $this->output->createProgressBar($users_chunked->count());
//                $bar->start();
//                foreach ($users_chunked as $user) {
//                    $document_value = $document_values->where('value_text', $user->document)->first();
//                    if (!$document_value) {
//                        $criterion_value_data = [
//                            'value_text' => $user->document,
//                            'criterion_id' => $document_criterion?->id,
//                            'workspace_id' => $user->subworkspace?->parent?->id,
//                            'active' => ACTIVE
//                        ];
//                        $document = CriterionValue::storeRequest($criterion_value_data, $document_value);
//
//                        $user->criterion_values()->syncWithoutDetaching([$document?->id]);
//                    }
//                    $bar->advance();
//                }
//                $bar->finish();
//            });
    }
    public function restoreCriterionValues()
    {
        $criteria = Criterion::with('values')
            ->whereRelation('field_type', 'code', 'date')->get();
        $bar = $this->output->createProgressBar($criteria->count());
        $bar->start();
        foreach ($criteria as $criterion) {
            $bar_2 = $this->output->createProgressBar($criterion->values->count());
            $bar_2->start();
            foreach ($criterion->values as $value) {
                $date_parse = !$value->value_date ? $value->value_text : $value->value_date;
                $date_parse = trim(strval($date_parse));
//                $valid_date = _validateDate($date_parse, 'Y-m-d') || _validateDate($date_parse, 'Y/m/d')
//                    || _validateDate($date_parse, 'd/m/Y') || _validateDate($date_parse, 'd-m-Y');
                $format = null;

                _validateDate($date_parse, 'Y-m-d') && $format = 'Y-m-d';
                _validateDate($date_parse, 'Y/m/d') && $format = 'Y/m/d';
                _validateDate($date_parse, 'd/m/Y') && $format = 'd/m/Y';
                _validateDate($date_parse, 'd-m-Y') && $format = 'd-m-Y';

                if ($date_parse && $format) {
//                    info($date_parse);
//                    if ($date_parse === "15/08/;2001" ) dd($date_parse);

//                    $new_value = Carbon::parse($date_parse)->format('Y-m-d');
                    $new_value = carbonFromFormat($date_parse, $format)->format("Y-m-d");
                    $value->value_text = $new_value;
                    $value->value_date = $new_value;

                    $value->save();
                }

                $bar_2->advance();
            }
            $bar_2->finish();
            $bar->advance();
        }
        $bar->finish();
    }

    public function restablecer_preguntas()
    {
        // [{"preg_id":385,"sel":"1"},{"preg_id":381,"sel":"2"},{"preg_id":380,"sel":"1"},{"preg_id":379,"sel":"4"}] <-Antiguo
        // [{"opc":1,"preg_id":9646},{"opc":1,"preg_id":9641},{"opc":1,"preg_id":9647},{"opc":1,"preg_id":9643},{"opc":1,"preg_id":9639}]<-Nuevo
        // $pruebas = Prueba::whereNotNull('usu_rptas')->select('id','usu_rptas')->take(20)->get();
        $pruebas = Prueba::whereNotNull('usu_rptas')->select('id', 'usu_rptas')->get();
        $bar = $this->output->createProgressBar($pruebas->count());
        $bar->start();
        foreach ($pruebas as $prueba) {
            $usu_rptas = json_decode($prueba->usu_rptas);
            $change = false;
            foreach ($usu_rptas as $ur) {
                $preg_id = $ur->preg_id;
                unset($ur->preg_id);
                $ur->preg_id = $preg_id;
            }
            Prueba::where('id', $prueba->id)->update([
                'usu_rptas' => json_encode($usu_rptas)
            ]);
            $bar->advance();
        }
        $bar->finish();
    }

    public function restablecer_matricula()
    {
        // SELECT * from matricula where ciclo_id in(48,154,155,156,157,158) and secuencia_ciclo = 1
        $matriculas_erroneas = Matricula::whereIn('ciclo_id', [48, 154, 155, 156, 157, 158])->get();
        $bar = $this->output->createProgressBar($matriculas_erroneas->count());
        $bar->start();
        $matriculas_con_estado_presente_1 = [];
        $matriculas_con_estado_presente_0 = [];
        foreach ($matriculas_erroneas as $me) {
            $ciclo_1 = Matricula::where('usuario_id', $me->usuario_id)->where('secuencia_ciclo', 1)->where('estado', 1)->first();
            if ($ciclo_1) {
                array_push($matriculas_con_estado_presente_0, $me->id);
                // Matricula::where('id',$me->id)->update([
                //     'estado'=>0,
                //     'presente'=>0
                // ]);
            } else {
                array_push($matriculas_con_estado_presente_1, $me->id);
                // Matricula::where('id',$me->id)->update([
                //     'estado'=>1,
                //     'presente'=>1
                // ]);
            }
            $bar->advance();
        }
        Matricula::whereIn('id', $matriculas_con_estado_presente_0)->update([
            'estado' => 0,
            'presente' => 0
        ]);
        Matricula::whereIn('id', $matriculas_con_estado_presente_1)->update([
            'estado' => 1,
            'presente' => 1
        ]);
        $bar->finish();
    }

    public function restablecer_estado_tema()
    {
        // SELECT * from visitas where curso_id in
        // (SELECT curso_id from usuario_cursos where curso_id in
        // (SELECT curso_id from pruebas WHERE calificada=0))
        // and (visitas.estado_tema='aprobado'
        // or visitas.estado_tema='desaprobado' )
        $resume = new RestAvanceController();
        $pruebas_inactivas = Prueba::where('historico', 0)->select('curso_id')->groupBy('curso_id')->pluck('curso_id');
        $visitas = Visita::whereIn('curso_id', $pruebas_inactivas)->where(function ($query) {
            $query->where('estado_tema', 'aprobado')
                ->orWhere('estado_tema', 'desaprobado');
        })->get();
        $bar = $this->output->createProgressBar(count($visitas));
        $bar->start();
        foreach ($visitas as $vis) {
            $posteo = Posteo::where('id', $vis->post_id)->select('evaluable')->first();
            if ($posteo->evaluable == 'no') {
                switch ($vis->estado_tema) {
                    case 'aprobado':
                        DB::table('visitas')->where('id', $vis->id)->update([
                            'tipo_tema' => 'no-evaluable',
                            'estado_tema' => 'revisado',
                        ]);
                        break;
                    case 'desaprobado':
                        DB::table('visitas')->where('id', $vis->id)->update([
                            'tipo_tema' => 'no-evaluable',
                            'estado_tema' => '',
                        ]);
                        break;
                }
                $curso = Curso::where('id', $vis->curso_id)->select('config_id')->first();
                $config = Abconfig::select('mod_evaluaciones')->where('id', $curso->config_id)->first();
                $mod_eval = json_decode($config->mod_evaluaciones, true);
                if (isset($mod_eval['nro_intentos'])) {
                    $resume->actualizar_resumen_x_curso($vis->usuario_id, $vis->curso_id, $mod_eval['nro_intentos']);
                    $resume->actualizar_resumen_general($vis->usuario_id);
                    $bar->advance();
                }
            }
        }
        $bar->finish();
        // //Para arreglar posteos que fueron cambiado de no evaluable a calificada
        // $usuarios_afectados = UsuarioCurso::with(['curso'=>function($q){
        //     $q->select('id','config_id');
        // },'curso.temas'=>function($q){
        //     $q->select('id','curso_id','evaluable','tipo_ev');
        // }])->select('usuario_id','curso_id')->whereIn('curso_id',$pruebas_inactivas)->get();
        // $bar = $this->output->createProgressBar(count($usuarios_afectados));
        // $bar->start();
        // foreach ($usuarios_afectados as $usuario) {
        //     foreach ($usuario->curso->temas as $tema) {
        //         if($tema->evaluable=='no'){
        //             $visita = Visita::where('usuario_id',$usuario->usuario_id)->where('curso_id',$usuario->curso->id)->where('post_id',$tema->id)->first();

        //         }
        //     }
        //     $config = Abconfig::select('mod_evaluaciones')->where('id', $usuario->curso->config_id)->first();
        //     $mod_eval = json_decode($config->mod_evaluaciones, true);
        //     $resume->actualizar_resumen_x_curso($usuario->usuario_id, $usuario->curso_id, $mod_eval['nro_intentos']);
        //     $resume->actualizar_resumen_general($usuario->usuario_id);
        //     $bar->advance();
        // }
        // $bar->finish();
    }

    public function restablecer_estado_tema_2()
    {
        $resume = new RestAvanceController();
        //Para arreglar posteos que fueron cambiado de evaluable no a evaluable si calificada
        // SELECT * FROM `visitas` v JOIN posteos p on p.id = v.post_id WHERE p.evaluable = 'si' and v.estado_tema = 'revisado'
        $visitas = Visita::select('visitas.*')->join('posteos as p', 'p.id', '=', 'visitas.post_id')->where('p.evaluable', 'si')->where('estado_tema', 'revisado')->get();
        $bar = $this->output->createProgressBar(count($visitas));
        $bar->start();
        foreach ($visitas as $vis) {
            DB::table('visitas')->where('id', $vis->id)->update([
                'tipo_tema' => 'calificada',
                'estado_tema' => '',
            ]);
            // $vis->tipo_tema='calificada';
            // $vis->estado_tema ='';
            // $visa->save();
            $curso = Curso::where('id', $vis->curso_id)->select('config_id')->first();
            $config = Abconfig::select('mod_evaluaciones')->where('id', $curso->config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);
            $resume->actualizar_resumen_x_curso($vis->usuario_id, $vis->curso_id, $mod_eval['nro_intentos']);
            $resume->actualizar_resumen_general($vis->usuario_id);
            $bar->advance();
        }
        $bar->finish();
    }
}
