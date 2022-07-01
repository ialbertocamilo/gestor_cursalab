<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Usuario_upload extends Model
{
    

    protected $table = 'usuario_uploads';

    /* AUDIT TAGS */
    public function generateTags(): array
    {
        return [
            'modelo_independiente'
        ];
    }
    /* AUDIT TAGS */
}