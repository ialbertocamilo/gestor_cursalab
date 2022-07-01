<?php

namespace App\Exports;

use App\Models\Usuario;
use App\Models\PosteoCompas;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportReporteCompatible implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $temas_n_r,$temas_o_r;
    public function view(): View
    {
        $p_compas = PosteoCompas::with( ['posteo'=> function($q){
            $q->select('id','nombre','curso_id');
        },'posteo_compa' => function($q){
            $q->select('id','nombre','curso_id');
        },'posteo.curso' => function($q){
            $q->select('id','nombre','config_id','categoria_id');
        },'posteo_compa.curso' => function($q){
            $q->select('id','nombre','config_id','categoria_id');
        },'posteo.curso.config' => function($q){
            $q->select('id','etapa');
        },'posteo_compa.curso.config' => function($q){
            $q->select('id','etapa');
        },'posteo_compa.curso.categoria' => function($q){
            $q->select('id','nombre');
        },'posteo.curso.categoria' => function($q){
            $q->select('id','nombre');
        }
        ])->orderBy('id')->get();
        //Insertas primer valor
        $j_p = [];
        $datos=[];
        foreach ($p_compas as $p_compa) {
            //Verificar si existe la ida
            $inve=[
                'tema_id' => $p_compa->tema_compa_id,
                'tema_compa_id' => $p_compa->tema_id,
            ];
            $veri = array_search($inve,$j_p);
            if(!$veri){
                //Introducir la vuelta
                $j_p [] = [
                    'tema_id' => $p_compa->tema_id,
                    'tema_compa_id' => $p_compa->tema_compa_id,
                ];
                $datos[] = $p_compa;
            }
        }
        return view('exportar.reporte_posteos_compatibles', [
            'p_compas'=>$datos,
        ]);
    }
}