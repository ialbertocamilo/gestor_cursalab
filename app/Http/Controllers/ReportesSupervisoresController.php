<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\SupervisoresImport;

use App\Http\Requests\ReporteSupervisor\ReporteSupervisorDeleteRequest;
use App\Http\Requests\ReporteSupervisor\ReporteSupervisoreStoreRequest;
use App\Http\Resources\ReporteSupervisor\ReporteSupervisorSearchResource;

class ReportesSupervisoresController extends Controller
{
    public function search(Request $request)
    {
        $q = Usuario::whereHas('reporte_supervisor',function($q) use($request){
                if ($request->area){
                    $q->where('criterios.id', $request->area);
                }
            })
            ->with(['reporte_supervisor'=>function($q){
                $q->select('id','valor');
            }])
            ->where('estado', 1);
        if ($request->modulo)
            $q->where('config_id', $request->modulo);
        if ($request->q)
            $q->where(function ($q_search) use ($request) {
                $q_search->where('nombre', 'like', "%$request->q%")
                    ->orWhere('dni', 'like', "%$request->q%");
            });
        
        $supervisores = $q->paginate(10);
        ReporteSupervisorSearchResource::collection($supervisores);

        return response()->json(compact('supervisores'));
    }

    public function getUsuarios(Request $request)
    {
        $q = Usuario::with('config')
            ->where('config_id', $request->get('confid_id'))
            ->where(function ($q_search) use ($request) {
                $q_search->where('nombre', 'like', "%".$request->get('q')."%")
                    ->orWhere('dni', 'like', "%".$request->get('q')."%");
            })
            // ->where('grupo', $id)
            ->where('estado', 1)
            ->select('id as value', 'grupo_nombre', DB::raw("CONCAT(dni,' - ', nombre) as text"), 'dni', 'cargo', 'config_id');
        $usuarios = $q->get();

        $usuarios_seleccionados = Usuario::with('config')
            ->whereHas('reporte_supervisor')
            ->where('config_id', $request->config_id)
            ->where('estado', 1)
            ->whereIn('id',$usuarios->pluck('value'))
            ->select('id as value', 'grupo_nombre', DB::raw("CONCAT(dni,' - ', nombre) as text"), 'dni', 'cargo', 'config_id')
            ->get();

        return response()->json(compact('usuarios_seleccionados', 'usuarios'));
    }

    public function getListSelects()
    {
        $modulos = Abconfig::select('id as value', 'etapa as text')->get();
        $area = Criterio::select()->get();

        return response()->json(compact('modulos'));
    }

    public function getCriterioxModulo(Abconfig $modulo,$tipo)
    {
        $areas = Criterio::select('id as value', 'valor as text');
        if($tipo=='only_selected'){
            $areas =  $areas->whereHas('supervisores');
        }
        $areas=$areas->where('config_id', $modulo->id)->get();

        return response()->json(compact('areas'));
    }

    public function storeSupervisor(ReporteSupervisoreStoreRequest $request)
    {
        $data = $request->validated();
        // $usuarios = [];

        // DB::table('supervisores')
        //     ->where('criterio_id', $data['criterio_id'])
        //     ->delete();

        foreach ($data['usuarios'] as $us) {
            DB::table('supervisores')->updateOrInsert(
                 ['usuario_id' => $us['value']],
                //  ,'criterio_id' => $us['grupo']['value']],
                 [
                     'usuario_id' => $us['value'],
                     'criterio_id' => $us['grupo']['value']
                ]
             );
        }

        // DB::table('supervisores')->insert($usuarios);

        return response()->json([
            'msg' => 'Supervisor(es) asignado(s) correctamente'
        ]);
    }

    public function deleteSupervisor(ReporteSupervisorDeleteRequest $request)
    {
        $data = $request->validated();

        DB::table('supervisores')
            ->where('usuario_id', $data['usuario_id'])
            ->where('criterio_id', $data['criterio_id'])
            ->delete();

        return response()->json([
            'msg' => 'Se removió al supervisor correctamente'
        ]);
    }

    public function importSupervisores(Request $request){
        $info = [];
        if ($request->hasFile("archivo")) {
            $import = new SupervisoresImport();
            Excel::import($import, $request->file('archivo'));
            $info = $import->get_data();
            return response()->json([
                'error' => false,
                'msg' => $info['msg'],
                'info' => $info
            ], 200);
        }
        return response()->json([
            'error' => true,
            'msg' => 'No se ha seleccionado ningún archivo',
            'info' => $info
        ], 200);
    }
}

