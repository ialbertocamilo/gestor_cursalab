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
                    ->where('active', ACTIVE)
                    ->select('id', 'model_id')
                    ->with('values', function ($q) {
                        $q
                            ->with('criterion_value', function ($q) {
                                $q
                                    ->where('active', ACTIVE)
                                    ->select('id', 'value_text', 'value_date', 'value_boolean')
                                    ->with('criterion', function ($q) {
                                        $q->select('id', 'name', 'code');
                                    });
                            })
                            ->select('id', 'segment_id', 'criterion_id', 'criterion_value_id');

                    });
            },
            'summaries' => function ($q) {
                $q->where('user_id', auth()->user()->id);
            },
            'compatibilities_a:id',
            'compatibilities_b:id',
        ],
        'user-progress' => [
            'segments' => function ($q) {
                $q
                    ->where('active', ACTIVE)
                    ->select('id', 'model_id')
                    ->with('values', function ($q) {
                        $q
                            ->with('criterion_value', function ($q) {
                                $q
                                    ->where('active', ACTIVE)
                                    ->select('id', 'value_text', 'value_date', 'value_boolean')
                                    ->with('criterion', function ($q) {
                                        $q->select('id', 'name', 'code');
                                    });
                            })
                            ->select('id', 'segment_id', 'criterion_id', 'criterion_value_id');

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
                            // ->select('id', 'user_id', 'topic_id', 'status_id', 'attempts', 'grade', 'passed')
                            ->with('status:id,name,code')
                            ->where('user_id', auth()->user()->id);
                    },
                    'summaries' => function ($q) {
                        $q
                            // ->select('id', 'user_id', 'topic_id', 'status_id', 'attempts', 'grade', 'passed')
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
        'course-view-app-user' => [
            'segments' => function ($q) {
                $q
                    ->where('active', ACTIVE)
                    ->select('id', 'model_id')
                    ->with('values', function ($q) {
                        $q
                            ->with('criterion_value', function ($q) {
                                $q
                                    ->where('active', ACTIVE)
                                    ->select('id', 'value_text', 'value_date', 'value_boolean')
                                    ->with('criterion', function ($q) {
                                        $q->select('id', 'name', 'code');
                                    });
                            })
                            ->select('id', 'segment_id', 'criterion_id', 'criterion_value_id');

                    });
            },
            'summaries' => function ($q) {
                $q
                    ->with('status:id,name,code')
                    ->where('user_id', auth()->user()->id);
            },
            'polls',
            'schools' => function ($q) {
                $q
                    ->select('id', 'imagen', 'name', 'position')
                    ->where('active', ACTIVE);
            },
            'type:id,code',
            'topics' => function ($q) {
                $q
                    ->where('active', ACTIVE)
                    ->with([
                        'evaluation_type:id,code',
                        'requirements.summaries_topics' => function ($q) {
                            $q
                                // ->select('id', 'user_id', 'topic_id', 'status_id', 'attempts', 'grade', 'passed')
                                ->with('status:id,name,code')
                                ->where('user_id', auth()->user()->id);
                        },
                        'summaries' => function ($q) {
                            $q
                                // ->select('id', 'user_id', 'topic_id', 'status_id', 'attempts', 'grade', 'passed')
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
        ]
    ]
];
