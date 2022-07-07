<?php

namespace App\Models\UCMigrationData;

// use App\Models\Support\ExternalDatabase;
// use App\Models\Support\ExternalLMSMigration;
use Illuminate\Database\Eloquent\Model;
use DB;

class Migration_3 extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function migratePruebas()
    {
        $data = self::getPruebasData();
        self::insertChunkedData($data, 'records');

        $data = self::getPruebasLibresData();
        self::insertChunkedData($data, 'records');
    }

    protected function migrateEncuestas()
    {
        $data = self::getEncuestasData();
        self::insertChunkedData($data, 'polls');
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

    protected function getPruebasData()
    {
        $db = self::connect();

        $pruebas = $db->getTable('pruebas')->get();

        $topics = Topic::select('id', 'external_id')->get();
        $sources = Taxonomy::getData('system', 'source')->get();
        $type_id = Taxonomy::getFirstData('quiz', 'type', 'graded')->id;
        $users = User::select('id', 'external_id')->get();

        $data = [];

        foreach ($pruebas as $prueba)
        {
            $topic_id = $topics->where('external_id', $prueba->posteo_id)->first();
            $user_id = $users->where('external_id', $prueba->usuario_id)->first();
            $source_id = $sources->where('code', $prueba->fuente)->first();
            // $user_id = User::where('external_id', $prueba->usuario_id)->first();

            $data[] = [
                // 'external_id' => $prueba->id,

                'topic_id' => $topic_id,
                'user_id' => $user_id,
                'attempts' => $prueba->intentos,

                'correct_answers' => $prueba->rptas_ok,
                'failed_answers' => $prueba->rptas_fail,

                'grade' => $prueba->nota,
                'last_time_evaluated_at' => $prueba->last_ev,
                'answers' => $prueba->usu_rptas,
                'source_id' => $source_id,
                'approved' => $prueba->resultado,

                'created_at' => $prueba->created_at,
                'updated_at' => $prueba->updated_at,
            ];
        }

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }

    protected function getPruebasLibresData()
    {
        $db = self::connect();

        $pruebas = $db->getTable('ev_abiertas')->get();

        $topics = Topic::select('id', 'external_id')->get();
        $sources = Taxonomy::getData('system', 'source')->get();
        $type_id = Taxonomy::getFirstData('quiz', 'type', 'free')->id;
        $users = User::select('id', 'external_id')->get();

        $data = [];

        foreach ($pruebas as $prueba)
        {
            $topic_id = $topics->where('external_id', $prueba->posteo_id)->first();
            $user_id = $users->where('external_id', $prueba->usuario_id)->first();
            $source_id = $sources->where('code', $prueba->fuente)->first();
            // $user_id = User::where('external_id', $prueba->usuario_id)->first();

            $data[] = [
                // 'external_id' => $prueba->id,

                'topic_id' => $topic_id,
                'user_id' => $user_id,
                'answers' => $prueba->usu_rptas,
                'source_id' => $source_id,
                'type_id' => $type_id,
                // 'approved' => $prueba->resultado,

                'created_at' => $prueba->created_at,
                'updated_at' => $prueba->updated_at,
            ];
        }

        return array_chunk($data, self::CHUNK_LENGTH, true);
    }
}
