<?php

namespace Database\Seeders;

use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\User;
use App\Models\Usuario;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    const ENTITY_TYPE = 'App\Models\User';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //DB::beginTransaction();

        //try {

            $this->insertCriteria();
            $this->insertWorkspaces();

            $user = User::where('email', 'usuario_inretail_elvis@cursalab.io')
                ->first();

            if (!$user) {
                $this->insertUsers();
                $this->insertAdmins();
            }

            DB::commit();
        //} catch (\Exception $e) {
            DB::rollback();
        //}


    }

    public function insertCriteria() {

        $criterion = Criterion::where('code', 'business_unit_id')
                    ->first();

        if ($criterion) return;

        $criteria = [
            ['code' => 'business_unit_id', 'name' => 'Business_Unit_Id','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'business_unit_name', 'name' => 'Business_Unit_Name','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'gender', 'name' => 'Genero','required' =>1,'show_in_segmentation' =>1],
            ['code' => 'position_name', 'name' => 'Position_Name','required' =>1,'show_in_segmentation' =>1],
            ['code' => 'position_code', 'name' => 'Position_Code','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'date_start', 'name' => 'Date_Start','required' =>1,'show_in_segmentation' =>1],
            ['code' => 'seniority_date', 'name' => 'Seniority_date','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'birthday_date', 'name' => 'Birthday_date','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'tipo_de_bono', 'name' => 'Tipo de bono','required' =>1,'show_in_segmentation' =>1],
            ['code' => 'grupo_ocupacional', 'name' => 'Grupo ocupacional','required' =>1,'show_in_segmentation' =>1],
            ['code' => 'location_code', 'name' => 'location_code','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'location_name', 'name' => 'location_name','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'department_name', 'name' => 'department_name','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'department_code', 'name' => 'department_code','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'modalidad_de_trabajo', 'name' => 'modalidad de trabajo','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'department_name_nivel_1', 'name' => 'department_name_nivel_1','required' =>1,'show_in_segmentation' =>0],
            ['code' => 'department_name_nivel_2', 'name' => 'department_name_nivel_2','required' =>1,'show_in_segmentation' =>1],
            ['code' => 'department_name_nivel_3', 'name' => 'department_name_nivel_3','required' =>1,'show_in_segmentation' =>1],
            ['code' => 'department_name_nivel_4', 'name' => 'department_name_nivel_4','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'department_name_nivel_5', 'name' => 'department_name_nivel_5','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'department_name_nivel_6', 'name' => 'department_name_nivel_6','required' =>0,'show_in_segmentation' =>0],
            ['code' => 'national_identifier_number_manager', 'name' => 'National_Identifier_Number_Manager','required' =>0,'show_in_segmentation' =>0],
            ['code' => 'nombre_de_jefe', 'name' => 'nombre de jefe','required' =>0,'show_in_segmentation' =>0],
            ['code' => 'posicion_jefe', 'name' => 'posicion jefe','required' =>0,'show_in_segmentation' =>0],
            ['code' => 'clasificacion_de_evd', 'name' => 'Clasificación de EVD','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'gor_gerente_de_área', 'name' => 'Gor/Gerente de área','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'botica', 'name' => 'Boticas','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'grupo', 'name' => 'Grupos','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'zonal', 'name' => 'Zonal','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'correo_zonal', 'name' => 'Correo zonal','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'tipo_de_publico', 'name' => 'Tipo de Publico','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'division', 'name' => 'División','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'area', 'name' => 'Área','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'region', 'name' => 'Región','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'region_de_tienda', 'name' => 'Región de tienda','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'correo_jefe', 'name' => 'correo jefe','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'grupos_de_supervision_supply', 'name' => 'Grupos de supervisión (Supply)','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'gerente_de_area_o_mall', 'name' => 'Gerente de área o Mall','required' =>0,'show_in_segmentation' =>1],
            //['code' => 'module', 'name' => 'Módulo','required' =>0,'show_in_segmentation' =>1],
            ['code' => 'cycle', 'name' => 'Ciclo','required' =>0,'show_in_segmentation' =>1],
        ];

        foreach ($criteria as $criterion) {
            Criterion::create($criterion);
        }
    }

    /**
     * Insert workspaces and subworkspaces
     */
    public function insertWorkspaces() {

        // When workspace already exists, stop method

        if (Workspace::find(20)) {
            return;
        }

        // InRetail workspace
        // ----------------------------------------

        Workspace::create([
            'id' => 20,
            'name' => 'InRetail',
            'slug' => 'inretail',
            'active' => 1
        ]);

        // Intercorp Retail subworkspace
        // ----------------------------------------

        Workspace::create([
            'id' => 21,
            'name' => 'Intercorp Retail',
            'slug' => 'intercorp-retail',
            'logo' => 'images/workspace4-20220816165310-9825.png',
            'parent_id' => 20,
            'active' => 1
        ]);
        $workspaceId = DB::getPdo()->lastInsertId();

        CriterionValue::create([
            'criterion_id' => 1,
            'value_text' => 'Intercorp Retail',
            'active' => 1
        ]);
        $criterionValueId = DB::getPdo()->lastInsertId();

        DB::table('criterion_value_workspace')->insert([
            'workspace_id' => $workspaceId,
            'criterion_value_id' => $criterionValueId
        ]);

        // Quimica Suiza subworkspace
        // ----------------------------------------

        Workspace::create([
            'id' => 22,
            'name' => 'Quimica Suiza',
            'slug' => 'quimica-suiza',
            'logo' => 'images/workspace1-20220816165243-3521.png',
            'parent_id' => 20,
            'active' => 1
        ]);

        $workspaceId = DB::getPdo()->lastInsertId();

        CriterionValue::create([
            'criterion_id' => 1,
            'value_text' => 'Quimica Suiza',
            'active' => 1
        ]);
        $criterionValueId = DB::getPdo()->lastInsertId();

        DB::table('criterion_value_workspace')->insert([
            'workspace_id' => $workspaceId,
            'criterion_value_id' => $criterionValueId
        ]);

        // Financiera Oh subworkspace
        // ----------------------------------------

        Workspace::create([
            'id' => 23,
            'name' => 'Financiera Oh',
            'slug' => 'financiera-oh',
            'logo' => 'images/workspace2-20220816165253-1851.png',
            'parent_id' => 20,
            'active' => 1
        ]);

        $workspaceId = DB::getPdo()->lastInsertId();

        CriterionValue::create([
            'criterion_id' => 1,
            'value_text' => 'Financiera Oh',
            'active' => 1
        ]);
        $criterionValueId = DB::getPdo()->lastInsertId();

        DB::table('criterion_value_workspace')->insert([
            'workspace_id' => $workspaceId,
            'criterion_value_id' => $criterionValueId
        ]);

        // Promart subworkspace
        // ----------------------------------------

        Workspace::create([
            'id' => 24,
            'name' => 'Promart',
            'slug' => 'promart',
            'logo' => 'images/workspace3-20220816165303-6210.png',
            'parent_id' => 20,
            'active' => 1
        ]);

        $workspaceId = DB::getPdo()->lastInsertId();

        CriterionValue::create([
            'criterion_id' => 1,
            'value_text' => 'Financiera Oh',
            'active' => 1
        ]);
        $criterionValueId = DB::getPdo()->lastInsertId();

        DB::table('criterion_value_workspace')->insert([
            'workspace_id' => $workspaceId,
            'criterion_value_id' => $criterionValueId
        ]);

        // Inkafarma subworkspace
        // ----------------------------------------

        Workspace::create([
            'id' => 25,
            'name' => 'Inkafarma',
            'slug' => 'inkafarma',
            'logo' => 'images/inkafarma.png',
            'parent_id' => 1,
            'active' => 1
        ]);

        $workspaceId = DB::getPdo()->lastInsertId();

        CriterionValue::create([
            'criterion_id' => 1,
            'value_text' => 'Inkafarma',
            'active' => 1
        ]);
        $criterionValueId = DB::getPdo()->lastInsertId();

        DB::table('criterion_value_workspace')->insert([
            'workspace_id' => $workspaceId,
            'criterion_value_id' => $criterionValueId
        ]);

        // Mifarma subworkspace
        // ----------------------------------------

        $mifarma = Workspace::find(3);
        $mifarma->logo = 'images/mifarma.png';
        $mifarma->save();
    }

    /**
     * Insert users
     */
    public function insertUsers() {

        $users = [
            [
                'name' => 'Elvis Usuario Universidad',
                'email' => 'usuario_universidad_elvis@cursalab.io',
                'subworkspace_id' => 3,
            ]
            ,
            [
                'name' => 'Elvis Usuario Inretail',
                'email' => 'usuario_inretail_elvis@cursalab.io',
                'subworkspace_id' => 21,
            ]
            ,
            [
                'name' => 'Daniel Usuario Universidad',
                'email' => 'usuario_universidad_daniel@cursalab.io',
                'subworkspace_id' => 3,
            ]
            ,
            [
                'name' => 'Daniel Usuario Inretail',
                'email' => 'usuario_inretail_daniel@cursalab.io',
                'subworkspace_id' => 21,
            ]
            ,
            [
                'name' => 'JeanCarlo Usuario Universidad',
                'email' => 'usuario_universidad_jeancarlo@lamediadl.com',
                'subworkspace_id' => 3,
            ]
            ,
            [
                'name' => 'JeanCarlo Usuario Inretail',
                'email' => 'usuario_inretail_jeancarlo@lamediadl.com',
                'subworkspace_id' => 21,
            ]
            ,
            [
                'name' => 'Kevin Usuario Universidad',
                'email' => 'usuario_universidad_kevin@lamediadl.com',
                'subworkspace_id' => 3,
            ]
            ,
            [
                'name' => 'Kevin Usuario Inretail',
                'email' => 'usuario_inretail_kevin@lamediadl.com',
                'subworkspace_id' => 21,
            ]
            ,
            [
                'name' => 'Deivy Usuario Universidad',
                'email' => 'usuario_universidad_deyvi@lamediadl.com',
                'subworkspace_id' => 3,
            ]
            ,
            [
                'name' => 'Deivy Usuario Inretail',
                'email' => 'usuario_inretail_deyvi@lamediadl.com',
                'subworkspace_id' => 21,
            ]
            ,
            [
                'name' => 'Rodrigo Usuario Universidad',
                'email' => 'usuario_universidad_rodrigo@lamediadl.com',
                'subworkspace_id' => 3,
            ]
            ,
            [
                'name' => 'Rodrigo Usuario Inretail',
                'email' => 'usuario_inretail_rodrigo@lamediadl.com',
                'subworkspace_id' => 21,
            ]
        ];


        foreach ($users as &$userData) {

            $userData['username'] = explode('@', $userData['email'])[0];
            $userData['password'] = bcrypt('12345');;//Hash::make('12345');
            $userData['active'] = 1;

            if (str_contains($userData['email'], 'universidad')) {
                $workspace = Workspace::where('slug', 'universidad-corporativa')->first();
                $workspaceId = $workspace->id;
            }

            if (str_contains($userData['email'], 'inretail')) {
                $workspace = Workspace::where('slug', 'inretail')->first();
                $workspaceId = $workspace->id;
            }

            User::insert($userData);
            DB::table('assigned_roles')
                ->insert([
                    'role_id' => 7, // USER role
                    'entity_type' => self::ENTITY_TYPE,
                    'entity_id' => DB::getPdo()->lastInsertId(),
                    'scope' => $workspaceId
                ]);
        }
    }

    /**
     * Insert admin users
     */
    public function insertAdmins() {

        $users = [
            [
                'name' => 'Elvis Admin Universidad',
                'email' => 'admin_universidad_elvis@cursalab.io',
            ]
            ,
            [
                'name' => 'Elvis Admin InRetail',
                'email' => 'admin_inretail_elvis@cursalab.io',
            ]
            ,
            [
                'name' => 'Daniel Config Universidad',
                'email' => 'config_universidad_daniel@cursalab.io',
            ]
            ,
            [
                'name' => 'Daniel Config Inretail',
                'email' => 'config_inretail_daniel@cursalab.io',
            ]
            ,
            [
                'name' => 'JeanCarlo Content Universidad',
                'email' => 'content_manager_universidad_jeancarlo@lamediadl.com',
            ]
            ,
            [
                'name' => 'JeanCarlo Content InRetail',
                'email' => 'content_manager_jeancarlo@lamediadl.com',
            ]
            ,
            [
                'name' => 'Kevin Trainer Universidad',
                'email' => 'trainer_universidad_kevin@lamediadl.com',
            ]
            ,
            [
                'name' => 'Kevin Trainer InRetail',
                'email' => 'trainer_inretail_kevin@lamediadl.com',
            ]
            ,
            [
                'name' => 'Deyvi Reports Universidad',
                'email' => 'reports_universidad_deyvi@lamediadl.com',
            ]
            ,
            [
                'name' => 'Deyvi Reports InRetail',
                'email' => 'reports_inretail_deyvi@lamediadl.com',
            ]
            ,
            [
                'name' => 'Rodrigo Super',
                'email' => 'super_rodrigo@lamediadl.com',
            ]
        ];


        foreach ($users as &$userData) {

            $userData['password'] = bcrypt('12345');
            $userData['username'] = explode('@', $userData['email'])[0];
            $userData['active'] = 1;
            $roleId = 0;

            // Get role id

            if (str_contains($userData['email'], 'super_')) $roleId = 1;
            if (str_contains($userData['email'], 'config_')) $roleId = 2;
            if (str_contains($userData['email'], 'admin_')) $roleId = 3;
            if (str_contains($userData['email'], 'content_')) $roleId = 4;
            if (str_contains($userData['email'], 'trainer_')) $roleId = 5;
            if (str_contains($userData['email'], 'reports')) $roleId = 6;

            if (str_contains($userData['email'], 'universidad')) {
                $workspace = Workspace::where('slug', 'universidad-corporativa')->first();
                $workspaceId = $workspace->id;
            }

            if (str_contains($userData['email'], 'inretail')) {
                $workspace = Workspace::where('slug', 'inretail')->first();
                $workspaceId = $workspace->id;
            }

            User::insert($userData);

            // Create roles

            DB::table('assigned_roles')
                ->insert([

                    'role_id' => $roleId,
                    'entity_type' => self::ENTITY_TYPE,
                    'entity_id' => DB::getPdo()->lastInsertId(),
                    'scope' => $workspaceId
                ]);
        }

    }
}
