<?php

namespace Database\Seeders;

use Bouncer;

use App\Models\Poll;
use App\Models\Post;
use App\Models\Role;

use App\Models\User;

use App\Models\Audit;
use App\Models\Ayuda;
use App\Models\Error;
use App\Models\Media;

use App\Models\Topic;

use App\Models\Course;

use App\Models\School;
use App\Models\Ticket;
use App\Models\Ability;
use App\Models\Account;

use App\Models\Meeting;

use App\Models\Segment;

use App\Models\Ambiente;
use App\Models\AyudaApp;
use App\Models\Glossary;
use App\Models\Question;
use App\Models\Taxonomy;
use App\Models\Attendant;

use App\Models\CheckList;

use App\Models\Criterion;
use App\Models\Vademecum;
use App\Models\Videoteca;

use App\Models\Workspace;
use App\Models\Permission;


use App\Models\Supervisor;
use App\Models\Certificate;

use App\Models\Announcement;

use App\Models\Notification;

use App\Models\PollQuestion;

// use App\Models\Setting;
// use App\Models\Tag;
use App\Models\CriterionValue;

use Illuminate\Database\Seeder;
use App\Models\PollQuestionAnswer;

class BouncerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Bouncer::role()->firstOrCreate([
        //     'name' => 'super-user',
        //     'title' => 'Super Administrador',
        // ]);
        // Bouncer::role()->firstOrCreate([
        //     'name' => 'config',
        //     'title' => 'Configurador',
        // ]);
        // Bouncer::role()->firstOrCreate([
        //     'name' => 'admin',
        //     'title' => 'Administrator',
        // ]);
        // Bouncer::role()->firstOrCreate([
        //     'name' => 'content-manager',
        //     'title' => 'Gestor de Contenidos',
        // ]);
        // Bouncer::role()->firstOrCreate([
        //     'name' => 'trainer',
        //     'title' => 'Entrenador',
        // ]);
        // Bouncer::role()->firstOrCreate([
        //     'name' => 'reports',
        //     'title' => 'Reportero',
        // ]);
        // Bouncer::role()->firstOrCreate([
        //     'name' => 'user',
        //     'title' => 'Usuario',
        // ]);
        // Bouncer::allow('superadmin')->everything();
        // Bouncer::allow('coder')->everything();

        $default_actions = ['list', 'create', 'edit', 'show', 'delete', 'status', 'audit'];
        $menus = [
            [
                'group'=>'gestor',
                'type' => 'menu',
                'position'=>1,
                'name' => "RESUMEN",
                'icon' => "fas fa-dice-d6",
                'children' => [
                    [
                        'code' => 'dashboard',
                        'model' => 'powerbi.index',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'name' => "Dashboard",
                        'icon' => "fas fa-tachometer-alt",
                        'extra_attributes'=>[
                            'path'=> "/home",
                            'subpaths' => ["home"],
                        ],
                        'abilities' =>[[
                            'name' => 'show',
                            'title' => 'Mostrar submenú',
                        ]],
                        'roles' => [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer",
                            "reports",
                        ]
                    ],
                    [
                        'model'=> 'App\Services\DashboardService',
                        'group'=>'gestor',
                        'code' => 'learning-analytics',
                        'type' => 'submenu',
                        'name' => "Learning Analytics",
                        'icon' => "fas fa-chart-line",
                        'extra_attributes'=>[
                            'path'=> "/dashboard_pbi",
                            'subpaths' => ["dashboard_pbi"],
                        ],
                        'abilities' =>[[
                            'name' => 'show',
                            'title' => 'Mostra submenú',
                        ]],
                        'roles' => [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer",
                            "reports"
                        ]
                    ]
                ]
            ],
            [
                'group'=>'gestor',
                'type' => 'menu',
                'name' => "SESIONES LIVE",
                'icon' => "fas fa-chalkboard",
                'children' => [
                    [
                        'model'=> 'App\Models\Meeting',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'code' => 'meetings',
                        'name' => "Gestiona sesiones",
                        'icon' => "fas fa-chalkboard-teacher",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path'=> "/aulas-virtuales/reuniones",
                            'subpaths'=> ["aulas-virtuales/reuniones"],
                        ],
                        'roles'=> ["super-user"]
                    ],
                    [
                        'model'=> 'App\Models\Account',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'code' => 'account-meetings',
                        'name' => "Cuentas Zoom",
                        'icon' => "fas fa-chalkboard-teacher",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path'=> "/aulas-virtuales/cuentas",
                            'subpaths'=> ["aulas-virtuales/cuentas"],
                        ],
                        'roles' => ["super-user"]
                    ]
                ]
            ],
            [
                'group'=>'gestor',
                'type' => 'menu',
                'name'=> "BENEFICIOS",
                'icon' => "fas fa-gift",
                'children' => [
                    [
                        'model'=> 'App\Models\Benefit',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'name'=> "Configuración",
                        'code' => 'benefits',
                        'icon' => "fas fa-gift",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' => "/beneficios",
                            'subpaths'=> ["beneficios"],
                        ],
                        'roles' => [
                            "super-user",
                            "admin"
                        ]
                    ],
                    [
                        'model'=> 'App\Models\Speaker',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'code' => 'speaker',
                        'name'=> "Facilitadores",
                        'icon' => "fas fa-gift",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' => "/speakers",
                            'subpaths'=> ["speakers"],
                        ],
                        'roles' => [
                            "super-user",
                            "admin"
                        ]
                    ]
                ]
            ],
            [
                'group'=>'gestor',
                'type' => 'menu',
                'name' => "USUARIOS",
                'icon' => "fas fa-users-cog",
                'children' => [
                    [
                        'model'=> 'App\Models\Workspace',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'code' => 'modules',
                        'name' => "Módulos",
                        'icon' => "fas fa-th-large",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' => "/modulos",
                            'subpaths' => [
                                "modulos"
                            ],
                        ],
                        'roles' => [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    ],
                    [
                        'model'=> 'App\Models\User',
                        'group'=>'gestor',
                        'code' => 'users',
                        'type' => 'submenu',
                        'name' => "Usuarios",
                        'icon' => "fas fa-users",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' => "/usuarios",
                            'subpaths' => ["usuarios"],
                        ],
                        'roles' => ["super-user", "admin"]
                    ],
                    [
                        'model'=> 'App\Models\Usuario',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'code' => 'admins',
                        'name' => "Administradores",
                        'icon' => "fas fa-users-cog",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'roles' => ["super-user"],
                        'extra_attributes'=>[
                            'path' => "/users",
                            'subpaths' => ["users"],
                        ],
                    ],
                    [
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'model'=> 'App\Models\Criteria',
                        'code' => 'criteria',
                        'name' => "Criterios",
                        'icon' => "fas fa-clipboard-list",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' => "/criterios",
                            'subpaths' => ["criterios", "valores"],
                        ],
                        'roles' => ["super-user", "config", "admin"]
                    ],
                    [
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'model'=> 'App\Models\Supervisor',
                        'name' =>"Supervisores",
                        'code' => 'supervisor',
                        'icon' =>"fas fa-sitemap",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' =>"/supervisores",
                            'subpaths' => ["reportes-supervisores"],
                        ],
                        'roles' =>["super-user","admin"]
                    ],
                ]
            ],
            [
                'group'=>'gestor',
                'type' => 'menu',
                'name' => "GESTIONA TUS CURSOS",
                'icon' => "fas fa-cog",
                'active' => false,
                'functionality' => ['default'],
                'children' => [
                    [
                        'model'=> 'App\Models\Workspace',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'code' => 'modules',
                        'name' => "_Módulos",
                        'icon' => "fas fa-th-large",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' => "/modulos",
                            'subpaths' => [
                                "modulos",
                            ],
                        ],
                        'selected' => false,
                        'permission' => "modulos",
                        'roles' => [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    ],
                    [
                        'model'=> 'App\Models\School',
                        'group'=>'gestor',
                        'code' => 'school',
                        'type' => 'submenu',
                        'name' => "Escuelas",
                        'icon' => "fas fa-th-large",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' => "/escuelas",
                            'subpaths' => ["escuelas"],
                        ],
                        'permission' => "escuelas",
                        'roles' => [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    ],
                    [
                        'model'=> 'App\Models\Course',
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'name' => "Cursos",
                        'code' => 'course',
                        'icon' => "mdi mdi-notebook",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' => "/cursos",
                            'subpaths' => ["cursos"],
                        ],
                        'selected' => false,
                        'isBeta' => false,
                        'permission' => "cursos",
                        'roles' => [
                            "super-user",
                            "admin",
                            "content-manager",
                        ]
                    ],
                ]
            ],
            [
                'name' =>  "DIPLOMAS",
                'icon' =>  "fas fa-medal",
                'active' =>  false,
                'isBeta' =>  true,
                'functionality' =>  ['default'],
                'children' =>  [
                    [
                        'model'=> 'App\Models\Certificate',
                        'name' =>  "Listar",
                        'icon' =>  "fas fa-list",
                        'code' => 'list-certificate',
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/diplomas",
                            'subpaths' =>  ["diplomas"],
                        ],
                        'selected' =>  false,
                        'isBeta' =>  true,
                        'permission' =>  "diplomas",
                        'roles' =>  [
                            "super-user",
                        ]
                    ],
                    [
                        'model'=> 'App\Models\Certificate',
                        'name' =>  "Crear",
                        'icon' =>  "fas fa-plus",
                        'code' => 'create-certificate',
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/diploma/create",
                            'subpaths' =>  ["diploma/create"],
                        ],
                        'selected' =>  false,
                        'isBeta' =>  true,
                        'permission' =>  "diploma_create",
                        'roles' =>  [
                            "super-user",
                        ]
                    ]
                ]
            ],
            [
                'name' =>  "GESTIONA TU CONTENIDO",
                'icon' =>  "fas fa-pen-square",
                'active' =>  false,
                'functionality' =>  ['default'],
                'children' =>  [
                    [
                        'code'=>'vademecun',
                        'name' => "Protocolos y Documentos",
                        'icon' => "fas fa-file-invoice",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'extra_attributes'=>[
                            'path' => "/protocolos-y-documentos",
                            'subpaths' => ["protocolos-y-documentos"],
                        ],
                        'selected' => false,
                        'permission' => "vademecum",
                        'roles' => ["super-user", "admin", "content-manager"]
                    ],
                    [
                        'model'=> 'App\Models\Announcement',
                        'name' =>  "Anuncios",
                        'icon' =>  "far fa-newspaper",
                        'code' => 'announcement',
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/anuncios",
                            'subpaths' =>  ["anuncios"],
                        ],
                        'selected' =>  false,
                        'permission' =>  "anuncios",
                        'roles' =>  [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    ],
                    [
                        'code' => 'pool',
                        'model'=> 'App\Models\Pool',
                        'name' =>  "Encuestas",
                        'icon' =>  "fas fa-pencil-alt",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/encuestas",
                            'subpaths' =>  ["encuestas"],
                        ],
                        'selected' =>  false,
                        'permission' =>  "encuestas",
                        'roles' =>  [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    ],
                    [
                        'code' => 'media',
                        'model'=> 'App\Models\Media',
                        'name' =>  "Multimedia",
                        'icon' =>  "fas fa-photo-video",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/multimedia",
                            'subpaths' =>  ["multimedia"],
                        ],
                        'selected' =>  false,
                        'permission' =>  "multimedia",
                        'roles' =>  [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    ],

                    [
                        'code' => 'videoteca',
                        'model'=> 'App\Models\Videoteca',
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                         'name' => "Videoteca",
                         'icon' => "fas fa-caret-square-right",
                         'extra_attributes'=>[
                             'path' => "/videoteca/list",
                             'subpaths' => ["videoteca"],
                         ],
                         'selected' => false,
                         'permission' => "videoteca",
                         'roles' => ["super-user","admin","content-manager","trainer"]
                     ],
                ]
            ],
            [
                'name' =>  "CHECKLIST",
                'icon' =>  "fas fa-business-time",
                'active' =>  false,
                'isBeta' =>  true,
                'functionality' =>  ['default'],
                'children' =>  [
                    [
                        'model'=> 'App\Models\Trainer',
                        'name' =>  "Entrenadores y equipo",
                        'code' => 'trainer',
                        'icon' =>  "fas fa-user-graduate",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/entrenamiento/entrenadores",
                            'subpaths' =>  ["entrenamiento/entrenador"],
                        ],
                        'selected' =>  false,
                        'permission' =>  "entrenadores",
                        'roles' =>  [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    ],
                    [
                        'code' => 'checklist',
                        'model'=> 'App\Models\Checklist',
                        'name' =>  "Gestiona Checklist",
                        'icon' =>  "fas fa-tasks",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/entrenamiento/checklists",
                            'subpaths' =>  ["entrenamiento/checklist"],
                        ],
                        'selected' =>  false,
                        'permission' =>  "checklist",
                        'roles' =>  [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    ]
                ]
            ],
            [
                'name' =>  "VOTACIONES",
                'icon' =>  "fas fa-paper-plane",
                'active' =>  false,
                'functionality' =>  ['reconocimiento'],
                'children' =>  [
                    [
                        'code' => 'list-campaign',
                        'model'=> 'App\Models\CampaignList',
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'name' =>  "Campaña",
                        'icon' =>  "fas fa-list",
                        'extra_attributes'=>[
                            'path' =>  "/votaciones",
                            'subpaths' =>  ["votaciones"],
                        ],
                        'selected' =>  false,
                        'isBeta' =>  true,
                        'permission' =>  "votaciones",
                        'roles' =>  [
                            "super-user",
                        ]
                    ],

                    [
                        'model'=> 'App\Models\CampaignCreate',
                        'code' => 'create-campaign',
                        'name' =>  "Crear Campaña",
                        'icon' =>  "fas fa-plus",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/votacion/create",
                            'subpaths' =>  ["votacion/create"],
                        ],
                        'selected' =>  false,
                        'isBeta' =>  true,
                        'permission' =>  "votacion_create",
                        'roles' =>  [
                            "super-user",
                        ]
                    ]
                ]
            ],
            [
                'name' =>  "REPORTES",
                'icon' =>  "fas fa-download",
                'active' =>  false,
                'functionality' =>  ['default'],
                'children' =>  [
                    [
                        'name' =>  "General",
                        'icon' =>  "fas fa-download",
                        'code' => 'report',
                        'extra_attributes'=>[
                            'path' =>  "/exportar/node",
                            'subpaths' =>  ["exportar/node"],
                        ],
                        'selected' =>  false,
                        'permission' =>  "reportes",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'roles' =>  [
                            "super-user",
                            "admin",
                            "trainer",
                            "reports",
                            'only-reports'
                        ]
                    ],
                    [
                        'name' =>  "Reporte de Encuestas",
                        'icon' =>  "fas fa-poll",
                        'code' => 'poll-report',
                        'extra_attributes'=>[
                            'path' =>  "/resumen_encuesta",
                            'subpaths' =>  ["resumen_encuesta"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                        ],
                        'selected' =>  false,
                        'permission' =>  "resumen_encuesta",
                        'roles' =>  [
                            "super-user",
                            "admin",
                            "trainer",
                            "reports",
                            'only-reports'
                        ]
                    ],
                    [
                        'name' =>  "Evaluaciones",
                        'icon' =>  "fas fa-file-alt",
                        'code' => 'evaluation-report',
                        'extra_attributes'=>[
                            'path' =>  "/resumen_evaluaciones",
                            'subpaths' =>  ["resumen_evaluaciones"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'selected' =>  false,
                        'isBeta' =>  true,
                        'permission' =>  "resumen_evaluaciones",
                        'roles' =>  [
                            "super-user",
                        ]
                    ]
                ]
            ],
            [
                'name' =>  "HERRAMIENTAS",
                'icon' =>  "fas fa-tools",
                'active' =>  false,
                'functionality' =>  ['default'],
                'children' =>  [
                    [
                        'name' =>  "Notificaciones push",
                        'icon' =>  "fas fa-envelope-open-text",
                        'code' => 'push-notification',
                        'extra_attributes'=>[
                            'path' =>  "/notificaciones_push",
                            'subpaths' =>  ["notificaciones_push"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'create',
                                'title' => 'Crear',
                            ],
                        ],
                        'selected' =>  false,
                        'permission' =>  "notificaciones",
                        'roles' =>  ["super-user", "admin"]
                    ],
                    [
                        'name' =>  "Intentos masivos",
                        'code' => 'attemps-massive',
                        'icon' =>  "fas fa-redo-alt",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/intentos-masivos",
                            'subpaths' =>  ["intentos-masivos"],
                        ],
                        'selected' =>  false,
                        'permission' =>  "reinicio_usuarios",
                        'roles' =>  ["super-user", "admin"]
                    ],
                    [
                        'name' =>  "Procesos masivos",
                        'code' => 'process-massive',
                        'icon' =>  "fas fa-share-square",
                        'extra_attributes'=>[
                            'path' =>  "/procesos-masivos",
                            'subpaths' =>  ["procesos-masivos"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'selected' =>  false,
                        'permission' =>  "proceso_masivo",
                        'roles' =>  ["super-user", "admin"]
                    ],
                    [
                        'name' =>  "Subida de notas",
                        'code' => 'upload-grades-massive',
                        'icon' =>  "fas fa-share-square",
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'extra_attributes'=>[
                            'path' =>  "/importar-notas",
                            'subpaths' =>  ["importar-notas"],
                        ],
                        'selected' =>  false,
                        'permission' =>  "proceso_masivo_notas",
                        'roles' =>  ["super-user", "admin"]
                    ],
                    [
                        'code' => 'documentation-api',
                        'name' =>  "Documentación API",
                        'icon' =>  "fas fa-file",
                        'extra_attributes'=>[
                            'path' =>  "/documentation-api",
                            'subpaths' =>  ["documentation-api"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'selected' =>  false,
                        'permission' =>  "documentation_api",
                        'roles' =>  ["super-user"]
                    ]
                ]
            ],
            [
                'name' =>  "ATENCIÓN AL COLABORADOR",
                'icon' =>  "fas fa-headset",
                'active' =>  false,
                'functionality' =>  ['default'],
                'children' =>  [
                    [
                        'code' => 'frequent-questions',
                        'name' =>  "Preguntas frecuentes",
                        'icon' =>  "far fa-question-circle",
                        'extra_attributes'=>[
                            'path' =>  "/preguntas-frecuentes",
                            'subpaths' =>  ["preguntas-frecuentes"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'selected' =>  false,
                        'roles' =>  ["super-user", "config"]
                    ],
                    [
                        'code' => 'help-form',
                        'name' =>  "Formulario de Ayuda",
                        'icon' =>  "far fa-clipboard",
                        'extra_attributes'=>[
                            'path' =>  "/soporte/formulario-ayuda",
                            'subpaths' =>  ["formulario-ayuda"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'selected' =>  false,
                        'roles' =>  ["super-user"]
                    ],
                    [
                        'code' => 'support',
                        'name' => "Soporte",
                        'icon' => "fas fa-headset",
                        'extra_attributes'=>[
                            'path' => "/soporte",
                            'subpaths' => ["soporte"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ]
                        ],
                        'selected' => false,
                        'roles' =>  ["super-user", "admin"]
                    ],
                ]
            ],
            [
                'name' =>  "CONFIGURACIÓN",
                'icon' =>  "fas fa-wrench",
                'active' =>  false,
                'children' =>  [
                    [
                        'code'=>'configuration-environment',
                        'name' =>  "Ambiente",
                        'icon' =>  "fas fa-cog",
                        'extra_attributes'=>[
                            'path' =>  "/ambiente",
                            'subpaths' =>  ["ambiente"],
                        ],
                        'abilities' =>[
                            [
                                'name' => 'show',
                                'title' => 'Mostrar submenú',
                            ],
                            [
                                'name' => 'edit',
                                'title' => 'Editar',
                            ],
                        ],
                        'selected' =>  false,
                        'roles' =>  ["super-user"]
                    ],
                ]
            ]
        ];
        
        // Bouncer::ability([
        //     'title' => 'Listar',
        //     'name' => 'list'
        // ])->save();
        // Bouncer::ability([
        //     'title' => 'Crear',
        //     'name' => 'create'
        // ])->save();
        
        // Bouncer::ability([
        //     'title' => 'Editar',
        //     'name' => 'edit'
        // ])->save();

        
        // Bouncer::ability([
        //     'title' => 'Logs',
        //     'name' => 'log'
        // ])->save();
        $roles_to_sync = [];
        foreach ($menus as $index_menu => $menu) {
            $new_menu = Taxonomy::where('group','gestor')->where('type','menu')->where('name',$menu['name'])->first();
            if(!$new_menu){
                $new_menu = new Taxonomy();
                $new_menu->group = 'gestor';
                $new_menu->type = 'menu';
                $new_menu->position = $index_menu+1;
                $new_menu->name = $menu['name'];
                $new_menu->icon = $menu['icon'];
                $new_menu->extra_attributes = [
                    'is_beta'=> $menu['is_beta'] ?? false,
                    'show_upgrade'=> $menu['show_upgrade'] ?? false,
                ];
                $new_menu->save();
            }
            
            foreach ($menu['children'] as $index_submenu => $children) {
                $submenu = Taxonomy::where('group','gestor')->where('type','submenu')->where('name',$children['name'])->first();
                if(!$submenu){
                    $submenu = new Taxonomy();
                    $submenu->group = 'gestor';
                    $submenu->type = 'submenu';
                    $submenu->parent_id = $new_menu->id;
                    $submenu->position = $index_submenu+1;
                    $submenu->code = $children['code'];
                    $submenu->name = $children['name'];
                    $submenu->icon = $children['icon'];
                    $submenu->extra_attributes = $children['extra_attributes'];
                    $submenu->save();
                }
                $abilities_id = [];
                foreach ($children['abilities'] as $children_ability) {
                    $ability = Ability::where('title',$children_ability['title'])->where('name',$children_ability['name'])->where('entity_id',$submenu->id)->first();
                    if(!$ability){
                        $ability = new Ability();
                        $ability->title = $children_ability['title'];
                        $ability->name = $children_ability['name'];
                        $ability->entity_id = $submenu->id;
                        $ability->save();
                        // Bouncer::ability([
                        //     'title' => $ability['title'],
                        //     'name' =>  $ability['name'],
                        //     'entity_id' => $submenu->id
                        // ])->save();
                        
                    }
                    $abilities_id[] = $ability->id;
                }
                foreach ($children['roles'] as  $role_name) {
                    if(isset($roles_to_sync[$role_name])){
                        $roles_to_sync[$role_name] = array_merge($roles_to_sync[$role_name],$abilities_id);
                    }else{
                        $roles_to_sync[$role_name] = $abilities_id;
                    }
                    // $role = Role::where('name',$role_name)->first();
                    // info([
                    //     'role'=>
                    // ]);
                }
            }
        }
        foreach ($roles_to_sync as $role_name => $abilities_id) {
            $role = Role::where('name',$role_name)->first();
            $role->abilities()->sync($abilities_id);
        }
        // // Bouncer::scope()->to($platform_master->id);

        // // Bouncer::allow($role)->to(['list', 'show', 'create'], Taxonomy::class);

        // $roles = [
        //     'admin'=> [
        //         'menus'
        //     ], 
        //     'config', 
        //     'content-manager', 
        //     'trainer',
        //     'reports',
        //     'user',
        //     'only-reports'
        // ];

        // foreach ($roles as $key => $role)
        // {
        // Bouncer::allow('config')->to($default_actions, Course::class);
        // Bouncer::allow('config')->to($default_actions, Meeting::class);

        // Bouncer::allow('admin-users')->to($default_actions, Workspace::class);
        // Bouncer::allow('admin-users')->to($default_actions, User::class);
        // Bouncer::allow('admin-users')->to($default_actions, Role::class);
        // Bouncer::allow('admin-users')->to($default_actions, Permission::class);

        // Bouncer::allow('admin-environment')->to(['update', 'audit'], Ambiente::class);
        // // }
        // Bouncer::allow('admin-audits')->to($default_actions, Audit::class);

        // Bouncer::allow('admin-support')->to($default_actions, Ayuda::class);
        // Bouncer::allow('admin-support')->to($default_actions, AyudaApp::class);
        // Bouncer::allow('admin-support')->to($default_actions, Ticket::class);
        // Bouncer::allow('admin-support')->to($default_actions, Post::class);

        // Bouncer::allow('admin-checklists')->to($default_actions, CheckList::class);
        
        // Bouncer::allow('admin-supervisor')->to($default_actions, Supervisor::class);

        // Bouncer::allow('admin-courses')->to($default_actions, School::class);
        // Bouncer::allow('admin-courses')->to($default_actions, Course::class);
        // Bouncer::allow('admin-courses')->to($default_actions, Topic::class);
        // Bouncer::allow('admin-courses')->to($default_actions, Question::class);
        // Bouncer::allow('admin-courses')->to($default_actions, Segment::class);
        // Bouncer::allow('admin-courses')->to($default_actions, Certificate::class);
        // Bouncer::allow('admin-courses')->to($default_actions, Media::class);
        // Bouncer::allow('admin-courses')->to($default_actions, Poll::class);
        // Bouncer::allow('admin-courses')->to($default_actions, PollQuestion::class);
        // Bouncer::allow('admin-courses')->to($default_actions, PollQuestionAnswer::class);

        // Bouncer::allow('admin-criteria')->to($default_actions, Criterion::class);
        // Bouncer::allow('admin-criteria')->to($default_actions, CriterionValue::class);

        // Bouncer::allow('admin-glossary')->to($default_actions, Glossary::class);
        // Bouncer::allow('admin-documents')->to($default_actions, Vademecum::class);

        // Bouncer::allow('admin-notifications')->to($default_actions, Notification::class);

        // Bouncer::allow('admin-video-library')->to($default_actions, Videoteca::class);

        // Bouncer::allow('admin-announcements')->to($default_actions, Announcement::class);

        // // Editor

        // $roles = ['visitor'];

        // $default_actions = ['index', 'show'];

        // foreach ($roles as $key => $role)
        // {
        //     Bouncer::allow($role)->to(['index', 'show',], Audit::class);
        // }

        // Bouncer::allow('technical-support')->to(['index', 'show',], Error::class);



        // // Administrativo

        // Bouncer::allow('administrative')->to($default_actions, Client::class);

        // // Marketing

        // Bouncer::allow('marketing')->to($default_actions, Client::class);
        // Bouncer::allow('marketing')->to($default_actions, Post::class);
        // Bouncer::allow('marketing')->to($default_actions, NotificationType::class);



        // Bouncer::allow('admin')->everything();
        // Bouncer::forbid('admin')->toManage(User::class);

        // Bouncer::allow('support')->to('create', Plan::class);
        // Bouncer::allow('support')->toOwn(Plan::class);



        // Bouncer::ability([
        //     'title' => 'Crear usuarios',
        //     'name' => 'user-create'
        // ])->save();
        // Bouncer::allow('developer')->to('user-create');

        // $platform_client = Taxonomy::getFirstData('system', 'platform', 'client');

        // Bouncer::scope()->to($platform_client->id);

        // Bouncer::allow('owner')->everything();

        // Bouncer::allow('admin')->to(['index', 'show',], Post::class);
        // Bouncer::allow('admin-content')->to(['index', 'show',], Post::class);
        // Bouncer::allow('admin-course')->to(['index', 'show',], Post::class);
        // Bouncer::allow('admin-user')->to(['index', 'show',], Post::class);
        // Bouncer::allow('supervisor')->to(['index', 'show',], Post::class);


        // $platform_user = Taxonomy::getFirstData('system', 'platform', 'user');

        // Bouncer::scope()->to($platform_user->id);

        // Bouncer::allow('user')->to(['index', 'show',], Feature::class);


        // // Bouncer::scope()->to(PE)->onlyRelations()->dontScopeRoleAbilities();

        // $user = User::find(1);
        // $user->assign('superadmin');

        // $user = User::find(2);
        // $user->assign('superadmin');
    }
}
