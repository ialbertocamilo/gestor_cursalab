<?php

namespace App\Models\UCMigrationData;

use Illuminate\Database\Eloquent\Model;
use App\Models\Encuesta;
use App\Models\Taxonomy;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\User;
use DB;

class Migration_3 extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    // protected function migratePruebas()
    // {
    //     $data = self::getPruebasData();
    //     self::insertChunkedData($data, 'records');

    //     $data = self::getPruebasLibresData();
    //     self::insertChunkedData($data, 'records');
    // }

    protected function migrateEncuestas()
    {
        $data = self::getEncuestasData();
        self::insertChunkedData($data, 'polls');

        $data = self::getEncuestasPreguntasData();
        self::insertChunkedData($data, 'poll_questions');

        $data = self::getEncuestasPreguntasRespuestasData();
        self::insertChunkedData($data, 'poll_question_answers');
    }

    protected function migrateResumenes()
    {
        $data = self::getResumenGeneralData();
        self::insertChunkedData($data, 'summary_users');

        $data = self::getResumenCursosData();
        self::insertChunkedData($data, 'summary_courses');

        self::getAndInsertResumenTemasData();
        // self::insertChunkedData($data, 'summary_topics');
    }


    public function insertChunkedData($data, $table_name)
    {
        foreach ($data as $chunk)
        {
            DB::table($table_name)->insert($chunk);
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
            $topic_id = $types->where('code', $encuesta->tipo)->first();

            $data[] = [
                'external_id' => $encuesta->id,
                'type_id' => $type_id,
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
        $polls = Encuesta::all();

        $data = [];

        foreach ($preguntas as $key => $pregunta)
        {
            $type_id = $types->where('code', $pregunta->tipo_pregunta)->first();
            $poll_id = $polls->where('external_id', $pregunta->encuesta_id)->first();

            $data[] = [
                'external_id' => $pregunta->id,
                'poll_id' => $poll_id,
                'type_id' => $type_id,
                'titulo' => $pregunta->titulo,
                'opciones' => $pregunta->opciones,
                'active' => $pregunta->estado,
                'created_at' => $pregunta->created_at,
                'updated_at' => $pregunta->updated_at,
            ];
        }

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getEncuestasPreguntasRespuestasData()
    {
        $db = self::connect();

        $respuestas = $db->getTable('encuestas_respuestas')->get();
        $courses = Course::select('id', 'external_id')->get();
        $types = Taxonomy::getData('poll', 'tipo-pregunta')->get();
        $users = User::select('id', 'external_id')->whereNull('email')->get();

        $data = [];

        foreach ($respuestas as $key => $respuesta)
        {
            $type_id = $types->where('code', $respuesta->tipo_pregunta)->first();
            $poll_id = $polls->where('external_id', $respuesta->encuesta_id)->first();
            $user_id = $users->where('external_id', $respuesta->usuario_id)->first();
            $course_id = $courses->where('external_id', $respuesta->curso_id)->first();

            $data[] = [
                'course_id' => $course_id,
                'user_id' => $user_id,
                'type_id' => $type_id,
                'poll_question_id' => $question_id,

                'respuestas' => $respuesta->respuestas,
                'created_at' => $respuesta->created_at,
                'updated_at' => $respuesta->updated_at,
            ];
        }

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getResumenGeneralData()
    {
        $db = self::connect();

        $rows = $db->getTable('resumen_general')->get();

        $users = User::select('id', 'external_id')->whereNull('email')->get();

        $data = [];

        foreach ($rows as $row)
        {
            $user_id = $users->where('external_id', $row->usuario_id)->first();
            // $user_id = User::where('external_id', $row->usuario_id)->first();

            $data[] = [
                'user_id' => $user_id,

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

    protected function getResumenCursosData()
    {
        $db = self::connect();

        $rows_cursos = $db->getTable('resumen_x_curso')->get();
        $rows_reinicios = $db->getTable('reinicios')->where('tipo', 'por_curso')->get();
        $rows_diplomas = $db->getTable('diplomas')->whereNull('posteo_id')->get();

        $users = User::select('id', 'external_id')->whereNull('email')->get();
        $courses = Course::select('id', 'external_id')->get();
        $admins = User::select('id', 'external_id', 'email')->whereNotNull('email')->get();
        $statuses = Taxonomy::getData('course', 'user-status')->get();

        $data = [];

        foreach ($rows_cursos as $row)
        {
            $status_id = $statuses->where('code', $row->estado)->first();
            $user_id = $users->where('external_id', $row->usuario_id)->first();
            $course_id = $courses->where('external_id', $row->curso_id)->first();
            $restart = $rows_reinicios->where('usuario_id', $row->usuario_id)->where('curso_id', $row->curso_id)->first();
            $certification = $rows_diplomas->where('usuario_id', $row->usuario_id)->where('curso_id', $row->curso_id)->first();

            $restarts = 0;
            $restarter = NULL;

            if ($restart)
            {
                $restarts = $restart->acumulado;
                $restarter = $admins->where('external_id', $row->admin_id)->first();
            }

            $restarter_id = $users->where('external_id', $row->usuario_id)->first();

            $data[] = [
                'user_id' => $user_id,
                'course_id' => $course_id,
                'status_id' => $status_id,

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
                'certification_issued_at' => $certification->fecha_emision ?? NULL,

                // 'active' => $row->last_ev,

                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        }

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getAndInsertResumenTemasData()
    {
        $db = self::connect();

        $users = User::select('id', 'external_id')->whereNull('email')->get();
        $admins = User::select('id', 'external_id', 'email')->whereNotNull('email')->get();
        $topics = Posteo::select('id', 'external_id')->get();
        $sources = Taxonomy::getData('system', 'source')->get();
        $statuses = Taxonomy::getData('topic', 'user-status')->get();

        $db->getTable('pruebas')->chunkById(5000, function ($rows_pruebas) use ($users, $topics, $sources) {
            
            $chunk = [];
            
            foreach ($rows_pruebas as $row) {
                //
                $user_id = $users->where('external_id', $row->usuario_id)->first();
                $topic_id = $topics->where('external_id', $row->curso_id)->first();
                // $source_id = $sources->where('code', $prueba->fuente)->first();

                $chunk[] = [
                    'user_id' => $user_id,
                    'topic_id' => $topic_id,

                    // 'source_id' => $source_id,

                    'attempts' => $row->intentos,
                    'correct_answers' => $row->rptas_ok,
                    'failed_answers' => $row->rptas_fail,

                    'grade' => $row->nota,
                    'passed' => $row->resultado,

                    'answers' => $prueba->usu_rptas,
                    
                    'last_time_evaluated_at' => $row->last_ev ?? NULL,

                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }

            DB::table('summary_topics')->insert($chunk);
        });

        $db->getTable('ev_abiertas')->where('posteo_id', '<>', 0)->chunkById(5000, function ($rows_ev_abiertas) use ($db, $topics, $users, $sources) {
            
            $chunk = [];

            foreach ($rows_ev_abiertas as $prueba)
            {
                $topic_id = $topics->where('external_id', $prueba->posteo_id)->first();
                $user_id = $users->where('external_id', $prueba->usuario_id)->first();
                // $source_id = $sources->where('code', $prueba->fuente)->first();
                // $user_id = User::where('external_id', $prueba->usuario_id)->first();

                $chunk[] = [
                    'topic_id' => $topic_id,
                    'user_id' => $user_id,
                    'answers' => $prueba->usu_rptas,
                    // 'source_id' => $source_id,
                    'type_id' => $type_id,

                    'created_at' => $prueba->created_at,
                    'updated_at' => $prueba->updated_at,
                ];
            }

            DB::table('summary_topics')->insert($chunk);
        });

        $db->getTable('reinicios')->where('tipo', 'por_tema')->chunkById(5000, function ($rows_reinicios) use ($admins, $db, $topics, $users) {
            foreach ($rows_reinicios as $restart) {

                $user_id = $users->where('external_id', $restart->usuario_id)->first();
                $topic_id = $topics->where('external_id', $restart->curso_id)->first();

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

                $db->getTable('summary_topics')
                    ->where('user_id', $user_id)
                    ->where('topic_id', $topic_id)
                    ->update($data);
            }
        });

        $db->getTable('visitas')->chunkById(5000, function ($rows_visitas) use ($db, $topics, $users, $statuses) {
            foreach ($rows_visitas as $row) {

                $user_id = $users->where('external_id', $row->usuario_id)->first();
                $topic_id = $topics->where('external_id', $row->curso_id)->first();
                $status_id = $statuses->where('code', $row->estado_tema)->first();

                $data = [
                    'downloads' => $row->descargas, 
                    'views' => $row->sumatorias, 
                    'status_id' => $status_id, 
                ];

                $db->getTable('summary_topics')
                    ->updateOrInsert(['user_id' => $user_id, 'topic_id' => $topic_id], $data);
            }
        });

    }
}
