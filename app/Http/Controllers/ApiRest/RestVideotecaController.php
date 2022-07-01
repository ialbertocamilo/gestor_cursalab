<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Taxonomia;
use App\Models\Videoteca;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RestVideotecaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.jwt');
        return auth()->shouldUse('api');
        Carbon::setLocale('es');
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $request->modulo_id = $user->config_id;

        $videoteca = Videoteca::search($request, true);

        $items = Videoteca::prepareData($videoteca->items());

        $videoteca = [
            'current_page' => $videoteca->currentPage(),
            'last_page' => $videoteca->lastPage(),
            'data' => $items,
        ];

        return response()->json(compact('videoteca'));
    }

    public function show(Videoteca $videotecaRequest)
    {
        $user = auth()->user();
        $videoteca = Videoteca::processData($videotecaRequest, ['related_tags']);

        return response()->json(compact('videoteca'));
    }

    public function getRelated(Videoteca $videoteca)
    {
        $user = auth()->user();
        $videoteca->load('categoria');
        $related = Videoteca::getRelated($videoteca, $user);

        return response()->json(compact('related'));
    }

    public function getSelects()
    {
        $user = auth()->user();
        $category_ids = Videoteca::select('category_id')->whereHas('modules', function ($q) use ($user) {
            $q->where('module_id', $user->config_id);
        })->where('active',1)->groupBy('category_id')->pluck('category_id');
        $data['categorias'] = Taxonomia::whereIn('id',$category_ids)
        ->where('grupo', 'videoteca')->where('tipo', 'categoria')->get()->pluck('nombre', 'id')->toArray();
        return response()->json(compact('data'));
    }

    public function storeVisit(Videoteca $videoteca)
    {
        $user_id = auth()->user()->id;

        $accion_visita = Taxonomia::where('grupo', 'videoteca')->where('tipo', 'accion')->where('nombre', 'view')->first();
//        info("[storeVisit]", [$accion_visita]);
        $row = $videoteca->incrementAction($accion_visita->id, $user_id);

        return response()->json(compact('row'));
    }
}
