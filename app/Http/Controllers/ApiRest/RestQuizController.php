<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestQuizController extends Controller
{
    // public function evaluarpreguntas_v2($topic_id, $user_id, $rpta_ok, $rpta_fail, $usu_rptas)
    public function evaluar_preguntas(Request $request)
    {
        // $user = Auth::user();
        
        // return $request->all();
        $apiResponse = ['error' => true, 'msg' => 'Error. Algo ha ocurrido.'];
        $appUser     = auth()->user();
        $rpta_ok     = 0;
        $rpta_fail   = 0;
        $resultado   = 0;

        $subworkspace_id   = $appUser->subworkspace_id;
        $usuario_id  = $appUser->id;
        $post_id     = $request->tema;
        $rptas       = $request->respuestas;
        $usu_rptas   = $rptas;

        if (count($usu_rptas) == 0) {
            $apiResponse = ['error' => true, 'msg' => 'Respuestas no enviadas.'];
            return response()->json($apiResponse, 200);
        }
        $ev = Prueba::select('id', 'nota', 'intentos', 'rptas_ok', 'rptas_fail', 'resultado', 'categoria_id', 'curso_id', 'posteo_id')
                    ->where('posteo_id', $post_id)
                    ->where('usuario_id', $usuario_id)
                    ->first();
        if (!$ev) {
            $apiResponse = ['error' => true, 'msg' => 'EV no existe.'];
            return response()->json($apiResponse, 200);
        }
        $preguntas = Pregunta::select('id', 'rpta_ok')
                    ->where('post_id', $post_id)
                    ->get();

        foreach ($preguntas as $preg) {
            foreach ($usu_rptas as $rpta) {
                if ($preg->id == $rpta['preg_id']) {
                    if ($preg->rpta_ok == $rpta['opc']) $rpta_ok++;
                    else $rpta_fail++;
                    continue;
                }
            }
        }

        $helper             = new HelperController();
        $config_evaluacion  = $helper->configEvaluacionxModulo($subworkspace_id);
        $nota_aprobatoria   = $config_evaluacion['nota_aprobatoria'];
        $nota_calculada     = ($rpta_ok == 0) ? 0 : ((20 / ($rpta_ok + $rpta_fail)) * $rpta_ok);
        $resultado          = ($nota_calculada >= $nota_aprobatoria) ? 1 : 0;
        $ultima_evaluacion = Carbon::now();
        $data_ev = array(
            'rptas_ok'   => $rpta_ok,
            'rptas_fail' => $rpta_fail,
            'resultado'  => $resultado,
            'usu_rptas'  => json_encode($usu_rptas),
            'nota'       => round($nota_calculada),
            'last_ev' =>$ultima_evaluacion,
        );
        // return $data_ev;
        $nota_bd = $ev->nota;
        $data_tema_siguiente = false;
        if ($nota_calculada >= $nota_bd) {
            Prueba::where('id', $ev->id)->update($data_ev);

            $actividad = Visita::select('id')
                ->where('post_id', $post_id)
                ->where('usuario_id', $usuario_id)
                ->first();
            if ($actividad) {
                $actividad->tipo_tema = 'calificada';
                if ($resultado == 1) $actividad->estado_tema = 'aprobado';
                else $actividad->estado_tema = 'desaprobado';
                $actividad->save();
                $data_ev['resultado'] = ($actividad->estado_tema=='aprobado') ? 1 : 0 ;
            }
            if($resultado){
                $topic = Posteo::where('id',$ev->posteo_id)->select('orden')->first();
                $topic_siguiente = Posteo::where('curso_id',$ev->curso_id)->whereNotIn('id',[$ev->posteo_id])->where('orden','>=',$topic->orden)->select('id')->first();
                $data_tema_siguiente = ($topic_siguiente) ? $topic_siguiente->id : false;
            }
            $data_ev['ev_updated']      = 1;
            $data_ev['ev_updated_msg']  = "(1) EV actualizada";
        } else {
            $data_ev['ev_updated']      = 0;
            $data_ev['ev_updated_msg']  = "(0) EV no actualizada (nota obtenida menor que nota existente)";
        }
        $data_ev['tema_siguiente'] = $data_tema_siguiente;
        $data_ev['curso'] = Curso::where('id',$ev->curso_id)->select('id','nombre')->first();
        $data_ev['curso_id'] = $ev->curso_id;
        $data_ev['tema_id'] = $ev->posteo_id;
        // $data_ev['curso_id'] = $ev->curso_id;
        $data_ev['intentos_realizados'] = $ev->intentos;

        $restAvanceController = new RestAvanceController();
        // ACTUALIZAR RESUMENES
        $restAvanceController->actualizar_resumen_x_curso($usuario_id, $ev->curso_id, $config_evaluacion['nro_intentos']);
        $restAvanceController->actualizar_resumen_general($usuario_id);
        DB::table('resumen_general')->where('usuario_id',$usuario_id)->update([
            'last_ev' =>$ultima_evaluacion,
        ]);
        DB::table('resumen_x_curso')->where('usuario_id',$usuario_id)->where('curso_id',$ev->curso_id)->update([
            'last_ev' =>$ultima_evaluacion,
        ]);
        $data_ev['encuesta_pendiente'] = Curso_encuesta::getEncuestaPendiente($usuario_id,$ev->curso_id);
        $apiResponse = ['error' => false, 'data' => $data_ev];

        return response()->json($apiResponse, 200);


        

        return $this->successApp(['data' => $anuncios]);
    }

    public function guardaEvaluacionAbierta(Request $request)
    {

    }

    public function evalPendientes2(Request $request)
    {

    }

    public function usuarioRespuestasEval(Request $request)
    {

    }

    public function cargar_preguntas($topic_id)
    {
        $apiResponse = ['data' => [], 'error' => true];
        
        $appUser = auth()->user();

        $topic = Topic::find('id', $topic_id)->first();


        if (!$topic) return response()->json($apiResponse, 200);

        $data = [];
        $subworkspace_id = $appUser->subworkspace_id;
        $helper = new HelperController();
        $config_eva = $helper->configEvaluacionxModulo($subworkspace_id);

        if ($topic->tipo_ev  == 'calificada') {
            $preguntas = Pregunta::where('post_id', $topic->id)
                            ->where('estado', 1)
                            ->where('tipo_pregunta','selecciona')
                            ->select('id', 'tipo_pregunta', 'pregunta', 'ubicacion', 'estado', 'rptas_json')
                            ->inRandomOrder()
                            ->limit($config_eva['preg_x_ev'])
                            ->get();
            // APLICAR SHUFFLE A ALTERNATIVAS DE CADA PREGUNTA
            foreach ($preguntas as $value) {
                $val_rpta = json_decode($value->rptas_json, true);
                $tempRptas = collect();
                foreach ($val_rpta as $key => $value2) {
                    $tempRpta = ['id'=> $key , 'opc' =>$value2];
                    $tempRptas->push($tempRpta);
                }
                // shuffle($val_rpta);
                $shu = $tempRptas->shuffle()->all();

                // info($value);
                $value->rptas_json = $shu;
                // $value->rptas_json = $val_rpta;
            }
        } else {
            $preguntas = Pregunta::where('post_id', $topic->id)
                            ->where('estado', 1)
                            ->where('tipo_pregunta','texto')
                            ->select('id', 'tipo_pregunta', 'pregunta', 'ubicacion', 'estado', 'rptas_json')
                            ->get();
        }


        $data = ["tipo_evaluacion"=>$topic->tipo_ev,"curso_id"=>$topic->curso_id,"posteo_id" => $topic->id, "nombre" => $topic->nombre, "preguntas" => $preguntas];
        $ultima_evaluacion = Carbon::now();

        DB::table('resumen_general')->where('usuario_id',$appUser->id)->update([
            'last_ev' =>$ultima_evaluacion,
        ]);

        DB::table('resumen_x_curso')->where('usuario_id',$appUser->id)->where('curso_id',$topic->curso_id)->update([
            'last_ev' =>$ultima_evaluacion,
        ]);
        
        if (count($preguntas) > 0) $apiResponse = ['error' => false, 'data' => $data];
        else $apiResponse = ['error' => true, 'data' => null];

        return response()->json($apiResponse, 200);
    }

}
