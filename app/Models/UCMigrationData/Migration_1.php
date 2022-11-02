<?php

namespace App\Models\UCMigrationData;

use App\Models\Block;
use App\Models\Course;
use App\Models\Criterio;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\School;
use App\Models\Segment;
use App\Models\SegmentValue;
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
            $module = self::MODULOS_CRITERION_VALUE[$relation['config_id']] ?? false;
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

    protected function fixGrupoValuesRelationships($output)
    {
        $grupo_values = CriterionValue::disableCache()->whereRelation('criterion', 'code', 'grupo')
            ->select('id', 'value_text', 'criterion_id')
            ->get();

        foreach ($grupo_values as $grupo_value) {
            $explode_1 = explode("::", $grupo_value->value_text);
            $explode_2 = $explode_1[0][1];
            $module_value = self::MODULOS_CRITERION_VALUE[$explode_2] ?? false;

            $grupo_value->parents()->sync([$module_value]);
        }
    }

    protected function fixCarreraValuesRelationships($output)
    {
        $grupo_values = CriterionValue::disableCache()->whereRelation('criterion', 'code', 'career')
            ->select('id', 'value_text', 'criterion_id')
            ->get();

        foreach ($grupo_values as $grupo_value) {
            $explode_1 = explode("::", $grupo_value->value_text);
            $explode_2 = $explode_1[0][1];
            $module_value = self::MODULOS_CRITERION_VALUE[$explode_2] ?? false;

            $grupo_value->parents()->sync([$module_value]);
        }
    }

    protected function insertSegmentacionCarrerasCiclosData($output)
    {
        $db = self::connect();

        $direct_segmentation_type = Taxonomy::getFirstData('segment', 'type', 'direct-segmentation');
        $courses = Course::disableCache()->select('id', 'external_id', 'name')->get();
        $carreras_values = CriterionValue::disableCache()->whereRelation('criterion', 'code', 'career')
            ->select('id', 'value_text', 'external_id', 'criterion_id')
            ->get();
        $ciclos_values = CriterionValue::disableCache()->whereRelation('criterion', 'code', 'cycle')
            ->select('id', 'value_text', 'criterion_id')
            ->get();
        $grupos_values = CriterionValue::disableCache()->whereRelation('criterion', 'code', 'grupo')
            ->select('id', 'value_text', 'criterion_id', 'external_id')
            ->get();

        $curriculas = $db->getTable('curricula')
            ->join('ciclos', 'ciclos.id', 'curricula.ciclo_id')
            ->join('carreras', 'carreras.id', 'curricula.carrera_id')
            ->whereIn('curricula.carrera_id', $carreras_values->pluck('external_id')->toArray())
            ->select('curricula.id as curricula_id', 'curricula.carrera_id', 'curricula.curso_id', 'ciclos.nombre as ciclo_nombre', 'all_criterios',
                'carreras.config_id', 'curricula.created_at', 'curricula.updated_at')
//            ->limit(200)
//            ->whereIn('curso_id', [804, 805])
            ->get();

        $curricula_grouped_by_course_id = $curriculas->groupBy('curso_id');

        $bar = $output->createProgressBar($curricula_grouped_by_course_id->count());
        $bar->start();

        foreach ($curricula_grouped_by_course_id as $course_external_id => $curricula) {
            $bar->advance();

            $course = $courses->where('external_id', $course_external_id)->first();

            if (!$course) {
                info("curso externo {$course_external_id} no existe");
                continue;
            }

            $curricula_by_carrera = $curricula->groupBy('carrera_id');

            foreach ($curricula_by_carrera as $carrera_id => $curricula_carrera) {
                $segment_criterion = collect();
                $career = $carreras_values->where('external_id', $carrera_id)->first();

                if (!$career) {
                    info("carrera externa {$career} no existe");
                    continue;
                }

                $ciclos = $ciclos_values->whereIn('value_text', $curricula_carrera->pluck('ciclo_nombre'));

                $grupos = collect([]);

                if ($curricula_carrera->first()->all_criterios) {
                    $grupos_id = $db->getTable('curricula_criterio')
                        ->where('curricula_id', $curricula_carrera->first()->curricula_id)
                        ->pluck('criterio_id')
                        ->toArray();
//                    info($grupos_id);
                    $grupos = $grupos_values->whereIn('external_id', $grupos_id);
//                    info($grupos->pluck('value_text', 'id')->toArray());
                }

                //CriterionValue::whereRelation('criterion', 'code', 'grupo')->whereHas('parents', function ($q) {$q->where('id', 19);})->count();

                $segment_criterion->push($career);
                $segment_criterion = $segment_criterion->merge($ciclos);
                $segment_criterion = $segment_criterion->merge($grupos);
//                info($segment_criterion->toArray());
                $values = [];
                foreach ($segment_criterion as $criterion_value) {
                    $values[] = [
                        'criterion_value_id' => $criterion_value->id,
                        'criterion_id' => $criterion_value->criterion_id,
                        'type_id' => null,
                    ];
                }

//                info("CURSO :: {$course_external_id} - GRUPOS :: {$grupos->count()} - VALUES :: " . count($values));
//                continue;
                $segment = Segment::create([
                    'name' => "Segmentacion migrada (Curso UCFP-{$course_external_id})",
                    'model_type' => Course::class,
                    'model_id' => $course->id,
                    'type_id' => $direct_segmentation_type->id,
                    'active' => ACTIVE,
                ]);

                $segment->values()->sync($values);
            }

        }

        $bar->finish();

        return;
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

    protected function fixSubworkpsaceIdUsers($bar)
    {

        /*
         select
            cvu.user_id,
                    u.name,
                    u.document,
                    w.name as modulo_sub,
                    u.subworkspace_id,

            c.name criterion_name,
            cv.value_text
        from
            users u
                inner join workspaces w on w.id = u.subworkspace_id
                inner join criterion_value_user cvu on cvu.user_id = u.id
                inner join criterion_values cv on cv.id = cvu.criterion_value_id
                inner join criteria c on c.id = cv.criterion_id and criterion_id = 1
        where
            w.criterion_value_id <> cv.id

         order by cv.id;
         */
        $temp = [];

        $subworkspaces = Workspace::whereNotNull('parent_id')->select('id', 'parent_id')->get();
        $users = User::with('subworkspace:id,criterion_value_id')
            ->select('id', 'external_id', 'subworkspace_id')
            ->whereIn('document', ["46433433", "42247612", "46565943", "44330661", "46887809", "42741364", "40597411", "28308031",
                "29352756", "32906236", "19097673", "10837066", "47513408", "15451095", "46206350", "12345678", "75663555", "05070324",
                "45060159", "72891715", "70773655", "08347556", "71572018", "47862484", "10616934", "40424512", "43803826", "45744664",
                "46049224", "47592776", "10731959", "74254235", "40517832", "41659279", "44044164", "07637425", "45553468", "48338391",
                "70231669", "42219757", "41581150", "47108286", "47452220", "76141781", "47573998", "70105227", "73207776", "47831934",
                "40767297", "44176799", "48000944", "43548565", "74893711", "02664328", "73180221", "80019474", "46999044", "46273667",
                "10627213", "47409571", "47133565", "44477935", "75281810", "72692589", "73518166", "72260598", "43832960", "76591670",
                "70585847", "41736644", "46162402", "76149320", "43921575", "70158566", "48423715", "07642898", "74990343", "47047503",
                "47181011", "72860795", "75911233", "10614358", "76436084", "77054697", "75151181", "46894486", "71522997", "00000001",
                "77160728", "48684317", "76306261", "45601256", "10549769", "09537241", "11223344", "47732319", "75387687", "48110606",
                "46902369", "77226368", "75169793", "47020692", "76247389", "72921929", "44180295", "46481548", "70565193", "77867069",
                "32836810", "70972433", "70978430", "43511255", "46264965", "46969409", "42260942", "73238982", "45517931", "45731902",
                "45617421", "71003619", "75998526", "46668255", "25829447", "42212145", "42642909", "48986495", "44814865", "44538161",
                "43881654", "47042869", "73120320", "41246507", "72932141", "46011932", "42626031", "45642286", "75581472", "48238488",
                "70036718", "75338751", "71608937", "77293698", "70168069", "70363934", "46011926", "42128916", "72226569", "76001082",
                "47875138", "45990845", "45944034", "73062738", "46652379", "10340111", "45031869", "10443321", "76448275", "47011955",
                "62768481"])
            ->chunkById(200, function ($users_chunked) {

                foreach ($users_chunked as $user) {
                    $correct_sub_workspace_value = $user->subworkspace->criterion_value_id;
                    $wrong_sub_workspace_value = $user->criterion_values()
                        ->whereRelation('criterion', 'code', 'module')
                        ->first();

                    info("El usuario {$user->id} tiene el criterion_value {$wrong_sub_workspace_value->id} y se le va a cambiar por {$correct_sub_workspace_value}");

                    DB::table('criterion_value_user')
                        ->where('user_id', $user->id)
                        ->where('criterion_value_id', $wrong_sub_workspace_value->id)
                        ->update(['criterion_value_id' => $correct_sub_workspace_value]);

                }

            });
    }

    protected function fixTypeCourses($output)
    {
        $db = $this->connect();

        $courses = Course::disableCache()->whereNotNull('external_id')->select('id', 'external_id')->get();
        $type_courses = Taxonomy::getData('course', 'type')->select('id', 'code')->get();
        $cursos = $db->getTable('cursos')
            ->join('categorias', 'categorias.id', 'cursos.categoria_id')
            ->select(
                'cursos.id as curso_id',
                'categorias.modalidad',
                'categorias.id as categoria_id',
            )
            ->get();

        $equivalencias = [
            'extra' => 'extra-curricular',
            'libre' => 'free',
            'regular' => 'regular'
        ];

        $bar = $output->createProgressBar($courses->count());
        $bar->start();

        foreach ($cursos as $curso) {
            $bar->advance();

            $course = $courses->where('external_id', $curso->curso_id)->first();
            $type = $type_courses->where('code', $equivalencias[$curso->modalidad] ?? false)->first();

            if (!$course || !$type) continue;

            info("Se va a actualizar al curso externo {$course->external_id} con el tipo {$type->code}");
            $course->update([
                'type_id' => $type->id,
            ]);

        }
        $bar->finish();
    }

    protected function unificarCarreras()
    {
        $db = $this->connect();

        $carreras_values = CriterionValue::disableCache()
            ->whereRelation('criterion', 'code', 'career')
            ->whereNotNull('external_id')
            ->select('id', 'value_text', 'external_id', 'criterion_id')
            ->get();

        foreach ($carreras_values as $carrera) {
            if (str_contains($carrera->value_text, '::')) {
                $temp = explode('::', $carrera->value_text);
                $new_value_text = $temp[1];

                $carrera->update(['value_text' => $new_value_text]);
            }
            $carrera->parents()->sync([]);

            $carreras = $db->getTable('carreras')->where('nombre', $carrera->value_text)->get();
            foreach ($carreras as $carreraUC) {
                $module_value = self::MODULOS_CRITERION_VALUE[$carreraUC->config_id] ?? false;
                if ($module_value)
                    $carrera->parents()->attach($module_value);
            }

        }

        $carreras_values = CriterionValue::disableCache()
            ->whereRelation('criterion', 'code', 'career')
            ->select('id', 'value_text', 'external_id', 'criterion_id')
            ->get();

        $grouped_bye_value_text = $carreras_values->groupBy('value_text');
        $temp = [];

        foreach ($grouped_bye_value_text as $carrera_name => $values) {
            $temp[$values->first()->id] = $values->where('id', '<>', $values->first()->id)->pluck('id')->toArray();
        }

        foreach ($temp as $criterion_value_id => $equivalents) {
            SegmentValue::whereIn('criterion_value_id', $equivalents)
                ->update(['criterion_value_id' => $criterion_value_id]);

            DB::table('criterion_value_user')
                ->whereIn('criterion_value_id', $equivalents)
                ->update(['criterion_value_id' => $criterion_value_id]);

            DB::table('criterion_value_workspace')
                ->whereIn('criterion_value_id', $equivalents)
                ->update(['criterion_value_id' => $criterion_value_id]);

            CriterionValue::whereIn('id', $equivalents)->delete();
        }

//        $segmentos = Segment::disableCache()
//            ->where('name', 'like', '%Segmentacion migrada%')
//            ->get();
//
//        foreach ($segmentos as $segment) {
//
//            $config_id = $db->getTable('cursos')->where('id', $segment->model->external_id)->first()->config_id;
//            $modulo_value_id = self::MODULOS_CRITERION_VALUE[$config_id] ?? false;
//
//            if ($modulo_value_id) {
//
//                $segment->values()->firstOrCreate(
//                    ['criterion_value_id' => $modulo_value_id],
//                    [
//                        'criterion_value_id' => $modulo_value_id,
//                        'criterion_id' => 1, // Modulo criteria_id
//                        'type_id' => NULL,
//                    ]
//                );
//
//            }
//
//        }

//        CriterionValue::with('parents:id,value_text')->where('value_text', 'Técnico de Farmacia')->get();

    }

}
