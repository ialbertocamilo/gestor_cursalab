<?php

namespace App\Http\Controllers;

use App\Http\Requests\VademecumImportExcelRequest;
use App\Http\Requests\VademecumStoreRequest;
use App\Http\Resources\VademecumCategoriaResource;
use App\Http\Resources\VademecumResource;
use App\Models\Abconfig;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Media;
use App\Models\Taxonomy;
use App\Models\Vademecum;
use App\Models\Workspace;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VademecumController extends Controller
{
    /*

        Methods for Vademecum routes

     -------------------------------------------------------------------------*/

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
        $modules = Workspace::loadSubWorkspaces(['criterion_value_id as id', 'name']);
        $categories = Taxonomy::vademecumCategory(
            get_current_workspace()->id
        )->get();

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to for main view
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {

        $vademecum = Vademecum::search($request);
        $modules = Workspace::loadSubWorkspaces(['criterion_value_id as id', 'name']);
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
     * Process request to load data for create form
     *
     * @return JsonResponse
     */
    public function create()
    {
        $modules = Workspace::loadSubWorkspaces(['criterion_value_id as id', 'name']);
        $categories = Taxonomy::getDataForSelectWorkspace('vademecum', 'categoria');
        $subcategories = Taxonomy::getDataForSelectWorkspace('vademecum', 'subcategoria');

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

        $files = isset($data['file_media']) ? [$data['file_media']] : [];
        $hasStorageAvailable = Media::validateStorageByWorkspace($files);

        if (!$hasStorageAvailable) {

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

        } else {

            return response()->json([
                'msg' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }
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

        $modules = Workspace::loadSubWorkspaces(['criterion_value_id as id', 'name']);
        $categories = Taxonomy::getDataForSelectWorkspace('vademecum', 'categoria');
        $subcategories = Taxonomy::getDataForSelectWorkspace('vademecum', 'subcategoria');

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to update the specified record
     *
     * @param Vademecum $vademecum
     * @param VademecumStoreRequest $request
     * @return JsonResponse
     */
    public function update(Vademecum $vademecum, VademecumStoreRequest $request)
    {
        $data = $request->validated();

        $files = isset($data['file_media']) ? [$data['file_media']] : [];
        $hasStorageAvailable = Media::validateStorageByWorkspace($files);

        if ($hasStorageAvailable) {

            $data = Media::requestUploadFileForId(
                $data, 'media', $data['name'] ?? null
            );

            $result = Vademecum::storeRequest($data, $vademecum);

            // return $this->response($result);
            return $this->success(['msg' => 'Vademecum actualizado correctamente.']);

        } else {

            return response()->json([
                'msg' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }
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

    // todo: check usage
    public function response($result, $route = 'vademecum.index')
    {
        if ($result['status'] == 'error')
            return redirect()->back()
                ->with('info', $result['message']);

        return redirect()->route($route)
            ->with('info', $result['message']);
    }

    // todo: check usage
    public function categorias(Request $request)
    {
        $query = Taxonomy::categoriaVademecum();

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");
        $categorias_vademecum = $query->paginate(15);

        return view('vademecum.categorias.index', compact('categorias_vademecum'));
    }

    /*

        Methods for categories routes

    -------------------------------------------------------------------------*/

    // todo: check usage
    public function categorias_create()
    {
        // return view('vademecum.categorias.create');
    }

    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function categorias_search(Request $request)
    {

        $query = Taxonomy::vademecumCategory(get_current_workspace()->id)
                         ->withCount('children');

        if ($request->q)
            $query->where('name', 'like', "%{$request->q}%");

        $field = $request->sortBy ?? 'name';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);
        $categorias_vademecum = $query->paginate($request->paginate);

        VademecumCategoriaResource::collection($categorias_vademecum);

        return $this->success($categorias_vademecum);
    }

    /**
     * Process request to store a record
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function categorias_store(Request $request)
    {
        Taxonomy::create([
            'type' => 'categoria',
            'group' => 'vademecum',
            'name' => $request->name,
            'workspace_id' => get_current_workspace()->id,
            'active' => 1
        ]);


        return $this->success(['msg' => 'Categoría creada correctamente.']);
    }

    /**
     * Process request to load data for edit form
     *
     * @param Taxonomy $categoria
     * @return JsonResponse
     */
    public function categorias_edit(Taxonomy $categoria)
    {
        return $this->success(get_defined_vars());
    }

    /**
     * Process request to update the specified record
     *
     * @param Request $request
     * @param Taxonomy $categoria
     * @return JsonResponse
     */
    public function categorias_update(Request $request, Taxonomy $categoria)
    {
        $categoria->update($request->all());

        return $this->success(['msg' => 'Categoría actualizada correctamente.']);
    }

    /**
     * Process request to delete record
     *
     * @param Taxonomy $categoria
     * @return JsonResponse
     */
    public function categorias_destroy(Taxonomy $categoria)
    {
        $categoria->delete();
        return $this->success(['msg' => 'Categoría eliminada correctamente.']);
    }

    /*

        Methods for sub categories routes

    --------------------------------------------------------------------------*/

    // todo: check usage
    public function subcategorias(Taxonomy $categoria, Request $request)
    {
        $categoria = Taxonomy::find($category_id);
        $query = Taxonomy::subcategoriaVademecum($category_id);

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");

        $subcategorias = $query->paginate(15);

        return view('vademecum.categorias.subcategorias.index', compact('subcategorias', 'categoria'));
    }

    /**
     * Process request to load records filtered according search term
     *
     * @param Taxonomy $categoria
     * @param Request $request
     * @return JsonResponse
     */
    public function subcategorias_search(Taxonomy $categoria, Request $request)
    {
        $query = Taxonomy::vademecumSubcategory(
            get_current_workspace()->id, $categoria->id
        );

        if ($request->q)
            $query->where('name', 'like', "%{$request->q}%");

        $field = $request->sortBy ?? 'name';
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

    /**
     * Process request to store a record
     *
     * @param Taxonomy $categoria
     * @param Request $request
     * @return JsonResponse
     */
    public function subcategorias_store(Taxonomy $categoria, Request $request)
    {
        Taxonomy::create([
            'group' => 'vademecum',
            'type' => 'subcategoria',
            'name' => $request->name,
            'parent_id' => $categoria->id,
            'workspace_id' => get_current_workspace()->id,
            'active' => 1
        ]);

        return $this->success(['msg' => 'Sub categoría creada correctamente.']);
    }

    /**
     * Process request to load data for edit form
     *
     * @param Taxonomy $categoria
     * @param Taxonomy $subcategoria
     * @return JsonResponse
     */
    public function subcategorias_edit(Taxonomy $categoria, Taxonomy $subcategoria)
    {
        return $this->success(get_defined_vars());
    }

    /**
     * Process request to update the specified record
     *
     * @param Request $request
     * @param Taxonomy $categoria
     * @param Taxonomy $subcategoria
     * @return JsonResponse
     */
    public function subcategorias_update(Request $request, Taxonomy $categoria, Taxonomy $subcategoria)
    {
        $subcategoria->update($request->all());

        return $this->success(['msg' => 'Sub Categoría actualizada correctamente.']);
    }

    /**
     * Process request to delete record
     *
     * @param Taxonomy $categoria
     * @param Taxonomy $subcategoria
     * @return JsonResponse
     */
    public function subcategorias_destroy(Taxonomy $categoria, Taxonomy $subcategoria)
    {
        $subcategoria->delete();

        return $this->success(['msg' => 'Sub Categoría eliminada correctamente.']);
    }

    // todo: check usage
    public function getSubCategoriaByCategoriaId($category_id)
    {
        $subcategorias = Taxonomy::subcategoriaVademecum($category_id)
                                    ->pluck('nombre', 'id')
                                    ->toArray();

        return response()->json(compact('subcategorias'), 200);
    }

    // todo: check usage
    public function getSubCategoriesByCategory(Request $request)
    {
        $subcategories = Taxonomy::getSelectChildrenData(
            $request->category_id, 'vademecum', 'subcategoria'
        );

        return $this->success(get_defined_vars());
    }

    /*

    Methods for imports routes

    --------------------------------------------------------------------------*/

    // todo: check usage
    public function import()
    {
        $modules = Abconfig::where('active', 1)->pluck('etapa', 'id')->toArray();
        $categorias = Taxonomy::getDataForSelect('vademecum', 'categoria');

        return view('vademecum.import', compact('modules', 'categorias'));
    }

    // todo: check usage
    public function importFile(VademecumImportExcelRequest $request)
    {
        $result = Vademecum::importFromFile($request->validated());

        return $this->response($result);
    }
}
