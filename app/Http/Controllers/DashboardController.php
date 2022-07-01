<?php

namespace App\Http\Controllers;

use DB;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Visita;
use App\Models\Prueba;
use App\Models\Carrera;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Matricula;

use Carbon\Carbon;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->refresh == 'true')
            cache()->flush();

        $modulo_id = request('modulo_id', NULL);

        $cache_name = 'dashboard_cards';
        $cache_name .= $modulo_id ? "-modulo-{$modulo_id}" : '';

        $data = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_DATA, function () use ($modulo_id) {

            $data['time'] = now();

            $data['totales'] = [

                'temas' => Posteo::when($modulo_id, function($q) use ($modulo_id){
                                $q->whereHas('curso', function($query) use ($modulo_id){
                                    $query->where('config_id', $modulo_id);
                                });
                            })->count(),

                'cursos' => Curso::when($modulo_id, function($q) use ($modulo_id){
                                $q->where('config_id', $modulo_id);
                            })->count(),

                'usuarios' => Usuario::where('rol', 'default')->when($modulo_id, function($q) use ($modulo_id){
                                $q->where('config_id', $modulo_id);
                            })->count(),

                'usuarios_activos' => Usuario::where('estado', 1)->where('rol', 'default')->when($modulo_id, function($q) use ($modulo_id){
                                $q->where('config_id', $modulo_id);
                            })->count(),

                'temas_evaluables' => Posteo::when($modulo_id, function($q) use ($modulo_id){
                                $q->whereHas('curso', function($query) use ($modulo_id){
                                    $query->where('config_id', $modulo_id);
                                });
                            })->where('evaluable', 'si')->count(),
            ];

            $data['data'] = [
                'modulos' => Abconfig::select('id', 'etapa', 'logo')->get(),
                'categorias' => Categoria::select('id', 'nombre')->pluck('nombre', 'id'),
            ];

            return $data;
        });

        $data['last_update']['time'] = $data['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $data['time']->diffForHumans();

        // $cate_populares = [];
        // $cate_avance = [];
        // $top_cursos = [];

        // $pruebas_exis = Prueba::count();
        // $pruebas_proyectado = $tot_usuarios * $cur_eval;

        // $cur_rea = 0;

        // if ($pruebas_proyectado > 0)
        //     $cur_rea = ($pruebas_exis / $pruebas_proyectado) * 100;

        // $cur_rea = number_format($cur_rea, 2, '.', ',');

        // $pruebas_ok = Prueba::where("resultado",'1')->count();
        // $pruebas_ok_proyectado = $tot_usuarios * $cur_eval;

        // $cur_apro = 0;

        // if ($pruebas_proyectado > 0)
        //     $cur_apro = ($pruebas_ok / $pruebas_ok_proyectado) * 100;

        // $cur_apro = number_format($cur_apro, 2, '.', ',');

        return view('home', compact('data'));
    }

    public function clearCache()
    {
        cache()->flush();

        return [];
    }

    public function getDataForGraphicTopBoticas()
    {
        $modulo_id = request('modulo_id', NULL);

        $response = Prueba::getTopBoticas($modulo_id);

        $result = $response['data']->toArray();

        $data['values'] = array_column($result, 'total_usuarios');
        $data['labels'] = array_column($result, 'botica');

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $data;
    }

    public function getDataForGraphicVisitasPorfecha()
    {
        $modulo_id = request('modulo_id', NULL);

        $response = Visita::getVisitasPorUsuario($modulo_id);

        foreach ($response['data'] as $row)
        {
            $data['labels'][] = Carbon::parse($row->fechita)->format('d/m/Y');
            $data['values'][] = $row->cant;
        }

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $data;
    }

    public function getDataForGraphicEvaluacionesPorfecha()
    {
        $modulo_id = request('modulo_id', NULL);

        $response = Prueba::getEvaluacionesPorfecha($modulo_id);

        foreach ($response['data'] as $row)
        {
            $data['labels'][] = Carbon::parse($row->fechita)->format('d/m/Y');
            $data['values'][] = $row->cant;
        }

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $data;
    }
}
