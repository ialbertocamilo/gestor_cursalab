<?php

namespace App\Http\Controllers;

use App\Http\Requests\GlosarioImportExcelRequest;
use App\Http\Requests\GlossaryStoreRequest;
use App\Http\Resources\GlossaryResource;
use App\Models\Abconfig;
use App\Models\Carrera;
use App\Models\Criterion;
use App\Models\Glossary;
use App\Models\Taxonomy;
use App\Models\Vademecum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlossaryController extends Controller
{

    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $glossaries = Glossary::search($request, false, $request->paginate);
        GlossaryResource::collection($glossaries);
        return $this->success($glossaries);
    }

    /**
     * Get items list for select inputs
     *
     * @return JsonResponse
     */
    public function getListSelects()
    {
        $modules = Criterion::getValuesForSelect('module');
        $categories = Taxonomy::getDataForSelect('glosario', 'categoria');
        $laboratorios = Taxonomy::getDataForSelect('glosario', 'laboratorio');
        $principios_activos = Taxonomy::getDataForSelect('glosario', 'principio_activo');

        return $this->success(
            compact('modules', 'categories')
        );
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

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
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
     * Process request to save new record
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

    /**
     * Process request to delete record
     *
     * @param Glossary $glossary
     * @return JsonResponse
     */
    public function destroy(Glossary $glossary)
    {
        $glossary->delete();
        return $this->success(['msg' => 'Glosario eliminado correctamente.']);
    }

    /**
     * Process request to toggle poll status between 1 or 0
     *
     * @param Glossary $glossary
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Glossary $glossary, Request $request)
    {

        $glossary->update(['active' => !$glossary->active]);

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
