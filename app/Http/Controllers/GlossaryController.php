<?php

namespace App\Http\Controllers;

use App\Http\Requests\GlosarioImportExcelRequest;
use App\Http\Requests\GlossaryStoreRequest;
use App\Http\Resources\GlosarioResource;
use App\Models\Abconfig;
use App\Models\Carrera;
use App\Models\Glossary;
use App\Models\Taxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlossaryController extends Controller
{

    public function search(Request $request)
    {
        $glossaries = Glossary::search($request, false, $request->paginate);
        GlosarioResource::collection($glossaries);
        return $this->success($glossaries);
    }

    public function getListSelects()
    {
        $modules = Abconfig::select('id', 'etapa as nombre')->get();
        // $modulos = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        $categories = Taxonomy::getDataForSelect('glosario', 'categoria');
        $laboratorios = Taxonomy::getDataForSelect('glosario', 'laboratorio');
        $principios_activos = Taxonomy::getDataForSelect('glosario', 'principio_activo');

        return $this->success(compact('modules', 'categories'));
    }

    public function index(Request $request)
    {
        $glosarios = Glossary::search($request);

        $modulos = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        $categorias = Taxonomy::getDataForSelect('glosario', 'categoria');
        $laboratorios = Taxonomy::getDataForSelect('glosario', 'laboratorio');
        $principios_activos = Taxonomy::getDataForSelect('glosario', 'principio_activo');

        return view('glosarios.index', compact('glosarios', 'laboratorios', 'modulos', 'principios_activos', 'categorias'));
    }

    public function import()
    {
        $modulos = Abconfig::getModulesForSelect();
        $categorias = Taxonomy::getDataForSelect('glosario', 'categoria');

        return $this->success(get_defined_vars());
    }

    public function importFile(GlosarioImportExcelRequest $request)
    {
        return Glossary::importFromFile($request->validated());
    }

    public function carreerCategories()
    {
        $modulos = Abconfig::getModulesForSelect();

        $carreras = Carrera::with('glosario_categorias:id,nombre')->where('estado', 1)
                            ->get(['id', 'config_id', 'nombre']);

        $carreras = $carreras->groupBy('config_id');

        $categorias = Taxonomy::getDataForSelect('glosario', 'categoria');

        return $this->success(get_defined_vars());
    }

    public function carreerCategoriesStore(Request $request)
    {
        return Glossary::storeCarreerCategories($request->all());
    }

    public function create()
    {
        $selects = $this->getSelectsForForm();
        $modulos = Glossary::getModulesWithCode();

        return $this->success(get_defined_vars());
    }

    public function getSelectsForForm()
    {
        $selects = config('data.glosario.selects');

        foreach ($selects as $key => $select)
        {
            $selects[$key]['list'] = Taxonomy::getDataForSelect('glosario', $select['key']);
        }

        return $selects;
    }

    /**
     * Process request to create new glossary
     *
     * @param GlossaryStoreRequest $request
     * @return JsonResponse
     */
    public function store(GlossaryStoreRequest $request)
    {
        $data = $request->validated();

        $result = Glossary::storeRequest($data);

        $message = ($result['status'] === 'success')
                    ? 'Glosario creado correctamente.'
                    : $result['message'];

        return $this->success(['msg' => $message]);
    }

    public function edit(Glossary $glosario)
    {
        $glosario->load('laboratorio', 'categoria', 'jerarquia', 'advertencias', 'condicion_de_venta', 'via_de_administracion', 'grupo_farmacologico', 'dosis_adulto', 'dosis_nino', 'recomendacion_de_administracion', 'principios_activos', 'contraindicaciones', 'interacciones', 'reacciones', 'modulos');

        $selects = $this->getSelectsForForm();
        $modulos = Glossary::getModulesWithCode($glosario->modulos);

        return $this->success(get_defined_vars());
    }

    public function update(Glossary $glosario, GlossaryStoreRequest $request)
    {
        $data = $request->validated();

        $result = Glossary::storeRequest($data, $glosario);

        return $this->success(['msg' => 'Glosario actualizado correctamente.']);
    }

    public function destroy(Glossary $glosario)
    {
        $glosario->delete();

        return $this->success(['msg' => 'Glosario eliminado correctamente.']);
    }

    public function status(Glossary $glosario, Request $request)
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
