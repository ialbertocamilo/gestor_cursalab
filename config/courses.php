<?php


return [
    'status' => [
        "" => "",
        "aprobado" => "Aprobado",
        "realizado" => "Realizado",
        "revisado" => "Revisado",
        "desaprobado" => "Desaprobado",
        "desarrollo" => "En desarrollo",
        "pendiente" => "Pendiente",
        "enc_pend" => "Encuesta Pendiente",
        "bloqueado" => "Bloqueado",
        "completado" => "Completado",
        "por-iniciar" => "Por iniciar",
        "continuar" => "En desarrollo"
    ],
    'user-courses-query' => [
        'soft' => [
            'segments' => function ($q) {
                $q
                    ->select('id', 'model_id')
                    ->with('values', function ($q) {
                        $q->select('id', 'segment_id', 'criterion_id', 'criterion_value_id')
                            ->with('criterion_value', function ($q) {
                                $q->select('id', 'value_text', 'value_date', 'value_boolean')
                                    ->with('criterion', function ($q) {
                                        $q->select('id', 'name', 'code');
                                    });
                            });
                    });
            },
        ],
        'user-progress' => [
            'segments' => function ($q) {
                $q
                    ->select('id', 'model_id')
                    ->with('values', function ($q) {
                        $q->select('id', 'segment_id', 'criterion_id', 'criterion_value_id')
                            ->with('criterion_value', function ($q) {
                                $q->select('id', 'value_text', 'value_date', 'value_boolean')
                                    ->with('criterion', function ($q) {
                                        $q->select('id', 'name', 'code');
                                    });
                            });
                    });
            },
            'type:id,code'
        ],
    ]
];
