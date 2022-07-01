<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Models\SortingModel;
use App\Models\Categoria;
use App\Models\Criterio;
use App\Models\Abconfig;
use App\Models\Carrera;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use ApiResponse;

    public function prepareDefaultSelectResponse($data)
    {
        $temp = [];
        foreach ($data as $item) {
            $temp[] = [
                'id' => $item->id,
                'nombre' => $item->etapa ?? $item->nombre ?? $item->titulo ?? $item->valor ?? 'Sin nombre',
            ];
        }
        return $temp;
    }

    public function getModulos()
    {
        $modulos = Abconfig::select('id', 'etapa as nombre')->get();

        return $this->success($modulos);
    }

    public function getEscuelasByModulo(Abconfig $modulo, Request $request)
    {
        $q  = Categoria::where('config_id', $modulo->id)->where('estado', 1);

        if ($request->not)
            $q->whereNotIn('id', $request->not);

        $escuelas = $this->prepareDefaultSelectResponse($q->get());

        return $this->success($escuelas);
    }

    public function changeOrder(Request $request)
    {
        return SortingModel::setChangeOrder($request);
    }

    public function getCarrerasByModulo(Abconfig $modulo): \Illuminate\Http\JsonResponse
    {
        $carreras = $this->prepareDefaultSelectResponse($modulo->carreras);

        return $this->success($carreras);
    }

    public function getCicloByCarrera(Carrera $carrera): \Illuminate\Http\JsonResponse
    {
        $carreras = $this->prepareDefaultSelectResponse($carrera->ciclos);

        return $this->success($carreras);
    }

}
