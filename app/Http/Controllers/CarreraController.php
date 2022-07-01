<?php

namespace App\Http\Controllers;

use App\Models\Abconfig;
use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Categoria;

use DB;
use App\Http\Requests\CarreraSR;
use Illuminate\Http\Request;

class CarreraController extends Controller
{

    public function ciclos(Abconfig $abconfig, Carrera $carrera, Request $request)
    {

        $ciclos = $carrera->ciclos()->paginate();
        $ciclos_id = $carrera->ciclos()->pluck('id');

        $categorias = Categoria::pluck('nombre','id');
        $cate_color = Categoria::pluck('color','id');

        $cursos = DB::table('cursos as c')
                        ->select('c.id','r.ciclo_id', 'c.nombre', 'c.imagen', 'c_evaluable', 'c.estado', 'c.categoria_id')
                        ->join('curricula AS r','c.id','=','r.curso_id')
                        // ->leftJoin('curso_encuesta AS e','c.id','=','e.curso_id')
                         ->whereIn('r.ciclo_id', $ciclos_id)
                         ->groupBy('r.curso_id')
                         ->orderBy('c.categoria_id','ASC')
                         ->orderBy('c.orden','ASC')
                         ->get();

        // return $cursos;

        return view('carreras.ciclos', compact('abconfig','carrera', 'ciclos', 'cursos', 'categorias', 'cate_color'));
    }

    // Carreras
    public function index()
    {
        $carreras = Carrera::orderBy('nombre')->get();
        $configs = Abconfig::select('id','etapa','estado')->get();

        return view('carreras.index', compact('carreras','configs'));
    }

    // Lista para select con Vue
    public function carreras_x_modulo(Request $request)
    {
        $config_id = $request->config_id;
        $carreras = Carrera::select('id','nombre')->where('config_id', $config_id)->where('estado', 1)->orderBy('nombre')->get();
        return $carreras;
    }

    public function create(Abconfig $abconfig)
    {
        $config_array = Abconfig::select('id','etapa')->where('id', $abconfig->id)->pluck('etapa','id' );
        return view('carreras.create', compact('config_array', 'abconfig'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Abconfig $abconfig, CarreraSR $request)
    {
        $data = $request->all();

        // if ($request->has('imagen')) {
        //     // Mover imagen a carpea public/images
        //     $image= $request->file('imagen');
        //     $new_name = rand() . '.' . $image->getClientOriginalExtension();
        //     $image->move(public_path('images'), $new_name);
        //     $data['imagen'] = 'images/'.$new_name;
        // }
        // // Archivo malla
        // if ($request->has('malla_archivo')) {
        //     $archivo= $request->file('malla_archivo');
        //     $new_nombre = rand() . '.' . $archivo->getClientOriginalExtension();
        //     $archivo->move(public_path('archivos'), $new_nombre);
        //     $data['malla_archivo'] = 'archivos/'.$new_nombre;
        // }

        if ($request->filled('imagen')) {
           $data['imagen'] = 'images/'.$request->imagen;
        }
        if ($request->filled('malla_archivo')) {
            $data['malla_archivo'] = 'archivos/'.$request->malla_archivo;
        }

        $carrera = Carrera::create($data);

        return redirect()->route('carreras.index')
                ->with('info', 'Registro guardado con éxito');
    }


    public function edit(Abconfig $abconfig, Carrera $carrera)
    {
        $config_array = Abconfig::select('id','etapa')->where('id', $abconfig->id)->pluck('etapa','id' );
        if ($carrera->imagen != "") {
            $carrera->imagen = str_replace("images/", "", $carrera->imagen);
        }

        if ($carrera->malla_archivo != "") {
            $carrera->malla_archivo = str_replace("archivos/", "", $carrera->malla_archivo);
        }
        return view('carreras.edit', compact('carrera','config_array', 'abconfig'));
    }

    public function update(Abconfig $abconfig, CarreraSR $request, Carrera $carrera)
    {
        // return $request;
        $data = $request->all();

        // if ($request->has('imagen')) {
        //     // Mover imagen a carpea public/images
        //     $image= $request->file('imagen');
        //     $new_name = rand() . '.' . $image->getClientOriginalExtension();
        //     $image->move(public_path('images'), $new_name);
            
        //      //cambiar valor de name en el request
        //     $data['imagen'] = 'images/'.$new_name;

        //     //eliminar imagen anterior
        //     \File::delete(public_path().'/'.$carrera->imagen);
        // }

        //         // Archivo malla
        // if ($request->has('malla_archivo')) {
        //     $archivo= $request->file('malla_archivo');
        //     $new_nombre = rand() . '.' . $archivo->getClientOriginalExtension();
        //     $archivo->move(public_path('archivos'), $new_nombre);
        //     $data['malla_archivo'] = 'archivos/'.$new_nombre;
        //     // Eliminar archivo anterior
        //     \File::delete(public_path().'/'.$carrera->malla_archivo);
        // }

        if ($request->filled('imagen')) {
           $data['imagen'] = 'images/'.$request->imagen;
        }
        if ($request->filled('malla_archivo')) {
            $data['malla_archivo'] = 'archivos/'.$request->malla_archivo;
        }

        $carrera->update($data);

        return redirect()->route('carreras.index')
                ->with('info', 'Registro actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Categoria  $carrera
     * @return \Illuminate\Http\Response
     */
    public function destroy(Abconfig $abconfig, Carrera $carrera)
    {
        // \File::delete(public_path().'/'.$carrera->imagen);
        
        // eliminar dependencias
        // $carrera->cate_perfiles()->delete();
        $carrera->delete();

        return redirect()->route('carreras.index')->with('info', 'Eliminado correctamente');
    }
}
