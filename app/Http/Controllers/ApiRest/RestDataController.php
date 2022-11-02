<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Glossary;
//use App\Models\Matricula;
use App\Models\Carrera;
use App\Models\Criterion;
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
                $data[$select['key']]['list'] = Taxonomy::getDataForSelect('glosario', $select['key']);
            endif;

        endforeach;

        return $data;

        $data['categoria']['list'] = [];

        #get critrion by user_index
        $matricula = Matricula::with('carrera.glosario_categorias')->where('usuario_id', auth()->user()->id)->first();

        // $categorias = Taxonomy::getDataForSelect('glosario', 'categoria');
        $carreras = Carrera::with('glosario_categorias')->get();
        
        return $carreras;

        if ($matricula and $matricula->carrera) :
            $data['categoria']['list'] = $matricula->carrera->glosario_categorias->pluck('nombre', 'id')->toArray();
        endif;

        return $data;
    }

    public function glosarioSearch(Request $request)
    {
        $request->merge(['modulo_id' => auth()->user()->subworkspace_id, 'estado' => 1]);

        $glosarios = Glossary::search($request, true);
        $data = Glossary::prepareSearchedData($glosarios);

        return array('error' => 0, 'data' => $data);
    }
}
