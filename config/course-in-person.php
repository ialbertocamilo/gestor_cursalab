<?php

return [
    'host' => [
        [
            'title' => 'Tomar asistencia',
            'code' => 'take-assistance',
            'description' => 'Toma nota de los colaboradores presentes en este evento.',
            'show' => true,
            'status' => ['name'=>'Pendiente','code'=>'pending']
        ],
        // [
        //     'title' => 'Presentación',
        //     'code' => 'presentation',
        //     'description' => 'Aquí podrás observar tus archivos para presentar a tus colaboradores.',
        //     'show' => true
        // ],
        [
            'title' => 'Evaluación',
            'code' => 'evaluation',
            'description' => 'Evalúa a tus colaboradores sobre los temas que se han presentado.',
            'show' => true,
            'status' => ['name'=>'Pendiente','code'=>'pending']
        ],
        [
            'title' => 'Encuesta',
            'code' => 'poll',
            'description' => 'Inicia el feedback de parte de tus colaboradores.',
            'show' => true,
            'status' => ['name'=>'Pendiente','code'=>'pending']
        ]
    ],
    'user' => [
        [
            'title' => 'Asistencia',
            'code' => 'take-assistance',
            'description' => 'Visualiza el estado de su asistencia.',
            'show' => true,
            'status' => ['name'=>'Asistió','code'=>'attended']
        ],
        // [
        //     'title' => 'Material del curso',
        //     'code' => 'multimedias',
        //     'description' => 'Descarga archivos complementarios de tu evento.',
        //     'show' => true
        // ],
        [
            'title' => 'Evaluación',
            'code' => 'evaluation',
            'description' => 'Aquí podrás demostrar lo aprendido en este evento.',
            'show' => true,
            'status' => ['name'=>'Pendiente','code'=>'pending']
        ],
        [
            'title' => 'Encuesta',
            'code' => 'poll',
            'description' => 'Queremos saber tu opinión sobre este evento.',
            'show' => true,
            'status' => ['name'=>'Pendiente','code'=>'pending']
        ],
        [
            'title' => 'Certificado',
            'code' => 'certificate',
            'description' => 'Descárgalo y compártelo en tus redes.',
            'show' => true
        ]
    ],
    'filters'=>[
        [
            'title'=>'Todos',
            'code'=>'all',
        ],
        [
            'title'=>'Sesiones live',
            'code'=>'live',
        ],
        [
            'title'=>'Cursos presenciales',
            'code'=>'in-person',
        ],
        [
            'title'=>'Cursos virtuales',
            'code'=>'online',
        ]
    ]
];
