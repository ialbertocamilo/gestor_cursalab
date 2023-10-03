<?php

return [
    'recommendations' => [
        "Para realizar cada tarea asignada descarga todos los recursos adjuntos.",
        "Una vez enviada tu tarea, esta pasará por un proceso de revisión y no habrá opción de edición.",
        "Te sugerimos estar conectado a una red WiFi para la carga y descarga de archivos.",
    ],
    'constraints'=>[
        'admin'=>[
            'max_quantity_upload_files' =>3,
            'max_size_upload_files'=>25,//en MB
        ],
        'user'=>[
            'max_size_upload_files' =>30,
            'max_quantity_upload_files' =>3
        ]
    ]
    // 'estados'=>[
    //     ['code'=>'aprobado' ,'label'=>'Aprobado','group'=>'terminado'],
    //     ['code'=>'desaprobado' ,'label'=>'Desaprobado','group'=>'terminado'],
    //     ['code'=>'pendiente' ,'label'=>'Pendiente','group'=>'pendiente'],
    //     ['code'=>'observado' ,'label'=>'Observado','group'=>'pendiente'],
    //     ['code'=>'revision' ,'label'=>'En revisión','group'=>'pendiente']
    // ]
];
