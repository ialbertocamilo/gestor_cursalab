<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Models\Videoteca;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RestVideotecaController extends Controller
{ 
    /* public function __construct()
    {
        $this->middleware('auth.jwt');
        return auth()->shouldUse('api');
        Carbon::setLocale('es');
    }*/

    public function search(Request $request)
    {
        //return response()->json($request->all());
        $user = auth()->user();
        $request->merge(['workspace_id' => $user->subworkspace->parent_id, 
                         'modulo_id' => $user->subworkspace_id ]);

        $videoteca = Videoteca::search($request, true);
        //return response()->json($videoteca);
        $items = Videoteca::prepareData($videoteca->items());

        $videoteca = [
            'current_page' => $videoteca->currentPage(),
            'last_page' => $videoteca->lastPage(),
            'data' => $items,
        ];

        return response()->json(compact('videoteca'));
    }

    public function show(Videoteca $videoteca)
    {
        $user = auth()->user();
        $videoteca = Videoteca::processData($videoteca, ['related_tags']);

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

        $category_ids = Videoteca::select('category_id')
                                 ->whereHas('modules', function ($q) use ($user) {
            $q->where('module_id', $user->subworkspace_id);
        })->where('active',1)
          ->groupBy('category_id')
          ->pluck('category_id');
        
        $data['categorias'] = Taxonomy::whereIn('id', $category_ids)
                                      ->where('group', 'videoteca')
                                      ->where('type', 'categoria')
                                      ->get()->pluck('name', 'id')->toArray();
        
        return response()->json(compact('data'));
    }

    public function storeVisit(Videoteca $videoteca)
    {
        $user_id = auth()->user()->id;

        $accion_visita = Taxonomy::where('group', 'videoteca')
                                 ->where('type', 'accion')
                                 ->where('name', 'view')
                                 ->first();
//        info("[storeVisit]", [$accion_visita]);
        $row = $videoteca->incrementAction($accion_visita->id, $user_id);

        return response()->json(compact('row'));
    }
}
