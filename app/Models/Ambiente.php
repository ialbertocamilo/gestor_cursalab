<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ambiente extends Model
{
    protected $table = 'config_general';
    protected $fillable = [
        //gestor
        'link_genially',
        'color_primario', 'color_secundario',
        'titulo', 'titulo_login',
        'fondo', 'logo', 'icono', 'logo_empresa',
        //app
        'titulo_login_app', 'subtitulo_login_app', 'form_login_transparency',  'form_login_position', 
        'color_primario_app', 'color_secundario_app', 'color_terciario_app', 'fondo_app', 'fondo_invitados_app','logo_app',
        'logo_cursalab_position','show_blog_btn','logo_cursalab',
        'completed_courses_logo', 'enrolled_courses_logo', 'diplomas_logo','male_logo', 'female_logo',
        'template', 'identity_validation_enabled', 'password_expiration_enabled',
        'is_v1_migrated',
        //relation
        'workspace_id','type'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected function getCustomAmbienteByWorkspace($workspace_id){
        $taxonomy_functionality =Taxonomy::select('id')->where('group','system')->where('type','functionality')->where('code','custom-workspace')->first(); 
        $custom_ambiente = null;
        $has_custom_ambiente_avaiable = 
        DB::table('workspace_functionalities')
            ->where('workspace_id',$workspace_id)
            ->where('functionality_id',$taxonomy_functionality?->id)
            ->where('active',1)->first();
        if(
            $taxonomy_functionality && $has_custom_ambiente_avaiable
        ){
            $ambiente = Ambiente::select(
                'fondo_app','logo_app','logo_cursalab','logo_empresa','completed_courses_logo',
                'enrolled_courses_logo','diplomas_logo','male_logo','female_logo'
            )->where('workspace_id',$workspace_id)->where('type','workspace')
            ->first();
          
            if($ambiente){
                $custom_ambiente['fondo_app'] = get_media_url($ambiente['fondo_app']);
                $custom_ambiente['logo_app'] = get_media_url($ambiente['logo_app']);
                $custom_ambiente['logo'] = get_media_url($ambiente['logo_app']);
                $custom_ambiente['logo_cursalab'] = get_media_url($ambiente['logo_cursalab']);
                $custom_ambiente['app_main_isotipo'] = get_media_url($ambiente['logo_empresa']);
                $custom_ambiente['completed_courses_logo'] = get_media_url($ambiente['completed_courses_logo']);
                $custom_ambiente['enrolled_courses_logo'] = get_media_url($ambiente['enrolled_courses_logo']);
                $custom_ambiente['diplomas_logo'] = get_media_url($ambiente['diplomas_logo']);
                $custom_ambiente['male_logo'] = get_media_url($ambiente['male_logo']);
                $custom_ambiente['female_logo'] = get_media_url($ambiente['female_logo']);
            }
        }
        return $custom_ambiente;
    }
}
