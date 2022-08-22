<?php

namespace Database\Seeders;

use App\Models\AssignedRole;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    // Workspaces list
    public static $workspaces = [
        [   
            'id'=>1,
            'code' => 'INRE',
            'name' => 'InRetail',
            'children' => [
                ['name' => 'Agora'],
                ['name' => 'InDigital XP'],
                ['name' => 'Intercorp Retail'],
            ],
        ],
        [   
            'id'=>5,
            'code' => 'QUSU',
            'name' => 'Química Suiza',
            'children' => [
                ['name' => 'Química Suiza'],
                ['name' => 'Vanttive'],
            ],
        ],
        [   
            'id'=>8,
            'code' => 'FIOH',
            'name' => 'Financiera Oh',
            'children' => [
                ['name' => 'Financiera Oh - Masivo'],
                ['name' => 'Financiera Oh - Central'],
            ],
        ],
        [   
            'id'=>11,
            'code' => 'HOPE',
            'name' => 'Homecenters Peruanos',
            'children' => [
                ['name' => 'Promart - Masivo'],
                ['name' => 'Promart - Central'],
            ],
        ],
        [   
            'id'=>14,
            'code' => 'TIPE',
            'name' => 'Tiendas Peruanas',
            'children' => [
                ['name' => 'Oechsle'],
            ],
            
        ],
        [   
            'id'=>16,
            'code' => 'REPL',
            'name' => 'Real Plaza',
            'children' => [
                ['name' => 'Real Plaza'],
            ],
        ],
        [   
            'id'=>18,
            'code' => 'SUPE',
            'name' => 'Supermercados Peruanos',
            'children' => [
                ['name' => 'Plaza Vea Oriente'],
                ['name' => 'Compañia Hard Discount'],
                ['name' => 'Operadora de Servicios Logisticos'],
                ['name' => 'Compañia Food Retail'],
                ['name' => 'Administración Food Regional'],
                ['name' => 'Makro Supermayorista'],
            ],
        ],
        [   
            'id'=>25,
            'code' => 'FAPE',
            'name' => 'Farmacias Peruanas',
            'children' => [
                ['name' => 'Mi Farma'],
                ['name' => 'Inkafarma'],
                ['name' => 'FAPE Masivos'],
                ['name' => 'Administrativos FAPE'],
            ],
        ],
    ];

    public static $default_admins = [
        [
            'name'=> 'Rodrigo C',
            'email'=> 'rodrigo@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ]
            ]
        ],
        [
            'name'=> 'Elvis F',
            'email'=> 'elvis@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>5,
                    'role_id'=>4,
                ]
            ]
        ],
        [
            'name'=> 'Jean Carlo P',
            'email'=> 'jeancarlo@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>8,
                    'role_id'=>5,
                ]
            ]
        ],
        [
            'name'=> 'Crusbel D',
            'email'=> 'crusbel@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>11,
                    'role_id'=>6,
                ]
            ]
        ],
        [
            'name'=> 'Luis A',
            'email'=> 'luis@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ],
                [
                    'id'=>5,
                    'role_id'=>3,
                ],
                [
                    'id'=>8,
                    'role_id'=>3,
                ],
                [
                    'id'=>11,
                    'role_id'=>3,
                ],
                [
                    'id'=>14,
                    'role_id'=>3,
                ],
                [
                    'id'=>16,
                    'role_id'=>3,
                ],
                [
                    'id'=>18,
                    'role_id'=>3,
                ],
                [
                    'id'=>25,
                    'role_id'=>3,
                ],
            ]
        ],
        [
            'name'=> 'Fiorella L',
            'email'=> 'friorella@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ],
                [
                    'id'=>5,
                    'role_id'=>3,
                ],
                [
                    'id'=>8,
                    'role_id'=>3,
                ],
                [
                    'id'=>11,
                    'role_id'=>3,
                ],
                [
                    'id'=>14,
                    'role_id'=>3,
                ],
                [
                    'id'=>16,
                    'role_id'=>3,
                ],
                [
                    'id'=>18,
                    'role_id'=>3,
                ],
                [
                    'id'=>25,
                    'role_id'=>3,
                ],
            ]
        ],
        [
            'name'=> 'Alyona V',
            'email'=> 'alyona@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ],
                [
                    'id'=>5,
                    'role_id'=>3,
                ],
                [
                    'id'=>8,
                    'role_id'=>3,
                ],
                [
                    'id'=>11,
                    'role_id'=>3,
                ],
                [
                    'id'=>14,
                    'role_id'=>3,
                ],
                [
                    'id'=>16,
                    'role_id'=>3,
                ],
                [
                    'id'=>18,
                    'role_id'=>3,
                ],
                [
                    'id'=>25,
                    'role_id'=>3,
                ],
            ]
        ],
        [
            'name'=> 'Gabriel R',
            'email'=> 'gabriel@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ],
                [
                    'id'=>5,
                    'role_id'=>3,
                ],
                [
                    'id'=>8,
                    'role_id'=>3,
                ],
                [
                    'id'=>11,
                    'role_id'=>3,
                ],
                [
                    'id'=>14,
                    'role_id'=>3,
                ],
                [
                    'id'=>16,
                    'role_id'=>3,
                ],
                [
                    'id'=>18,
                    'role_id'=>3,
                ],
                [
                    'id'=>25,
                    'role_id'=>3,
                ],
            ]
        ],
        [
            'name'=> 'Gian Lucca M',
            'email'=> 'gianlucca@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ],
                [
                    'id'=>5,
                    'role_id'=>3,
                ],
                [
                    'id'=>8,
                    'role_id'=>3,
                ],
                [
                    'id'=>11,
                    'role_id'=>3,
                ],
                [
                    'id'=>14,
                    'role_id'=>3,
                ],
                [
                    'id'=>16,
                    'role_id'=>3,
                ],
                [
                    'id'=>18,
                    'role_id'=>3,
                ],
                [
                    'id'=>25,
                    'role_id'=>3,
                ],
            ]
        ],
        [
            'name'=> 'Kevin Alcántara',
            'email'=> 'Kevin.Alcantara@intercorpretail.pe',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ],
                [
                    'id'=>5,
                    'role_id'=>3,
                ],
                [
                    'id'=>8,
                    'role_id'=>3,
                ],
                [
                    'id'=>11,
                    'role_id'=>3,
                ],
                [
                    'id'=>14,
                    'role_id'=>3,
                ],
                [
                    'id'=>16,
                    'role_id'=>3,
                ],
                [
                    'id'=>18,
                    'role_id'=>3,
                ],
                [
                    'id'=>25,
                    'role_id'=>3,
                ],
            ]
        ],
        [
            'name'=> 'Vanessa Chavez',
            'email'=> 'Vanessa.Chavez@intercorpretail.pe',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ],
                [
                    'id'=>5,
                    'role_id'=>3,
                ],
                [
                    'id'=>8,
                    'role_id'=>3,
                ],
                [
                    'id'=>11,
                    'role_id'=>3,
                ],
                [
                    'id'=>14,
                    'role_id'=>3,
                ],
                [
                    'id'=>16,
                    'role_id'=>3,
                ],
                [
                    'id'=>18,
                    'role_id'=>3,
                ],
                [
                    'id'=>25,
                    'role_id'=>3,
                ],
            ]
        ],
        [
            'name'=> 'Kevin G',
            'email'=> 'kevin@cursalab.io',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>1,
                ],
                [
                    'id'=>5,
                    'role_id'=>1,
                ],
                [
                    'id'=>8,
                    'role_id'=>1,
                ],
                [
                    'id'=>11,
                    'role_id'=>1,
                ],
                [
                    'id'=>14,
                    'role_id'=>1,
                ],
                [
                    'id'=>16,
                    'role_id'=>1,
                ],
                [
                    'id'=>18,
                    'role_id'=>1,
                ],
                [
                    'id'=>25,
                    'role_id'=>1,
                ],
            ]
        ]

    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->insertCriteria();
        $this->insertWorkspaces();

        // $this->insertAdmins();
        $this->insertDefaultAdmins();
        //$this->insertUsers();
    }

    public function insertCriteria() {

        $default_type = Taxonomy::getFirstData('criterion', 'type', 'default');
        $date_type = Taxonomy::getFirstData('criterion', 'type', 'date');
        $numeric_type = Taxonomy::getFirstData('criterion', 'type', 'number');
        $boolean_type = Taxonomy::getFirstData('criterion', 'type', 'boolean');

        $criteria = [
            ['code' => 'module', 'name' => 'Módulo','required' =>0,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'business_unit_id', 'name' => 'Business_Unit_Id','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'business_unit_name', 'name' => 'Business_Unit_Name','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'gender', 'name' => 'Genero','required' =>1,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'position_name', 'name' => 'Position_Name','required' =>1,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'position_code', 'name' => 'Position_Code','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'date_start', 'name' => 'Date_Start','required' =>1,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $date_type->id],
            ['code' => 'seniority_date', 'name' => 'Seniority_date','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $date_type->id],
            ['code' => 'birthday_date', 'name' => 'Birthday_date','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $date_type->id],
            ['code' => 'tipo_de_bono', 'name' => 'Tipo de bono','required' =>1,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'grupo_ocupacional', 'name' => 'Grupo ocupacional','required' =>1,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'location_code', 'name' => 'location_code','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'location_name', 'name' => 'location_name','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'department_name', 'name' => 'department_name','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'department_code', 'name' => 'department_code','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'modalidad_de_trabajo', 'name' => 'modalidad de trabajo','required' =>0,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'department_name_nivel_1', 'name' => 'department_name_nivel_1','required' =>1,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'department_name_nivel_2', 'name' => 'department_name_nivel_2','required' =>1,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'department_name_nivel_3', 'name' => 'department_name_nivel_3','required' =>1,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'department_name_nivel_4', 'name' => 'department_name_nivel_4','required' =>0,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'department_name_nivel_5', 'name' => 'department_name_nivel_5','required' =>0,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'department_name_nivel_6', 'name' => 'department_name_nivel_6','required' =>0,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'national_identifier_number_manager', 'name' => 'National_Identifier_Number_Manager','required' =>0,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'nombre_de_jefe', 'name' => 'nombre de jefe','required' =>0,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'posicion_jefe', 'name' => 'posicion jefe','required' =>0,'show_in_segmentation' =>0,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'clasificacion_de_evd', 'name' => 'Clasificación de EVD','required' =>0,'show_in_segmentation' =>1,'is_default' => 1, 'field_id' => $default_type->id],
            ['code' => 'gor_gerente_de_área', 'name' => 'Gor/Gerente de área','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'botica', 'name' => 'Boticas','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'grupo', 'name' => 'Grupos','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'zonal', 'name' => 'Zonal','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'correo_zonal', 'name' => 'Correo zonal','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'tipo_de_publico', 'name' => 'Tipo de Publico','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'division', 'name' => 'División','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'area', 'name' => 'Área','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'region', 'name' => 'Región','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'region_de_tienda', 'name' => 'Región de tienda','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'correo_jefe', 'name' => 'correo jefe','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'grupos_de_supervision_supply', 'name' => 'Grupos de supervisión (Supply)','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'gerente_de_area_o_mall', 'name' => 'Gerente de área o Mall','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'cycle', 'name' => 'Ciclo','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
            ['code' => 'career', 'name' => 'Carrera','required' =>0,'show_in_segmentation' =>1,'is_default' => 0, 'field_id' => $default_type->id],
        ];

        foreach ($criteria as $criterion) {
            Criterion::create($criterion);
        }
    }

    /**
     * Insert workspaces and subworkspaces
     */
    public function insertWorkspaces() {

        foreach(static::$workspaces AS $row)
        {
            $workspace = Workspace::create([
                'name' => $row['name'],
                'active' => ACTIVE
            ]);

            foreach ($row['children'] as $key => $child)
            {
                $criterion_value = CriterionValue::create([
                    'criterion_id' => 1,
                    'value_text' => $child['name'],
                    'active' => 1
                ]);

                $subworkspace = Workspace::create([
                    'criterion_value_id' => $criterion_value->id,
                    'name' => $child['name'],
                    'parent_id' => $workspace->id,
                    'active' => 1
                ]);

                DB::table('criterion_value_workspace')->insert([
                    'workspace_id' => $workspace->id,
                    'criterion_value_id' => $criterion_value->id
                ]);

            }
        }         
    }

    /**
     * Insert users
     */
    public function insertUsers() {

        $idWorkspaceInRetail = Workspace::where('slug', 'intercorp-retail')->first()->id;
        $idWorkspaceUni = Workspace::where('slug', 'inkafarma')->first()->id;

        $users = [
            [
                'name' => 'Elvis Usuario Universidad',
                'email' => 'usuario_universidad_elvis@cursalab.io',
                'subworkspace_id' => $idWorkspaceUni,
            ]
            ,
            [
                'name' => 'Elvis Usuario Inretail',
                'email' => 'usuario_inretail_elvis@cursalab.io',
                'subworkspace_id' => $idWorkspaceInRetail,
            ]
            ,
            [
                'name' => 'Daniel Usuario Universidad',
                'email' => 'usuario_universidad_daniel@cursalab.io',
                'subworkspace_id' => $idWorkspaceUni,
            ]
            ,
            [
                'name' => 'Daniel Usuario Inretail',
                'email' => 'usuario_inretail_daniel@cursalab.io',
                'subworkspace_id' => $idWorkspaceInRetail,
            ]
            ,
            [
                'name' => 'JeanCarlo Usuario Universidad',
                'email' => 'usuario_universidad_jeancarlo@lamediadl.com',
                'subworkspace_id' => $idWorkspaceUni,
            ]
            ,
            [
                'name' => 'JeanCarlo Usuario Inretail',
                'email' => 'usuario_inretail_jeancarlo@lamediadl.com',
                'subworkspace_id' => $idWorkspaceInRetail,
            ]
            ,
            [
                'name' => 'Kevin Usuario Universidad',
                'email' => 'usuario_universidad_kevin@lamediadl.com',
                'subworkspace_id' => $idWorkspaceUni,
            ]
            ,
            [
                'name' => 'Kevin Usuario Inretail',
                'email' => 'usuario_inretail_kevin@lamediadl.com',
                'subworkspace_id' => $idWorkspaceInRetail,
            ]
            ,
            [
                'name' => 'Deivy Usuario Universidad',
                'email' => 'usuario_universidad_deyvi@lamediadl.com',
                'subworkspace_id' => $idWorkspaceUni,
            ]
            ,
            [
                'name' => 'Deivy Usuario Inretail',
                'email' => 'usuario_inretail_deyvi@lamediadl.com',
                'subworkspace_id' => $idWorkspaceInRetail,
            ]
            ,
            [
                'name' => 'Rodrigo Usuario Universidad',
                'email' => 'usuario_universidad_rodrigo@lamediadl.com',
                'subworkspace_id' => $idWorkspaceUni,
            ]
            ,
            [
                'name' => 'Rodrigo Usuario Inretail',
                'email' => 'usuario_inretail_rodrigo@lamediadl.com',
                'subworkspace_id' => $idWorkspaceInRetail,
            ]
        ];


        foreach ($users as &$userData) {

            $userData['username'] = explode('@', $userData['email'])[0];
            $userData['password'] = bcrypt('12345');;//Hash::make('12345');
            $userData['active'] = 1;

            if (str_contains($userData['email'], 'universidad')) {
                $workspace = Workspace::where('slug', 'farmacias-peruanas')->first();
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
                    'entity_type' => AssignedRole::USER_ENTITY,
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
                $workspace = Workspace::where('slug', 'farmacias-peruanas')->first();
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
                    'entity_type' => AssignedRole::USER_ENTITY,
                    'entity_id' => DB::getPdo()->lastInsertId(),
                    'scope' => $workspaceId
                ]);
        }

    }

    /**
     * Insert workspaces and subworkspaces
     */
    public function insertDefaultAdmins() {

        foreach(static::$default_admins AS $child)
        {
            // Create admins
            $user = User::create([
                'email'=>$child['email'],
                'name' => $child['name'],
                'password' => 'CursalabV2-$2022',
                'active' => 1
            ]);

            // Assign admins
            foreach ($child['workspaces'] as $key => $wk_arr)
            {
                AssignedRole::insert([
                    'entity_type' => AssignedRole::USER_ENTITY,
                    'entity_id' => $user->id,
                    'scope' => $wk_arr['id'],
                    'role_id' => $wk_arr['role_id']
                ]);
            }

        }         
    }
}
