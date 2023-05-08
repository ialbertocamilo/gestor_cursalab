<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ambiente extends Model
{
    protected $table = 'config_general';
    protected $fillable = [
        'titulo_login_app', 'subtitulo_login_app', 'form_login_transparency',  'form_login_position', 
        'color_primario_app', 'color_secundario_app', 'fondo_app', 'logo_app',
        'logo_cursalab_position','show_blog_btn','logo_cursalab',
        'completed_courses_logo', 'enrolled_courses_logo', 'diplomas_logo'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
