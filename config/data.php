<?php

// Test subida

return [
    'glosario' => [

        'selects' => [
            [	'id' => 'categoria_id', 'key' => 'categoria', 'relation' => 'categoria', 'name' => 'Categoría', 'show_values' => 1,
                'placeholder' => 'Seleccione un categoría', 'multiple' => false, 'api' => true],

            [	'id' => 'jerarquia_id', 'key' => 'jerarquia', 'relation' => 'jerarquia', 'name' => 'Jerarquía', 'show_values' => 1,
                'placeholder' => 'Seleccione una jerarquía', 'multiple' => false, 'api' => true],

            [	'id' => 'laboratorio_id', 'key' => 'laboratorio', 'relation' => 'laboratorio', 'name' => 'Laboratorio', 'show_values' => 1,
                'placeholder' => 'Seleccione un laboratorio', 'multiple' => false, 'api' => true],

            [	'id' => 'principios_activos', 'key' => 'principio_activo', 'relation' => 'principios_activos', 'name' => 'Principios Activos', 'show_values' => 4,
                'placeholder' => null, 'multiple' => true, 'api' => true],

            [	'id' => 'advertencias_id', 'key' => 'advertencias', 'relation' => 'advertencias', 'name' => 'Advertencias', 'show_values' => 1,
                'placeholder' => 'Seleccione una advertencia', 'multiple' => false, 'api' => false],

            [	'id' => 'condicion_de_venta_id', 'key' => 'condicion_de_venta', 'relation' => 'condicion_de_venta', 'name' => 'Condición de Venta', 'show_values' => 1,
                'placeholder' => 'Seleccione una condición de venta', 'multiple' => false, 'api' => false],

            [	'id' => 'via_de_administracion_id', 'key' => 'via_de_administracion', 'relation' => 'via_de_administracion', 'name' => 'Vía de Administración', 'show_values' => 1,
                'placeholder' => 'Seleccione un vía de administración', 'multiple' => false, 'api' => false],

            [	'id' => 'grupo_farmacologico_id', 'key' => 'grupo_farmacologico', 'relation' => 'grupo_farmacologico', 'name' => 'Grupo Farmacológico', 'show_values' => 1,
                'placeholder' => 'Seleccione un grupo farmacológico', 'multiple' => false, 'api' => true],

            [	'id' => 'dosis_adulto_id', 'key' => 'dosis_adulto', 'relation' => 'dosis_adulto', 'name' => 'Dosis Adulto', 'show_values' => 1,
                'placeholder' => 'Seleccione una dosis adulto', 'multiple' => false, 'api' => false],

            [	'id' => 'dosis_nino_id', 'key' => 'dosis_nino', 'relation' => 'dosis_nino', 'name' => 'Dosis Niño', 'show_values' => 1,
                'placeholder' => 'Seleccione una dosis niño', 'multiple' => false, 'api' => false],

            [	'id' => 'recomendacion_de_administracion_id', 'key' => 'recomendacion_de_administracion', 'relation' => 'recomendacion_de_administracion', 'name' => 'Recomendación de Administración', 'show_values' => 1,
                'placeholder' => 'Seleccione una recomendación de administración', 'multiple' => false, 'api' => false],


            [	'id' => 'contraindicaciones', 'key' => 'contraindicacion', 'relation' => 'contraindicaciones', 'name' => 'Contraindicación', 'show_values' => 5,
                'placeholder' => null, 'multiple' => true, 'api' => false],

            [	'id' => 'interacciones', 'key' => 'interaccion', 'relation' => 'interacciones', 'name' => 'Interacciones Más Frecuentes', 'show_values' => 4,
                'placeholder' => null, 'multiple' => true, 'api' => false],

            [	'id' => 'reacciones', 'key' => 'reaccion', 'relation' => 'reacciones', 'name' => 'Reacciones Más Frecuentes', 'show_values' => 4,
                'placeholder' => null, 'multiple' => true, 'api' => false],
        ]
    ],

    'destinos' => [
        // ''=> '- NINGUNO -',
        // 'escuelas'=> 'Escuelas',
        // 'malla'=> 'Malla Curricular',
        // 'progreso'=> 'Progreso',
        // 'cursos_extra'=> 'Cursos Extracurriculares',
        // 'diplomas'=> 'Diplomas',
        // 'preguntas_frec'=> 'Preguntas Frecuentes',
        // 'glosario' => 'Glosario',
        // 'ranking' => 'Ranking',
        // 'reuniones' => 'Reuniones',
        // 'vademecum' => 'Vademécum',
        // 'ayuda'=> 'Ayuda'
        // [ 'id' => 'malla', 'nombre' => 'Malla Curricular'],
        // [ 'id' => 'escuelas', 'nombre' => 'Escuelas'],

        [ 'id' => 'cursos', 'nombre' => 'Cursos'],
        [ 'id' => 'progreso', 'nombre' => 'Progreso'],
        [ 'id' => 'cursos_extra', 'nombre' => 'Cursos Extracurriculares'],
        [ 'id' => 'modulo-reconocimiento', 'nombre' => 'Reconocimiento'],
        [ 'id' => 'faq', 'nombre' => 'Preguntas Frecuentes'],
        [ 'id' => 'reuniones', 'nombre'  => 'Sesiones live'],
        [ 'id' => 'diplomas', 'nombre' => 'Diplomas'],
        [ 'id' => 'ranking', 'nombre'  => 'Ranking'],
        [ 'id' => 'glosario', 'nombre'  => 'Glosario'],
        [ 'id' => 'vademecum', 'nombre'  => 'Vademécum'],
        [ 'id' => 'ayuda', 'nombre' => 'Ayuda'],
    ],

    'polls' => [
        // todo: remove this since is now stored on Taxonomies table
        'secciones' => [
            ['id' => 'xcurso', 'nombre' => 'Cursos'],
            ['id' =>  'libre', 'nombre' => 'Libre'],
        ],
        'tipos' => [
            ['id' => 1, 'nombre'  => 'Anónima'],
            ['id' => 0, 'nombre' => 'No anónima'],
        ],
    ],

    'tipo-criterios' => [
        ['id' => 'Texto', 'nombre' => 'Texto' ],
        ['id' => 'Numérico', 'nombre' => 'Numérico' ],
        ['id' => 'Fecha', 'nombre' => 'Fecha' ],
    ],

    'soporte-estados' => [
        ['id' => 'pendiente', 'nombre' => 'Pendiente' ],
        ['id' => 'revisando', 'nombre' => 'Revisando' ],
        ['id' => 'solucionado', 'nombre' => 'Solucionado' ],
    ],

    // todo: remove this since is now stored on Taxonomies table
    'tipo-preguntas' => [
        ['id' => 'texto', 'nombre' => 'Respuesta en texto'],
        ['id' => 'simple', 'nombre' => 'Opción única'],
        ['id' => 'multiple', 'nombre' => 'Opción múltiple'],
        ['id' => 'califica', 'nombre' => 'Calificar del 1 al 5'],
    ],

    'media-types' => [
        ['nombre' => 'Youtube', 'id' => 'youtube'],
        ['nombre' => 'Vimeo', 'id' => 'vimeo'],
        ['nombre' => 'Audio', 'id' => 'audio'],
        ['nombre' => 'Video', 'id' => 'video'],
        ['nombre' => 'PDF', 'id' => 'pdf'],
        ['nombre' => 'SCORM', 'id' => 'scorm'],
        ['nombre' => 'Imagen', 'id' => 'image'],
    ],

    'cuentas-zoom-tipos' => [
        ['id' => 'PRO', 'nombre' => 'PRO'],
        ['id' => 'BUSINESS', 'nombre' => 'BUSINESS'],
    ],

    'filters' => [
        'certificates' => ['all' => 'Todos', 'accepted' => 'Aceptados', 'pending' => 'Pendientes'],
    ],

    'months' => [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Setiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ],
];
