<?php

namespace App\Http\Controllers;

use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Usuario;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function getModulos()
    {
        $modulos = Abconfig::select('id', 'etapa as nombre')->get();

        return $this->success(compact('modulos'));
    }

    public function getCardsInfo(Request $request)
    {
        if ($request->refresh === 'true')
            cache()->flush();

        $modulo_id = request('modulo_id', NULL);

//        $cache_name = 'dashboard_cards';
        $cache_name = 'dashboard_cards-v2';
        $cache_name .= $modulo_id ? "-modulo-{$modulo_id}" : '';

        $data = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_DATA, function () use ($modulo_id) {

            $data['time'] = now();

            $data['totales'] = [

                'temas' => [
                    'title' => 'Temas',
                    'icon' => 'mdi-book-open',
                    'color' => '#FFB300',
                    'value' => Posteo::when($modulo_id, function ($q) use ($modulo_id) {
                        $q->whereHas('curso', function ($query) use ($modulo_id) {
                            $query->where('config_id', $modulo_id);
                        });
                    })->count(),
                ],

                'cursos' => [
                    'title' => 'Cursos',
                    'icon' => 'mdi-book',
                    'color' => '#E01717',
                    'value' => Curso::when($modulo_id, function ($q) use ($modulo_id) {
                        $q->where('config_id', $modulo_id);
                    })->count(),
                ],

                'usuarios' => [
                    'title' => 'Usuarios totales',
                    'icon' => 'mdi-account-group',
                    'color' => '#5458ea',
                    'value' => Usuario::where('rol', 'default')->when($modulo_id, function ($q) use ($modulo_id) {
                        $q->where('config_id', $modulo_id);
                    })->count(),
                ],

                'usuarios_activos' => [
                    'title' => 'Usuarios activos',
                    'icon' => 'mdi-account-group',
                    'color' => '#22B573',
                    'value' => Usuario::where('estado', 1)->where('rol', 'default')->when($modulo_id, function ($q) use ($modulo_id) {
                        $q->where('config_id', $modulo_id);
                    })->count(),
                ],

                'temas_evaluables' => [
                    'title' => 'Temas evaluables',
                    'icon' => 'mdi-text-box-check',
                    'color' => '#4E5D8C',
                    'value' => Posteo::when($modulo_id, function ($q) use ($modulo_id) {
                        $q->whereHas('curso', function ($query) use ($modulo_id) {
                            $query->where('config_id', $modulo_id);
                        });
                    })->where('evaluable', 'si')->count(),
                ],
            ];
            return $data;
        });

        return $this->success(compact('data'));

    }

    public function getEvaluacionesPorfecha(Request $request)
    {
        if ($request->refresh === 'true')
            cache()->flush();

        $modulo_id = request('modulo_id', NULL);
        $response = Prueba::getEvaluacionesPorfecha($modulo_id);

        foreach ($response['data'] as $row) {
            $data['labels'][] = Carbon::parse($row->fechita)->format('d/m/Y');
            $data['values'][] = $row->cant;

        }
        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $this->success(compact('data'));
    }

    public function getVisitasPorfecha(Request $request)
    {
        if ($request->refresh === 'true')
            cache()->flush();

        $modulo_id = request('modulo_id', NULL);
        $response = Visita::getVisitasPorUsuario($modulo_id);

        foreach ($response['data'] as $row) {
            $data['labels'][] = Carbon::parse($row->fechita)->format('d/m/Y');
            $data['values'][] = $row->cant;

        }
        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $this->success(compact('data'));
    }

    public function getTopBoticas(Request $request)
    {
        if ($request->refresh === 'true')
            cache()->flush();

        $modulo_id = request('modulo_id', NULL);

        $response = Prueba::getTopBoticas($modulo_id);

        $result = $response['data']->toArray();

        $data['values'] = array_column($result, 'total_usuarios');
        $data['labels'] = array_column($result, 'botica');

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $this->success(compact('data'));
    }


}
