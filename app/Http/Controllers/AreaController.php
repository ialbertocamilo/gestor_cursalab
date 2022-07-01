<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Http\Requests\AreaStoreRequest;
use Illuminate\Http\Request;

class AreaController extends Controller
{

    // public function users(Area $area)
    // {
    //     $usuarios = $area->users()->paginate();
    //     // return $areas;

    //     return view('usuarios.index', compact('usuarios'));
    // }

    public function usuarios(Area $area)
    {
        $usuarios = $area->usuarios()->paginate();
        // return $perfils;

        return view('areas.usuarios', compact('area','usuarios'));
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
            $areas = Area::where('nombre', 'like', '%'.$question.'%')->paginate();
        }else{
            $areas = Area::paginate();
        }
        return view('areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('areas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AreaStoreRequest $request)
    {
        $area = Area::create($request->all());

        // return redirect()->route('areas.edit', $area->id)
        //         ->with('info', 'Area guardado con éxito');
                return redirect()->route('areas.index')
                ->with('info', 'Area guardada con éxito');
    }

    public function edit(Area $area)
    {
        return view('areas.edit', compact('area'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function update(AreaStoreRequest $request, Area $area)
    {
        $area->update($request->all());

        // return redirect()->route('areas.edit', $area->id)
        //         ->with('info', 'Area actualizado con éxito');
        return redirect()->route('areas.index')
                ->with('info', 'Area guardada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function destroy(Area $area)
    {
        $area->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
