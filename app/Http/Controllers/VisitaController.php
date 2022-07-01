<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Http\Requests\VisitaStoreRequest;
use Illuminate\Http\Request;

class VisitaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $visitas = Visita::paginate();

        return view('visitas.index', compact('visitas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('visitas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VisitaStoreRequest $request)
    {
        $visita = Visita::create($request->all());

        return redirect()->route('visitas.edit', $visita->id)
                ->with('info', 'Visitao guardado con éxito');
    }

    public function edit(Visita $visita)
    {
        return view('visitas.edit', compact('visita'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Visita  $visita
     * @return \Illuminate\Http\Response
     */
    public function update(VisitaStoreRequest $request, Visita $visita)
    {
        $visita->update($request->all());

        return redirect()->route('visitas.edit', $visita->id)
                ->with('info', 'Visitao actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Visita  $visita
     * @return \Illuminate\Http\Response
     */
    public function destroy(Visita $visita)
    {
        $visita->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
