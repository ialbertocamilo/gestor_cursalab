<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Usuario;
use App\Models\Taxonomia;
use App\Models\Vademecum;
use App\Models\UsuarioAccion;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

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
        $accion_visita = Taxonomia::where('grupo', 'vademecum')->where('tipo', 'accion')->where('nombre', 'view')->first();

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
        $data['categorias'] = Taxonomia::whereHas('vademecums', function($q){
                                        $q->where('estado', 1);
                                    })
                                    ->where('grupo', 'vademecum')
                                    ->where('tipo', 'categoria')
                                    ->get()->pluck('nombre', 'id')->toArray();

        return array('error' => 0, 'data' => $data);
    }

    public function getSubCategorias($categoria_id)
    {
        $subcategorias = Taxonomia::subcategoriaVademecum($categoria_id)
                                    ->select('nombre', 'id', 'parent_taxonomia_id as categoria_id')
                                    ->get();
        return response()->json(compact('subcategorias'), 200);
    }
}
