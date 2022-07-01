<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Http\Requests\GrupoStoreRequest;
use Illuminate\Http\Request;

class GrupoController extends Controller
{

    public function usuarios(Grupo $grupo)
    {
        $usuarios = $grupo->usuarios()->paginate();
        // return $grupos;

        return view('grupos.usuarios', compact('grupo','usuarios'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('q')) {
            $question = $request->input('q');
            // return $question;
            $grupos = Grupo::where('nombre', 'like', '%'.$question.'%')->paginate();
        }else{
            $grupos = Grupo::paginate();
        }
        return view('grupos.index', compact('grupos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('grupos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GrupoStoreRequest $request)
    {
        $grupo = Grupo::create($request->all());

        // return redirect()->route('grupos.edit', $grupo->id)
        //         ->with('info', 'Grupoo guardado con éxito');
        return redirect()->route('grupos.index')
                ->with('info', 'Grupo guardado con éxito');
    }

    public function edit(Grupo $grupo)
    {
        return view('grupos.edit', compact('grupo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Grupo  $grupo
     * @return \Illuminate\Http\Response
     */
    public function update(GrupoStoreRequest $request, Grupo $grupo)
    {
        $grupo->update($request->all());

        // return redirect()->route('grupos.edit', $grupo->id)
        //         ->with('info', 'Grupo actualizado con éxito');
        return redirect()->route('grupos.index')
                ->with('info', 'Grupo guardado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Grupo  $grupo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Grupo $grupo)
    {
        $grupo->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
