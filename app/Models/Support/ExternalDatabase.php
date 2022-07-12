<?php

namespace App\Models\Support;

use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use DB;

class ExternalDatabase extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function insertMigrationData_1($data)
    {
        $this->insertUsersData($data);

        $this->insertModulosData($data);

        $this->insertCarrerasData($data);
        $this->insertCiclosData($data);

        $this->insertGruposData($data);
        $this->insertBoticasData($data);

//        $this->insertUserModuleData($data);
        $this->insertCriterionUserData($data);
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

    public function insertCriterionUserData($data)
    {
        $db = self::connect();

        $users = User::all();
        $modules_value = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
        $ciclos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'ciclo'))->get();
        $grupos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'group'))->get();
        $boticas_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'botica'))->get();
        $matriculas = $db->getTable('matricula')
            ->select('usuario_id', 'carrera_id', 'ciclo_id', 'secuencia_ciclo', 'presente', 'estado')
            ->whereIn('usuario_id', $users->pluck('external_id'))
            ->get();

        $criterion_user = [];

        foreach ($users as $user) {

            $user_relations_key = array_search($user->external_id, array_column($data['usuario_relations'], 'usuario_id'));
            $user_relations = $user_relations_key === false ? false : $data['usuario_relations'][$user_relations_key];

            $usuario_matriculas = $matriculas->where('usuario_id', $user->external_id);

            if ($usuario_matriculas->count() > 0):

                $module = $modules_value->where('external_id', $user_relations['config_id'])->first();
                $career = $carreras_values->where('external_id', $usuario_matriculas->first()->carrera_id)->first();
                $ciclos = $ciclos_values->whereIn('position', $usuario_matriculas->pluck('secuencia_ciclo'));

                if ($career and $module and $ciclos->count() > 0):

                    // Push modulo
                    $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $module->id];

                    // Push carrera
                    $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $career->id];

                    // Push ciclos
                    foreach ($ciclos as $ciclo)
                        $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $ciclo->id];

                endif;

            endif;

            if ($user_relations):

                $group = $grupos_values->where('external_id', $user_relations['grupo_id'])->first();
                $botica = $boticas_values->where('external_id', $user_relations['botica_id'])->first();

                if ($group and $botica):

                    // Push grupo
                    $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $group->id];

                    // Push botica
                    $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $botica->id];

                endif;

            endif;

        }

        $this->makeChunkAndInsert($criterion_user, 'criterion_value_user');
    }
}
