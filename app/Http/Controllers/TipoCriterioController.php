<?php

namespace App\Http\Controllers;

use App\Models\TipoCriterio;
use App\Models\SortingModel;
use Illuminate\Http\Request;

use App\Http\Resources\TipoCriterioResource;
use App\Http\Requests\TipoCriterioStoreRequest;

class TipoCriterioController extends Controller
{
    public function search(Request $request)
    {
        $tipo_criterios = TipoCriterio::search($request);

        TipoCriterioResource::collection($tipo_criterios);

        return $this->success($tipo_criterios);
    }
    
    public function index()
    {
        // //Obteniendo datos de la tabla tipo criterios
        // //$tipo_criterios = TipoCriterio::all();
        // //contar las cantidad de criterios
        // $tipo_criterios = TipoCriterio::withCount('criterios')->orderBy('orden')->paginate();
        // /* \Log::info('message',[$query]); */
        // //lista de los criterios
        // $lista_criterios['0'] = 'Ninguno';
        // $tipo_criterios_data = TipoCriterio::pluck('nombre','id')->all();
        // foreach ($tipo_criterios_data as $key => $value) {
        //     $lista_criterios[$key] = $value;
        // }
        // return view('tipo_criterio.index',compact('tipo_criterios','tipo_criterios_data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $default_order = SortingModel::getNextItemOrderNumber(TipoCriterio::class);
        $tipos = config('data.tipo-criterios');

        return $this->success(get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TipoCriterioStoreRequest $request)
    {
        $data = $request->validated();
        $tipo_criterio = TipoCriterio::create($data);

        SortingModel::reorderItems($tipo_criterio);

        return $this->success(['msg' => 'Tipo Criterio creada correctamente.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TipoCriterio  $tipoCriterio
     * @return \Illuminate\Http\Response
     */
    public function show(TipoCriterio $tipo_criterio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TipoCriterio  $tipo_criterio
     * @return \Illuminate\Http\Response
     */
    public function edit(TipoCriterio $tipo_criterio)
    {
        $tipos = config('data.tipo-criterios');
        $tipo_criterio->default_order = SortingModel::getLastItemOrderNumber(TipoCriterio::class);

        return $this->success(get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TipoCriterio  $tipoCriterio
     * @return \Illuminate\Http\Response
     */
    public function update(TipoCriterioStoreRequest $request, TipoCriterio $tipo_criterio)
    {
        $data = $request->validated();
    
        $last_order = $tipo_criterio->orden;
        
        $tipo_criterio->update($data);

        SortingModel::reorderItems($tipo_criterio, [], $last_order);

        return $this->success(['msg' => 'Tipo Criterio actualizada correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TipoCriterio  $tipoCriterio
     * @return \Illuminate\Http\Response
     */
    public function destroy(TipoCriterio $tipoCriterio)
    {
        //
    }

    // public function changeOrder(TipoCriterio $tipoCriterio, TipoCriterio $new)
    // {
    //     SortingModel::changeItemsPosition($tipoCriterio, $new);

    //     return redirect()->back()->with('info', 'Orden actualizado correctamente');
    // }
}
