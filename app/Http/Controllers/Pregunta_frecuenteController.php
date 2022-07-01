<?php

namespace App\Http\Controllers;

use App\Models\Pregunta_frecuente;
use App\Models\SortingModel;
use Illuminate\Http\Request;
use App\Http\Requests\Pregunta_frecuenteStoreRequest;
use App\Http\Resources\PreguntaFrecuenteResource;

class Pregunta_frecuenteController extends Controller
{
    public function search(Request $request)
    {
        $preguntas_frecuentes = Pregunta_frecuente::search($request);

        PreguntaFrecuenteResource::collection($preguntas_frecuentes);

        return $this->success($preguntas_frecuentes);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // if ($request->has('q')) {
        //     $question = $request->input('q');
        //     // return $question;
        //     $pregunta_frecuentes = Pregunta_frecuente::where('pregunta', 'like', '%'.$question.'%')->paginate();
        // }else{
        //     $pregunta_frecuentes = Pregunta_frecuente::paginate();
        // }
        // return view('pregunta_frecuentes.index', compact('pregunta_frecuentes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $default_order = SortingModel::getNextItemOrderNumber(Pregunta_frecuente::class);

        return $this->success(get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Pregunta_frecuenteStoreRequest $request)
    {
        $pregunta_frecuente = Pregunta_frecuente::create($request->validated());

        SortingModel::reorderItems($pregunta_frecuente);

        return $this->success(['msg' => 'Pregunta frecuente creada correctamente.']);
    }

    public function edit(Pregunta_frecuente $pregunta_frecuente)
    {
        $pregunta_frecuente->default_order = SortingModel::getLastItemOrderNumber(Pregunta_frecuente::class);

        return $this->success(get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Pregunta_frecuente  $pregunta_frecuente
     * @return \Illuminate\Http\Response
     */
    public function update(Pregunta_frecuenteStoreRequest $request, Pregunta_frecuente $pregunta_frecuente)
    {
        $last_order = $pregunta_frecuente->orden;

        $pregunta_frecuente->update($request->validated());

        SortingModel::reorderItems($pregunta_frecuente, [], $last_order);

        return $this->success(['msg' => 'Pregunta frecuente actualizada correctamente.']);
    }

    public function status(Pregunta_frecuente $pregunta_frecuente, Request $request)
    {
        $pregunta_frecuente->update(['estado' => !$pregunta_frecuente->estado]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Pregunta_frecuente  $pregunta_frecuente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pregunta_frecuente $pregunta_frecuente)
    {
        $pregunta_frecuente->delete();

        return $this->success(['msg' => 'Pregunta frecuente eliminada correctamente.']);
    }
}
