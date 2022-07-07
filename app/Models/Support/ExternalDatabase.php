<?php

namespace App\Models\Support;

use App\Models\CriterionValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use DB;

class ExternalDatabase extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function insertMigrationData_1($data)
    {
        $this->insertUsersData($data);

        $this->insertModulosData($data);
        $this->insertUserModuleData($data);

        $this->insertCarrerasData($data);
//        $this->insertCiclosData($data);
//        $this->insertUserCarreraData($data);

//        $this->insertCoursesData($data);
//
//        $this->insertCourseSchoolData($data);
//
//        $this->insertTopicsData($data);
//
//        $this->insertQuestionData($data);
    }

    public function insertChunkedData($table_name, $data)
    {
        foreach ($data as $chunk)
            DB::table($table_name)->insert($chunk);
    }

    public function insertUsersData($data)
    {
        $this->insertChunkedData('users', $data['users']);
    }

    public function insertModulosData($data)
    {
        $this->insertChunkedData('criterion_values', $data['modulos']);
    }

    public function insertUserModuleData($data)
    {
        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $users = User::all();
        $temp = [];

        foreach ($data['user_modulo'] as $relation) {
            $module = $modules_values->where('external_id', $relation['config_id'])->first();
            $user = $users->where('external_id', $relation['usuario_id'])->first();
            unset($relation['config_id'], $relation['usuario_id']);

            if ($module and $user)
                $temp[] = array_merge($relation, ['criterion_id' => $module->id, 'user_id' => $user->id]);

        }

        $chunk = array_chunk($temp, self::CHUNK_LENGTH, true);

        $this->insertChunkedData('criteria_user', $chunk);
    }

    public function insertCarrerasData($data)
    {
        $this->insertChunkedData('criterion_values', $data['carreras']);

        $temp = [];
        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();

        foreach ($data['modulo_carrera_relation'] as $relation) {
            $module = $modules_values->where('external_id', $relation['config_id'])->first();
            $carrera = $carreras_values->where('value_text', $relation['nombre'])->first();

            if ($module and $carrera)
                $temp[] = ['criterion_value_parent_id' => $module->id, 'criterion_value_id' => $carrera->id];
        }

        $chunk = array_chunk($temp, self::CHUNK_LENGTH, true);

        $this->insertChunkedData('criterion_value_relationship', $chunk);
    }

    public function insertCiclosData($data)
    {
        $this->insertChunkedData('criterion_values', $data['ciclos']);
    }


//    public function insertQuestionData($data)
//    {
//        $topics = Topic::all();
//
//        foreach ($data['questions'] as $question) {
//            $topic = $topics->where('external_id', $question['post_id'])->first();
//            $options = $question['rptas_json'];
//            unset($question['post_id'], $question['rptas_json']);
//
//            if ($topic) {
//                $temp_question = array_merge($question, ['model_id' => $topic->id]);
//
//                $db->getTable('questions')->insert($temp_question);
//
//                $this->insertOptionsData($db, array_merge($question, ['rptas_json' => $options]));
//            }
//        }
//    }
//
//    public function insertOptionsData($db, $data)
//    {
//        $now = now()->format('Y-m-d H:i:s');
//
//        $options = json_decode($data['rptas_json'] ?? [], true);
//
//        $position = 1;
//        $temp = [];
//        foreach ($options as $option) {
//            $temp[] = [
//                'question_id' => $data['external_id'],
//
//                'statement' => $option['opc'],
//                'position' => $position,
//
//                'is_correct' => $option['correcta'],
//
//                'active' => ACTIVE,
//                'created_at' => $now,
//                'updated_at' => $now,
//            ];
//            $position++;
//        }
//
//        $db->getTable('options')->insert($temp);
//    }

}
