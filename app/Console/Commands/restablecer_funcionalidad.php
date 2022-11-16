<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Curso;
use App\Models\Topic;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Criterion;
use App\Models\Matricula;
use App\Models\Requirement;
use App\Models\SummaryUser;
use App\Models\SummaryTopic;
use App\Models\UsuarioCurso;
use App\Models\SummaryCourse;
use App\Models\CriterionValue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiRest\RestAvanceController;

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
        // $this->restoreSummayUser();
        // $this->restoreSummaryCourse();
        $this->restore_summary_course();
        $this->info("\n Fin: " . now());
        info(" \n Fin: " . now());
    }
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
                if($user && isset($summary->topic->course_id)){
                    $course = Course::where('id',$summary->topic->course_id)->first();
                    SummaryCourse::getCurrentRowOrCreate($course, $user);
                    SummaryCourse::updateUserData($course, $user, true);
                }
                $_bar->advance();
                // SummaryTopic::where('id',$summary->id)->update([
                //     'passed'=>1
                // ]);
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
        User::select('id','subworkspace_id')->whereIn('subworkspace_id',[26,27,28,29])
            ->where('active',1)
            ->whereRelation('summary', 'updated_at','<','2022-11-09 20:00:00')
            ->chunkById(2500, function ($users_chunked)use($i){
            $this->info($i);
            $_bar = $this->output->createProgressBar($users_chunked->count());
            $_bar->start();
            foreach ($users_chunked as $user) {
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
