<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErroresMasivo extends Model
{
    public function get_header($tipo)
    {
        return $this->{$tipo};
    }

    private $evaluaciones = [
        ['text'=>"Módulo", 'align'=> "center", 'value'=> "err_data.modulo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Módulo vacio'] ,'atributo'=>'config_id','atr_change'=>['atr_table'=>'etapa','val'=>'modulo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'ab_config',
                    'item_text'=>'etapa',
                    'deps'=>[],
                ],
                'table_dep'=>[
                    [
                        'name'=>'cursos',
                        'atributo'=>'curso_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Curso no encontrado',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'curso','data_extra'=>false]
                        ],
                    ],
                    [
                        'name'=>'posteos',
                        'atributo'=>'post_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Tema no encontrado',
                        'data'=>[
                            ['value'=>'curso_id','bind'=>'curso_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'tema','data_extra'=>false]
                        ],
                    ],
                ]
            ],
        ],
        ['text'=>"Curso", 'align' => "center" ,'value'=> "err_data.curso", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['required'=>'Curso vacio'],'atributo'=>'curso_id','atr_change'=>['atr_table'=>'nombre','val'=>'curso'],
            'table_info'=>[
                'table'=>[
                    'name'=>'cursos',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                    ]
                ],
                'table_dep'=>[
                    [
                        'name'=>'posteos',
                        'atributo'=>'post_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Tema no encontrado',
                        'data'=>[
                            ['value'=>'curso_id','bind'=>'curso_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'tema','data_extra'=>false]
                        ],
                    ],
                ]
            ],
        ],
        ['text'=>"Tema", 'align' => "center" ,'value'=> "err_data.tema", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['required'=>'Tema vacio'],'atributo'=>'post_id','atr_change'=>['atr_table'=>'nombre','val'=>'tema'],
            'table_info'=>[
                'table'=>[
                    'name'=>'posteos',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'curso_id','bind'=>'curso'],
                        ['value'=>'nombre','bind'=>'tema'],
                    ]
                ],
                'table_dep'=>[]
            ],
        ],
        ['text'=>"Pregunta", 'align'=> "center", 'value'=> "err_data.pregunta", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Nombre de la pregunta vacio'],'atributo'=>'pregunta','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Respuesta", 'align'=> "center", 'value'=> "err_data.respuesta", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Respuesta no encontrada'],'atributo'=>'rpta_ok','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Eliminar", 'align'=> "center", 'value'=> "actions", 'tipo'=>'none','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>'' ,'atributo'=>'no','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ] 
    ];
    private $temas=[
        ['text'=>"Módulo", 'align'=> "center", 'value'=> "err_data.modulo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Módulo vacio'] ,'atributo'=>'config_id','atr_change'=>['atr_table'=>'etapa','val'=>'modulo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'ab_config',
                    'item_text'=>'etapa',
                    'deps'=>[],
                ],
                'table_dep'=>[
                    [
                        'name'=>'categorias',
                        'atributo'=>'categoria_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Escuela no encontrada',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'escuela','data_extra'=>false]
                        ],
                    ],
                    [
                        'name'=>'cursos',
                        'atributo'=>'curso_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Curso no encontrado',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'categoria_id','bind'=>'categoria_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'curso','data_extra'=>false]
                        ],
                    ],
                ]
            ],
        ],
        ['text'=>"Escuela", 'align' => "center" ,'value'=> "err_data.escuela", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['required'=>'Escuela vacio'],'atributo'=>'categoria_id','atr_change'=>['atr_table'=>'nombre','val'=>'escuela'],
            'table_info'=>[
                'table'=>[
                    'name'=>'categorias',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                    ]
                ],
                'table_dep'=>[
                    [
                        'name'=>'cursos',
                        'atributo'=>'curso_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Curso no encontrado',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'categoria_id','bind'=>'categoria_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'curso','data_extra'=>false]
                        ],
                    ],
                ]
            ]
        ],
        ['text'=>"Curso", 'align' => "center" ,'value'=> "err_data.curso", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['required'=>'Curso vacio'],'atributo'=>'curso_id','atr_change'=>['atr_table'=>'nombre','val'=>'curso'],
            'table_info'=>[
                'table'=>[
                    'name'=>'cursos',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                        ['value'=>'categoria_id','bind'=>'categoria_id'],
                    ]
                ],
                'table_dep'=>[
                    [
                        'not_verify_if'=>[['value'=>'','attr'=>'requisito'],['value'=>'No tiene','attr'=>'requisito']], // NO VERIFICA LA DEPENDENCIA si el atributo ES IGUAL A VACIO o 'No tiene'
                        'name'=>'posteos',
                        'atributo'=>'requisito_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Requisito no encontrado',
                        'data'=>[
                            ['value'=>'curso_id','bind'=>'curso_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'requisito','data_extra'=>false]
                        ],
                    ],
                ],
            ],
        ],
        ['text'=>"Nombre", 'align'=> "center", 'value'=> "err_data.nombre", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Nombre del curso vacio'],'atributo'=>'nombre','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Requisito", 'align'=> "center", 'value'=> "err_data.requisito", 'tipo'=>'select','data_extra'=>true,'could_be_empty' => true,'change_select'=> false,'rules'=>[],'extra_rules'=>['rule'=>'required_if_dif','atr'=>'requisito','val'=>'No tiene'],'message_rule'=>[''],'atributo'=>'requisito_id','atr_change'=>['atr_table'=>'nombre','val'=>'requisito'],
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Estado",'align'=> "center", 'value'=> "err_data.estado", 'tipo'=>'select','data_extra'=>'global','could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Estado vacio'],'atributo'=>'estado','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Imagen",'align'=> "center", 'value'=> "err_data.imagen", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => true,'change_select'=> false,'rules'=>[],'extra_rules'=>false,'message_rule'=>[],'atributo'=>'imagen','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Eliminar", 'align'=> "center", 'value'=> "actions", 'tipo'=>'none','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>'' ,'atributo'=>'','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ] 
    ];
    private $cursos = [
        ['text'=>"Módulo", 'align'=> "center", 'value'=> "err_data.modulo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Módulo vacio'] ,'atributo'=>'config_id','atr_change'=>['atr_table'=>'etapa','val'=>'modulo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'ab_config',
                    'item_text'=>'etapa',
                    'deps'=>[],
                ],
                'table_dep'=>[
                    [
                        'name'=>'categorias',
                        'atributo'=>'categoria_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Escuela no encontrada',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'escuela','data_extra'=>false]
                        ],
                    ],
                ]
            ],
        ],
        ['text'=>"Escuela", 'align' => "start" ,'value'=> "err_data.escuela", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['required'=>'Escuela vacio'],'atributo'=>'categoria_id','atr_change'=>['atr_table'=>'nombre','val'=>'escuela'],
            'table_info'=>[
                'table'=>[
                    'name'=>'categorias',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                    ]
                ],
                'table_dep'=>[]
            ]
        ],
        ['text'=>"Nombre", 'align'=> "center", 'value'=> "err_data.nombre", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Nombre del curso vacio'],'atributo'=>'nombre','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Estado",'align'=> "center", 'value'=> "err_data.estado", 'tipo'=>'select','data_extra'=>'global','could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Estado vacio'],'atributo'=>'estado','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Imagen",'align'=> "center", 'value'=> "err_data.imagen", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => true,'change_select'=> false,'rules'=>[],'extra_rules'=>false,'message_rule'=>['Imagen vacio'],'atributo'=>'imagen','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Eliminar", 'align'=> "center", 'value'=> "actions", 'tipo'=>'none','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>'' ,'atributo'=>'no','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ] 
    ];
    private $cesados= [
        ['text'=>"DNI", 'align' => "start" ,'value'=> "err_data.dni", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=>  false,'rules'=>['required|min:8|numeric'],'extra_rules'=>['accion'=>'unique:usuarios'],'message_rule'=>['required'=>'Dni vacio','digits'=>'Solo 8 digitos','unique'=>'Dni duplicado','numeric'=>'Solo se aceptan números'],'atributo'=>'dni','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Eliminar", 'align'=> "center", 'value'=> "actions", 'tipo'=>'none','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>'' ,'atributo'=>'no','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ] 
    ];
    private $activos= [
        ['text'=>"DNI", 'align' => "start" ,'value'=> "err_data.dni", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=>  false,'rules'=>['required|min:8|numeric'],'extra_rules'=>['accion'=>'unique:usuarios'],'message_rule'=>['required'=>'Dni vacio','digits'=>'Solo 8 digitos','unique'=>'Dni duplicado','numeric'=>'Solo se aceptan números'],'atributo'=>'dni','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Eliminar", 'align'=> "center", 'value'=> "actions", 'tipo'=>'none','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>'' ,'atributo'=>'no','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ],
    ];
    private $usuarios= [
        ['text'=>"Modulo", 'align'=> "center", 'value'=> "err_data.modulo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Módulo vacio'] ,'atributo'=>'config_id','atr_change'=>['atr_table'=>'etapa','val'=>'modulo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'ab_config',
                    'item_text'=>'etapa',
                    'deps'=>[],
                ],
                'table_dep'=>[
                    [
                        'name'=>'carreras',
                        'atributo'=>'carrera_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Carrera no encontrada',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'carrera','data_extra'=>false]
                        ],
                    ],
                    [
                        'name'=>'ciclos',
                        'atributo'=>'ciclo_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Ciclo no encontrada',
                        'data'=>[
                            ['value'=>'carrera_id','bind'=>'carrera_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'ciclo','data_extra'=>false]
                        ],
                    ],
                    [
                        'name'=>'criterios',
                        'atributo'=>'grupo_id',
                        'item_text'=>'valor',
                        'mensajes'=>'Grupo no encontrada',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'valor','bind'=>'grupo','data_extra'=>false],
                        ],
                    ],
                    [
                        'name'=>'boticas',
                        'atributo'=>'botica_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Botica no encontrada',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'criterio_id','bind'=>'grupo_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'botica','data_extra'=>false]
                        ],
                    ]
                ]
            ],
        ],
        ['text'=>"DNI", 'align' => "center" ,'value'=> "err_data.dni", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=>  false,'rules'=>['required|min:8|numeric'],'extra_rules'=>['accion'=>'unique:usuarios'],'message_rule'=>['required'=>'Dni vacio','digits'=>'Solo 8 digitos','unique'=>'Dni duplicado','numeric'=>'Solo se aceptan números'],'atributo'=>'dni','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Nombres y Apellidos", 'align'=> "center", 'value'=> "err_data.nombre", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Nombre vacio'],'atributo'=>'nombre','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Grupo", 'align'=> "center", 'value'=> "err_data.grupo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Grupo vacio'] ,'atributo'=>'grupo_id','atr_change'=>['atr_table'=>'valor','val'=>'grupo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'criterios',
                    'item_text'=>'valor',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                    ],
                ],
                'table_dep'=>[
                    [
                        'name'=>'boticas',
                        'atributo'=>'botica_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Botica no encontrado',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'criterio_id','bind'=>'grupo_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'botica','data_extra'=>false]
                        ],
                    ],
                ],
            ]
        ],
        ['text'=>"Botica", 'align'=> "center", 'value'=> "err_data.botica", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Botica vacio'] ,'atributo'=>'botica_id','atr_change'=>['atr_table'=>'nombre','val'=>'botica'],
            'table_info'=>[
                'table'=>[
                    'name'=>'boticas',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                        ['value'=>'criterio_id','bind'=>'grupo_id'],
                    ],
                ],'table_dep'=>'[]'
            ]
        ],
        ['text'=>"Cargo", 'align'=> "center", 'value'=> "err_data.cargo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Cargo vacio'] ,'atributo'=>'cargo','atr_change'=>['atr_table'=>'nombre','val'=>'cargo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'cargos',
                    'item_text'=>'nombre',
                    'deps'=>[]
                ],'table_dep'=>'[]'
            ]
        ],
        ['text'=>"Carrera", 'align'=> "center", 'value'=> "err_data.carrera", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Carrera vacio'] ,'atributo'=>'carrera_id','atr_change'=>['atr_table'=>'nombre','val'=>'carrera'],
            'table_info'=>[
                'table'=>[
                    'name'=>'carreras',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                    ],
                ],
                'table_dep'=>[
                    [
                        'name'=>'ciclos',
                        'atributo'=>'ciclo_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Ciclo no encontrado',
                        'data'=>[
                            ['value'=>'carrera_id','bind'=>'carrera_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'ciclo','data_extra'=>false]
                        ],
                    ],
                ]
            ]
        ],
        ['text'=>"Ciclo", 'align'=> "center", 'value'=> "err_data.ciclo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Ciclo vacio'] ,'atributo'=>'ciclo_id','atr_change'=>['atr_table'=>'nombre','val'=>'ciclo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'ciclos',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'carrera_id','bind'=>'carrera_id'],
                    ],
                ],'table_dep'=>'[]'
            ]
        ],
        ['text'=>"Género", 'align'=> "center", 'value'=> "err_data.sexo", 'tipo'=>'select','data_extra'=>'global','could_be_empty' => false,'change_select'=> false,'rules'=>['required|max:1'],'extra_rules'=>false,'message_rule'=>['Género vacio','Solo F o M'],'atributo'=>'sexo','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ],
        ['text'=>"Acción",'align'=> "center", 'value'=> "err_data.accion", 'tipo'=>'select','data_extra'=>'global','could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Acción vacio'],'atributo'=>'accion','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Eliminar", 'align'=> "center", 'value'=> "actions", 'tipo'=>'none','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>'' ,'atributo'=>'no','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ] 
    ];

    private $ciclos_carreras = [
        ['text'=>"Módulo", 'align'=> "center", 'value'=> "err_data.modulo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Módulo vacio'] ,'atributo'=>'config_id','atr_change'=>['atr_table'=>'etapa','val'=>'modulo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'ab_config',
                    'item_text'=>'etapa',
                    'deps'=>[],
                ],
                'table_dep'=>[
                    [
                        'name'=>'carreras',
                        'atributo'=>'carrera_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Carrera no encontrada',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'carrera','data_extra'=>false]
                        ],
                    ],
                    [
                        'name'=>'ciclos',
                        'atributo'=>'ciclo_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Ciclo no encontrada',
                        'data'=>[
                            ['value'=>'carrera_id','bind'=>'carrera_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'ciclo','data_extra'=>false]
                        ],
                    ],
                    [
                        'name'=>'criterios',
                        'atributo'=>'grupo_id',
                        'item_text'=>'valor',
                        'mensajes'=>'Grupo no encontrada',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'valor','bind'=>'grupo','data_extra'=>false],
                        ],
                    ],
                    [
                        'name'=>'boticas',
                        'atributo'=>'botica_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Botica no encontrada',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'criterio_id','bind'=>'grupo_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'botica','data_extra'=>false]
                        ],
                    ]
                ]
            ],
        ],
        ['text'=>"DNI", 'align' => "center" ,'value'=> "err_data.dni", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=>  false,'rules'=>['required|min:8|numeric'],'extra_rules'=>['accion'=>'unique:usuarios'],'message_rule'=>['required'=>'Dni vacio','digits'=>'Solo 8 digitos','unique'=>'Dni duplicado','numeric'=>'Solo se aceptan números'],'atributo'=>'dni','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Nombres y Apellidos", 'align'=> "center", 'value'=> "err_data.nombre", 'tipo'=>'input','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Nombre vacio'],'atributo'=>'nombre','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Grupo", 'align'=> "center", 'value'=> "err_data.grupo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Grupo vacio'] ,'atributo'=>'grupo_id','atr_change'=>['atr_table'=>'valor','val'=>'grupo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'criterios',
                    'item_text'=>'valor',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                    ],
                ],
                'table_dep'=>[
                    [
                        'name'=>'boticas',
                        'atributo'=>'botica_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Botica no encontrado',
                        'data'=>[
                            ['value'=>'config_id','bind'=>'config_id','data_extra'=>true],
                            ['value'=>'criterio_id','bind'=>'grupo_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'botica','data_extra'=>false]
                        ],
                    ],
                ],
            ]
        ],
        ['text'=>"Botica", 'align'=> "center", 'value'=> "err_data.botica", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Botica vacio'] ,'atributo'=>'botica_id','atr_change'=>['atr_table'=>'nombre','val'=>'botica'],
            'table_info'=>[
                'table'=>[
                    'name'=>'boticas',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                        ['value'=>'criterio_id','bind'=>'grupo_id'],
                    ],
                ],'table_dep'=>'[]'
            ]
        ],
        ['text'=>"Cargo", 'align'=> "center", 'value'=> "err_data.cargo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Cargo vacio'] ,'atributo'=>'cargo','atr_change'=>['atr_table'=>'nombre','val'=>'cargo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'cargos',
                    'item_text'=>'nombre',
                    'deps'=>[]
                ],'table_dep'=>'[]'
            ]
        ],
        ['text'=>"Carrera", 'align'=> "center", 'value'=> "err_data.carrera", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=> true,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Carrera vacio'] ,'atributo'=>'carrera_id','atr_change'=>['atr_table'=>'nombre','val'=>'carrera'],
            'table_info'=>[
                'table'=>[
                    'name'=>'carreras',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'config_id','bind'=>'config_id'],
                    ],
                ],
                'table_dep'=>[
                    [
                        'name'=>'ciclos',
                        'atributo'=>'ciclo_id',
                        'item_text'=>'nombre',
                        'mensajes'=>'Ciclo no encontrado',
                        'data'=>[
                            ['value'=>'carrera_id','bind'=>'carrera_id','data_extra'=>true],
                            ['value'=>'nombre','bind'=>'ciclo','data_extra'=>false]
                        ],
                    ],
                ]
            ]
        ],
        ['text'=>"Ciclo", 'align'=> "center", 'value'=> "err_data.ciclo", 'tipo'=>'select','data_extra'=>'table','could_be_empty' => false,'change_select'=>  false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Ciclo vacio'] ,'atributo'=>'ciclo_id','atr_change'=>['atr_table'=>'nombre','val'=>'ciclo'],
            'table_info'=>[
                'table'=>[
                    'name'=>'ciclos',
                    'item_text'=>'nombre',
                    'deps'=>[
                        ['value'=>'carrera_id','bind'=>'carrera_id'],
                    ],
                ],'table_dep'=>'[]'
            ]
        ],
        ['text'=>"Género", 'align'=> "center", 'value'=> "err_data.sexo", 'tipo'=>'select','data_extra'=>'global','could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Género vacio','Solo F o M'],'atributo'=>'sexo','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ],
        ['text'=>"Acción_migración",'align'=> "center", 'value'=> "err_data.accion", 'tipo'=>'select','data_extra'=>'global','could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>['Acción vacio'],'atributo'=>'accion','atr_change'=>false,
            'table_info'=>['table'=>'','table_dep'=>'']
        ],
        ['text'=>"Eliminar", 'align'=> "center", 'value'=> "actions", 'tipo'=>'none','data_extra'=>false,'could_be_empty' => false,'change_select'=> false,'rules'=>['required'],'extra_rules'=>false,'message_rule'=>'' ,'atributo'=>'no','atr_change'=>false,
            'table_info'=>[
                'table'=>'','table_dep'=>'[]'
            ]
        ] 
    ];
}