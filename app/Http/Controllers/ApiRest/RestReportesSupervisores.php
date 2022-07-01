<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Curso;
use App\Models\Usuario;
use App\Models\Criterio;
use App\Models\Categoria;
use App\Models\Matricula;
use App\Models\Matricula_criterio;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Requests\ReporteSupervisor\ReporteSupervisorSearchRequest;
use App\Http\Requests\ReporteSupervisor\ReporteSupervisorSelectsRequest;

class RestReportesSupervisores extends Controller
{
    public function getEscuelasXGrupo(){
        $relation = $this->getAreaSupervisor();
        $criterio = Criterio::where('id',$relation->criterio_id)->select('id','valor')->first();

        $cursos_id = $this->getCursosXArea($criterio);
        
        $escuelas = Categoria::whereIn('id', Curso::whereIn('id',$cursos_id)->select('categoria_id')->where('estado',1)->pluck('categoria_id'))
        ->select('id', 'nombre')
        ->get();
        
        return response()->json(compact('escuelas'));
    }

    public function getEscuelas()
    {
        $supervisor = auth('api')->user();
        $escuelas = Categoria::where('config_id', $supervisor->config_id)
            ->select('id', 'nombre')
            ->get();
        
        return response()->json(compact('escuelas'));
    }

    public function getCursos(Request $request)
    {  
        $relation = $this->getAreaSupervisor();
        $criterio = Criterio::where('id',$relation->criterio_id)->select('id','valor')->first();

        $cursos_id = $this->getCursosXArea($criterio);
        $escuela_ids = $request->get('escuela_id');
        $cursos = Curso::whereIn('id',$cursos_id)->whereIn('categoria_id', $escuela_ids)->select('id', 'nombre','categoria_id')->get();
        return response()->json(compact('cursos'));
    }

    public function init(ReporteSupervisorSearchRequest $request)
    {
        $relation = $this->getAreaSupervisor();

        $criterio = Criterio::where('id',$relation->criterio_id)->select('id','valor')->first();
        $area = [
            "id" => $criterio->id,
            "nombre" => $criterio->valor,
        ];

        $usuarios = Usuario::with([
                'resumen_x_curso' => function ($res_x_curso) {
                    $res_x_curso->select('id', 'curso_id', 'usuario_id', 'categoria_id', 'estado')->where('libre',0)->where('estado_rxc',1);
                },
                'resumen_general'
            ])
            ->whereHas('matricula_presente.matricula_criterio', function ($q) use ($area) {
                $q->where('criterio_id', $area['id']);
            })
            ->select('id', 'nombre', 'dni', 'config_id')
            ->where('estado', 1)
            ->where('rol', 'default')
            ->groupBy('id')
            ->get();

        $CA = 0; // aprobados
        $CD = 0; // desaprobados
        $CED = 0; // en desarrollo
        $CEP = 0; // encuesta pendiente
        $CP = 0; // pendiente
        foreach ($usuarios as $usuario) {
            $cursos_asignados = $usuario->resumen_general->cur_asignados;
            $CP += ($cursos_asignados - $usuario->resumen_x_curso->count());
            $CA += $usuario->resumen_x_curso->where('estado', 'aprobado')->count();
            $CD += $usuario->resumen_x_curso->where('estado', 'desaprobado')->count();
            $CED += $usuario->resumen_x_curso->where('estado', 'desarrollo')->count();
            $CEP += $usuario->resumen_x_curso->where('estado', 'enc_pend')->count();
        }

        $response = [
            'reportes' => [
                'supervisores_avance_curricula' => 'Reporte de avance de curricula',
                'supervisores_notas' => 'Reporte de notas'
            ],
            'estados' => [
                'aprobado' => 'Aprobado',
                'desaprobado' => 'Desaprobado',
                'desarrollo' => 'Desarrollo',
                'enc_pend' => 'Encuesta pendiente',
                'pendiente' => 'Pendiente'
            ],
            'area' => $area['nombre'],
            'area_id' => $area['id'],
            'usuarios_activos' => $usuarios->count(),
            'aprobados' => $CA,
            'desaprobados' => $CD,
            'desarrollo' => $CED,
            'encuesta_pendiente' => $CEP,
            'pendientes' => $CP,
        ];

        return response()->json($response);
    }
    private function getAreaSupervisor(){
        $user = auth('api')->user();
        $supervisor = Usuario::find($user->id);
        return DB::table('supervisores')->where('usuario_id',$supervisor->id)->first();
    }

    private function getCursosXArea($criterio){
        $matriculas = Matricula::with([
            'matricula_criterio'=>function($q){$q->select('id','matricula_id','criterio_id');},
            'curricula'=>function($q){$q->select('id','ciclo_id','carrera_id','curso_id','estado','all_criterios')->where('estado',1);},
            'curricula.curso'=>function($q){$q->select('id','estado')->where('estado',1);},
            'curricula.curricula_criterio'=>function($q){$q->select('id','curricula_id','criterio_id');},
        ])->whereHas('usuario',function($q) use ($criterio){
            $q->where('estado',1)->where('rol','default')->where('grupo',$criterio->id);
        })
        ->where('matricula.estado', 1)->get();
        $helper = new HelperController();
        return $helper->get_cursos_matriculas_id($matriculas);
    }
}
