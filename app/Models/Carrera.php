<?php

namespace App\Models;
use DB;
use App\Curso;
use App\Curricula;
use App\Posteo;

use Illuminate\Database\Eloquent\Model;


class Carrera extends Model
{


    protected $fillable = [
    	'config_id', 'nombre', 'malla_archivo', 'estado'
    ];

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    }

    public function ciclos()
    {
        return $this->hasMany(Ciclo::class, 'carrera_id');
    }

    public function config()
    {
        return $this->belongsTo(Abconfig::class, 'config_id');
    }

    public function glosario_categorias()
    {
        return $this->belongsToMany(
            Taxonomy::class,
            'carrera_glosario_categoria',
            'carrera_id',
            'glosario_categoria_id'
        );
    }

    public function cursos($categoria_id)
    {
        $ciclo_ids = $this->ciclos()->pluck('id');

        $cursos = Curso::select('cursos.id', 'cursos.nombre', 'cursos.imagen')
                        ->join('curricula', 'curricula.curso_id', '=', 'cursos.id')
                        ->whereIn('curricula.ciclo_id', $ciclo_ids)
                        ->where('cursos.categoria_id', $categoria_id)
                        ->where('cursos.estado', 1)
                        ->orderBy('cursos.orden')
                        ->get();


        return $cursos;
    }

    // public function temas($categoria_id, $posteo_id)
    // {
    //     // $ciclo_ids = $this->ciclos()->pluck('id');
    //     // $curso_ids = Curricula::whereIn('ciclo_id', $ciclo_ids)->pluck('curso_id');
    //     $curso_ids = Curso::where('categoria_id', $categoria_id)->pluck('id');

    //     $posteos = Posteo::select('id', 'nombre', 'curso_id')
    //                     ->where('categoria_id', $categoria_id)
    //                     ->whereIn('curso_id', $curso_ids)
    //                     ->where('id', '!=' ,$posteo_id)
    //                     ->where('estado', 1)
    //                     ->orderBy('nombre')
    //                     ->get();

    //     return $posteos;
    // }

    // public function cate_perfiles()
    // {
    //     return $this->hasMany(Categoria_perfil::class, 'categoria_id');
    // }

    // public function delete()
    // {
    //     $this->temas()->delete();
    //     // $this->temas()->delete();

    //     return parent::delete();
    // }

    /* AUDIT TAGS */
    public function generateTags(): array
    {
        return [
            'modelo_independiente'
        ];
    }
    /* AUDIT TAGS */

    protected function getHostIds()
    {
        return Carrera::whereIn('nombre',
            [""]
        )->pluck('id')->toArray();
    }
}
