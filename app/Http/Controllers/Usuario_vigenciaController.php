<?php

namespace App\Http\Controllers;

use App\Models\Usuario_vigencia;
use App\Models\Usuario;
use App\Http\Requests\Usuario_vigenciaStoreRequest;
use Illuminate\Http\Request;

class Usuario_vigenciaController extends Controller
{
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
        //     $usuario_vigencias = Usuario_vigencia::where('name', 'like', '%'.$question.'%')->paginate();
        // }else{
            $usuario_vigencias = Usuario_vigencia::paginate();
        // }
        return view('usuario_vigencias.index', compact('usuario_vigencias'));
     }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $user_array = Usuario::select('id', \DB::raw("CONCAT(COALESCE(nombre,''),' ',COALESCE(ap_paterno,'')) as nombres"))->pluck('nombres','id');
        $user_array = Usuario::select('id', \DB::raw("nombre as nombres"))->pluck('nombres','id');
// return $user_array;
        return view('usuario_vigencias.create', compact('user_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Usuario_vigenciaStoreRequest $request)
    {
        $usuario_vigencia = Usuario_vigencia::create($request->all());

        return redirect()->route('usuario_vigencias.index')
                ->with('info', 'Usuario_vigenciao guardado con éxito');
    }

    public function edit(Usuario_vigencia $usuario_vigencia)
    {
        // $user_array = Usuario::select('id', \DB::raw("CONCAT(COALESCE(nombre,''),' ',COALESCE(ap_paterno,'')) as nombres"))->pluck('nombres','id');
        $user_array = Usuario::select('id', \DB::raw("nombre as nombres"))->pluck('nombres','id');
        return view('usuario_vigencias.edit', compact('usuario_vigencia', 'user_array'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Usuario_vigencia  $usuario_vigencia
     * @return \Illuminate\Http\Response
     */
    public function update(Usuario_vigenciaStoreRequest $request, Usuario_vigencia $usuario_vigencia)
    {
        $usuario_vigencia->update($request->all());

        return redirect()->route('usuario_vigencias.index')
                ->with('info', 'Usuario_vigencia actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Usuario_vigencia  $usuario_vigencia
     * @return \Illuminate\Http\Response
     */
    public function destroy(Usuario_vigencia $usuario_vigencia)
    {
        $usuario_vigencia->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
