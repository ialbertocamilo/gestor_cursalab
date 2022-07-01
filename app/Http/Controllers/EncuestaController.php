<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Posteo;
use App\Models\Media;

use App\Http\Requests\EncuestaStoreRequest;
use Illuminate\Http\Request;
use App\Http\Resources\EncuestaResource;

class EncuestaController extends Controller
{
    public function search(Request $request)
    {
        $encuestas = Encuesta::search($request);

        EncuestaResource::collection($encuestas);

        return $this->success($encuestas);
    }

    public function preguntas(Encuesta $encuesta)
    {
        $encuestas_preguntas = $encuesta->encuestas()->paginate();
        // return $encuestas_preguntas;

        return view('encuestas.preguntas', compact('encuesta','encuestas_preguntas'));
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
            $encuestas = Encuesta::where('titulo', 'like', '%'.$question.'%')->paginate();
        }else{
        $encuestas = Encuesta::paginate();
       }
        return view('encuestas.index', compact('encuestas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {    
        $secciones = config('data.encuestas.secciones');
        $tipos = config('data.encuestas.tipos');

        return $this->success(get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EncuestaStoreRequest $request)
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'imagen');

        $encuesta = Encuesta::create($data);

        $msg = 'Encuesta creada correctamente.';
        
        return $this->success(compact('msg'));
    }

    public function edit(Encuesta $encuesta)
    {
        $secciones = config('data.encuestas.secciones');
        $tipos = config('data.encuestas.tipos');

        return $this->success(get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Encuesta  $encuesta
     * @return \Illuminate\Http\Response
     */
    public function update(EncuestaStoreRequest $request, Encuesta $encuesta)
    {
        $data = $request->validated();
    
        $data = Media::requestUploadFile($data, 'imagen');

        $encuesta->update($data);

        $msg = 'Encuesta actualizada correctamente.';

        return $this->success(compact('msg'));
    }

    public function status(Encuesta $encuesta, Request $request)
    {
        $encuesta->update(['estado' => !$encuesta->estado]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Encuesta  $encuesta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Encuesta $encuesta)
    {
        // $encuesta->preguntas()->delete();

        if ($encuesta->countCoursesRelated() > 0)
            return $this->error('Una encuesta asociada a uno o más cursos no puede eliminarse.');

        $encuesta->delete();

        return $this->success(['msg' => 'Encuesta eliminada correctamente.']);
    }
}
