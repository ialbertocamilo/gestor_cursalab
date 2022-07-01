<?php

namespace App\Http\Controllers;

use App\Models\Post_electivo;
use App\Models\Abconfig;
use App\Http\Requests\Post_electivoStoreRequest;
use Illuminate\Http\Request;

class Post_electivoController extends Controller
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
            $post_electivos = Post_electivo::where('nombre', 'like', '%'.$question.'%')->paginate();
        }else{
            $post_electivos = Post_electivo::paginate();
        }
        return view('post_electivos.index', compact('post_electivos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $config_array = Abconfig::select('id','etapa')->pluck('etapa','id' );
        return view('post_electivos.create', compact('config_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Post_electivoStoreRequest $request)
    {
        // Mover imagen a carpea public/images
        $imagen= $request->file('imagen');
        $new_nombre = rand() . '.' . $imagen->getClientOriginalExtension();
        $imagen->move(public_path('images'), $new_nombre);
        
         //cambiar valor de name en el request
        $data = $request->all();
        $data['imagen'] = 'images/'.$new_nombre;

        if ($request->has('archivo')) {
            // Mover archivo a carpea public/images
            $archivo= $request->file('archivo');
            $new_nombre = rand() . '.' . $archivo->getClientOriginalExtension();
            $archivo->move(public_path('archivos'), $new_nombre);
            
             //cambiar valor de name en el request
            $data['archivo'] = 'archivos/'.$new_nombre;
        }


        $post_electivo = Post_electivo::create($data);

        return redirect()->route('post_electivos.index')
                ->with('info', 'Post_electivo guardado con éxito');
    }
    
    public function edit(Post_electivo $post_electivo)
    {
        $config_array = Abconfig::select('id','etapa')->pluck('etapa','id' );
        return view('post_electivos.edit', compact('post_electivo','config_array'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post_electivo  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Post_electivoStoreRequest $request, Post_electivo $post_electivo)
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
            \File::delete(public_path().'/'.$post_electivo->imagen);
        }

        if ($request->has('archivo')) {
            // Mover archivon a carpea public/archivos
            $archivo= $request->file('archivo');
            $new_nombre = rand() . '.' . $archivo->getClientOriginalExtension();
            $archivo->move(public_path('archivo'), $new_nombre);
            
             //cambiar valor de name en el request
            $data['archivo'] = 'archivo/'.$new_nombre;

                    //eliminar archivon anterior
            \File::delete(public_path().'/'.$post_electivo->archivo);
        }



        $post_electivo->update($data);

        return redirect()->route('post_electivos.index')
                ->with('info', 'Post_electivo actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post_electivo  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post_electivo $post_electivo)
    {
        \File::delete(public_path().'/'.$post_electivo->imagen);
        $post_electivo->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
