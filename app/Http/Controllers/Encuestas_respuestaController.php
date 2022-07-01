<?php

namespace App\Http\Controllers;

use App\Models\Encuestas_respuesta;
use App\Models\Usuario;
use App\Models\Pregunta;

use App\Http\Requests\Encuestas_respuestaStoreRequest;
use Illuminate\Http\Request;

class Encuestas_respuestaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $encuestas_respuestas = Encuestas_respuesta::paginate();

        return view('encuestas_respuestas.index', compact('encuestas_respuestas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    { 
        $pregunta_array = Pregunta::select('id','pregunta')->pluck('pregunta','id' );
        $user_array = Usuario::select('id', \DB::raw("CONCAT(COALESCE(nombre,''),' ',COALESCE(ap_paterno,'')) as nombres"))->pluck('nombres','id');
        return view('encuestas_respuestas.create', compact('user_array', 'pregunta_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Encuestas_respuestaStoreRequest $request)
    {
        $encuestas_respuesta = Encuestas_respuesta::create($request->all());

        return redirect()->route('encuestas_respuestas.edit', $encuestas_respuesta->id)
                ->with('info', 'Encuestas_respuesta guardado con éxito');
    }

    public function edit(Encuestas_respuesta $encuestas_respuesta)
    {
        $pregunta_array = Pregunta::select('id','pregunta')->pluck('pregunta','id' );
        $user_array = Usuario::select('id', \DB::raw("CONCAT(COALESCE(nombre,''),' ',COALESCE(ap_paterno,'')) as nombres"))->pluck('nombres','id');
        return view('encuestas_respuestas.edit', compact('encuestas_respuesta', 'user_array', 'pregunta_array'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Encuestas_respuesta  $encuestas_respuesta
     * @return \Illuminate\Http\Response
     */
    public function update(Encuestas_respuestaStoreRequest $request, Encuestas_respuesta $encuestas_respuesta)
    {
        $encuestas_respuesta->update($request->all());

        return redirect()->route('encuestas_respuestas.edit', $encuestas_respuesta->id)
                ->with('info', 'Encuestas_respuestao actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Encuestas_respuesta  $encuestas_respuesta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Encuestas_respuesta $encuestas_respuesta)
    {
        $encuestas_respuesta->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
