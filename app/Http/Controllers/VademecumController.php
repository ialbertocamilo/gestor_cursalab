<?php

namespace App\Http\Controllers;

use App\Http\Requests\VademecumImportExcelRequest;
use App\Http\Requests\VademecumStoreRequest;
use App\Http\Resources\VademecumCategoriaResource;
use App\Http\Resources\VademecumResource;
use App\Models\Abconfig;
use App\Models\Criterion;
use App\Models\Media;
use App\Models\Taxonomy;
use App\Models\Vademecum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VademecumController extends Controller
{
    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $vademecum = Vademecum::search($request, false, $request->paginate);

        VademecumResource::collection($vademecum);

        return $this->success($vademecum);
    }

    /**
     * Get items list for select inputs
     *
     * @return JsonResponse
     */
    public function getListSelects()
    {
        $modules = Criterion::getValuesForSelect('module');
        $categories = Taxonomy::getDataForSelect('vademecum', 'categoria');

        return $this->success(get_defined_vars());
    }

    public function index(Request $request)
    {

        $vademecum = Vademecum::search($request);

        $modules = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        $categories = Taxonomy::getDataForSelect('vademecum', 'categoria');
        $subcategories = [];

        if ($request->subcategory_id){
            $subcategories = Taxonomy::subcategoriaVademecum($request->category_id)
                                     ->pluck('name', 'id')
                                     ->toArray();
        }

        return view(
            'vademecum.index',
            compact('vademecum', 'modules', 'categories', 'subcategories')
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create()
    {
        $modules = Criterion::getValuesForSelect('module');
        $categories = Taxonomy::getDataForSelect('vademecum', 'categoria');
        $subcategories = Taxonomy::getDataForSelect('vademecum', 'subcategoria');

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to store a record
     *
     * @param VademecumStoreRequest $request
     * @return JsonResponse
     */
    public function store(VademecumStoreRequest $request)
    {

        $data = $request->validated();
        $data = Media::requestUploadFileForId(
            $data, 'media', $data['name'] ?? null
        );

        $result = Vademecum::storeRequest($data);
        if ($result['status'] === 'success') {
            $message = 'Vademecum creado correctamente.';
        } else {
            $message = $result['message'];
        }

        return $this->success(['msg' => $message]);
    }

    /**
     * Process request to load data for edit form
     *
     * @param Vademecum $vademecum
     * @return JsonResponse
     */
    public function edit(Vademecum $vademecum)
    {

        $vademecum->load('modules', 'category', 'subcategory');

        $vademecum->scorm = $vademecum->media->file ?? null;

        $modulos = Criterion::getValuesForSelect('module');
        $categorias = Taxonomy::getDataForSelect('vademecum', 'categoria');
        $subcategorias = Taxonomy::getDataForSelect('vademecum', 'subcategoria');

        return $this->success(get_defined_vars());
    }

    /**
     * Process to update the specified resource in storage.
     *
     * @param Vademecum $vademecum
     * @param VademecumStoreRequest $request
     * @return JsonResponse
     */
    public function update(Vademecum $vademecum, VademecumStoreRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFileForId(
            $data, 'media', $data['name'] ?? null
        );

        $result = Vademecum::storeRequest($data, $vademecum);

        // return $this->response($result);
        return $this->success(['msg' => 'Vademecum actualizado correctamente.']);
    }

    /**
     * Process request to toggle poll status between 1 or 0
     *
     * @param Vademecum $vademecum
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Vademecum $vademecum, Request $request)
    {
        $vademecum->update(['active' => !$vademecum->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Process request to delete record
     *
     * @param Vademecum $vademecum
     * @return JsonResponse
     */
    public function destroy(Vademecum $vademecum)
    {
        $vademecum->delete();
        return $this->success(['msg' => 'Vademecum eliminado correctamente.']);
    }

    public function response($result, $route = 'vademecum.index')
    {
        if ($result['status'] == 'error')
            return redirect()->back()
                ->with('info', $result['message']);

        return redirect()->route($route)
            ->with('info', $result['message']);
    }

    public function categorias(Request $request)
    {
        $query = Taxonomy::categoriaVademecum();

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");
        $categorias_vademecum = $query->paginate(15);

        return view('vademecum.categorias.index', compact('categorias_vademecum'));
    }

    public function categorias_search(Request $request)
    {
        $query = Taxonomy::withCount('child')->categoriaVademecum();

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");

        $field = $request->sortBy ?? 'nombre';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        $categorias_vademecum = $query->paginate($request->paginate);

        VademecumCategoriaResource::collection($categorias_vademecum);

        return $this->success($categorias_vademecum);
    }

    public function categorias_create()
    {
        // return view('vademecum.categorias.create');
    }

    public function categorias_store(Request $request)
    {
        Taxonomy::create([
            'tipo' => 'categoria',
            'grupo' => 'vademecum',
            'nombre' => $request->nombre,
            'estado' => 1
        ]);

        return $this->success(['msg' => 'Categoría creada correctamente.']);
    }

    public function categorias_edit(Taxonomy $categoria)
    {
        return $this->success(get_defined_vars());
    }

    public function categorias_update(Request $request, Taxonomy $categoria)
    {
        $categoria->update($request->all());

        return $this->success(['msg' => 'Categoría actualizada correctamente.']);
    }

    public function categorias_destroy(Taxonomy $categoria)
    {
        $categoria->delete();

        return $this->success(['msg' => 'Categoría eliminada correctamente.']);
    }

    public function subcategorias(Taxonomy $categoria, Request $request)
    {
        $categoria = Taxonomy::find($category_id);
        $query = Taxonomy::subcategoriaVademecum($category_id);

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");

        $subcategorias = $query->paginate(15);

        return view('vademecum.categorias.subcategorias.index', compact('subcategorias', 'categoria'));
    }

    public function subcategorias_search(Taxonomy $categoria, Request $request)
    {
        $query = Taxonomy::subcategoriaVademecum($categoria->id);

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");

        $field = $request->sortBy ?? 'nombre';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        $subcategorias_vademecum = $query->paginate($request->paginate);

        VademecumCategoriaResource::collection($subcategorias_vademecum);

        return $this->success($subcategorias_vademecum);
    }

    public function subcategorias_create(Taxonomy $categoria)
    {
        return $this->success(get_defined_vars());
    }

    public function subcategorias_store(Taxonomy $categoria, Request $request)
    {
        Taxonomy::create([
            'grupo' => 'vademecum',
            'tipo' => 'subcategoria',
            'nombre' => $request->nombre,
            'parent_taxonomia_id' => $categoria->id,
            'estado' => 1
        ]);

        return $this->success(['msg' => 'Sub Categoría creada correctamente.']);
    }

    public function subcategorias_edit(Taxonomy $categoria, Taxonomy $subcategoria)
    {
        return $this->success(get_defined_vars());
    }

    public function subcategorias_update(Request $request, Taxonomy $categoria, Taxonomy $subcategoria)
    {
        $subcategoria->update($request->all());

        return $this->success(['msg' => 'Sub Categoría actualizada correctamente.']);
    }

    public function subcategorias_destroy(Taxonomy $categoria, Taxonomy $subcategoria)
    {
        $subcategoria->delete();

        return $this->success(['msg' => 'Sub Categoría eliminada correctamente.']);
    }

    public function getSubCategoriaByCategoriaId($category_id)
    {
        $subcategorias = Taxonomy::subcategoriaVademecum($category_id)
                                    ->pluck('nombre', 'id')
                                    ->toArray();

        return response()->json(compact('subcategorias'), 200);
    }

    public function getSubCategoriesByCategory(Request $request)
    {
        $subcategories = Taxonomy::getSelectChildrenData(
            $request->category_id, 'vademecum', 'subcategoria'
        );

        return $this->success(get_defined_vars());
    }

    public function import()
    {
        $modules = Abconfig::where('active', 1)->pluck('etapa', 'id')->toArray();
        $categorias = Taxonomy::getDataForSelect('vademecum', 'categoria');

        return view('vademecum.import', compact('modules', 'categorias'));
    }

    public function importFile(VademecumImportExcelRequest $request)
    {
        $result = Vademecum::importFromFile($request->validated());

        return $this->response($result);
    }
}
