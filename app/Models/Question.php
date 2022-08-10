<?php

namespace App\Models;

class Question extends BaseModel
{
    protected $fillable = [
        'topic_id', 'type_id', 'pregunta',
        'rptas_json', 'rpta_ok',
    ];

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }
}
