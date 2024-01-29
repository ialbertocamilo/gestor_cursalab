<?php

return [
    'host'=>collect([
        [
            'title'=>'Tomar asitencia',
            'code'=>'take-assistance',
            'description'=> 'Toma nota de los colaboradores presentes en este evento.',
            'show'=>true
        ],
        [
            'title'=>'Presentación',
            'code'=>'presentation',
            'description'=> 'Aquí podras observar tus archivos para presentar a tus colaboradores.',
            'show'=>true
        ],
        [
            'title'=>'Evaluation',
            'code'=>'evaluation',
            'description'=> 'Evalúa a tus colaboradores sobre los temas que se ha presentado.',
            'show'=>true
        ],
        [
            'title'=>'Encuesta',
            'code'=>'poll',
            'description'=> 'Inicia el feedback de parte de tus colaboradores.',
            'show'=>true
        ],
    ]),
    'user' =>[
        [
            'title'=>'Material del curso',
            'code'=>'multimedias',
            'description'=> 'Descarga archivos complementarios de tu evento.',
            'show'=>true
        ],
        [
            'title'=>'Evaluación',
            'code'=>'evaluation',
            'description'=> 'Aquí podras demostrar lo aprendido en este evento.',
            'show'=>true
        ],
        [
            'title'=>'Encuesta',
            'code'=>'poll',
            'description'=> 'Queremos saber tu opinión sobre este evento.',
            'show'=>true
        ],
        [
            'title'=>'Certificado',
            'code'=>'certificate',
            'description'=> 'Descargalo y compartelo en tus redes.',
            'show'=>true
        ],
    ]
];
