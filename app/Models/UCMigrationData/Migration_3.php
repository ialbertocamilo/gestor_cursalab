<?php

namespace App\Models\UCMigrationData;

use App\Models\Poll;
use App\Models\PollQuestion;
use Illuminate\Database\Eloquent\Model;
use App\Models\Taxonomy;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\User;
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
        self::insertChunkedData($data, 'polls');

        info('getEncuestasPreguntasData');
        $data = self::getEncuestasPreguntasData($output);
        self::insertChunkedData($data, 'poll_questions');
    }


    protected function migrateEncuestasRespuestas()
    {
        info('getAndInsertEncuestasPreguntasRespuestasData');
        self::getAndInsertEncuestasPreguntasRespuestasData();
    }

    protected function migrateSummaryUsers($output)
    {
        info('getResumenGeneralData');
        $data = self::getResumenGeneralData($output);
        self::insertChunkedData($data, 'summary_users', $output);
    }

    protected function migrateSummaryCourses($output)
    {
        info('getAndInsertResumenCursosData');
        self::getAndInsertResumenCursosData($output);
    }

    protected function migrateSummaryTopics($output)
    {
        info('getAndInsertResumenTemasData');
        self::getAndInsertResumenTemasData($output);
    }


    public function insertChunkedData($data, $table_name, $output)
    {
        $output->line('init chunked ' . $table_name);
        $output->newLine();

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

        $output->line('init getEncuestasData');
        $output->newLine();

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

        $output->line('init getEncuestasPreguntasData');
        $output->newLine();

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

        $output->line('init getAndInsertEncuestasPreguntasRespuestasData');
        $output->newLine();

        $courses = Course::select('id', 'external_id')->whereNotNull('external_id')->get();
        $types = Taxonomy::getData('poll', 'tipo-pregunta')->get();
        $users = [];
        // $users = User::select('id', 'external_id')->whereNull('email')->get();
        $preguntas = PollQuestion::select('id', 'external_id')->get();

        $count = $db->getTable('encuestas_respuestas')->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $db->getTable('encuestas_respuestas')->chunkById(2500, function ($respuestas) use ($users, $types, $courses, $preguntas, $bar) {

            $chunk = [];

            foreach ($respuestas as $key => $respuesta)
            {
                $type = $types->where('code', $respuesta->tipo_pregunta)->first();
                // $poll_id = $polls->where('external_id', $respuesta->encuesta_id)->first();
                // $user = $users->where('external_id', $respuesta->usuario_id)->first();
                $user = User::select('id', 'external_id', 'document')->where('external_id', $respuesta->usuario_id)->first();
                $course = $courses->where('external_id', $respuesta->curso_id)->first();
                $question = $preguntas->where('external_id', $respuesta->pregunta_id)->first();

                $chunk[] = [
                    'course_id' => $course->id ?? NULL,
                    'user_id' => $user->id ?? NULL,
                    'type_id' => $type->id ?? NULL,
                    'poll_question_id' => $question->id ?? NULL,

                    'respuestas' => $respuesta->respuestas,
                    'created_at' => $respuesta->created_at,
                    'updated_at' => $respuesta->updated_at,
                ];

                $bar->advance();
            }

            DB::table('poll_question_answers')->insert($chunk);

            info('poll_question_answers chunked inserted');
        });

        $bar->finish();
        $output->newLine();
    }

    protected function getResumenGeneralData($output)
    {
        $db = self::connect();

        $output->line('init getResumenGeneralData');
        $output->newLine();

        $rows = $db->getTable('resumen_general')->get();

        // $users = User::select('id', 'external_id')->whereNotNull('external_id')->get();
        $data = [];

        $bar = $output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row)
        {
            $user = User::select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
            // $user = $users->where('external_id', $row->usuario_id)->first();

            $current_summary_user = SummaryUser::getCurrentRow($user, $user);

            $bar->advance();

            if ($current_summary_user) {

                info("User => {$user->id} [OLD - {$row->usuario_id}] - {$user->document} ya tiene data en summary_user. Recalcular con la data actual.");
                
                continue;
            }

            $data[] = [
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

        $bar->finish();

        $output->newLine();

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getAndInsertResumenCursosData($output)
    {
        $db = self::connect();

        $output->line('init getAndInsertResumenCursosData CURSOS');
        $output->newLine();

        $rows_reinicios = $db->getTable('reinicios')->where('tipo', 'por_curso')->get();

        // $users = User::select('id', 'external_id')->whereNull('email')->get();
        $courses = Course::select('id', 'external_id')->whereNotNull('external_id')->get();
        // $admins = User::select('id', 'external_id', 'email')->whereNotNull('email')->get();
        $users = [];
        $admins = [];
        $statuses = Taxonomy::getData('course', 'user-status')->get();

        $count = $db->getTable('resumen_x_curso')->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $db->getTable('resumen_x_curso')->chunkById(1000, function ($rows_cursos) use ($users, $rows_reinicios, $courses, $statuses, $admins, $bar) {

            $chunk = [];

            info('Start resumen_x_curso chunk');

            foreach ($rows_cursos as $row)
            {
                $bar->advance();

                $status = $statuses->where('code', $row->estado)->first();
                // $user = $users->where('external_id', $row->usuario_id)->first();
                $user = User::select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                $course = $courses->where('external_id', $row->curso_id)->first();
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

        $output->line('init getAndInsertResumenCursosData DIPLOMAS');
        $output->newLine();

        $count = $db->getTable('diplomas')->count();

        $bar = $output->createProgressBar($count);
        $bar->start();

        $db->getTable('diplomas')->whereNull('posteo_id')->chunkById(2500, function ($rows_diplomas) use ($courses, $users, $bar) {
            foreach ($rows_diplomas as $row) {

                $bar->advance();

                // $user = $users->where('external_id', $row->usuario_id)->first();
                $user = User::select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                $course = $courses->where('external_id', $row->curso_id)->first();

                DB::table('summary_courses')
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->update(['certification_issued_at' => $row->fecha_emision]);

                // DB::table('summary_courses')
                    // ->updateOrInsert(['user_id' => $user->id ?? NULL, 'course_id' => $course->id ?? NULL], ['certification_issued_at' => $row->fecha_emision]);

            }

            info('summary_courses diplomas chunked updateOrInsert');
        });
        
        $bar->finish();

        $output->newLine();
    }

    protected function getAndInsertResumenTemasData($output)
    {
        $db = self::connect();

        $users = [];
        $admins = [];
        // $users = User::select('id', 'external_id')->whereNull('email')->get();
        // $admins = User::select('id', 'external_id', 'email')->whereNotNull('email')->get();
        $topics = Topic::select('id', 'external_id')->whereNotNull('external_id')->get();
        $sources = Taxonomy::getData('system', 'source')->get();
        $statuses = Taxonomy::getData('topic', 'user-status')->get();

        $db->getTable('pruebas')->chunkById(1000, function ($rows_pruebas) use ($users, $topics, $sources) {

            $chunk = [];

            foreach ($rows_pruebas as $row) {
                //
                // $user = $users->where('external_id', $row->usuario_id)->first();
                $user = User::select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                $topic = $topics->where('external_id', $row->posteo_id)->first();
                // $source_id = $sources->where('code', $prueba->fuente)->first();

                $chunk[] = [
                    'user_id' => $user->id ?? NULL,
                    'topic_id' => $topic->id ?? NULL,

                    // 'source_id' => $source_id,

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

            info('summary_topics pruebas chunked inserted');
        });

        $db->getTable('ev_abiertas')->where('posteo_id', '<>', 0)->chunkById(2500, function ($rows_ev_abiertas) use ($topics, $users, $sources) {

            $chunk = [];

            foreach ($rows_ev_abiertas as $prueba)
            {
                $topic = $topics->where('external_id', $prueba->posteo_id)->first();
                $user = User::select('id', 'external_id', 'document')->where('external_id', $prueba->usuario_id)->first();
                // $user = $users->where('external_id', $prueba->usuario_id)->first();
                // $source_id = $sources->where('code', $prueba->fuente)->first();
                // $user_id = User::where('external_id', $prueba->usuario_id)->first();

                $current_summary_topic = SummaryTopic::getCurrentRow($topic, $user);

                if ($current_summary_topic) {

                     info("User => {$user->id} [OLD - {$prueba->usuario_id}] - {$user->document} - TOPIC => {$topic->id} ya tiene data en summary_topic. Verificar según tipo de evaluación  (eva_bierta).");

                    $current_summary_topic->update(['answers_old' => $prueba->usu_rptas]);

                    continue;
                }

                // $field = $prueba->eva_abierta == 0 ? 'answers_open_old' : 'answers';

                $chunk[] = [
                    'topic_id' => $topic->id ?? NULL,
                    'user_id' => $user->id ?? NULL,
                    // 'answers' => $prueba->eva_abierta != 0 ? $prueba->usu_rptas : NULL,
                    'answers' => $prueba->usu_rptas,
                    // 'source_id' => $source_id,
                    // 'type_id' => $type_id,

                    'created_at' => $prueba->created_at,
                    'updated_at' => $prueba->updated_at,
                ];
            }

            DB::table('summary_topics')->insert($chunk);

            info('summary_topics ev_abiertas chunked inserted');
        });

        $db->getTable('reinicios')->where('tipo', 'por_tema')->chunkById(2500, function ($rows_reinicios) use ($admins, $db, $topics, $users) {
            foreach ($rows_reinicios as $restart) {

                // $user = $users->where('external_id', $restart->usuario_id)->first();
                $user = User::select('id', 'external_id', 'document')->where('external_id', $restart->usuario_id)->first();
                $topic = $topics->where('external_id', $restart->curso_id)->first();

                $restarts = 0;
                $restarter = NULL;

                if ($restart)
                {
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

            info('summary_topics reinicios chunked update');
        });

        $db->getTable('visitas')->chunkById(2500, function ($rows_visitas) use ($db, $topics, $users, $statuses) {
            foreach ($rows_visitas as $row) {

                // $user = $users->where('external_id', $row->usuario_id)->first();
                $user = User::select('id', 'external_id', 'document')->where('external_id', $row->usuario_id)->first();
                $topic = $topics->where('external_id', $row->curso_id)->first();
                $status = $statuses->where('code', $row->estado_tema)->first();

                $data = [
                    'downloads' => $row->descargas,
                    'views' => $row->sumatorias,
                    'status_id' => $status->id ?? NULL,
                ];

                DB::table('summary_topics')
                    ->updateOrInsert(['user_id' => $user->id ?? NULL, 'topic_id' => $topic->id ?? NULL], $data);

            }

            info('summary_topics visitas chunked updateOrInsert');
        });

    }
}
