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

    'user-courses-query' => function ($relation, $user_id) {

        return match ($relation) {
            'soft' => [
                'segments' => function ($q) {
                    $q
                        ->where('active', ACTIVE)
                        ->select('id', 'model_id', 'type_id')
                        ->with(['type:id,code', 'values' => function ($q) {
                            $q
                                // ->with('criterion_value', function ($q) {
                                //     $q
                                //         ->where('active', ACTIVE)
                                //         ->select('id', 'value_text', 'value_date', 'value_boolean')
                                //         ->with('criterion', function ($q) {
                                //             $q->select('id', 'name', 'code');
                                //         });
                                // })
                                ->select('id', 'segment_id', 'starts_at', 'finishes_at', 'criterion_id', 'criterion_value_id')->whereRelation('criterion', 'code', '<>', 'document');

                        }]);
                },
                'summaries' => function ($q) use ($user_id) {
                    $q
                        ->with('status:id,name,code')
                        ->where('user_id', $user_id);
                },
                'compatibilities_a:id',
                'compatibilities_b:id',
                'schools' => function ($query) {
                    $query
                        ->select('id', 'name')
                        ->where('active', ACTIVE);
                },
            ],
            'summary-user-update' => [
                'segments' => function ($q) {
                    $q
                        ->where('active', ACTIVE)
                        ->select('id', 'model_id')
                        ->with('values', function ($q) {
                            $q
                                // ->with('criterion_value', function ($q) {
                                //     $q
                                //         ->where('active', ACTIVE)
                                //         ->select('id', 'value_text', 'value_date', 'value_boolean')
                                //         ->with('criterion', function ($q) {
                                //             $q->select('id', 'name', 'code');
                                //         });
                                // })
                                ->select('id', 'segment_id', 'starts_at', 'finishes_at', 'criterion_id', 'criterion_value_id');

                        });
                },
            ],
            'user-progress' => [
                'segments' => function ($q) {
                    $q
                        ->where('active', ACTIVE)
                        ->select('id', 'model_id')
                        ->with('values', function ($q) {
                            $q
                                // ->with('criterion_value', function ($q) {
                                //     $q
                                //         ->where('active', ACTIVE)
                                //         ->select('id', 'value_text', 'value_date', 'value_boolean')
                                //         ->with('criterion', function ($q) {
                                //             $q->select('id', 'name', 'code');
                                //         });
                                // })
                                ->select('id', 'segment_id', 'criterion_id', 'starts_at', 'finishes_at', 'criterion_value_id');

                        });
                },
                'summaries' => function ($q) use ($user_id) {
                    $q
                        ->with('status:id,name,code')
                        ->where('user_id', $user_id);
                },
                'schools' => function ($query) {
                    $query
                        ->select('id', 'imagen', 'name', 'position')
                        ->where('active', ACTIVE);
                },
                'type:id,code',
                'topics' => function ($q) use ($user_id) {
                    $q->with([
                        'evaluation_type:id,code',
                        'qualification_type:id,code,position,name',
                        'requirements.summaries_topics' => function ($q) use ($user_id) {
                            $q
                                ->select('user_id', 'topic_id', 'status_id', 'id','grade','attempts',
                                    'passed', 'last_time_evaluated_at', 'views', 'answers', 'total_attempts',
                                    'last_media_access', 'last_media_duration', 'media_progress')
                                ->with('status:id,name,code')
                                ->where('user_id', $user_id);
                        },
                        'summaries' => function ($q) use ($user_id) {
                            $q
                                ->select('user_id', 'topic_id', 'status_id', 'id','grade','attempts',
                                    'passed', 'last_time_evaluated_at', 'views', 'answers', 'total_attempts',
                                    'last_media_access', 'last_media_duration', 'media_progress')
                                ->with('status:id,name,code')
                                ->where('user_id', $user_id);
                        }
                    ]);
                },
                // 'requirements.summaries_course' => function ($q) use ($user_id) {
                //     $q
                //         ->with('status:id,name,code')
                //         ->where('user_id', $user_id);
                // },
                'requirements' => [
                    'model_course' => [
                        'topics',
                        'schools',
                        'summaries' => function ($q) use ($user_id) {
                            $q
                                ->with('status:id,name,code')
                                ->where('user_id', $user_id);
                        },
                        'compatibilities_a:id',
                        'compatibilities_b:id',
                    ],
                    'summaries_course' => function ($q) use ($user_id) {
                        $q
                            ->with('status:id,name,code')
                            ->where('user_id', $user_id);
                    },
                ],
                'compatibilities_a:id',
                'compatibilities_b:id',
                'qualification_type:id,code,position,name',
            ],
            'course-view-app-user' => [
                'segments' => function ($q) {
                    $q
                        ->where('active', ACTIVE)
                        ->select('id', 'model_id')
                        ->with('values', function ($q) {
                            $q
                                // ->with('criterion_value', function ($q) {
                                //     $q
                                //         ->where('active', ACTIVE)
                                //         ->select('id', 'value_text', 'value_date', 'value_boolean')
                                //         ->with('criterion', function ($q) {
                                //             $q->select('id', 'name', 'code');
                                //         });
                                // })
                                ->select('id', 'segment_id', 'criterion_id', 'starts_at', 'finishes_at', 'criterion_value_id');

                        });
                },
                'summaries' => function ($q) use ($user_id) {
                    $q
                        ->with('status:id,name,code')
                        ->where('user_id', $user_id);
                },
                'polls',
                'schools' => function ($q) {
                    $q
                        ->select('id', 'imagen', 'name', 'position')
                        ->where('active', ACTIVE);
                },
                'type:id,code',
                'qualification_type:id,code,position,name',
                'topics' => function ($q) use ($user_id) {
                    $q
                        ->where('active', ACTIVE)
                        ->with([
                            'evaluation_type:id,code',
                            'qualification_type:id,code,position,name',
                            'requirements.summaries_topics' => function ($q) use ($user_id) {
                                $q
                                   ->select('user_id', 'topic_id', 'status_id', 'id','grade','attempts',
                                    'passed', 'last_time_evaluated_at', 'views', 'answers', 'total_attempts',
                                    'last_media_access', 'last_media_duration', 'media_progress')
                                    ->with('status:id,name,code')
                                    ->where('user_id', $user_id);
                            },
                            'requirements.model_topic:id,name',
                            'summaries' => function ($q) use ($user_id) {
                                $q
                                    ->select('user_id', 'topic_id', 'status_id', 'id','grade','attempts',
                                    'passed', 'last_time_evaluated_at', 'views', 'answers', 'total_attempts',
                                    'last_media_access', 'last_media_duration', 'media_progress')
                                    ->with('status:id,name,code')
                                    ->where('user_id', $user_id);
                            }
                        ]);
                },
                'requirements' => [
                    'model_course' => [
                        'topics',
                        'schools',
//                        'compatibilities_a:id',
//                        'compatibilities_b:id',
                        'summaries' => function ($q) use ($user_id) {
                            $q
                                ->with('status:id,name,code')
                                ->where('user_id', $user_id);
                        },
                        'compatibilities_a:id',
                        'compatibilities_b:id',
                    ],
                    'summaries_course' => function ($q) use ($user_id) {
                        $q
                            ->with('status:id,name,code')
                            ->where('user_id', $user_id);
                    },
                ],
//                'requirements.summaries_course' => function ($q) use($user_id){
//                    $q
//                        ->with('status:id,name,code')
//                        ->where('user_id', $user_id);
//                },
                'compatibilities_a:id',
                'compatibilities_b:id',
            ],

            default => [
                // 'segments.values.criterion_value.criterion',
                'segments' => function ($q) {
                    $q
                        ->where('active', ACTIVE)
                        ->select('id', 'model_id', 'type_id')
                        ->with(['type:id,code', 'values' => function ($q) {
                            $q
                                // ->with('criterion_value', function ($q) {
                                //     $q
                                //         ->where('active', ACTIVE)
                                //         ->select('id', 'value_text', 'value_date', 'value_boolean')
                                //         ->with('criterion', function ($q) {
                                //             $q->select('id', 'name', 'code');
                                //         });
                                // })
                                ->select('id', 'segment_id', 'starts_at', 'finishes_at', 'criterion_id', 'criterion_value_id')->whereRelation('criterion', 'code', '<>', 'document');

                        }]);
                },
                'requirements',
                'schools' => function ($query) {
                    $query->where('active', ACTIVE);
                },
                // 'topics' => [
                //     'evaluation_type',
                //     'requirements',
                //     'medias.type'
                // ],
                'topics' => function ($q) use ($user_id) {
                    $q
                        ->where('active', ACTIVE)
                        ->with([
                            'medias.type',
                            'evaluation_type:id,code',
                            'qualification_type:id,code,position,name',
                            'requirements.summaries_topics' => function ($q) use ($user_id) {
                                $q
                                    ->select('user_id', 'topic_id', 'status_id', 'id','grade','attempts',
                                    'passed', 'last_time_evaluated_at', 'views', 'answers', 'total_attempts',
                                    'last_media_access', 'last_media_duration', 'media_progress')
                                    ->with('status:id,name,code')
                                    ->where('user_id', $user_id);
                            },
                            'summaries' => function ($q) use ($user_id) {
                                $q
                                    ->select('user_id', 'topic_id', 'status_id', 'id','grade','attempts',
                                    'passed', 'last_time_evaluated_at', 'views', 'answers', 'total_attempts',
                                    'last_media_access', 'last_media_duration', 'media_progress')
                                    ->with('status:id,name,code')
                                    ->where('user_id', $user_id);
                            }
                        ]);
                },
                'polls.questions',
                'summaries' => function ($q) use ($user_id) {
                    $q->where('user_id', $user_id);
                },
                'compatibilities_a:id',
                'compatibilities_b:id',
                'qualification_type:id,code,position,name',
            ]
        };
    }
];
