<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Abconfig;
use App\Models\Taxonomia;
use App\Models\Vademecum;

use Illuminate\Http\Request;

use App\Http\Resources\VademecumResource;
use App\Http\Resources\VademecumCategoriaResource;
use App\Http\Resources\VademecumSubcategoriaResource;

use App\Http\Requests\VademecumStoreRequest;
use App\Http\Requests\VademecumImportExcelRequest;

class VademecumController extends Controller
{
    public function search(Request $request)
    {
        $vademecum = Vademecum::search($request, false, $request->paginate);

        VademecumResource::collection($vademecum);

        return $this->success($vademecum);
    }

    public function getListSelects()
    {
        $modulos = Abconfig::getModulesForSelect();
        $categorias = Taxonomia::getDataForSelect('vademecum', 'categoria');

        return $this->success(get_defined_vars());
    }

    public function index(Request $request)
    {
//        dd($request->all());
        $vademecum = Vademecum::search($request);

        $modulos = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        $categorias = Taxonomia::getDataForSelect('vademecum', 'categoria');
        $subcategorias = [];
        if ($request->subcategoria_id){
            $subcategorias = Taxonomia::subcategoriaVademecum($request->categoria_id)->pluck('nombre', 'id')->toArray();
        }

        return view('vademecum.index', compact('vademecum', 'modulos', 'categorias', 'subcategorias'));
    }

    public function import()
    {
        $modules = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        $categorias = Taxonomia::getDataForSelect('vademecum', 'categoria');

        return view('vademecum.import', compact('modules', 'categorias'));
    }

    public function importFile(VademecumImportExcelRequest $request)
    {
        $result = Vademecum::importFromFile($request->validated());

        return $this->response($result);
    }

    public function create()
    {
        // $modulos = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        // $categorias = Taxonomia::getDataForSelect('vademecum', 'categoria');
        // $subcategorias = [];

        // return view('vademecum.create', compact('categorias', 'modulos', 'subcategorias'));

        $modulos = Abconfig::getModulesForSelect();
        $categorias = Taxonomia::getDataForSelect('vademecum', 'categoria');
        $subcategorias = [];

        return $this->success(get_defined_vars());
    }

    public function store(VademecumStoreRequest $request)
    {
        // dd($request->all());
        $data = $request->validated();
        $data = Media::requestUploadFileForId($data, 'media', $data['nombre'] ?? null);
//        dd($data);
        $result = Vademecum::storeRequest($data);

        // return $this->response($result);
        return $this->success(['msg' => 'Vademecum creado correctamente.']);
    }

    public function edit(Vademecum $vademecum)
    {
//        dd($vademecum);
        // $elemento->load('modulos');

        // $modulos = Abconfig::where('estado', 1)->pluck('etapa', 'id')->toArray();
        // $categorias = Taxonomia::getDataForSelect('vademecum', 'categoria');
        // $subcategorias = Taxonomia::subcategoriaVademecum($elemento->categoria_id)
        //                             ->pluck('nombre', 'id')
        //                             ->toArray();


        // return view('vademecum.edit', compact('elemento', 'categorias', 'subcategorias', 'modulos'));

        $vademecum->load('modulos', 'categoria', 'subcategoria');

        $vademecum->scorm = $vademecum->media->file ?? null;

        $modulos = Abconfig::getModulesForSelect();
        $categorias = Taxonomia::getDataForSelect('vademecum', 'categoria');
        $subcategorias = Taxonomia::subcategoriaVademecum($vademecum->categoria_id)
                                    ->get(['nombre', 'id']);

        return $this->success(get_defined_vars());
    }

    public function update(Vademecum $vademecum, VademecumStoreRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFileForId($data, 'media', $data['nombre'] ?? null);
//        dd($data);

        $result = Vademecum::storeRequest($data, $vademecum);

        // return $this->response($result);
        return $this->success(['msg' => 'Vademecum actualizado correctamente.']);
    }

    public function status(Vademecum $vademecum, Request $request)
    {
        $vademecum->update(['estado' => !$vademecum->estado]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function destroy(Vademecum $vademecum)
    {
        $vademecum->delete();

        return $this->success(['msg' => 'Vademecum eliminado correctamente.']);
        // return $this->response(['status' => 'success', 'message' => 'Eliminado correctamente']);
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
        $query = Taxonomia::categoriaVademecum();

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");
        $categorias_vademecum = $query->paginate(15);

        return view('vademecum.categorias.index', compact('categorias_vademecum'));
    }

    public function categorias_search(Request $request)
    {
        $query = Taxonomia::withCount('child')->categoriaVademecum();

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
        Taxonomia::create([
            'tipo' => 'categoria',
            'grupo' => 'vademecum',
            'nombre' => $request->nombre,
            'estado' => 1
        ]);

        return $this->success(['msg' => 'Categoría creada correctamente.']);
    }

    public function categorias_edit(Taxonomia $categoria)
    {
        return $this->success(get_defined_vars());
    }

    public function categorias_update(Request $request, Taxonomia $categoria)
    {
        $categoria->update($request->all());

        return $this->success(['msg' => 'Categoría actualizada correctamente.']);
    }

    public function categorias_destroy(Taxonomia $categoria)
    {
        $categoria->delete();

        return $this->success(['msg' => 'Categoría eliminada correctamente.']);
    }

    public function subcategorias(Taxonomia $categoria, Request $request)
    {
        $categoria = Taxonomia::find($categoria_id);
        $query = Taxonomia::subcategoriaVademecum($categoria_id);

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");

        $subcategorias = $query->paginate(15);

        return view('vademecum.categorias.subcategorias.index', compact('subcategorias', 'categoria'));
    }

    public function subcategorias_search(Taxonomia $categoria, Request $request)
    {
        $query = Taxonomia::subcategoriaVademecum($categoria->id);

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");

        $field = $request->sortBy ?? 'nombre';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        $subcategorias_vademecum = $query->paginate($request->paginate);

        VademecumCategoriaResource::collection($subcategorias_vademecum);

        return $this->success($subcategorias_vademecum);
    }

    public function subcategorias_create(Taxonomia $categoria)
    {
        return $this->success(get_defined_vars());
    }

    public function subcategorias_store(Taxonomia $categoria, Request $request)
    {
        Taxonomia::create([
            'grupo' => 'vademecum',
            'tipo' => 'subcategoria',
            'nombre' => $request->nombre,
            'parent_taxonomia_id' => $categoria->id,
            'estado' => 1
        ]);

        return $this->success(['msg' => 'Sub Categoría creada correctamente.']);
    }

    public function subcategorias_edit(Taxonomia $categoria, Taxonomia $subcategoria)
    {
        return $this->success(get_defined_vars());
    }

    public function subcategorias_update(Request $request, Taxonomia $categoria, Taxonomia $subcategoria)
    {
        $subcategoria->update($request->all());

        return $this->success(['msg' => 'Sub Categoría actualizada correctamente.']);
    }

    public function subcategorias_destroy(Taxonomia $categoria, Taxonomia $subcategoria)
    {
        $subcategoria->delete();

        return $this->success(['msg' => 'Sub Categoría eliminada correctamente.']);
    }

    public function getSubCategoriaByCategoriaId($categoria_id)
    {
        $subcategorias = Taxonomia::subcategoriaVademecum($categoria_id)
                                    ->pluck('nombre', 'id')
                                    ->toArray();

        return response()->json(compact('subcategorias'), 200);
    }

    public function getSubCategoriesByCategory(Request $request)
    {
        $subcategorias = Taxonomia::subcategoriaVademecum($request->categoria_id)
                                    ->get(['nombre', 'id']);

        return $this->success(get_defined_vars());
    }
}
