<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Models\Vademecum;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RestVademecumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.jwt');
        return auth()->shouldUse('api');
        Carbon::setLocale('es');
    }

    public function storeVisit(Vademecum $elemento, Request $request)
    {
        $accion_visita = Taxonomy::where('group', 'vademecum')
                                  ->where('type', 'accion')
                                  ->where('name', 'view')
                                  ->first();

        $row = $elemento->incrementAction($accion_visita->id);

        return array('error' => 0, 'data' => compact('row'));
    }

    public function getElements(Request $request)
    {
        $request->merge(['modulo_id' => auth()->user()->config_id, 'estado' => 1]);

        $elementos = Vademecum::search($request, true);

        $data = Vademecum::prepareSearchedData($elementos);

        return array('error' => 0, 'data' => $data);
    }

    public function getSelects()
    {
        $data['categorias'] = Taxonomy::whereHas('vademecums', function($q){
                                        $q->where('active', 1);
                                    })
                                    ->where('group', 'vademecum')
                                    ->where('type', 'categoria')
                                    ->get()
                                    ->pluck('name', 'id')
                                    ->toArray();

        return array('error' => 0, 'data' => $data);
    }

    public function getSubCategorias($categoria_id)
    {
        $subcategorias = Taxonomy::subcategoriaVademecum($categoria_id)
                                    ->select('name', 'id', 'parent_taxonomia_id as categoria_id')
                                    ->get();
        return response()->json(compact('subcategorias'), 200);
    }
}
