<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Posteo;
use App\Models\Curso;

class MigracionController extends Controller
{

    public function compatibles(Request $request){
        $modulos = \App\Abconfig::select('id','etapa')->get();

        $data = array('modulos'=>$modulos);

        if ($request->has('mod')) {
            $mod = $request->input('mod');

            $compatibles = \DB::select( \DB::raw("SELECT curso_id, tema_id, curso_compa_id, tema_compa_id FROM posteos_compatibles  WHERE config_id = '".$mod."'"));

            $cursos = Curso::select('id', 'nombre')->where('config_id', $mod)->pluck('nombre','id');
            // $cursos = Curso::select('id', 'nombre')->pluck('nombre','id');
            $temas = Posteo::select('id', 'nombre')->pluck('nombre','id');

            $data = array('modulos'=>$modulos, 'compatibles'=>$compatibles, 'cursos'=>$cursos, 'temas'=>$temas);
        }
 
        return view('migracion.index', $data);
    }

}
