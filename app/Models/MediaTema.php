<?php

namespace App\Models;

class MediaTema extends BaseModel
{
    protected $fillable = [
      'tema_id', 'valor', 'embed', 'descarga', 'titulo', 'orden'
    ];

    protected $casts = [
        'embed' => 'boolean',
        'descarga' => 'boolean',
    ];
}
