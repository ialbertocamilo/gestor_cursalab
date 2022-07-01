<?php

namespace App\Http\Controllers;

use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Usuario;

use App\Http\Requests\ReporteSupervisor\ReporteSupervisorDeleteRequest;
use App\Http\Requests\ReporteSupervisor\ReporteSupervisoreStoreRequest;
use App\Http\Resources\ReporteSupervisor\ReporteSupervisorSearchResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisoresController extends Controller
{
    public function search(Request $request)
    {
        $q = Usuario::whereHas('reporte_supervisor', function ($q) use ($request) {
            if (!empty($request->areas)) {
                $q->whereIn('criterios.id', $request->areas);
            }
        })
            ->with(['reporte_supervisor' => function ($q) {
                $q->select('id', 'valor');
            }])
            ->where('estado', 1);

        if ($request->module)
            $q->where('config_id', $request->module);

        if ($request->q)
            $q->where(function ($q_search) use ($request) {
                $q_search->where('nombre', 'like', "%$request->q%")
                    ->orWhere('dni', 'like', "%$request->q%");
            });

        $supervisores = $q->paginate($request->paginate ?? 15);
        ReporteSupervisorSearchResource::collection($supervisores);

        return $this->success($supervisores);
    }

    public function getListSelects()
    {
        $modules = Abconfig::select('id', 'etapa as nombre')->get();

        return $this->success($modules);
    }

    public function getUsuarios(Request $request)
    {
        $q = Usuario::with(['config', 'criterio'])
            ->where('config_id', $request->get('confid_id'))
            ->where(function ($q_search) use ($request) {
                $q_search->where('nombre', 'like', "%" . $request->get('q') . "%")
                    ->orWhere('dni', 'like', "%" . $request->get('q') . "%");
            })
            // ->where('grupo', $id)
            ->where('estado', 1)
            ->select('id as value', 'grupo_nombre', DB::raw("CONCAT(dni,' - ', nombre) as text"),
                'dni', 'cargo', 'config_id', 'grupo');
        $usuarios = $q->get();

        $usuarios_seleccionados = Usuario::with(['config', 'criterio'])
            ->whereHas('reporte_supervisor')
            ->where('config_id', $request->config_id)
            ->where('estado', 1)
            ->whereIn('id', $usuarios->pluck('value'))
            ->select('id as value', 'grupo_nombre', DB::raw("CONCAT(dni,' - ', nombre) as text"),
                'dni', 'cargo', 'config_id')
            ->get();

        return $this->success(compact('usuarios_seleccionados', 'usuarios'));
    }

    public function getAreas(Abconfig $modulo, Request $request): \Illuminate\Http\JsonResponse
    {
        $q = Criterio::where('config_id', $modulo->id);

        if ($request->type === 'only_selected')
            $q->whereHas('supervisores');

        $areas = $q->get();

        $areas = $this->prepareDefaultSelectResponse($areas);

        return $this->success($areas);
    }

    public function storeSupervisor(ReporteSupervisoreStoreRequest $request)
    {
        $data = $request->validated();

        foreach ($data['usuarios'] as $us) {
//            info($us['value']);
            DB::table('supervisores')->updateOrInsert(
                ['usuario_id' => $us['value']],
                [
                    'usuario_id' => $us['value'],
                    'criterio_id' => $data['criterio_id']
                ]
            );
        }

        return $this->success(['msg' => 'Supervisor(es) asignado(s) correctamente']);
    }

    public function deleteSupervisor(ReporteSupervisorDeleteRequest $request)
    {
        $data = $request->validated();

        DB::table('supervisores')
            ->where('usuario_id', $data['usuario_id'])
            ->where('criterio_id', $data['criterio_id'])
            ->delete();

        return $this->success(['msg' => 'Se removió al supervisor correctamente']);
    }
}
