<?php

namespace App\Models\UCMigrationData;

use App\Models\Block;
use App\Models\Course;
use App\Models\Criterio;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\School;
use App\Models\Segment;
use App\Models\Support\OTFConnection;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Migration_1 extends Model
{
    const CHUNK_LENGTH = 5000;

    const MODULOS_EQUIVALENCIA = [
        4 => '26', // Mifarma
        5 => '27', // Inkafarma,
        6 => '28' // Capacitacion FP
    ];

    const MODULOS_CRITERION_VALUE = [
        4 => '18', // Mifarma
        5 => '19', // Inkafarma,
        6 => '20' // Capacitacion FP
    ];

    private $uc_workspace = null;

    public function __construct()
    {
        $this->uc_workspace = Workspace::where('slug', "farmacias-peruanas")->first();
        $this->db = self::connect();
    }

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function migrateData1($output)
    {
        $client_lms_data = $this->setMigrationData_1($output);

        $this->insertMigrationData_1($client_lms_data, $output);
    }

    protected function migrateCrudData($output)
    {
        $client_lms_data = $this->setCrudData($output);

        $this->insetCrudData($client_lms_data, $output);
    }

    public function setCrudData()
    {
        $db = $this->db;
        $client_LMS_data = [
            'carreras' => [], 'ciclos' => [], 'grupos' => [],
            'boticas' => [],
        ];

        $this->setCarrerasData($client_LMS_data, $db);

        $this->setCiclosData($client_LMS_data, $db);
        $this->setGruposData($client_LMS_data, $db);
        $this->setBoticasData($client_LMS_data, $db);

        return $client_LMS_data;
    }

    public function insetCrudData($data)
    {
        $this->insertCarrerasData($data);

        $this->insertCiclosData($data);

        $this->insertGruposData($data);
        $this->insertBoticasData($data);
    }


    protected function migrateUsers($output)
    {
        $client_lms_data = $this->setUCUsersData($output);
        $this->insertUCUsersData($client_lms_data);
    }

    public function setUCUsersData($output)
    {
        $db = $this->db;
        $client_LMS_data = [
            'users' => [],
        ];

        $this->setUsersData($client_LMS_data, $db, $output);
//        $this->setUsersDatav2($client_LMS_data, $db, $output);

        return $client_LMS_data;
    }

    public function insertUCUsersData($data)
    {
        $this->insertUsersData($data);
    }

    protected function migrateCriteriaUser($output)
    {
        $this->insertCriterionUserData($output);
    }

    public function setUsersData(&$result, $db, $output)
    {
        $temp['users'] = $db->getTable('usuarios')
            ->select(
                'id',
                'nombre',
                'email',
                'dni',
                'config_id',
                'grupo',
                'botica_id',
                'sexo',
                'estado',
                'created_at',
                'updated_at'
            )
//            ->skip(1000)
//            ->limit(10)
//            ->where('config_id', 6)
            ->get();

        $j = 0;
        $now = now();
        $type_client = Taxonomy::getFirstData('user', 'type', 'client');
        $bar = $output->createProgressBar(count($temp['users']));
        $bar->start();
        foreach ($temp['users'] as $user) {
            $bar->advance();

            $user_relations = [
                'usuario_id' => $user->id,
                'config_id' => $user->config_id,
                'grupo_id' => $user->grupo,
                'botica_id' => $user->botica_id,
                'genero' => $user->sexo
            ];

            $user_db = User::disableCache()
                ->with([
                    'subworkspace' => function ($q) {
                        $q->with('parent:id,name')
                            ->select('id', 'name', 'parent_id');
                    }
                ])
                ->select('id', 'name', 'lastname', 'surname', 'document', 'email', 'subworkspace_id')
                ->where('document', $user->dni)->first();

            if ($user_db) {
                $j++;
                $module_value = self::MODULOS_EQUIVALENCIA[$user->config_id] ?? false;

                $user_db->update(['external_id' => $user->id, 'user_relations' => $user_relations]);

                if ($module_value != $user_db->subworkspace_id)
                    info("{$user_db->subworkspace->parent->name} - {$user_db->subworkspace->name} - {$user_db->fullname} - {$user_db->document}");

                continue;
            }

            $result['users'][] = [
                'external_id' => $user->id,
                'user_relations' => json_encode($user_relations),

                'name' => $user->nombre,

                'email' => $user->email,
                'document' => $user->dni,

                'type_id' => $type_client->id,
                'config_id' => $user->config_id,

                'password' => bcrypt($user->dni),

                'active' => $user->estado,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }
        print_r("\n ya existen :: $j usuarios \n");
        $bar->finish();
    }
    public function setUsersDatav2(&$result, $db, $output)
    {
        $user_migrated = User::disableCache()->whereNotNull('external_id')->whereNotNull('user_relations')
            ->pluck('external_id')->toArray();

        $count = $db->getTable('usuarios')->count();
        $type_client = Taxonomy::getFirstData('user', 'type', 'employee');
        $bar = $output->createProgressBar($count);
        $bar->start();

        $temp['users'] = $db->getTable('usuarios')
            ->select(
                'id',
                'nombre',
                'email',
                'dni',
                'config_id',
                'grupo',
                'botica_id',
                'sexo',
                'estado',
                'created_at',
                'updated_at'
            )
            ->whereNotIn('id', $user_migrated)
            ->chunkById(5000, function ($usuarios) use ($bar, $type_client, $result) {
                $usuarios_dni = $usuarios->pluck('dni')->toArray();

                $users = User::disableCache()
                    ->with([
                        'subworkspace' => function ($q) {
                            $q->with('parent:id,name')
                                ->select('id', 'name', 'parent_id');
                        }
                    ])
                    ->select('id', 'name', 'lastname', 'surname', 'document', 'email', 'subworkspace_id')
                    ->whereIn('document', $usuarios_dni)
                    ->get();

                foreach ($usuarios as $key => $user) {
                    $bar->advance();

                    $user_relations = [
                        'usuario_id' => $user->id,
                        'config_id' => $user->config_id,
                        'grupo_id' => $user->grupo,
                        'botica_id' => $user->botica_id,
                        'genero' => $user->sexo
                    ];

//                    $user_db = User::disableCache()
//                        ->with([
//                            'subworkspace' => function ($q) {
//                                $q->with('parent:id,name')
//                                    ->select('id', 'name', 'parent_id');
//                            }
//                        ])
//                        ->select('id', 'name', 'lastname', 'surname', 'document', 'email', 'subworkspace_id')
//                        ->where('document', $user->dni)->first();
                    $user_db = $users->where('document', $user->dni)->first();

                    if ($user_db) {
                        $module_value = self::MODULOS_EQUIVALENCIA[$user->config_id] ?? false;

                        $user_db->update(['external_id' => $user->id, 'user_relations' => $user_relations]);

                        if ($module_value != $user_db->subworkspace_id)
                            info("{$user_db->subworkspace->parent->name} - {$user_db->subworkspace->name} - {$user_db->fullname} - {$user_db->document}");

                        continue;
                    }

                    $result['users'][] = [
                        'external_id' => $user->id,
                        'user_relations' => json_encode($user_relations),

                        'name' => $user->nombre,

                        'email' => $user->email,
                        'document' => $user->dni,

                        'type_id' => $type_client->id,
                        'config_id' => $user->config_id,

                        'password' => bcrypt($user->dni),

                        'active' => $user->estado,

                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ];
                }

            });
        $bar->finish();
        $output->newLine();

        return $result;
    }

    public function setModulosData(&$result, $db)
    {
        $module_criterion = Criterion::create([
            'name' => 'Módulo',
            'code' => 'module'
        ]);

        $modules = Workspace::disableCache()->whereNotNull('parent_id')->get();

        $temp['modulos'] = $db->getTable('ab_config')->get();

        foreach ($temp['modulos'] as $modulo) {

            $module_exists = $modules->where('name', 'like', "%{$modulo->etapa}%")->first();

            if ($module_exists) continue;

            $result['modulo_subworkspace'][] = [
                'external_id' => $modulo->id,
                'name' => $modulo->etapa,

                'logo' => $modulo->logo,
                'plantilla_diploma' => $modulo->plantilla_diploma,
                'codigo_matricula' => $modulo->codigo_matricula,
                'mod_evaluaciones' => $modulo->mod_evaluaciones,
                'reinicios_programado' => $modulo->reinicios_programado,

                'active' => $modulo->estado,
                'created_at' => $modulo->created_at,
                'updated_at' => $modulo->updated_at,
            ];

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
        $carrera_criterion = Criterion::firstOrCreate([
            'name' => 'Carrera',
            'code' => 'career'
        ]);

        $carreras_values = CriterionValue::disableCache()->whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
        $temp['carreras'] = $db->getTable('carreras')
            ->select(
                'id',
                'nombre',
                'imagen',
                'malla_archivo',
                'contar_en_graficos',
                'config_id',
                'estado',
                'created_at',
                'updated_at'
            )
            ->get();

        foreach ($temp['carreras'] as $carrera) {
            $name = "M{$carrera->config_id}::" . $carrera->nombre;

            $career_exists = $carreras_values->where('value_text', $name)->first();
            if ($career_exists) continue;

            $career_exists = $carreras_values->where('value_text', $carrera->nombre)->first();
            if ($career_exists) continue;

            $result['modulo_carrera'][] = [
                'config_id' => $carrera->config_id,
                'carrera_id' => $carrera->id,
            ];

            $result['carreras'][] = [
                'external_id' => $carrera->id,
                'criterion_id' => $carrera_criterion->id,

                'value_text' => $name,

                'active' => $carrera->estado,

                'created_at' => $carrera->created_at,
                'updated_at' => $carrera->updated_at,
            ];
        }

        $result['carreras'] = array_chunk($result['carreras'], self::CHUNK_LENGTH, true);


    }

    public function setCiclosData(&$result, $db) // agrupar y asignar por carreras
    {
        $ciclo_criterion = Criterion::where('code', 'cycle')->first();

        $ciclos_values = CriterionValue::disableCache()->whereHas('criterion', fn($q) => $q->where('code', 'cycle'))->get();

        $temp['grouped_ciclos'] = $db->getTable('ciclos')
            ->select(
                'id',
                'nombre',
                'secuencia',
                'carrera_id',
                'estado'
            )->groupBy('nombre')
            ->get();

        foreach ($temp['grouped_ciclos'] as $ciclo) {

            $ciclo_exists = $ciclos_values->where('value_text', $ciclo->nombre)->first();

            if ($ciclo_exists) continue;


            $result['grouped_ciclos'][] = [
                'criterion_id' => $ciclo_criterion->id,

                'value_text' => $ciclo->nombre,
                'position' => $ciclo->secuencia,

                'active' => $ciclo->estado,
            ];
        }

        $result['grouped_ciclos'] = array_chunk($result['grouped_ciclos'] ?? [], self::CHUNK_LENGTH, true);

        $temp['ciclos_all'] = $db->getTable('ciclos')
            ->select('nombre', 'carrera_id')
            ->get();

        foreach ($temp['ciclos_all'] as $ciclo) {
            $result['ciclos_all'][] = [
                'ciclo_nombre' => $ciclo->nombre,
                'carrera_id' => $ciclo->carrera_id
            ];
        }
    }

    public function setGruposData(&$result, $db) // migrar duplicados por modulos
    {
        $grupo_criterion = Criterion::where('code', 'grupo')->first();

        $grupos_values = CriterionValue::disableCache()->whereHas('criterion', fn($q) => $q->where('code', 'grupo'))
            ->select('value_text')->get();

        $temp['grupos'] = $db->getTable('criterios')
            ->select(
                'id',
                'valor',
                'config_id',
                'created_at',
                'updated_at'
            )
            ->get();

        foreach ($temp['grupos'] as $grupo) {

            $name = "M$grupo->config_id::" . $grupo->valor;

            $grupo_exists = $grupos_values->where('value_text', $grupo->valor)->first();
            if ($grupo_exists) continue;

            $grupo_exists = $grupos_values->where('value_text', $name)->first();
            if ($grupo_exists) continue;

            $result['grupo_carrera'][] = [
                'config_id' => $grupo->config_id,
                'grupo_id' => $grupo->id
            ];

            $result['grupos'][] = [
                'external_id' => $grupo->id,
                'criterion_id' => $grupo_criterion->id,

                'value_text' => $name,

                'created_at' => $grupo->created_at,
                'updated_at' => $grupo->updated_at,
            ];
        }

        $result['grupos'] = array_chunk($result['grupos'], self::CHUNK_LENGTH, true);
    }

    public function setBoticasData(&$result, $db) // migrar duplicados por grupos
    {
        $botica_criterion = Criterion::where('code', 'botica')->first();

        $boticas_values = CriterionValue::disableCache()->whereHas('criterion', fn($q) => $q->where('code', 'botica'))->get();

        $temp['boticas'] = $db->getTable('boticas')
            ->select(
                'id',
                'nombre',
                'criterio_id',
                'codigo_local',
                'created_at',
                'updated_at'
            )
            ->get();

        foreach ($temp['boticas'] as $botica) {
            $name = $botica->nombre;

            $botica_exist = $boticas_values->where('value_text', $name)->first();

            if ($botica_exist) continue;

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

    public function setSchoolsData(&$result, $db)
    {
        $temp['schools'] = $db->getTable('categorias')
            ->select('id', 'nombre', 'descripcion', 'imagen', 'plantilla_diploma',
                'orden', 'reinicios_programado',
                'estado', 'created_at', 'updated_at')
            ->get();

        foreach ($temp['schools'] as $escuela) {
            $result['schools'][] = [
                'external_id' => $escuela->id,

                'name' => $escuela->nombre,
                'description' => $escuela->descripcion,
                'imagen' => $escuela->imagen,
                'plantilla_diploma' => $escuela->plantilla_diploma,
                'scheduled_restarts' => $escuela->reinicios_programado,
                'position' => $escuela->orden,

                'active' => $escuela->estado,
                'created_at' => $escuela->created_at,
                'updated_at' => $escuela->updated_at,
            ];
        }

        $result['schools'] = array_chunk($result['schools'], self::CHUNK_LENGTH, true);
    }

    public function setCoursesData(&$result, $db)
    {
        $temp['courses'] = $db->getTable('cursos')
            ->select('id', 'nombre', 'descripcion', 'imagen', 'plantilla_diploma',
                'orden', 'reinicios_programado', 'c_evaluable', 'libre',
                'estado', 'created_at', 'updated_at')
            ->get();

        foreach ($temp['courses'] as $escuela) {
            $result['courses'][] = [
                'external_id' => $escuela->id,

                'name' => $escuela->nombre,
                'description' => $escuela->descripcion,
                'imagen' => $escuela->imagen,
                'plantilla_diploma' => $escuela->plantilla_diploma,
                'freely_eligible' => $escuela->libre,
                'assessable' => $escuela->c_evaluable,
                'scheduled_restarts' => $escuela->reinicios_programado,
                'position' => $escuela->orden,

                'active' => $escuela->estado,
                'created_at' => $escuela->created_at,
                'updated_at' => $escuela->updated_at,
            ];
        }

        $result['courses'] = array_chunk($result['courses'], self::CHUNK_LENGTH, true);
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
        $user_temp = [];
        print_r("\nUSERS A CREAR :: " . count($data['users']));

        foreach ($data['users'] as $user) {
            $module_value = self::MODULOS_EQUIVALENCIA[$user['config_id']] ?? false;
            unset($user['config_id']);

            if ($module_value) {

                $user_temp[] = array_merge($user, ['subworkspace_id' => $module_value,]);
            }
        }

        $this->makeChunkAndInsert($user_temp, 'users');
    }

    public function insertModulosData($data)
    {
        $this->insertChunkedData('criterion_values', $data['modulos']);

        $module = Criterion::where('code', 'module')->first();
        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $uc_workspace = $this->uc_workspace;

        DB::table('criterion_workspace')->insert(['workspace_id' => $uc_workspace->id, 'criterion_id' => $module->id]);

        $temp = [];

        foreach ($modules_values as $module) {
            $temp[] = ['workspace_id' => $uc_workspace->id, 'criterion_value_id' => $module->id];
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_workspace');

        foreach ($data['modulo_subworkspace'] as $modulo_subworkspace) {
            $criterion_value = $modules_values->where('external_id', $modulo_subworkspace['external_id'])->first();
            unset($modulo_subworkspace['external_id']);

            if ($criterion_value) {
                $modulo_subworkspace = array_merge($modulo_subworkspace, [
                    'parent_id' => $uc_workspace->id,
                    'criterion_value_id' => $criterion_value->id,
                ]);

                Workspace::create($modulo_subworkspace);
            }

        }
    }

    public function insertCarrerasData($data)
    {
        $this->insertChunkedData('criterion_values', $data['carreras']);

        $career = Criterion::where('code', 'career')->first();
        $carreras_values = CriterionValue::disableCache()->whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
        $uc_workspace = $this->uc_workspace;
        DB::table('criterion_workspace')->insert([
            'workspace_id' => $uc_workspace->id,
            'criterion_id' => $career->id
        ]);

        $temp = [];
        $criterion_values_workspace = [];
        foreach ($data['modulo_carrera'] ?? [] as $relation) {
            $module = self::MODULOS_EQUIVALENCIA[$relation['config_id']] ?? false;
            $career_value = $carreras_values->where('external_id', $relation['carrera_id'])->first();
            if ($module and $career) {
                $temp[] = ['criterion_value_parent_id' => $module, 'criterion_value_id' => $career_value->id];
                $criterion_values_workspace[] = ['criterion_value_id' => $career_value->id, 'workspace_id' => $uc_workspace->id];
            }
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
        $this->makeChunkAndInsert($criterion_values_workspace, 'criterion_value_workspace');
    }

    public function insertCiclosData($data)
    {
        $this->insertChunkedData('criterion_values', $data['grouped_ciclos']);

        $ciclo = Criterion::where('code', 'cycle')->first();
        $ciclos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'cycle'))->get();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
        $uc_workspace = $this->uc_workspace;

        DB::table('criterion_workspace')->insert(['workspace_id' => $uc_workspace->id, 'criterion_id' => $ciclo->id]);

        $temp = [];
        $criterion_values_workspace = [];
        foreach ($data['ciclos_all'] ?? [] as $relation) {
            $ciclo = $ciclos_values->where('value_text', $relation['ciclo_nombre'])->first();
            $career = $carreras_values->where('external_id', $relation['carrera_id'])->first();

            if ($ciclo and $career) {
                $temp[] = ['criterion_value_parent_id' => $career->id, 'criterion_value_id' => $ciclo->id];
                $criterion_values_workspace[] = ['criterion_value_id' => $ciclo->id, 'workspace_id' => $uc_workspace->id];
            }
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
        $this->makeChunkAndInsert($criterion_values_workspace, 'criterion_value_workspace');
    }

    public function insertGruposData($data)
    {
        $this->insertChunkedData('criterion_values', $data['grupos']);

        $group = Criterion::where('code', 'grupo')->first();
        $grupos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'grupo'))->get();
        $uc_workspace = $this->uc_workspace;

        DB::table('criterion_workspace')->insert(['workspace_id' => $uc_workspace->id, 'criterion_id' => $group->id]);

        $temp = [];
        $criterion_values_workspace = [];
        foreach ($data['grupo_carrera'] ?? [] as $relation) {
            $module = self::MODULOS_EQUIVALENCIA[$relation['config_id']] ?? false;
            $group = $grupos_values->where('external_id', $relation['grupo_id'])->first();

            if ($module and $group) {
                $temp[] = ['criterion_value_parent_id' => $module, 'criterion_value_id' => $group->id];
                $criterion_values_workspace[] = ['criterion_value_id' => $group->id, 'workspace_id' => $uc_workspace->id];
            }
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
        $this->makeChunkAndInsert($criterion_values_workspace, 'criterion_value_workspace');

    }

    public function insertBoticasData($data)
    {
        $this->insertChunkedData('criterion_values', $data['boticas']);

        $botica = Criterion::where('code', 'botica')->first();
        $grupos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'group'))->get();
        $boticas_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'botica'))->get();
        $uc_workspace = $this->uc_workspace;

        DB::table('criterion_workspace')->insert(['workspace_id' => $uc_workspace->id, 'criterion_id' => $botica->id]);

        $temp = [];
        $criterion_values_workspace = [];
        foreach ($data['grupo_botica'] ?? [] as $relation) {
            $group = $grupos_values->where('external_id', $relation['grupo_id'])->first();
            $botica = $boticas_values->where('external_id', $relation['botica_id'])->first();

            if ($group and $botica) {
                $temp[] = ['criterion_value_parent_id' => $group->id, 'criterion_value_id' => $botica->id];
                $criterion_values_workspace[] = ['criterion_value_id' => $botica->id, 'workspace_id' => $uc_workspace->id];
            }
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
        $this->makeChunkAndInsert($criterion_values_workspace, 'criterion_value_workspace');
    }

    public function insertCriterionUserData($output)
    {
        $db = self::connect();

        $users = User::disableCache()->whereNotNull('external_id')
            ->with([
                'criterion_values' => function ($q) {
                    $q->withWhereHas('criterion', function ($q) {
                        $q->whereIn('code', ['module', 'career', 'cycle', 'grupo', 'botica', 'gender']);
                    })
                        ->select('id', 'criterion_id', 'value_text');
                }
            ])
            ->select('id', 'external_id', 'user_relations', 'document')
//            ->limit(10)
            ->get();
        $module = Criterion::where('code', 'module')->first();
        $genero = Criterion::where('code', 'gender')->first();
        $career = Criterion::where('code', 'career')->first();
        $ciclo = Criterion::where('code', 'career')->first();
        $grupo = Criterion::where('code', 'grupo')->first();
        $botica = Criterion::where('code', 'botica')->first();
        $document = Criterion::where('code', 'document')->first();
        $modules_value = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
        $ciclos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'cycle'))->get();
        $grupos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'grupo'))->get();
        $boticas_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'botica'))->get();
        $generos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'gender'))->get();
        $document_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'document'))->get();
//        $matriculas = $db->getTable('matricula')
//            ->select('usuario_id', 'carrera_id', 'ciclo_id', 'secuencia_ciclo', 'presente', 'estado')
//            ->whereIn('usuario_id', $users->pluck('external_id'))
//            ->get();

        $criterion_user = [];
        $bar = $output->createProgressBar($users->count());
        $bar->start();
        foreach ($users as $user) {
            $bar->advance();

            $user_relations = $user->user_relations;

//            $usuario_matriculas = $matriculas->where('usuario_id', $user->external_id);
            $usuario_matriculas = $db->getTable('matricula')
                ->select('usuario_id', 'carrera_id', 'ciclo_id', 'secuencia_ciclo', 'presente', 'estado')
                ->where('usuario_id', $user->external_id)
                ->get();

            $user->syncDocumentCriterionValue(old_document: null, new_document: $user->document);

            $genero_value = $generos_values->where('value_text', $user_relations['genero'])->first();
            $this->validIfExistsCriterionValueUser($genero, $genero_value, $user, $criterion_user);


            if ($usuario_matriculas->count() > 0) :

                $module_value = self::MODULOS_CRITERION_VALUE[$user_relations['config_id']] ?? false;
                $career_value = $carreras_values->where('external_id', $usuario_matriculas->first()->carrera_id)->first();
                $ciclos = $ciclos_values->whereIn('position', $usuario_matriculas->pluck('secuencia_ciclo'));

                if ($career_value and $module_value and $ciclos->count() > 0) :

                    // Push modulo
                    $this->validIfExistsCriterionValueUser($module, $module_value, $user, $criterion_user);

                    // Push carrera
                    $this->validIfExistsCriterionValueUser($career, $career_value, $user, $criterion_user);

                    // Push ciclos
                    DB::table('criterion_value_user')->where('user_id', $user->id)
                        ->whereIn('criterion_value_id', $ciclos_values->pluck('id'))
                        ->delete();
                    foreach ($usuario_matriculas as $matricula) {
                        if ($matricula->estado) {
                            $ciclo_value = $ciclos_values->whereIn('position', $matricula->secuencia_ciclo)->first();
                            $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $ciclo_value->id];
                        }
                    }
                endif;

            endif;

            if ($user_relations) :

                $grupo_value = $grupos_values->where('external_id', $user_relations['grupo_id'])->first();
                $botica_value = $boticas_values->where('external_id', $user_relations['botica_id'])->first();

                if ($grupo_value and $botica_value) :

                    // Push grupo
                    $this->validIfExistsCriterionValueUser($grupo, $grupo_value, $user, $criterion_user);

                    // Push botica
                    $this->validIfExistsCriterionValueUser($botica, $botica_value, $user, $criterion_user);

                endif;

            endif;

        }

        $bar->finish();

        $this->makeChunkAndInsert($criterion_user, 'criterion_value_user');
    }

    public function insertSegmentacionCarrerasCiclosData()
    {
        $db = self::connect();

        $default = Taxonomy::getFirstData('criterion', 'type', 'default');

        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
        $ciclos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'ciclo'))->get();
        $curriculas = $db->getTable('curricula')
            ->whereIn('carrera_id', $carreras_values->pluck('external_id'))
            ->get();
        $ciclos_old = $db->getTable('ciclos')->get();

        foreach ($carreras_values as $career) {

            $ciclos_old_ids = $curriculas->whereIn('carrera_id', $career->external_id)->pluck('ciclo_id');
            $ciclos = $ciclos_old->whereIn('id', $ciclos_old_ids);

            $program = [
                'name' => "Programa: $career->value_text",
                'description' => $career->description,
                'parent' => true,
            ];

            $block = Block::create($program);

            $this->createSegmentValue($block, $career, $default);

            // $segment = $block->segments()->create(['name' => $block->name]);

            // $segment->values()->create([
            //     'criterion_id' => $career->parent_id,
            //     'criterion_value_id' => $career->id,
            //     'type' => 1,
            // ]);
            // $segment->values()->syncWithoutDetaching([$career->id]);

            foreach ($ciclos as $ciclo) {
                $child = Block::create(['name' => "Ruta de aprendizaje: $ciclo->nombre"]);
                $ciclo_value = $ciclos_values->where('value_text', $ciclo->nombre)->first();

                $block->children()->syncWithoutDetaching([$child->id]);

                // $block->block_segments->insert([
                // $block_segment_id = DB::table('blocks_children')->insertGetId([
                //     'block_id' => $block->id,
                //     'block_child_id' => $child->id
                // ]);

                if ($ciclo_value) :

                    $this->createSegmentValue($child, $ciclo_value, $default);

                    // DB::table('block_segment_criterion_value')->insert([
                    //     'block_segment_id' => $block_segment_id,
                    //     'criterion_value_id' => $ciclo_value->id
                    // ]);
                endif;
            }
        }
    }

    public function createSegmentValue($model, $criterion_value, $criterion_type)
    {
        $segment = $model->segments()->create(['name' => $model->name]);

        $segment->values()->create([
            'criterion_id' => $criterion_value->criterion_id,
            'criterion_value_id' => $criterion_value->id,
            'type_id' => $criterion_type->id,
        ]);
    }

    public function insertSchoolData($data)
    {
        $this->insertChunkedData('schools', $data['schools']);
    }

    public function insertCourseData($data)
    {
        $this->insertChunkedData('courses', $data['courses']);

        $db = self::connect();
        $courses_value = Course::all();
        $schools_value = School::all();
        $uc_cursos = $db->getTable('cursos')->select('id', 'categoria_id')->get();
        $uc_workspace = Workspace::where('name', "Universidad Corporativa")->first();

        $course_school = [];
        $course_workspace = [];
        foreach ($uc_cursos as $curso) {
            $course = $courses_value->where('external_id', $curso->id)->first();
            $school = $schools_value->where('external_id', $curso->categoria_id)->first();

            if ($course and $school) {
                $course_school[] = ['course_id' => $course->id, 'school_id' => $school->id];
                $course_workspace[] = ['course_id' => $course->id, 'workspace_id' => $uc_workspace->id];
            }
        }

        $this->makeChunkAndInsert($course_school, 'course_school');
        $this->makeChunkAndInsert($course_workspace, 'course_workspace');
    }

    public function validIfExistsCriterionValueUser($criterion, $criterion_value, $user, &$criterion_user)
    {
        $exists = $user->criterion_values()->where('criterion_id', $criterion->id)->first();
        if ($exists) {
            DB::table('criterion_value_user')->where('user_id', $user->id)
                ->where('criterion_value_id', $exists->id)
                ->update(['criterion_value_id' => $criterion_value->id ?? $criterion_value]);
        } else {
            $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $criterion_value->id ?? $criterion_value];
        }
    }
}
