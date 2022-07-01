<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Http\Requests\PermisoStoreRequest;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
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
            $permisos = Permiso::where('name', 'like', '%'.$question.'%')->paginate();
        }else{
            $permisos = Permiso::paginate();
        }
        return view('permisos.index', compact('permisos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('permisos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermisoStoreRequest $request)
    {
        $permiso = Permiso::create($request->all());
        return redirect()->route('permisos.index')
                ->with('info', 'Permiso guardado con éxito');
    }

    public function edit(Permiso $permiso)
    {
        return view('permisos.edit', compact('permiso'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Permiso  $permiso
     * @return \Illuminate\Http\Response
     */
    public function update(PermisoStoreRequest $request, Permiso $permiso)
    {
        $permiso->update($request->all());

        return redirect()->route('permisos.index')
                ->with('info', 'Permisoo actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Permiso  $permiso
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permiso $permiso)
    {
        $permiso->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
