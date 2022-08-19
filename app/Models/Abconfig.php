<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Abconfig extends Model
{


    protected $table = 'ab_config';

    protected $fillable = [
        'etapa',
        'logo',
        'color',
        'mod_agrupacion',
        'duracion_dias',
        'mod_cronometro',
        'mod_mainmenu',
        'mod_sidemenu',
        'mod_certificado',
        'mod_encuestas',
        'mod_evaluaciones',
        'mod_tipovalidacion',
        'mod_push',
        'etapa_requisito_id',
        'estado',
        'plantilla_diploma',
        'codigo_matricula',
        'reinicios_programado'
    ];

    protected $hidden = [
        'pivot'
    ];

    // protected $hidden = [
    //     'id', 'isotipo', 'color', 'mod_agrupacion', 'push_code', 'color2', 'thumb_diploma', 'duracion_dias', 'mod_cronometro', 'mod_mainmenu', 'mod_sidemenu', 'mod_certificado', 'mod_encuestas', 'mod_evaluaciones', 'mod_tipovalidacion', 'mod_push', 'etapa_requisito_id', 'estado', 'plantilla_diploma', 'created_at', 'updated_at'
    // ];

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    }


    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'config_id');
    }

    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'config_id');
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'config_id');
    }

    public function configuracion_requisito()
    {
        return $this->belongsTo(Abconfig::class, 'etapa_requisito_id');
    }

    public function criterios()
    {
        return $this->hasMany(Criterio::class, 'config_id');
    }

    // public function app_menu()
    // {
    //     return $this->belongsToMany(Taxonomy::class, 'modulos_app_menu', 'modulo_id', 'menu_id')
    //         ->where('tipo', 'main_menu')
    //         ->select('id', 'nombre');
    // }

    // public function main_menu()
    // {
    //     return $this->belongsToMany(Taxonomy::class, 'modulos_app_menu', 'modulo_id', 'menu_id')
    //         ->where('tipo', 'main_menu')
    //         ->select('id', 'nombre', 'code');
    // }

    // public function side_menu()
    // {
    //     return $this->belongsToMany(Taxonomy::class, 'modulos_app_menu', 'modulo_id', 'menu_id')
    //         ->where('tipo', 'side_menu')
    //         ->select('id', 'nombre', 'code');
    // }

    public function areas()
    {
        return $this->hasMany(Criterio::class, 'config_id');
    }

    // public function getReiniciosProgramadoAttribute($value){
    //     return json_decode($value);
    // }
    /* AUDIT TAGS */

    protected static function search($request)
    {
        $query = self::withCount(['usuarios', 'carreras', 'categorias']);

        if ($request->q)
            $query->where('etapa', 'like', "%$request->q%");

        return $query->paginate($request->paginate);
    }

    protected static function storeRequest($data, $modulo = null)
    {
        try {

            DB::beginTransaction();

            if ($modulo) :
                $modulo->update($data);
            else:
                $modulo = self::create($data);
            endif;

            if (!empty($data['app_menu'])):
                $modulo->app_menu()->sync($data['app_menu']);
            endif;

//            if (!empty($data['file_logo'])):
//                $path = Media::uploadFile($data['file_logo']);
//                $modulo->logo = $path;
//
//            endif;
//
//            if (!empty($data['file_plantilla_diploma'])):
//                $path = Media::uploadFile($data['file_plantilla_diploma']);
//                $modulo->plantilla_diploma = $path;
//            endif;

            $modulo->save();
            DB::commit();
            return $modulo;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }

    }

    protected function getModulesForSelect($ids = [])
    {
        return Abconfig::select('id','etapa as nombre')
                        ->where('estado', 1)
                        ->when($ids, function($q) use ($ids){
                            $q->whereIn('id', $ids);
                        })
                        ->get();
        // return Abconfig::select('id','etapa')->where('estado', 1)->pluck('etapa', 'id' )->toArray();
    }

    protected function getFullAppMenu($tipo, $codes)
    {
        $values = Taxonomy::getDataByGroupAndType('system', $tipo);

        $data = [];

        foreach($values AS $value)
        {
            $data[$value->code] = in_array($value->code, $codes);
        }

        return $data;
    }
}
