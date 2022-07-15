<?php

namespace App\Models;

class Question extends BaseModel
{
    protected $fillable = [
        'topic_id', 'type_id', 'prgunta',
        'rptas_json', 'rpta_ok',
    ];
}
