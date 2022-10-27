<?php

namespace App\Models;


class Usuario_upload extends BaseModel
{
    protected $table = 'usuario_uploads';

    protected $fillable = ['usuario_id', 'subworkspace_id', 'file', 'link', 'description'];

    /* AUDIT TAGS */
    public function generateTags(): array
    {
        return [
            'modelo_independiente'
        ];
    }
    /* AUDIT TAGS */
}
