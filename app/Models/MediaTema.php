<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaTema extends Model
{
    protected $fillable = [
      'tema_id', 'valor', 'embed', 'descarga', 'titulo', 'orden'
    ];

    protected $casts = [
        'embed' => 'boolean',
        'descarga' => 'boolean',
    ];
}
