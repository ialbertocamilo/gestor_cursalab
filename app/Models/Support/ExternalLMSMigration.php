<?php

namespace App\Models\Support;

use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\Workspace;
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
        $uc = [
            'name' => "Universidad Corporativa",
            'active' => ACTIVE
        ];
        $uc_workspace = Workspace::create($uc);

        $temp['users'] = $db->getTable('usuarios')
            ->select(
                'id', 'nombre', 'email', 'dni', 'config_id',
                'estado', 'created_at', 'updated_at'
            )
            ->limit(100)
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
                'workspace_id' => $uc_workspace->id,

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

    public function setCarrerasData(&$result, $db) // migrar duplicados por modulos
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
            $result['modulo_carrera'][] = [
                'config_id' => $carrera->config_id,
//                'nombre' => $carrera->nombre,
                'carrera_id' => $carrera->id,
            ];

//            $carreras_added = array_map('strtolower',array_column($result['carreras'], 'value_text'));
//            $found = in_array($carrera->nombre, $carreras_added);

//            if ($found === false) {
                $result['carreras'][] = [
                    'external_id' => $carrera->id,
                    'criterion_id' => $carrera_criterion->id,

                    'value_text' => "M{$carrera->config_id}::" .$carrera->nombre,

                    'active' => $carrera->estado,

                    'created_at' => $carrera->created_at,
                    'updated_at' => $carrera->updated_at,
                ];
//            }
        }

        $result['carreras'] = array_chunk($result['carreras'], self::CHUNK_LENGTH, true);
    }

    public function setCiclosData(&$result, $db) // agrupar y asignar por carreras
    {
        $ciclo_criterion = Criterion::create([
            'name' => 'Ciclo',
            'code' => 'ciclo',
            'multiple' => ACTIVE
        ]);

        $temp['grouped_ciclos'] = $db->getTable('ciclos')
            ->select(
                'id', 'nombre', 'secuencia', 'carrera_id', 'estado'
            )->groupBy('nombre')
            ->get();

        foreach ($temp['grouped_ciclos'] as $ciclo) {
            $result['grouped_ciclos'][] = [
                'criterion_id' => $ciclo_criterion->id,

                'value_text' => $ciclo->nombre,
                'position' => $ciclo->secuencia,

                'active' => $ciclo->estado,
            ];
        }

        $result['grouped_ciclos'] = array_chunk($result['grouped_ciclos'], self::CHUNK_LENGTH, true);

        $temp['ciclos_all'] = $db->getTable('ciclos')
            ->select('nombre', 'carrera_id')
            ->get();
        info("CICLOS COUNT");
        info($temp['ciclos_all']->count());
        foreach ($temp['ciclos_all'] as $ciclo){
            $result['ciclos_all'][] = [
              'ciclo_nombre' => $ciclo->nombre,
              'carrera_id' => $ciclo->carrera_id
            ];
        }


    }

    public function setGruposData(&$result, $db) // migrar duplicados por modulos
    {
        $grupo_criterion = Criterion::create([
            'name' => 'Grupo',
            'code' => 'group'
        ]);

        $temp['grupos'] = $db->getTable('criterios')
            ->select(
                'id', 'valor', 'config_id',
                'created_at', 'updated_at'
            )
            ->get();

        foreach ($temp['grupos'] as $grupo) {
            $result['grupo_carrera'][] = [
                'config_id' => $grupo->config_id,
                'grupo_id' => $grupo->id
            ];

            $result['grupos'][] = [
                'external_id' => $grupo->id,
                'criterion_id' => $grupo_criterion->id,

                'value_text' => "M{$grupo->config_id}::".$grupo->valor,

                'created_at' => $grupo->created_at,
                'updated_at' => $grupo->updated_at,
            ];
        }

        $result['grupos'] = array_chunk($result['grupos'], self::CHUNK_LENGTH, true);
    }

    public function setBoticasData(&$result, $db) // migrar duplicados por grupos
    {
        $botica_criterion = Criterion::create([
            'name' => 'Botica',
            'code' => 'botica'
        ]);

        $temp['boticas'] = $db->getTable('boticas')
            ->select(
                'id', 'nombre', 'criterio_id', 'codigo_local',
                'created_at', 'updated_at'
            )
            ->get();

        foreach ($temp['boticas'] as $botica) {
            $result['grupo_botica'][] = [
                'grupo_id' => $botica->criterio_id,
                'botica_id' => $botica->id
            ];

            $result['boticas'][] = [
                'external_id' => $botica->id,
                'criterion_id' => $botica_criterion->id,

                'value_text' => $botica->nombre,

                'created_at' => $botica->created_at,
                'updated_at' => $botica->updated_at,
            ];
        }

        $result['boticas'] = array_chunk($result['boticas'], self::CHUNK_LENGTH, true);
    }

}
