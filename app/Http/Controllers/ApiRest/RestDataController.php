<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Glosario;
use App\Models\Matricula;
use App\Models\Taxonomia;
use App\Models\Usuario_rest;
use Config;

class RestDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.jwt');
        return auth()->shouldUse('api');
    }

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
                $data[$select['key']]['list'] = Taxonomia::getDataForSelect('glosario', $select['key']);
            endif;

        endforeach;

        $data['categoria']['list'] = [];

        $matricula = Matricula::with('carrera.glosario_categorias')->where('usuario_id', auth()->user()->id)->first();

        if ($matricula and $matricula->carrera) :

            $data['categoria']['list'] = $matricula->carrera->glosario_categorias->pluck('nombre', 'id')->toArray();

        endif;

        return $data;
    }

    public function glosarioSearch(Request $request)
    {
        $request->merge(['modulo_id' => auth()->user()->config_id, 'estado' => 1]);

        $glosarios = Glosario::search($request, true);

        $data = Glosario::prepareSearchedData($glosarios);

        return array('error' => 0, 'data' => $data);
    }
}
