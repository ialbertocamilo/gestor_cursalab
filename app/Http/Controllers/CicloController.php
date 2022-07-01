<?php

namespace App\Http\Controllers;

use App\Models\Abconfig;
use App\Models\Ciclo;
use App\Models\Cursos;
use App\Models\Carrera;
use App\Models\CicloCompas;

use DB;

use App\Http\Requests\CicloSR;
use Illuminate\Http\Request;

class CicloController extends Controller
{
    // Lista para select con Vue
    public function ciclos_x_carrera(Request $request)
    {
        $carrera_id = $request->carrera_id;
        $ciclos = Ciclo::select('id','nombre')->where('carrera_id', $carrera_id)
                        ->where('estado', 1)->orderBy('nombre')->get();
        return $ciclos;
    }

    public function create(Carrera $carrera)
    {
        return view('ciclos.create', compact('carrera'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Carrera $carrera, CicloSR $request)
    {
        $data = $request->all();
        $data['carrera_id'] = $carrera->id;

        $ciclo = Ciclo::create($data);

        $params = [$carrera->config->id, $carrera->id];
        return redirect()->route('carreras.ciclos', $params)
                ->with('info', 'Registro guardado con éxito');
    }


    public function edit(Carrera $carrera, Ciclo $ciclo)
    {
        $carreras = Carrera::where('config_id', $carrera->config_id)->where('id', '!=' ,$carrera->id)
                        ->where('estado', 1)->orderBy('nombre')->get();
        // $carrera_ids = Carrera::where('config_id', $carrera->config_id)->pluck('id');
        // $ciclos_compa = Ciclo::select('id', 'nombre')->whereIn('carrera_id', $carrera_ids)->where('id', '!=' ,$ciclo->id)->get();

        // return $ciclos_compa;
        return view('ciclos.edit', compact('carrera','ciclo', 'carreras'));
    }

    public function update(Carrera $carrera, CicloSR $request, Ciclo $ciclo)
    {
        // return $request;
        $data = $request->all();
        $data['carrera_id'] = $carrera->id;

        $ciclo->update($data);

        // ciclos cpmaptibles
        if ($request->has('ciclos')) {
            //eliminar anteriores datos de posteo perfil
            $ciclo->compatibles()->delete();
            //guardar curricula
            $ciclos = $request->ciclos;
            foreach ($ciclos as $key => $value) {
                if($value > 0){
                    $ciclocompa = new CicloCompas;
                    $ciclocompa->config_id = $carrera->config->id;
                    $ciclocompa->ciclo_id_1 = $ciclo->id;
                    $ciclocompa->ciclo_id_2 = $value;
                    $ciclocompa->save();  
                } 
            }
        }

        $params = [$carrera->config->id, $carrera->id];
        return redirect()->route('carreras.ciclos', $params)
                ->with('info', 'Registro actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Categoria  $carrera
     * @return \Illuminate\Http\Response
     */
    public function destroy(Carrera $carrera, Ciclo $ciclo)
    {
        // eliminar dependencias
        // $carrera->cursos()->delete();
        // $carrera->cursos()->temas()->delete();
        $ciclo->delete();

        $params = [$carrera->config->id, $carrera->id];
        return redirect()->route('carreras.ciclos', $params)->with('info', 'Registro eliminado correctamente');
    }
}
