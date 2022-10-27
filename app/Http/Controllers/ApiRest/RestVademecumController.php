<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\Vademecum;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RestVademecumController extends Controller
{
    public function __construct()
    {
        //this->middleware('auth.jwt');
        //return auth()->shouldUse('api');
        Carbon::setLocale('es');
    }

    public function storeVisit(Vademecum $vademecum, Request $request): array
    {
        $visitAction = Taxonomy::where('group', 'vademecum')
                                  ->where('type', 'accion')
                                  ->where('name', 'view')
                                  ->first();

        $row = $vademecum->incrementAction($visitAction->id);

        return ['error' => 0, 'data' => compact('row')];
    }

    public function loadUserModuleVademecum(Request $request): array
    {
        $user = auth('api')->user();
        $subworkspace = Workspace::find($user->subworkspace_id);

        if ($subworkspace) {
            $request->merge([
                'module_id' => $subworkspace->criterion_value_id
            ]);

            $elementos = Vademecum::search($request, true);
            $data = Vademecum::prepareSearchedData($elementos);

        } else {
            $data = [];
        }

        return ['error' => 0, 'data' => $data];
    }

    public function getSelects(): array
    {
        $user = auth('api')->user();
        $subworkspace = Workspace::find($user->subworkspace_id);

        $data['categorias'] = Taxonomy::vademecumCategory(
            $subworkspace->parent_id
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getSubCategorias($categoryId)
    {
        $user = auth('api')->user();
        $subworkspace = Workspace::find($user->subworkspace_id);

        $query = Taxonomy::vademecumSubcategory(
            $subworkspace->parent_id, $categoryId
        );
        $subcategorias = $query->get();
        return response()->json(compact('subcategorias'), 200);
    }
}
