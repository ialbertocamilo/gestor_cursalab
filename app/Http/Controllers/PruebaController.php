<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Categoria;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PruebaStoreRequest;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class PruebaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pruebas = Prueba::paginate();

        return view('pruebas.index', compact('pruebas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pruebas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PruebaStoreRequest $request)
    {
        $prueba = Prueba::create($request->all());

        return redirect()->route('pruebas.edit', $prueba->id)
                ->with('info', 'Pruebao guardado con éxito');
    }

    public function edit(Prueba $prueba)
    {
        return view('pruebas.edit', compact('prueba'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Prueba  $prueba
     * @return \Illuminate\Http\Response
     */
    public function update(PruebaStoreRequest $request, Prueba $prueba)
    {
        $prueba->update($request->all());

        return redirect()->route('pruebas.edit', $prueba->id)
                ->with('info', 'Pruebao actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Prueba  $prueba
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prueba $prueba)
    {
        $prueba->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
