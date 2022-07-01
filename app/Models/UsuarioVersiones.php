<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioVersiones extends Model
{
    //
    protected $fillable = ['usuario_id', 'v_android', 'v_ios','android','ios','windows'];

    public static function actualizar_version_y_visita($usuario_id,$os,$version){
        //OS <- android o ios , si es null se supone que entra desde web
        //version <- la version de la aplicación si es web el valor es null
        $uv = UsuarioVersiones::where('usuario_id',$usuario_id)->first();
        if($uv){
            ($os == "android" || $os == "ios") ? $uv->$os = $uv->$os+1 : $uv->windows = $uv->windows+1;
            ($os != "" && $version != "" &&  ($version!='null')) && $uv['v_'.$os] = $version;
            $uv->save();
        }else{
            $data['usuario_id'] = $usuario_id;
            ($os == "android" || $os == "ios") ? $data[$os]=1 : $data['windows']=1;
            ($os != "" && $version != "" &&  ($version!='null')) && $data['v_'.$os] = $version;
            UsuarioVersiones::insert($data);
        }
    }
}
