<?php

namespace App\Models\Support;

use App\Models\Taxonomy;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Model;

use DB;

class ExternalLMSMigration extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect($db_data)
    {
        return new OTFConnection($db_data);
    }

    protected function setMigrationData($db_config)
    {
        $db = self::connect($db_config);
        $client_LMS_data = [
            'users' => [], 'schools' => [], 'courses' => [], 'course_school' => [],
            'topics' => [], 'questions' => []
        ];

        // TODO: Migrate users / usuarios
        $this->setUsersData($client_LMS_data, $db);
        // TODO: Migrate schools / categorias
        $this->setSchoolsData($client_LMS_data, $db);
        // TODO: Migrate courses / cursos
        $this->setCoursesData($client_LMS_data, $db);
        // TODO: Migrate topics / posteos
        $this->setTopicsData($client_LMS_data, $db);
        // TODO: Migrate preguntas / questions-options
        $this->setQuestions_OptionsData($client_LMS_data, $db);

        return $client_LMS_data;
    }

    public function setUsersData(&$result, $db)
    {
        $now = now()->format('Y-m-d H:i:s');
        $temp['users'] = $db->getTable('usuarios')
            ->select(
                'id', 'nombre', 'apellido_paterno', 'apellido_materno',
                'email', 'estado',
                'dni' // ???
            )
            ->get();

        foreach ($temp['users'] as $user) {
            $result['users'][] = [
                'external_id' => $user->id,

                'name' => $user->nombre,
                'lastname' => $user->apellido_paterno,
                'surname' => $user->apellido_materno,
                'email' => $user->email,

                'active' => $user->estado,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $result['users'] = array_chunk($result['users'], self::CHUNK_LENGTH, true);
    }

    public function setSchoolsData(&$result, $db)
    {
        $now = now()->format('Y-m-d H:i:s');

        $temp['schools'] = $db->getTable('categorias')
            ->select('id', 'nombre', 'descripcion', 'orden', 'estado')
            ->get();

        foreach ($temp['schools'] as $school) {
            $result['schools'][] = [
                'external_id' => $school->id,

                'name' => $school->nombre,
                'description' => $school->descripcion,
                'position' => $school->orden,

                'active' => $school->estado,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $result['schools'] = array_chunk($result['schools'], self::CHUNK_LENGTH, true);
    }

    public function setCoursesData(&$result, $db)
    {
        $now = now()->format('Y-m-d H:i:s');

        $temp['courses'] = $db->getTable('cursos')
            ->select('id', 'categoria_id', 'nombre', 'descripcion', 'orden', 'estado')
            ->get();

        foreach ($temp['courses'] as $course) {
            $result['course_school'][] = [
                'categoria_id' => $course->categoria_id,
                'curso_id' => $course->id,
            ];

            $result['courses'][] = [
                'external_id' => $course->id,

                'name' => $course->nombre,
                'description' => $course->descripcion,

                'active' => $course->estado,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $result['courses'] = array_chunk($result['courses'], self::CHUNK_LENGTH, true);
    }

    public function setTopicsData(&$result, $db)
    {
        $now = now()->format('Y-m-d H:i:s');

        $temp['topics'] = $db->getTable('posteos')
            ->select('id', 'curso_id', 'nombre', 'orden', 'estado')
            ->get();

        foreach ($temp['topics'] as $posteo) {
            $result['topics'][] = [
                'external_id' => $posteo->id,

                'name' => $posteo->nombre,
                'curso_id' => $posteo->curso_id,

                'active' => $posteo->estado,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    public function setQuestions_OptionsData(&$result, $db)
    {
        $now = now()->format('Y-m-d H:i:s');

        $temp['questions'] = $db->getTable('preguntas')
            ->select('id', 'post_id', 'tipo_pregunta', 'pregunta', 'rptas_json', 'rpta_ok', 'estado')
            ->get();

        $type = Taxonomy::getFirstData('question', 'type', 'select-answer');

        foreach ($temp['questions'] as $pregunta) {

            if ($pregunta->tipo_pregunta === 'selecciona') {

                $result['questions'][] = [
                    'post_id' => $pregunta->post_id,
                    'rptas_json' => $pregunta->rptas_json,

                    'external_id' => $pregunta->id,

                    'statement' => $pregunta->pregunta,

                    'model_type' => Topic::class,

                    'type_id' => $type->id,

                    'active' => $pregunta->estado,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
    }
}
