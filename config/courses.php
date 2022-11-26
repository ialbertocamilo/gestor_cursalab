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
            'summaries' => function ($q) {
                $q->where('user_id', auth()->user()->id);
            },
            'compatibilities:id'
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
            'summaries' => function ($q) {
                $q
                    ->with('status:id,name,code')
                    ->where('user_id', auth()->user()->id);
            },
            'schools' => function ($query) {
                $query
                    ->select('id', 'imagen', 'name', 'position')
                    ->where('active', ACTIVE);
            },
            'type:id,code',
            'topics' => function ($q) {
                $q->with([
                    'evaluation_type:id,code',
                    'requirements.summaries_topics' => function ($q) {
                        $q
                            ->with('status:id,name,code')
                            ->where('user_id', auth()->user()->id);
                    },
                    'summaries' => function ($q) {
                        $q
                            ->with('status:id,name,code')
                            ->where('user_id', auth()->user()->id);
                    }
                ]);
            },
            'requirements.summaries_course' => function ($q) {
                $q
                    ->with('status:id,name,code')
                    ->where('user_id', auth()->user()->id);
            },
        ],

        'default' => [
            'segments.values.criterion_value.criterion',
            'requirements',
            'schools' => function ($query) {
                $query->where('active', ACTIVE);
            },
            'topics' => [
                'evaluation_type',
                'requirements',
                'medias.type'
            ],
            'polls.questions',
            'summaries' => function ($q) {
                $q->where('user_id', auth()->user()->id);
            },
            'compatibilities'
        ]
    ]
];
