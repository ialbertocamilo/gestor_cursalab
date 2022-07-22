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

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function migrateEncuestas()
    {
        info('getEncuestasData');
        $data = self::getEncuestasData();
        self::insertChunkedData($data, 'polls');

        info('getEncuestasPreguntasData');
        $data = self::getEncuestasPreguntasData();
        self::insertChunkedData($data, 'poll_questions');

        info('getAndInsertEncuestasPreguntasRespuestasData');
        self::getAndInsertEncuestasPreguntasRespuestasData();
    }

    protected function migrateResumenes()
    {
        info('getResumenGeneralData');
        $data = self::getResumenGeneralData();
        self::insertChunkedData($data, 'summary_users');

        info('getAndInsertResumenCursosData');
        self::getAndInsertResumenCursosData();
        // self::insertChunkedData($data, 'summary_courses');

        info('getAndInsertResumenTemasData');
        self::getAndInsertResumenTemasData();
    }


    public function insertChunkedData($data, $table_name)
    {
        foreach ($data as $chunk)
        {
            DB::table($table_name)->insert($chunk);

            info($table_name . ' chunked inserted');
        }
    }

    protected function getEncuestasData()
    {
        $db = self::connect();

        $encuestas = $db->getTable('encuestas')->get();
        $types = Taxonomy::getData('poll', 'tipo')->get();

        $data = [];

        foreach ($encuestas as $key => $encuesta)
        {
            $type = $types->where('code', $encuesta->tipo)->first();

            $data[] = [
                'external_id' => $encuesta->id,
                'type_id' => $type->id ?? NULL,
                'anonima' => $encuesta->anonima,
                'titulo' => $encuesta->titulo,
                'imagen' => $encuesta->imagen,
                'active' => $encuesta->estado,
                'created_at' => $encuesta->created_at,
                'updated_at' => $encuesta->updated_at,
            ];
        }

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getEncuestasPreguntasData()
    {
        $db = self::connect();

        $preguntas = $db->getTable('encuestas_preguntas')->get();
        $types = Taxonomy::getData('poll', 'tipo-pregunta')->get();
        $polls = Poll::all();

        $data = [];

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
        }

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getAndInsertEncuestasPreguntasRespuestasData()
    {
        $db = self::connect();

        $courses = Course::select('id', 'external_id')->get();
        $types = Taxonomy::getData('poll', 'tipo-pregunta')->get();
        $users = User::select('id', 'external_id')->whereNull('email')->get();
        $preguntas = PollQuestion::select('id', 'external_id')->get();

        $db->getTable('encuestas_respuestas')->chunkById(2500, function ($respuestas) use ($users, $types, $courses, $preguntas) {

            $chunk = [];

            foreach ($respuestas as $key => $respuesta)
            {
                $type = $types->where('code', $respuesta->tipo_pregunta)->first();
                // $poll_id = $polls->where('external_id', $respuesta->encuesta_id)->first();
                $user = $users->where('external_id', $respuesta->usuario_id)->first();
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
            }

            DB::table('poll_question_answers')->insert($chunk);

            info('poll_question_answers chunked inserted');
        });



    }

    protected function getResumenGeneralData()
    {
        $db = self::connect();

        $rows = $db->getTable('resumen_general')->get();

        $users = User::select('id', 'external_id')->whereNull('email')->get();

        $data = [];

        foreach ($rows as $row)
        {
            $user = $users->where('external_id', $row->usuario_id)->first();
            // $user = User::where('external_id', $row->usuario_id)->first();

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

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getAndInsertResumenCursosData()
    {
        $db = self::connect();

        $rows_reinicios = $db->getTable('reinicios')->where('tipo', 'por_curso')->get();

        $users = User::select('id', 'external_id')->whereNull('email')->get();
        $courses = Course::select('id', 'external_id')->get();
        $admins = User::select('id', 'external_id', 'email')->whereNotNull('email')->get();
        $statuses = Taxonomy::getData('course', 'user-status')->get();

        $db->getTable('resumen_x_curso')->chunkById(1000, function ($rows_cursos) use ($users, $rows_reinicios, $courses, $statuses, $admins) {

            $chunk = [];

            info('Start resumen_x_curso chunk');

            foreach ($rows_cursos as $row)
            {
                $status = $statuses->where('code', $row->estado)->first();
                $user = $users->where('external_id', $row->usuario_id)->first();
                $course = $courses->where('external_id', $row->curso_id)->first();
                $restart = $rows_reinicios->where('usuario_id', $row->usuario_id)->where('curso_id', $row->curso_id)->first();
                // $certification = $rows_diplomas->where('usuario_id', $row->usuario_id)->where('curso_id', $row->curso_id)->first();

                $restarts = 0;
                $restarter = NULL;

                if ($restart)
                {
                    $restarts = $restart->acumulado;
                    $restarter = $admins->where('external_id', $restart->admin_id)->first();
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
                    'restarter_id' => $restarter->id ?? NULL,

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

        $db->getTable('diplomas')->whereNull('posteo_id')->chunkById(2500, function ($rows_diplomas) use ($courses, $users) {
            foreach ($rows_diplomas as $row) {

                $user = $users->where('external_id', $row->usuario_id)->first();
                $course = $courses->where('external_id', $row->curso_id)->first();

                DB::table('summary_courses')
                    ->updateOrInsert(['user_id' => $user->id ?? NULL, 'course_id' => $course->id ?? NULL], ['certification_issued_at' => $row->fecha_emision]);

            }

            info('summary_courses diplomas chunked updateOrInsert');
        });
    }

    protected function getAndInsertResumenTemasData()
    {
        $db = self::connect();

        $users = User::select('id', 'external_id')->whereNull('email')->get();
        $admins = User::select('id', 'external_id', 'email')->whereNotNull('email')->get();
        $topics = Posteo::select('id', 'external_id')->get();
        $sources = Taxonomy::getData('system', 'source')->get();
        $statuses = Taxonomy::getData('topic', 'user-status')->get();

        $db->getTable('pruebas')->chunkById(1000, function ($rows_pruebas) use ($users, $topics, $sources) {

            $chunk = [];

            foreach ($rows_pruebas as $row) {
                //
                $user = $users->where('external_id', $row->usuario_id)->first();
                $topic = $topics->where('external_id', $row->curso_id)->first();
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
                $user = $users->where('external_id', $prueba->usuario_id)->first();
                // $source_id = $sources->where('code', $prueba->fuente)->first();
                // $user_id = User::where('external_id', $prueba->usuario_id)->first();

                $chunk[] = [
                    'topic_id' => $topic->id ?? NULL,
                    'user_id' => $user->id ?? NULL,
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

                $user = $users->where('external_id', $restart->usuario_id)->first();
                $topic = $topics->where('external_id', $restart->curso_id)->first();

                $restarts = 0;
                $restarter = NULL;

                if ($restart)
                {
                    $restarts = $restart->acumulado;
                    $restarter = $admins->where('external_id', $restart->admin_id)->first();
                }

                $data = [
                    'restarts' => $restarts, // from reinicios
                    'restarter_id' => $restarter->id ?? NULL, // from reinicios
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

                $user = $users->where('external_id', $row->usuario_id)->first();
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
