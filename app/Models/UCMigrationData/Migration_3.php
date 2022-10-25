<?php

namespace App\Models\UCMigrationData;

use App\Models\Poll;
use App\Models\PollQuestion;
use Illuminate\Database\Eloquent\Model;
use App\Models\Taxonomy;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\Topic;
use App\Models\User;
use App\Models\SummaryCourse;
use App\Models\SummaryTopic;
use App\Models\SummaryUser;
use App\Models\Support\OTFConnection;
use Illuminate\Support\Facades\DB;

class Migration_3 extends Model
{
    const CHUNK_LENGTH = 2500;
    const W_UCFP_ID = 25;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function migrateEncuestas($output)
    {
        info('getEncuestasData');
        $data = self::getEncuestasData($output);
        self::insertChunkedData($data, 'polls', $output);

        info('getEncuestasPreguntasData');
        $data = self::getEncuestasPreguntasData($output);
        self::insertChunkedData($data, 'poll_questions', $output);
    }


    protected function migrateEncuestasRespuestas($output)
    {
        info('getAndInsertEncuestasPreguntasRespuestasData');
        self::getAndInsertEncuestasPreguntasRespuestasData($output);
    }

    protected function migrateSummaryUsers($output)
    {
        info('getResumenGeneralData');
        $data = self::getResumenGeneralData($output);
        // self::insertChunkedData($data, 'summary_users', $output);
    }

    protected function migrateSummaryCourses($output)
    {
        info('getAndInsertResumenCursosData');
        self::getAndInsertResumenCursosData($output);
    }

    protected function migrateSummaryCoursesCertifications($output)
    {
        info('getAndInsertResumenCursosDataCertifications');
        self::getAndInsertResumenCursosDataCertifications($output);
    }

    protected function migrateSummaryTopics($output, $type)
    {
        info('migrateSummaryTopics');

        if ($type == 'pruebas')
            self::getAndInsertResumenTemasDataPruebas($output);

        if ($type == 'abiertas')
            self::getAndInsertResumenTemasDataAbiertas($output);
        
        if ($type == 'reinicios')
            self::getAndInsertResumenTemasDataReinicios($output);
        
        if ($type == 'visitas')
            self::getAndInsertResumenTemasDataVisitas($output);
    }


    public function insertChunkedData($data, $table_name, $output)
    {
        $output->info('init chunked ' . $table_name);

        $bar = $output->createProgressBar(count($data));
        $bar->start();

        foreach ($data as $chunk)
        {
            DB::table($table_name)->insert($chunk);

            info($table_name . ' chunked inserted');

            $bar->advance();
        }

        $bar->finish();

        $output->newLine();
    }

    protected function getEncuestasData($output)
    {
        $db = self::connect();

        $output->info('init getEncuestasData');

        $encuestas = $db->getTable('encuestas')->get();
        $types = Taxonomy::getData('poll', 'tipo')->get();

        $data = [];

        $bar = $output->createProgressBar(count($encuestas));
        $bar->start();

        foreach ($encuestas as $key => $encuesta)
        {
            $type = $types->where('code', $encuesta->tipo)->first();

            $data[] = [
                'external_id' => $encuesta->id,
                'workspace_id' => self::W_UCFP_ID,
                'type_id' => $type->id ?? NULL,
                'anonima' => $encuesta->anonima,
                'titulo' => $encuesta->titulo,
                'imagen' => $encuesta->imagen,
                'active' => $encuesta->estado,
                'created_at' => $encuesta->created_at,
                'updated_at' => $encuesta->updated_at,
            ];

            $bar->advance();
        }

        $bar->finish();

        $output->newLine();

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getEncuestasPreguntasData($output)
    {
        $db = self::connect();

        $output->info('init getEncuestasPreguntasData');

        $preguntas = $db->getTable('encuestas_preguntas')->get();
        $types = Taxonomy::getData('poll', 'tipo-pregunta')->get();
        $polls = Poll::where('workspace_id', self::W_UCFP_ID)->whereNotNull('external_id')->get();

        $data = [];

        $bar = $output->createProgressBar(count($preguntas));
        $bar->start();

        foreach ($preguntas as $key => $pregunta)
        {
            $type = $types->where('code', $pregunta->tipo_pregunta)->first();
            $poll = $polls->where('external_id', $pregunta->encuesta_id)->first();

            $data[] = [
                'external_id' => $pregunta->id,
                'poll_id' => $poll->id ?? NULL,
                'type_id' => $type->id ?? NULL,
                'titulo' => $pregunta->titulo,
                'opciones' => $pregunta->opciones,
                'active' => $pregunta->estado,
                'created_at' => $pregunta->created_at,
                'updated_at' => $pregunta->updated_at,
            ];

            $bar->advance();
        }

        $bar->finish();

        $output->newLine();

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getAndInsertEncuestasPreguntasRespuestasData($output)
    {
        $db = self::connect();

        $output->info('init getAndInsertEncuestasPreguntasRespuestasData');

        // $courses = Course::select('id', 'external_id')->whereNotNull('external_id')->get();
        $courses = [];
        $types = Taxonomy::getData('poll', 'tipo-pregunta')->get();
        // $types = [];
        $users = [];
        // $users = User::select('id', 'external_id')->whereNull('email')->get();
        // $preguntas = PollQuestion::select('id', 'external_id')->whereNotNull('external_id')->get();
        $preguntas = [];

        $count = $db->getTable('encuestas_respuestas')->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $db->getTable('encuestas_respuestas')->chunkById(100, function ($respuestas) use ($users, $types, $courses, $preguntas, $bar) {

            $chunk = [];

            $usuarios_ids = $respuestas->pluck('usuario_id')->toArray();
            $courses_ids = $respuestas->pluck('curso_id')->toArray();
            $questions_ids = $respuestas->pluck('pregunta_id')->toArray();

            $users = User::disableCache()->select('id', 'external_id')->whereIn('external_id', $usuarios_ids)->get();
            $courses = Course::disableCache()->select('id', 'external_id')->whereNotNull('external_id')->whereIn('external_id', $courses_ids)->get();
            $preguntas = PollQuestion::disableCache()->select('id', 'external_id')->whereNotNull('external_id')->whereIn('external_id', $questions_ids)->get();

            foreach ($respuestas as $key => $respuesta)
            {
                $type = $types->where('code', $respuesta->tipo_pregunta)->first();
                // $poll_id = $polls->where('external_id', $respuesta->encuesta_id)->first();
                $user = $users->where('external_id', $respuesta->usuario_id)->first();
                // $user = User::disableCache()->select('id', 'external_id', 'document')->where('external_id', $respuesta->usuario_id)->first();
                $course = $courses->where('external_id', $respuesta->curso_id)->first();
                // $course = Course::select('id', 'external_id')->where('external_id', $respuesta->curso_id)->first();
                // $question = PollQuestion::select('id', 'external_id')->where('external_id', $respuesta->pregunta_id)->first();
                $question = $preguntas->where('external_id', $respuesta->pregunta_id)->first();

                $chunk[] = [
                    'course_id' => $course->id ?? NULL,
                    'user_id' => $user->id ?? NULL,
                    // 'user_id' => 1,
                    'type_id' => $type->id ?? NULL,
                    'poll_question_id' => $question->id ?? NULL,

                    'respuestas' => $respuesta->respuestas,
                    'created_at' => $respuesta->created_at,
                    'updated_at' => $respuesta->updated_at,
                ];

                $bar->advance();
            }

            // DB::enableQueryLog();

            DB::table('poll_question_answers')->insert($chunk);

            // info(DB::getQueryLog());

            // info('poll_question_answers chunked inserted');
        });

        $bar->finish();
        $output->newLine();
    }

    protected function getResumenGeneralData($output)
    {
        $db = self::connect();

        $output->info('init getResumenGeneralData');

        // $rows = $db->getTable('resumen_general')->get();
        $count = $db->getTable('resumen_general')->count();

        // $users = User::select('id', 'external_id')->whereNotNull('external_id')->get();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $db->getTable('resumen_general')->chunkById(250, function ($rows) use ($bar) {
            
            $chunk = [];

            $usuarios_ids = $rows->pluck('usuario_id')->toArray();

            $users = User::disableCache()->select('id', 'external_id')->whereIn('external_id', $usuarios_ids)->get();

            foreach ($rows as $row)
            {
                // $user = User::disableCache()->select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                $user = $users->where('external_id', $row->usuario_id)->first();

                $current_summary_user = SummaryUser::disableCache()->where('user_id', $user->id)->first();

                $bar->advance();

                if ($current_summary_user) {

                    info("User => {$user->id} [OLD - {$row->usuario_id}] - {$user->document} ya tiene data en summary_user. Recalcular con la data actual.");

                    continue;
                }

                $chunk[] = [
                    'user_id' => $user->id ?? NULL,

                    'score' => $row->rank,
                    'attempts' => $row->intentos,
                    'grade_average' => $row->nota_prom,
                    'advanced_percentage' => $row->porcentaje,

                    'courses_assigned' => $row->cur_asignados,
                    'courses_completed' => $row->tot_completados,

                    'last_time_evaluated_at' => $row->last_ev,

                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }

            DB::table('summary_users')->insert($chunk);

            info('summary_users chunked inserted');
        });

        $bar->finish();

        $output->newLine();

        // return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getAndInsertResumenCursosData($output)
    {
        $db = self::connect();

        $output->info('init getAndInsertResumenCursosData CURSOS');

        $rows_reinicios = $db->getTable('reinicios')->where('tipo', 'por_curso')->get();

        // $users = User::select('id', 'external_id')->whereNull('email')->get();
        // $courses = Course::select('id', 'external_id')->whereNotNull('external_id')->get();
        // $admins = User::select('id', 'external_id', 'email')->whereNotNull('email')->get();
        // $courses = [];
        // $users = [];
        // $admins = [];
        $statuses = Taxonomy::getData('course', 'user-status')->get();

        $count = $db->getTable('resumen_x_curso')->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $db->getTable('resumen_x_curso')->chunkById(100, function ($rows_cursos) use ($rows_reinicios, $statuses, $bar) {

            $chunk = [];

            $usuarios_ids = $rows_cursos->pluck('usuario_id')->toArray();
            $courses_ids = $rows_cursos->pluck('curso_id')->toArray();

            $users = User::disableCache()->select('id', 'external_id')->whereIn('external_id', $usuarios_ids)->get();
            $courses = Course::disableCache()->select('id', 'external_id')->whereNotNull('external_id')->whereIn('external_id', $courses_ids)->get();

            info('Start resumen_x_curso chunk');

            foreach ($rows_cursos as $row)
            {
                $bar->advance();

                $status = $statuses->where('code', $row->estado)->first();
                $user = $users->where('external_id', $row->usuario_id)->first();
                $course = $courses->where('external_id', $row->curso_id)->first();
                // $user = User::disableCache()->select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                // $course = Course::select('id', 'external_id')->where('external_id', $row->curso_id)->first();
                $restart = $rows_reinicios->where('usuario_id', $row->usuario_id)->where('curso_id', $row->curso_id)->first();
                // $certification = $rows_diplomas->where('usuario_id', $row->usuario_id)->where('curso_id', $row->curso_id)->first();

                $restarts = 0;
                $restarter = NULL;

                if ($restart)
                {
                    $restarts = $restart->acumulado;
                    // $restarter = $admins->where('external_id', $restart->admin_id)->first();
                    $restarter = $restart->admin_id;
                }

                // $restarter_id = $users->where('external_id', $row->usuario_id)->first();

                $chunk[] = [
                    'user_id' => $user->id ?? NULL,
                    'course_id' => $course->id ?? NULL,
                    'status_id' => $status->id ?? NULL,

                    'assigned' => $row->asignados,
                    'passed' => $row->aprobados,
                    'taken' => $row->realizados,
                    'reviewed' => $row->revisados,
                    'failed' => $row->desaprobados,

                    'grade_average' => $row->nota_prom,
                    'advanced_percentage' => $row->porcentaje,

                    'attempts' => $row->intentos,
                    'views' => $row->visitas,

                    'restarts' => $restarts,
                    // 'restarter_id' => $restarter->id ?? NULL,
                    'old_admin_id' => $restarter,

                    'last_time_evaluated_at' => $row->last_ev ?? NULL,
                    // 'certification_issued_at' => $certification->fecha_emision ?? NULL,

                    // 'active' => $row->last_ev,

                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }

            DB::table('summary_courses')->insert($chunk);

            info('summary_courses chunked inserted');
        });

        $bar->finish();
        $output->newLine();
    }

    protected function getAndInsertResumenCursosDataCertifications($output)
    {
        $db = self::connect();

        $output->info('init getAndInsertResumenCursosData DIPLOMAS');

        $count = $db->getTable('diplomas')->whereNull('posteo_id')->whereNull('categoria_id')->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $db->getTable('diplomas')->whereNull('posteo_id')->whereNull('categoria_id')->chunkById(100, function ($rows_diplomas) use ($bar) {

            $usuarios_ids = $rows_diplomas->pluck('usuario_id')->toArray();
            $courses_ids = $rows_diplomas->pluck('curso_id')->toArray();

            $users = User::disableCache()->select('id', 'external_id')->whereIn('external_id', $usuarios_ids)->get();
            $courses = Course::disableCache()->select('id', 'external_id')->whereNotNull('external_id')->whereIn('external_id', $courses_ids)->get();

            foreach ($rows_diplomas as $row) {

                $bar->advance();

                // $user = User::disableCache()->select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                // $course = Course::select('id', 'external_id')->where('external_id', $row->curso_id)->first();
                $user = $users->where('external_id', $row->usuario_id)->first();
                $course = $courses->where('external_id', $row->curso_id)->first();

                if ($user AND $course) {

                    DB::table('summary_courses')
                        ->where('user_id', $user->id ?? NULL)
                        ->where('course_id', $course->id ?? NULL)
                        ->update(['certification_issued_at' => $row->created_at]);

                } else {

                    info("[OLD USER - {$row->usuario_id}] o [OLD COURSE - {$row->curso_id}] ya no existe. Diploma no agregada.");
                }


                // DB::table('summary_courses')
                    // ->updateOrInsert(['user_id' => $user->id ?? NULL, 'course_id' => $course->id ?? NULL], ['certification_issued_at' => $row->fecha_emision]);

            }

            info('summary_courses diplomas chunked updateOrInsert');
        });

        $bar->finish();

        $output->newLine();
    }

    protected function getAndInsertResumenTemasDataPruebas($output)
    {
        $db = self::connect();

        $output->info('init getAndInsertResumenTemasDataPruebas');

        $count = $db->getTable('pruebas')->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $sources = Taxonomy::getData('system', 'platform')->get();

        $db->getTable('pruebas')->chunkById(50, function ($rows_pruebas) use ($sources, $bar) {

            $usuarios_ids = $rows_pruebas->pluck('usuario_id')->toArray();
            $topics_ids = $rows_pruebas->pluck('posteo_id')->toArray();

            $users = User::disableCache()->select('id', 'external_id')->whereIn('external_id', $usuarios_ids)->get();
            $topics = Topic::disableCache()->select('id', 'external_id')->whereNotNull('external_id')->whereIn('external_id', $topics_ids)->get();

            $chunk = [];

            foreach ($rows_pruebas as $row) {

                $bar->advance();

                // $user = User::disableCache()->select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                // $topic = Topic::select('id', 'external_id')->where('external_id', $row->posteo_id)->first();
                $user = $users->where('external_id', $row->usuario_id)->first();
                $topic = $topics->where('external_id', $row->posteo_id)->first();
                $source = $sources->where('code', $row->fuente)->first();

                $chunk[] = [
                    'user_id' => $user->id ?? NULL,
                    'topic_id' => $topic->id ?? NULL,

                    'source_id' => $source->id ?? NULL,

                    'attempts' => $row->intentos,
                    'correct_answers' => $row->rptas_ok,
                    'failed_answers' => $row->rptas_fail,

                    'grade' => $row->nota,
                    'passed' => $row->resultado,

                    'answers' => $row->usu_rptas,

                    'last_time_evaluated_at' => $row->last_ev ?? NULL,

                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }

            DB::table('summary_topics')->insert($chunk);

            // info('summary_topics pruebas chunked inserted');
        });

        $bar->finish();

        $output->newLine();
    }

    protected function getAndInsertResumenTemasDataAbiertas($output)
    {
        $db = self::connect();

        $output->info('init getAndInsertResumenTemasDataPruebas');

        $count = $db->getTable('ev_abiertas')->where('posteo_id', '<>', 0)->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $sources = Taxonomy::getData('system', 'platform')->get();

        $db->getTable('ev_abiertas')->where('posteo_id', '<>', 0)->chunkById(50, function ($rows_ev_abiertas) use ($sources, $bar) {

            $usuarios_ids = $rows_ev_abiertas->pluck('usuario_id')->toArray();
            $topics_ids = $rows_ev_abiertas->pluck('posteo_id')->toArray();

            $users = User::disableCache()->select('id', 'external_id')->whereIn('external_id', $usuarios_ids)->get();
            $topics = Topic::disableCache()->select('id', 'external_id')->whereNotNull('external_id')->whereIn('external_id', $topics_ids)->get();

            $chunk = [];

            foreach ($rows_ev_abiertas as $prueba) {

                $bar->advance();
                // $topic = Topic::select('id', 'external_id')->where('external_id', $prueba->posteo_id)->first();
                // $user = User::disableCache()->select('id', 'external_id', 'document')->where('external_id', $prueba->usuario_id)->first();
                $topic = $topics->where('external_id', $prueba->posteo_id)->first();
                $user = $users->where('external_id', $prueba->usuario_id)->first();
                $source = $sources->where('code', $prueba->fuente)->first();
                // $user_id = User::where('external_id', $prueba->usuario_id)->first();

                if (!$user OR !$topic)
                {
                    info("[OLD - {$prueba->usuario_id}] o [OLD TOPIC => {$prueba->posteo_id}] no existe en IR. Verificar Eva Abiertas.");

                    continue;
                }

                $current_summary_topic = SummaryTopic::disableCache()->where('user_id', $user->id)->where('topic_id', $topic->id)->first();

                if ($current_summary_topic) {

                    info("User => {$user->id} [OLD - {$prueba->usuario_id}] - {$user->document} - TOPIC => {$topic->id} ya tiene data en summary_topic. Verificar según tipo de evaluación  (eva_bierta).");

                    $current_summary_topic->update(['answers_old' => $prueba->usu_rptas]);

                    continue;
                }

                $chunk[] = [
                    'topic_id' => $topic->id ?? NULL,
                    'user_id' => $user->id ?? NULL,
                    // 'answers' => $prueba->eva_abierta != 0 ? $prueba->usu_rptas : NULL,
                    'answers' => $prueba->usu_rptas,
                    'source_id' => $source->id ?? NULL,
                    // 'type_id' => $type_id,

                    'created_at' => $prueba->created_at,
                    'updated_at' => $prueba->updated_at,
                ];
            }

            DB::table('summary_topics')->insert($chunk);

            // info('summary_topics ev_abiertas chunked inserted');
        });

        $bar->finish();

        $output->newLine();
    }

    protected function getAndInsertResumenTemasDataReinicios($output)
    {
        $db = self::connect();

        $output->info('init getAndInsertResumenTemasDataReinicios');

        $count = $db->getTable('reinicios')->where('tipo', 'por_tema')->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $db->getTable('reinicios')->where('tipo', 'por_tema')->chunkById(250, function ($rows_reinicios) use ($bar) {

            $usuarios_ids = $rows_reinicios->pluck('usuario_id')->toArray();
            $topics_ids = $rows_reinicios->pluck('posteo_id')->toArray();

            $users = User::disableCache()->select('id', 'external_id')->whereIn('external_id', $usuarios_ids)->get();
            $topics = Topic::disableCache()->select('id', 'external_id')->whereNotNull('external_id')->whereIn('external_id', $topics_ids)->get();

            foreach ($rows_reinicios as $restart) {

                $bar->advance();

                $user = $users->where('external_id', $restart->usuario_id)->first();
                $topic = $topics->where('external_id', $restart->posteo_id)->first();
                // $user = User::disableCache()->select('id', 'external_id', 'document')->where('external_id', $restart->usuario_id)->first();
                // $topic = Topic::select('id', 'external_id')->where('external_id', $restart->posteo_id)->first();

                if (!$user OR !$topic)
                {
                    info("[OLD - {$restart->usuario_id}] o [OLD TOPIC => {$restart->posteo_id}] no existe en IR. Verificar Reinicios.");

                    continue;
                }

                $current_summary_topic = SummaryTopic::disableCache()->where('user_id', $user->id)->where('topic_id', $topic->id)->first();

                if (!$current_summary_topic) {

                    info("User => {$user->id} [OLD - {$restart->usuario_id}] - {$user->document} - TOPIC => {$topic->id} no tiene data en summary_topic. Verificar en reinicios.");

                    continue;
                }

                $restarts = 0;
                $restarter = NULL;

                if ($restart) {

                    $restarts = $restart->acumulado;
                    // $restarter = $admins->where('external_id', $restart->admin_id)->first();
                    $restarter = $restart->admin_id;
                }

                $data = [
                    'restarts' => $restarts, // from reinicios
                    // 'restarter_id' => $restarter->id ?? NULL, // from reinicios
                    'old_admin_id' => $restarter, // from reinicios
                ];

                DB::table('summary_topics')
                    ->where('user_id', $user->id ?? NULL)
                    ->where('topic_id', $topic->id ?? NULL)
                    ->update($data);
            }

            // info('summary_topics reinicios chunked update');
        });

        $bar->finish();

        $output->newLine();
    }

    protected function getAndInsertResumenTemasDataVisitas($output)
    {
        $db = self::connect();

        $output->info('init getAndInsertResumenTemasDataVisitas');

        $count = $db->getTable('visitas')->where('id', '>', 2300000)->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $statuses = Taxonomy::getData('topic', 'user-status')->get();

        $db->getTable('visitas')->where('id', '>', 2300000)->chunkById(100, function ($rows_visitas) use ($statuses, $bar) {

            $usuarios_ids = $rows_visitas->pluck('usuario_id')->toArray();
            $topics_ids = $rows_visitas->pluck('post_id')->toArray();

            $users = User::disableCache()->select('id', 'external_id')->whereIn('external_id', $usuarios_ids)->get();
            $topics = Topic::disableCache()->select('id', 'external_id')->whereNotNull('external_id')->whereIn('external_id', $topics_ids)->get();

            foreach ($rows_visitas as $row) {

                $bar->advance();

                $user = $users->where('external_id', $row->usuario_id)->first();
                $topic = $topics->where('external_id', $row->post_id)->first();
                // $user = User::disableCache()->select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                // $topic = Topic::select('id', 'external_id')->where('external_id', $row->post_id)->first();
                $status = $statuses->where('code', $row->estado_tema)->first();

                if (!$user OR !$topic)
                {
                    info("[OLD - {$row->usuario_id}] o [OLD TOPIC => {$row->post_id}] no existe en IR. Verificar Visitas.");

                    continue;
                }

                $current_summary_topic = SummaryTopic::disableCache()->where('user_id', $user->id)->where('topic_id', $topic->id)->first();

                if (!$current_summary_topic) {

                    info("User => {$user->id} [OLD - {$row->usuario_id}] - {$user->document} - TOPIC => {$topic->id} no tiene data en summary_topic. Verificar en visitas.");

                    continue;
                }

                $data = [
                    'downloads' => $row->descargas,
                    'views' => $row->sumatoria,
                    'status_id' => $status->id ?? NULL,
                ];

                DB::table('summary_topics')
                    ->where('user_id', $user->id ?? NULL)
                    ->where('topic_id', $topic->id ?? NULL)
                    ->update($data);

                // DB::table('summary_topics')
                //     ->updateOrInsert(['user_id' => $user->id ?? NULL, 'topic_id' => $topic->id ?? NULL], $data);

            }

            // info('summary_topics visitas chunked updateOrInsert');
        });

        $bar->finish();

        $output->newLine();
    }
}
