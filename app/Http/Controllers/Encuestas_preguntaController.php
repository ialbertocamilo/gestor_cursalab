<?php

namespace App\Http\Controllers;

use App\Models\Encuestas_pregunta;
use App\Models\Usuario;
use App\Models\Encuesta;

use App\Http\Requests\Encuestas_preguntaStoreRequest;
use Illuminate\Http\Request;
use App\Http\Resources\EncuestaPreguntaResource;

class Encuestas_preguntaController extends Controller
{
    public function search(Request $request)
    {
        $preguntas = Encuestas_pregunta::search($request);

        EncuestaPreguntaResource::collection($preguntas);

        return $this->success($preguntas);
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
        //     $encuestas_preguntas = Encuestas_pregunta::where('titulo', 'like', '%'.$question.'%')->paginate();
        // }else{
        //     $encuestas_preguntas = Encuestas_pregunta::paginate();
        // }
        // return view('encuestas_preguntas.index', compact('encuestas_preguntas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Encuesta $encuesta)
    { 
        // $encuesta_array = Encuesta::select('id','titulo')->pluck('titulo','id' );
        // return view('encuestas_preguntas.create', compact('encuesta_array'));

        $tipos = config('data.tipo-preguntas');

        return $this->success(get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Encuesta $encuesta, Encuestas_preguntaStoreRequest $request)
    {
        $data = $request->validated();
        $data['encuesta_id'] = $encuesta->id;
        $data['opciones'] = Encuestas_pregunta::setOptionsToStore($request->opciones ?? []);

        $encuestas_pregunta = Encuestas_pregunta::create($data);

        return $this->success(['msg' => 'Pregunta creada correctamente.']);
    }

    public function edit(Encuesta $encuesta, Encuestas_pregunta $encuestas_pregunta)
    {
        $tipos = config('data.tipo-preguntas');

        $key = array_search($encuestas_pregunta->tipo_pregunta, array_column($tipos, 'id'));

        $encuestas_pregunta->tipo_pregunta = $tipos[$key]; 
        $encuestas_pregunta->opciones = $encuestas_pregunta->formatOptions(); 
        
        return $this->success(get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Encuestas_pregunta  $encuestas_pregunta
     * @return \Illuminate\Http\Response
     */
    public function update(Encuesta $encuesta, Encuestas_pregunta $encuestas_pregunta, Encuestas_preguntaStoreRequest $request)
    {
        $data = $request->validated();
        $data['opciones'] = Encuestas_pregunta::setOptionsToStore($request->opciones ?? []);

        $encuestas_pregunta->update($data);

        return $this->success(['msg' => 'Pregunta actualizada correctamente.']);
    }

    public function status(Encuesta $encuesta, Encuestas_pregunta $encuestas_pregunta, Request $request)
    {
        $encuestas_pregunta->update(['estado' => !$encuestas_pregunta->estado]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Encuestas_pregunta  $encuestas_pregunta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Encuesta $encuesta, Encuestas_pregunta $encuestas_pregunta)
    {
        $encuestas_pregunta->delete();

        return $this->success(['msg' => 'Pregunta eliminada correctamente.']);
    }
}
