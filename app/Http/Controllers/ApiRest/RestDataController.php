<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Glossary;
//use App\Models\Matricula;
use App\Models\Carrera;
use App\Models\User;
use App\Models\Taxonomy;
use Config;
use Illuminate\Http\Request;

class RestDataController extends Controller
{
    /*public function __construct()
    {
        $this->middleware('auth.jwt');
        return auth()->shogesuldUse('api');
    }*/

    public function glosarioSelects()
    {
        $data = $this->getSelectsForForm();

        return array('error' => 0, 'data' => $data);
    }

    public function getSelectsForForm()
    {
        $selects = config('data.glosario.selects');

        $data = [];

        foreach ($selects as $key => $select) :

            if ($select['api'] == true) :
                $data[$select['key']] = $select;
                $data[$select['key']]['list'] = Taxonomy::getDataForSelectAttrs('glosario', $select['key'], ['id', 'name as nombre']);
            endif;

        endforeach;
        $data['categoria']['list'] = [];


        # usuario - carrera -  criterios
        $usuario_criterios = User::find(auth()->user()->id)->criterion_user
                                 ->pluck('criterion_value_id')
                                 ->toArray();

        $usuario_categories = Carrera::whereIn('carrera_id', $usuario_criterios)
                                     ->where('module_id', auth()->user()->subworkspace_id)
                                     ->get();
                                       
        $glosario_categorias = Taxonomy::getDataForSelectAttrs('glosario', 'categoria', ['id', 'name as nombre']);

        foreach ($usuario_categories as $key => $uc_categoria) {
            foreach ($glosario_categorias as $gc_categoria) {
                if($uc_categoria->glosario_categoria_id === $gc_categoria->id) {
                    $data['categoria']['list'][$gc_categoria->id] = $gc_categoria->nombre;
                }
            }
        }

        return $data;
        //return ['user' => auth()->user(), 'data' => $data ];

        /*
        # get critrion by user_index
        # $matricula = Matricula::with('carrera.glosario_categorias')->where('usuario_id', auth()->user()->id)->first();
        # user carrera - categories 
        if ($matricula and $matricula->carrera) :
            $data['categoria']['list'] = $matricula->carrera->glosario_categorias->pluck('nombre', 'id')->toArray();
        endif;
        return $data;*/
    }

    public function glosarioSearch(Request $request)
    {
        $request->merge(['modulo_id' => auth()->user()->subworkspace_id, 
                         'estado' => 1]);

        $glosarios = Glossary::search($request, true);
        $data = Glossary::prepareSearchedData($glosarios);

        return array('error' => 0, 'data' => $data);
    }
}