<?php

namespace App\Http\Controllers;

use App\Http\Requests\Escuela\EscuelaStoreUpdateRequest;
use App\Http\Resources\Escuela\EscuelaSearchResource;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Media;
use App\Models\School;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EscuelaController extends Controller
{
    public function search(Workspace $abconfig, Request $request)
    {
        $workspace = session('workspace');
        $workspace_id = (is_array($workspace)) ? $workspace['id'] : null;

        $request->workspace_id = $workspace_id;

        $escuelas = School::search($request);

        EscuelaSearchResource::collection($escuelas);

        return $this->success($escuelas);
    }

    public function searchCategoria(Abconfig $abconfig, School $escuela)
    {
        $reinicio_automatico = json_decode($escuela->scheduled_restarts);
        $escuela->reinicio_automatico = $reinicio_automatico->activado ?? false;
        $escuela->reinicio_automatico_dias = $reinicio_automatico->reinicio_dias ?? 0;
        $escuela->reinicio_automatico_horas = $reinicio_automatico->reinicio_horas ?? 0;
        $escuela->reinicio_automatico_minutos = $reinicio_automatico->reinicio_minutos ?? 0;
        // $nombre_ciclo_0 = DB::table('nombre_escuelas')->where('escuela_id', $escuela->id)->first();
        // $escuela->nombre_ciclo_0 = $nombre_ciclo_0->nombre ?? null;
        $escuela->makeHidden('reinicios_programado');

        return $this->success([
            'escuela' => $escuela,
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

        $escuela = School::storeRequest($data);

        $msg = 'Escuela creado correctamente.';
        return $this->success(compact('escuela', 'msg'));
    }

    public function update(EscuelaStoreUpdateRequest $request, Abconfig $abconfig, School $escuela)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');

        $escuela_save = School::storeRequest($data, $escuela);

        $msg = 'Escuela actualizada correctamente.';
        return $this->success(compact('escuela_save', 'msg'));
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
