<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use App\Http\Requests\CargoStoreRequest;
use App\Http\Resources\CargoResource;

class CargoController extends Controller
{
    public function search(Request $request)
    {
        $cargos = Cargo::search($request);

        CargoResource::collection($cargos);

        return $this->success($cargos);
    }

    // public function index(){
    //     return view('cargos.index');
    // }

    // public function getInitialData()
    // {
    //     $cargos = Cargo::select('id','nombre')->paginate(15);
    //     return response()->json([
    //         'data' => $cargos->items(), 
    //         'pagina_actual' => $cargos->currentPage(), 
    //         'total_paginas' => $cargos->lastPage(),
    //     ], 200);
    // }

    // public function insert_or_edit(Request $request){
    //     $data = $request->all();
    //     $cargo = Cargo::updateOrCreate(
    //         ['id' => $data['id']],
    //         ['nombre' => $data['nombre']]
    //     );
    //     return response()->json('created or inserted ok');
    // }

    // public function delete_botica($id){
    //     $carg = Cargo::where('id',$id)->delete();
    //     if($carg){
    //         return response()->json('deleted');
    //     }
    //     return response()->json('error');
    // }
    // public function buscar_cargo($cargo_nombre){
    //     $boticas = Cargo::where('nombre','like','%'.$cargo_nombre.'%')->paginate(15);
    //     return response()->json([
    //         'data' => $boticas->items(), 
    //         'pagina_actual' => $boticas->currentPage(), 
    //         'total_paginas' => $boticas->lastPage(),
    //     ], 200);
    // }

    public function create()
    {
        // $modules = Abconfig::getModulesForSelect();
        // $destinos = config('data.destinos');

        // return $this->success(get_defined_vars());
    }
    
    public function getFormSelects()
    {
        // $modules = Abconfig::select('id', 'etapa as nombre')->get();

        // return $this->success(get_defined_vars());
    }

    public function store(CargoStoreRequest $request)
    {
        $data = $request->validated();

        $cargo = Cargo::create($data);
        
        return $this->success(['msg' => 'Cargo creado correctamente.']);
    }
    
    public function edit(Cargo $cargo)
    {
        return $this->success(get_defined_vars());
    }

    public function update(Cargo $cargo, CargoStoreRequest $request)
    {
        $data = $request->validated();
    
        $cargo->update($data);

        return $this->success(['msg' => 'Cargo actualizado correctamente.']);
    }

    public function destroy(Cargo $cargo)
    {
        $cargo->delete();

        return $this->success(['msg' => 'Cargo eliminado correctamente.']);
    }
}
