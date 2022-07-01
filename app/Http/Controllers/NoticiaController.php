<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Http\Requests\NoticiaStoreRequest;
use Illuminate\Http\Request;

class NoticiaController extends Controller
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
            $noticias = Noticia::where('nombre', 'like', '%'.$question.'%')->paginate();
        }else{
            $noticias = Noticia::paginate();
        }
        return view('noticias.index', compact('noticias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('noticias.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NoticiaStoreRequest $request)
    {
        // Mover imagen a carpea public/images
        $imagen= $request->file('imagen');
        $new_nombre = rand() . '.' . $imagen->getClientOriginalExtension();
        $imagen->move(public_path('images'), $new_nombre);
        
         //cambiar valor de name en el request
        $data = $request->all();
        $data['imagen'] = 'images/'.$new_nombre;

        $noticia = Noticia::create($data);

        return redirect()->route('noticias.index')
                ->with('info', 'Noticia guardado con éxito');
    }
    
    public function edit(Noticia $noticia)
    {
        return view('noticias.edit', compact('noticia'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Noticia  $post
     * @return \Illuminate\Http\Response
     */
    public function update(NoticiaStoreRequest $request, Noticia $noticia)
    {
        $data = $request->all();

        if ($request->has('imagen')) {
            // Mover imagenn a carpea public/imagens
            $imagen= $request->file('imagen');
            $new_nombre = rand() . '.' . $imagen->getClientOriginalExtension();
            $imagen->move(public_path('images'), $new_nombre);
            
             //cambiar valor de name en el request
            $data['imagen'] = 'images/'.$new_nombre;

                    //eliminar imagenn anterior
            \File::delete(public_path().'/'.$noticia->imagen);
        }



        $noticia->update($data);

        return redirect()->route('noticias.index')
                ->with('info', 'Noticia actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Noticia  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Noticia $noticia)
    {
        \File::delete(public_path().'/'.$noticia->imagen);
        $noticia->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
