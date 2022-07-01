<?php

namespace App\Exports;

use App\Models\Abconfig;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CurriculaExport implements FromView
{

    private $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {

        $data = Abconfig::with([
            'categorias' => function($query){
                $query->select('categorias.nombre as nom_categoria', 'config_id', 'id');
            },
            'categorias.cursos' => function($query){
                $query->select('cursos.nombre as nom_curso', 'config_id', 'categoria_id', 'id');
            }
        ])->get(['id', 'etapa']);

        foreach ($data as $modulo) {
            foreach ($modulo->categorias as $escuela) {
                foreach ($escuela->cursos as $curso) {
                    $curriculas = DB::table('cursos')
                                    ->where('cursos.id', '=', $curso->id)
                                    ->join('curricula as c', 'c.curso_id', 'cursos.id')
                                    ->join('carreras', 'carreras.id', 'c.carrera_id')
                                    ->join('ciclos', 'ciclos.id', 'c.ciclo_id')
                                    ->join('curricula_criterio as cc', 'cc.curricula_id', 'c.id')
                                    ->join('criterios as c2', 'c2.id', 'cc.criterio_id')
                                    ->get(['cursos.id as curso_id', 'c.id as curricula_id', 'cursos.nombre as nombre_curso', 'carreras.nombre as nombre_carrera',
                                        'ciclos.nombre as nombre_ciclo', 'c2.valor as nombre_criterio']);
                                                
                    $curso->curriculas = $curriculas;
                   
                }
            }
        }
       
        return view('exportar.curriculas_grupos',[
            'modulos'   => $data,
        ]);
    }
}
