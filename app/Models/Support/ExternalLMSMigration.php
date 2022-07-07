<?php

namespace App\Models\Support;

use App\Models\Taxonomy;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Model;
use App\Models\Criterion;

use DB;
use Illuminate\Support\Facades\Hash;

class ExternalLMSMigration extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function setMigrationData_1()
    {
        $db = self::connect();
        $client_LMS_data = [
            'users' => [], 'carreras' => [], 'ciclos' => [], 'grupos' => [],
            'boticas' => [], 'modulos' => []
        ];

        // TODO: Migrate usuarios / users
        $this->setUsersData($client_LMS_data, $db);
        // TODO: Migrate modulos / criterion_values
        $this->setModulosData($client_LMS_data, $db);
        // TODO: Migrate carreras / criterion_values
        $this->setCarrerasData($client_LMS_data, $db);
        // TODO: Migrate ciclos / criterion_values
        $this->setCiclosData($client_LMS_data, $db);
        // TODO: Migrate grupos / criterion_values
        $this->setGruposData($client_LMS_data, $db);
        // TODO: Migrate boticas / criterion_values
        $this->setBoticasData($client_LMS_data, $db);

        return $client_LMS_data;
    }

    public function setUsersData(&$result, $db)
    {
        $temp['users'] = $db->getTable('usuarios')
            ->select(
                'id', 'nombre', 'email', 'dni', 'config_id',
                'estado', 'created_at', 'updated_at'
            )
            ->limit(10)
            ->get();

        $type_client = Taxonomy::getFirstData('user', 'type', 'client');

        foreach ($temp['users'] as $user) {
            $result['user_modulo'][] = [
                'usuario_id' => $user->id,
                'config_id' => $user->config_id
            ];

            $result['users'][] = [
                'external_id' => $user->id,

                'name' => $user->nombre,

                'email' => $user->email,
                'code' => $user->dni,

                'type_id' => $type_client->id,

                'password' => Hash::make($user->dni),

                'active' => $user->estado,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        $result['users'] = array_chunk($result['users'], self::CHUNK_LENGTH, true);
    }

    public function setModulosData(&$result, $db)
    {
        $module_criterion = Criterion::create([
            'name' => 'Módulo',
            'code' => 'module'
        ]);

        $temp['modulos'] = $db->getTable('ab_config')
            ->select(
                'id', 'etapa',
                'estado', 'created_at', 'updated_at'
            )
            ->get();

        foreach ($temp['modulos'] as $modulo) {
            $result['modulos'][] = [
                'external_id' => $modulo->id,
                'criterion_id' => $module_criterion->id,

                'value_text' => $modulo->etapa,

                'active' => $modulo->estado,

                'created_at' => $modulo->created_at,
                'updated_at' => $modulo->updated_at,
            ];
        }

        $result['modulos'] = array_chunk($result['modulos'], self::CHUNK_LENGTH, true);
    }

    public function setCarrerasData(&$result, $db)
    {
        $carrera_criterion = Criterion::create([
            'name' => 'Carrera',
            'code' => 'career'
        ]);

        $temp['carreras'] = $db->getTable('carreras')
            ->select(
                'id', 'nombre', 'imagen', 'malla_archivo', 'contar_en_graficos',
                'config_id',
                'estado', 'created_at', 'updated_at'
            )
            ->get();

        foreach ($temp['carreras'] as $carrera) {
            $result['modulo_carrera_relation'][] = [
                'config_id' => $carrera->config_id,
                'nombre' => $carrera->nombre,
            ];

            $found = in_array($carrera->nombre, array_column($result['carreras'], 'value_text'));
            info($carrera->id.' - '.$carrera->nombre.' - '.$found);
            if ($found === false){
                $result['carreras'][] = [
                    'external_id' => $carrera->id,
                    'criterion_id' => $carrera_criterion->id,

                    'value_text' => $carrera->nombre,

                    'active' => $carrera->estado,

                    'created_at' => $carrera->created_at,
                    'updated_at' => $carrera->updated_at,
                ];
            }
        }

        $result['carreras'] = array_chunk($result['carreras'], self::CHUNK_LENGTH, true);
    }

    public function setCiclosData(&$result, $db)
    {
        $ciclo_criterion = Criterion::create([
            'name' => 'Ciclo',
            'code' => 'ciclo',
            'multiple' => ACTIVE
        ]);

        $temp['ciclos'] = $db->getTable('ciclos')
            ->select(
                'id', 'nombre', 'tipo', 'secuencia', 'carrera_id',
                'estado', 'created_at', 'updated_at'
            )
            ->get();

        foreach ($temp['ciclos'] as $ciclo) {
            $result['ciclos'][] = [
                'external_id' => $ciclo->id,
                'criterion_id' => $ciclo_criterion->id,

                'value_text' => $ciclo->nombre,
                'position' => $ciclo->secuencia,

                'active' => $ciclo->estado,

                'created_at' => $ciclo->created_at,
                'updated_at' => $ciclo->updated_at,
            ];
        }

        $result['ciclos'] = array_chunk($result['ciclos'], self::CHUNK_LENGTH, true);
    }

    public function setGruposData(&$result, $db)
    {
        $temp['grupos'] = $db->getTable('criterios')
            ->select(
                'id', 'valor', 'config_id',
                'created_at', 'updated_at'
            )
            ->get();

        foreach ($temp['grupos'] as $grupo) {
            $result['grupos'][] = [
                'external_id' => $grupo->id,
                'temp_parent_id' => $grupo->config_id,

                'value_text' => $grupo->valor,

                'created_at' => $grupo->created_at,
                'updated_at' => $grupo->updated_at,
            ];
        }

        $result['grupos'] = array_chunk($result['grupos'], self::CHUNK_LENGTH, true);
    }

    public function setBoticasData(&$result, $db)
    {
        $temp['boticas'] = $db->getTable('boticas')
            ->select(
                'id', 'nombre', 'criterio_id', 'codigo_local',
                'created_at', 'updated_at'
            )
            ->get();

        foreach ($temp['boticas'] as $botica) {
            $result['boticas'][] = [
                'external_id' => $botica->id,
                'temp_parent_id' => $botica->criterio_id,

                'value_text' => $botica->nombre,

                'created_at' => $botica->created_at,
                'updated_at' => $botica->updated_at,
            ];
        }

        $result['boticas'] = array_chunk($result['boticas'], self::CHUNK_LENGTH, true);
    }

}
