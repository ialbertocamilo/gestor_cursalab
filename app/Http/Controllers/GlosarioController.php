<?php

namespace App\Http\Controllers;

use App\Models\Glosario;
use App\Models\Abconfig;
use App\Models\Taxonomia;
use App\Models\Carrera;

use App\Http\Requests\GlosarioStoreRequest;
use App\Http\Requests\GlosarioImportExcelRequest;
use App\Http\Resources\GlosarioResource;
use Illuminate\Http\Request;

class GlosarioController extends Controller
{

    public function search(Request $request)
    {
        $glosario = Glosario::search($request, false, $request->paginate);

        GlosarioResource::collection($glosario);

        return $this->success($glosario);
    }

    public function getListSelects()
    {
        $modules = Abconfig::select('id', 'etapa as nombre')->get();
        // $modulos = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        $categories = Taxonomia::getDataForSelect('glosario', 'categoria');
        $laboratorios = Taxonomia::getDataForSelect('glosario', 'laboratorio');
        $principios_activos = Taxonomia::getDataForSelect('glosario', 'principio_activo');

        return $this->success(compact('modules', 'categories'));
    }

    public function index(Request $request)
    {
        $glosarios = Glosario::search($request);

        $modulos = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        $categorias = Taxonomia::getDataForSelect('glosario', 'categoria');
        $laboratorios = Taxonomia::getDataForSelect('glosario', 'laboratorio');
        $principios_activos = Taxonomia::getDataForSelect('glosario', 'principio_activo');

        return view('glosarios.index', compact('glosarios', 'laboratorios', 'modulos', 'principios_activos', 'categorias'));
    }

    public function import()
    {
        $modulos = Abconfig::getModulesForSelect();
        $categorias = Taxonomia::getDataForSelect('glosario', 'categoria');

        return $this->success(get_defined_vars());
    }

    public function importFile(GlosarioImportExcelRequest $request) 
    {
        return Glosario::importFromFile($request->validated());
    }

    public function carreerCategories()
    {
        $modulos = Abconfig::getModulesForSelect();

        $carreras = Carrera::with('glosario_categorias:id,nombre')->where('estado', 1)
                            ->get(['id', 'config_id', 'nombre']);

        $carreras = $carreras->groupBy('config_id');

        $categorias = Taxonomia::getDataForSelect('glosario', 'categoria');

        return $this->success(get_defined_vars());
    }

    public function carreerCategoriesStore(Request $request)
    {
        return Glosario::storeCarreerCategories($request->all());
    }

    public function create()
    {
        $selects = $this->getSelectsForForm();
        $modulos = Glosario::getModulesWithCode();

        return $this->success(get_defined_vars());
    }

    public function getSelectsForForm()
    {
        $selects = config('data.glosario.selects');

        foreach ($selects as $key => $select)
        {
            $selects[$key]['list'] = Taxonomia::getDataForSelect('glosario', $select['key']);
        }

        return $selects;
    }

    public function store(GlosarioStoreRequest $request)
    {
        $data = $request->validated();

        $result = Glosario::storeRequest($data);

        return $this->success(['msg' => 'Glosario creado correctamente.']);
    }
    
    public function edit(Glosario $glosario)
    {
        $glosario->load('laboratorio', 'categoria', 'jerarquia', 'advertencias', 'condicion_de_venta', 'via_de_administracion', 'grupo_farmacologico', 'dosis_adulto', 'dosis_nino', 'recomendacion_de_administracion', 'principios_activos', 'contraindicaciones', 'interacciones', 'reacciones', 'modulos');

        $selects = $this->getSelectsForForm();
        $modulos = Glosario::getModulesWithCode($glosario->modulos);

        return $this->success(get_defined_vars());
    }

    public function update(Glosario $glosario, GlosarioStoreRequest $request)
    {
        $data = $request->validated();

        $result = Glosario::storeRequest($data, $glosario);

        return $this->success(['msg' => 'Glosario actualizado correctamente.']);
    }

    public function destroy(Glosario $glosario)
    {
        $glosario->delete();

        return $this->success(['msg' => 'Glosario eliminado correctamente.']);
    }

    public function status(Glosario $glosario, Request $request)
    {
        $glosario->update(['estado' => !$glosario->estado]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function response($result, $route = 'glosarios.index')
    {
        if ( $result['status'] == 'error' )
            return redirect()->back()
                    ->with('info', $result['message']);

        return redirect()->route($route)
                    ->with('info', $result['message']);
    }
}
