<?php

namespace App\Models;


class Usuario_upload extends BaseModel
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
