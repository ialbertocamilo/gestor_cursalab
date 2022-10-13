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
        // Capacitacion FP
    ];

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function migrateData1()
    {
        $client_lms_data = $this->setMigrationData_1();

        $this->insertMigrationData_1($client_lms_data);
    }

    public function setMigrationData_1()
    {
        $db = self::connect();
        $client_LMS_data = [
            'users' => [], 'carreras' => [], 'ciclos' => [], 'grupos' => [],
            'boticas' => [], 'modulos' => [], 'courses' => [], 'schools' => []
        ];

        $this->setUsersData($client_LMS_data, $db);
//        $this->setModulosData($client_LMS_data, $db);
//        $this->setCarrerasData($client_LMS_data, $db);
//        $this->setCiclosData($client_LMS_data, $db);
//        $this->setGruposData($client_LMS_data, $db);
//        $this->setBoticasData($client_LMS_data, $db);

//        $this->setSchoolsData($client_LMS_data, $db);
//        $this->setCoursesData($client_LMS_data, $db);

        return $client_LMS_data;
    }

    public function insertMigrationData_1($data)
    {
//        $this->insertModulosData($data);

        $this->insertUsersData($data);

//        $this->insertCarrerasData($data);
//        $this->insertCiclosData($data);
//
//        $this->insertGruposData($data);
//        $this->insertBoticasData($data);
//
//        $this->insertCriterionUserData($data);
//
//        $this->insertSegmentacionCarrerasCiclosData();

//        $this->insertSchoolData($data);
//        $this->insertCourseData($data);
    }

    public function setUsersData(&$result, $db)
    {
//        $uc = [
//            'name' => "Universidad Corporativa",
//            'active' => ACTIVE
//        ];
//        $uc_workspace = Workspace::create($uc);

        $uc_workspace = Workspace::where('slug', 'farmacias-peruanas')->first();

        $user_db = User::select('document', 'email')->get();

        $temp['users'] = $db->getTable('usuarios')
            ->select(
                'id',
                'nombre',
                'email',
                'dni',
                'config_id',
                'grupo',
                'botica_id',
                'estado',
                'created_at',
                'updated_at'
            )
            ->limit(100)
//            ->whereNotIn('dni', $users->pluck('document'))
//            ->whereNotIn('email', $users->pluck('email'))
            ->get();
//            ->count();

//        return $temp['users'];

        $type_client = Taxonomy::getFirstData('user', 'type', 'client');

        foreach ($temp['users'] as $user) {

            $dni_exists = $user->dni && $user_db->where('document', $user->dni)->first();
            $email_exists = $user->email && $user_db->where('document', $user->email)->first();

            if ($dni_exists || $email_exists) continue;

//            info("Usuario a agregar {$user->dni}");
//            print_r("Usuario a agregar {$user->dni}\n");
            $result['usuario_relations'][] = [
                'usuario_id' => $user->id,
                'config_id' => $user->config_id,
                'grupo_id' => $user->grupo,
                'botica_id' => $user->botica_id
            ];

            $result['users'][] = [
                'external_id' => $user->id,

                'name' => $user->nombre,

                'email' => $user->email,
                'document' => $user->dni,

                'type_id' => $type_client->id,
                'config_id' => $user->config_id,


                // TODO: Add fields ?
//                'person_number' => ???,
//                'phone_number' => ???,
                // TODO: Add fields ?


                'password' => bcrypt($user->dni),

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

        $modules = Workspace::whereNotNull('parent_id')->get();

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
        $carrera_criterion = Criterion::create([
            'name' => 'Carrera',
            'code' => 'career'
        ]);

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

                'value_text' => "M{$carrera->config_id}::" . $carrera->nombre,

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
                'id',
                'nombre',
                'secuencia',
                'carrera_id',
                'estado'
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

        foreach ($temp['ciclos_all'] as $ciclo) {
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
                'id',
                'valor',
                'config_id',
                'created_at',
                'updated_at'
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

                'value_text' => "M{$grupo->config_id}::" . $grupo->valor,

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
                'id',
                'nombre',
                'criterio_id',
                'codigo_local',
                'created_at',
                'updated_at'
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
//        info("DATA USERS");
//        print_r($data['users']);
//        info("============================");
        $this->insertChunkedData('users', $data['users']);
        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();

        $user_temp = [];
        foreach ($data['users'] as $user) {
//            info("Usuario a agregar {$user['document']}");
//            $module_value = $modules_values->where('external_id', $user['config_id'])->first();
            $module_value = self::MODULOS_EQUIVALENCIA[$user['config_id']] ?? false;
            unset($user['config_id']);

            if ($module_value) {
                $user_temp[] = array_merge($user, ['subworkspace_id' => $module_value,]);
//                $user_temp[] = array_merge($user, ['subworkspace_id' => $module_value->id,]);
            }
        }

        $this->makeChunkAndInsert($user_temp, 'users');
    }

    public function insertModulosData($data)
    {
        $this->insertChunkedData('criterion_values', $data['modulos']);

        $module = Criterion::where('code', 'module')->first();
        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $uc_workspace = Workspace::where('name', "Universidad Corporativa")->first();

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
        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
        $uc_workspace = Workspace::where('name', "Universidad Corporativa")->first();
        DB::table('criterion_workspace')->insert(['workspace_id' => $uc_workspace->id, 'criterion_id' => $career->id]);

        $temp = [];
        $criterion_values_workspace = [];
        foreach ($data['modulo_carrera'] as $relation) {
            $module = $modules_values->where('external_id', $relation['config_id'])->first();
            $career = $carreras_values->where('external_id', $relation['carrera_id'])->first();

            if ($module and $career) {
                $temp[] = ['criterion_value_parent_id' => $module->id, 'criterion_value_id' => $career->id];
                $criterion_values_workspace[] = ['criterion_value_id' => $career->id, 'workspace_id' => $uc_workspace->id];
            }
        }

        $this->makeChunkAndInsert($temp, 'criterion_value_relationship');
        $this->makeChunkAndInsert($criterion_values_workspace, 'criterion_value_workspace');
    }

    public function insertCiclosData($data)
    {
        $this->insertChunkedData('criterion_values', $data['grouped_ciclos']);

        $ciclo = Criterion::where('code', 'ciclo')->first();
        $ciclos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'ciclo'))->get();
        $carreras_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'career'))->get();
        $uc_workspace = Workspace::where('name', "Universidad Corporativa")->first();

        DB::table('criterion_workspace')->insert(['workspace_id' => $uc_workspace->id, 'criterion_id' => $ciclo->id]);

        $temp = [];
        $criterion_values_workspace = [];
        foreach ($data['ciclos_all'] as $relation) {
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

        $group = Criterion::where('code', 'group')->first();
        $modules_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'module'))->get();
        $grupos_values = CriterionValue::whereHas('criterion', fn($q) => $q->where('code', 'group'))->get();
        $uc_workspace = Workspace::where('name', "Universidad Corporativa")->first();

        DB::table('criterion_workspace')->insert(['workspace_id' => $uc_workspace->id, 'criterion_id' => $group->id]);

        $temp = [];
        $criterion_values_workspace = [];
        foreach ($data['grupo_carrera'] as $relation) {
            $module = $modules_values->where('external_id', $relation['config_id'])->first();
            $group = $grupos_values->where('external_id', $relation['grupo_id'])->first();

            if ($module and $group) {
                $temp[] = ['criterion_value_parent_id' => $module->id, 'criterion_value_id' => $group->id];
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
        $uc_workspace = Workspace::where('name', "Universidad Corporativa")->first();

        DB::table('criterion_workspace')->insert(['workspace_id' => $uc_workspace->id, 'criterion_id' => $botica->id]);

        $temp = [];
        $criterion_values_workspace = [];
        foreach ($data['grupo_botica'] as $relation) {
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

            if ($usuario_matriculas->count() > 0) :

                $module = $modules_value->where('external_id', $user_relations['config_id'])->first();
                $career = $carreras_values->where('external_id', $usuario_matriculas->first()->carrera_id)->first();
                $ciclos = $ciclos_values->whereIn('position', $usuario_matriculas->pluck('secuencia_ciclo'));

                if ($career and $module and $ciclos->count() > 0) :

                    // Push modulo
                    $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $module->id];

                    // Push carrera
                    $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $career->id];

                    // Push ciclos
                    foreach ($usuario_matriculas as $matricula) {
                        if ($matricula->estado) {
                            $ciclo = $ciclos_values->whereIn('position', $matricula->secuencia_ciclo)->first();
                            $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $ciclo->id];
                        }
                    }
//                    foreach ($ciclos as $ciclo){
//                        $matricula = $matriculas->where('usuario_id', $user->external_id);
//                        $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $ciclo->id];
//                    }

                endif;

            endif;

            if ($user_relations) :

                $group = $grupos_values->where('external_id', $user_relations['grupo_id'])->first();
                $botica = $boticas_values->where('external_id', $user_relations['botica_id'])->first();

                if ($group and $botica) :

                    // Push grupo
                    $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $group->id];

                    // Push botica
                    $criterion_user[] = ['user_id' => $user->id, 'criterion_value_id' => $botica->id];

                endif;

            endif;
        }

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
}
