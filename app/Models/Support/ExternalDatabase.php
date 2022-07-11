<?php

namespace App\Models\Support;

use App\Models\CriterionValue;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use DB;

class ExternalDatabase extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function insertMigrationData_1($data)
    {
        $this->insertUsersData($data);

        $this->insertModulosData($data);

        $this->insertCarrerasData($data);
        $this->insertCiclosData($data);

        $this->insertGruposData($data);
        $this->insertBoticasData($data);

//        $this->insertUserModuleData($data);
//        $this->insertUserCarreraData($data);
    }

    public function insertChunkedData($table_name, $data)
    {
        foreach ($data as $chunk)
            DB::table($table_name)->insert($chunk);
    }

    public function makeChunkAndInsert($data, $table_name)
    {
        $chunk = array_chunk($data, self::CHUNK_LENGTH, true);

        $this->insertChunkedData($table_name, $chunk);
    }

    public function insertUsersData($data)
    {
        $this->insertChunkedData('users', $data['users']);
    }

    public function insertModulosData($data)
    {
        $this->insertChunkedData('criterion_values', $data['modulos']);

        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $temp = [];

        $uc_workspace = Workspace::where('name', "Universidad Corporativa")->first();

        foreach ($modules_values as $module) {
            $temp[] = ['workspace_id' => $uc_workspace->id, 'criterion_value_id' => $module->id];
        }

        $this->makeChunkAndInsert($temp, 'criterion_workspace');
    }

    public function insertCarrerasData($data)
    {
        $this->insertChunkedData('criterion_values', $data['carreras']);

        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();

        $temp = [];
        foreach ($data['modulo_carrera'] as $relation) {
            $module = $modules_values->where('external_id', $relation['config_id'])->first();
            $career = $carreras_values->where('external_id', $relation['carrera_id'])->first();
//            $carrera = $carreras_values->where('value_text', $relation['nombre'])->first();

            if ($module and $career)
                $temp[] = ['criterion_value_parent_id' => $module->id, 'criterion_value_id' => $career->id];
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
    }

    public function insertCiclosData($data)
    {
        $this->insertChunkedData('criterion_values', $data['grouped_ciclos']);

        $ciclos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'ciclo'))->get();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();

        $temp = [];
        foreach ($data['ciclos_all'] as $relation) {
            $ciclo = $ciclos_values->where('value_text', $relation['ciclo_nombre'])->first();
            $career = $carreras_values->where('external_id', $relation['carrera_id'])->first();

            if ($ciclo and $career)
                $temp[] = ['criterion_value_parent_id' => $career->id, 'criterion_value_id' => $ciclo->id];
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
    }

    public function insertGruposData($data)
    {
        $this->insertChunkedData('criterion_values', $data['grupos']);

        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $grupos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'group'))->get();

        $temp = [];
        foreach ($data['grupo_carrera'] as $relation) {
            $module = $modules_values->where('external_id', $relation['config_id'])->first();
            $group = $grupos_values->where('external_id', $relation['grupo_id'])->first();

            if ($module and $group)
                $temp[] = ['criterion_value_parent_id' => $module->id, 'criterion_value_id' => $group->id];
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
    }

    public function insertBoticasData($data)
    {
        $this->insertChunkedData('criterion_values', $data['boticas']);

        $grupos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'group'))->get();
        $boticas_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'botica'))->get();

        $temp = [];
        foreach ($data['grupo_botica'] as $relation) {
            $group = $grupos_values->where('external_id', $relation['grupo_id'])->first();
            $botica = $boticas_values->where('external_id', $relation['botica_id'])->first();

            if ($group and $botica)
                $temp[] = ['criterion_value_parent_id' => $group->id, 'criterion_value_id' => $botica->id];
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
    }

    public function insertUserModuleData($data)
    {
        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $users = User::all();
        $temp = [];

        foreach ($data['user_modulo'] as $relation) {
            $module = $modules_values->where('external_id', $relation['config_id'])->first();
            $user = $users->where('external_id', $relation['usuario_id'])->first();

            if ($module and $user)
                $temp[] = ['criterion_id' => $module->id, 'user_id' => $user->id];

        }

        $chunk = array_chunk($temp, self::CHUNK_LENGTH, true);

        $this->insertChunkedData('criterion_user', $chunk);
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
