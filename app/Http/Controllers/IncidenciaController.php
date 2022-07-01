<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

use App\Http\Resources\IncidenciaResource;

class IncidenciaController extends Controller
{
    public function index()
    {
        $incidencias = DB::table('incidencias')->where('tipo', '<>', 'ejecutando')->where('estado', 1)->get();

        $incidencias_x_comando = $incidencias->groupBy('comando'); 

        $ejecutando = DB::table('incidencias')->where('tipo','ejecutando')->first();
        $disabled = (isset($ejecutando->total) && ($ejecutando->total > 0)) ? true : false;

        return view('incidencias.index',compact('incidencias_x_comando', 'ejecutando', 'disabled'));
    }

    public function search(Request $request)
    {
        $incidencias = Incidencia::search($request);

        IncidenciaResource::collection($incidencias);

        return $this->success($incidencias);
    }

    public function ejecutar_comando(Request $request)
    {
        $ejecuntando = DB::table('incidencias')->where('tipo','ejecutando')->where('total','>',0)->first();

        if(isset($ejecuntando)){
            return redirect()->route('incidencias.index')
                    ->with('modal-info', '<strong>Mensaje</strong> <br> 
                    El comando ya esta corriendo, por favor espere.');
        }

        Incidencia::updateOrCreate(
            ['tipo' => 'ejecutando'],
            ['afectados' => json_encode($request->get('procesos'))]
        );

        $d = Artisan::call('buscar:incidencias', ['tipo' => 'desde_front']);

        Log::info(json_encode($d));

        return redirect()->route('incidencias.index')
                    ->with('modal-info', '<strong>Mensaje</strong> <br> 
                    El comando se ha ejecutado, este proceso puede tardar entre 30 a 60 minutos.');
    
    }

    public function destroy($incidencia_id)
    {
        Incidencia::where('id', $incidencia_id)->update([
            'estado' => 0
        ]);

        return redirect()->route('incidencias.index')
                        ->with('modal-info', '<strong>Mensaje</strong> <br> 
                        Eliminado correctamente.');
    }
}
