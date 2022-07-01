<?php

namespace App\Http\Controllers;

use App\Models\Mallas;
use App\Models\Abconfig;
use App\Models\Perfil;
use App\Http\Requests\MallasStoreRequest;
use Illuminate\Http\Request;

class MallasController extends Controller
{

    public function index(Request $request)
    {
        $mallas = Mallas::paginate();
        return view('mallas.index', compact('mallas'));
    }

    public function create()
    {
        $mallas = Mallas::select('config_id','perfil_id');
        // $config_array = Abconfig::select('id','etapa')->whereNotIn('id', $mallas->pluck('config_id'))->pluck('etapa','id' );
        $config_array = Abconfig::select('id','etapa')->pluck('etapa','id' );
        // $perfil_array = Perfil::select('id','nombre')->whereNotIn('id', $mallas->pluck('perfil_id'))->pluck('nombre','id' );
        $perfil_array = Perfil::select('id','nombre')->pluck('nombre','id' );
        return view('mallas.create', compact('config_array', 'perfil_array'));
    }

    public function store(MallasStoreRequest $request)
    {
        $data = $request->all();

        if ($request->has('archivo')) {
            $archivo= $request->file('archivo');
            $new_nombre = rand() . '.' . $archivo->getClientOriginalExtension();
            $archivo->move(public_path('archivos'), $new_nombre);
            $data['archivo'] = 'archivos/'.$new_nombre;
        }

        $malla = Mallas::create($data);

        return redirect()->route('mallas.index')
                ->with('info', 'Registro guardado con éxito');
    }

    public function edit(Mallas $malla)
    {
        $mallas = Mallas::select('config_id','perfil_id')->where('id','!=', $malla->id);
        // $config_array = Abconfig::select('id','etapa')->whereNotIn('id', $mallas->pluck('config_id'))->pluck('etapa','id' );
        $config_array = Abconfig::select('id','etapa')->pluck('etapa','id' );
        // $perfil_array = Perfil::select('id','nombre')->whereNotIn('id', $mallas->pluck('perfil_id'))->pluck('nombre','id' );
        $perfil_array = Perfil::select('id','nombre')->pluck('nombre','id' );
        return view('mallas.edit', compact('malla', 'config_array', 'perfil_array'));
    }


    public function update(MallasStoreRequest $request, Mallas $malla)
    {
        $data = $request->all();

        if ($request->has('archivo')) {
            $archivo= $request->file('archivo');
            $new_nombre = rand() . '.' . $archivo->getClientOriginalExtension();
            $archivo->move(public_path('archivos'), $new_nombre);
            $data['archivo'] = 'archivos/'.$new_nombre;
            // Eliminar archivo anterior
            \File::delete(public_path().'/'.$malla->archivo);
        }

        $malla->update($data);

         return redirect()->route('mallas.index')
                ->with('info', 'Registro actualizado con éxito');
    }

    public function destroy(Mallas $malla)
    {
        \File::delete(public_path().'/'.$malla->archivo);
        $malla->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }

}
