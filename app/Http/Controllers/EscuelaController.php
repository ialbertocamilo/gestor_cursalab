<?php

namespace App\Http\Controllers;

use App\Http\Requests\Escuela\EscuelaStoreUpdateRequest;
use App\Http\Resources\Escuela\EscuelaSearchResource;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EscuelaController extends Controller
{
    public function search(Abconfig $abconfig, Request $request)
    {
        $request->modulo_id = $abconfig->id;
        $escuelas = Categoria::search($request);

        EscuelaSearchResource::collection($escuelas);

        return $this->success($escuelas);
    }

    public function searchCategoria(Abconfig $abconfig, Categoria $categoria)
    {
        $reinicio_automatico = json_decode($categoria->reinicios_programado);
        $categoria->reinicio_automatico = $reinicio_automatico->activado ?? false;
        $categoria->reinicio_automatico_dias = $reinicio_automatico->reinicio_dias ?? 0;
        $categoria->reinicio_automatico_horas = $reinicio_automatico->reinicio_horas ?? 0;
        $categoria->reinicio_automatico_minutos = $reinicio_automatico->reinicio_minutos ?? 0;
        $nombre_ciclo_0 = DB::table('nombre_escuelas')->where('escuela_id', $categoria->id)->first();
        $categoria->nombre_ciclo_0 = $nombre_ciclo_0->nombre ?? null;
        $categoria->makeHidden('reinicios_programado');

        return $this->success([
            'escuela' => $categoria,
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        return $compactResponse ? [] : $this->success([]);
    }

    public function store(EscuelaStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');
//        dd($data);

        $escuela = Categoria::storeRequest($data);

        $msg = 'Escuela creado correctamente.';
        return $this->success(compact('escuela', 'msg'));
    }

    public function update(EscuelaStoreUpdateRequest $request, Abconfig $abconfig, Categoria $categoria)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');
//        dd($data);

        $escuela = Categoria::storeRequest($data, $categoria);

        $msg = 'Escuela actualizada correctamente.';
        return $this->success(compact('escuela', 'msg'));
    }

    public function destroyEscuela(Abconfig $abconfig, Categoria $categoria)
    {
        $validate = Categoria::validateEscuelaEliminar($categoria);
        if (!$validate['validate'])
            return $this->success(compact('validate'), 422);

        dd($validate);
        return;
        $categoria->delete();

        return $this->success(['msg' => 'Escuela eliminada correctamente.']);
    }

    public function updateStatus(Abconfig $abconfig, Categoria $categoria)
    {
//        return $categoria;

        $estado = ($categoria->estado == 1) ? 0 : 1;
        $categoria->estado = $estado;
        $categoria->save();
        return $this->success(['info' => 'Estado actualizado con éxito.']);
    }
}
