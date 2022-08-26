<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\SummaryCourse;
use App\Models\Announcement;
use App\Models\SummaryUser;
use App\Models\Question;
use App\Models\Topic;


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
        $topic_id     = $request->tema;
        $rptas       = $request->respuestas;
        $usu_rptas   = $rptas;

        if (count($usu_rptas) == 0) {
            $apiResponse = ['error' => true, 'msg' => 'Respuestas no enviadas.'];
            return response()->json($apiResponse, 200);
        }
        $ev = Prueba::select('id', 'nota', 'intentos', 'rptas_ok', 'rptas_fail', 'resultado', 'categoria_id', 'curso_id', 'posteo_id')
                    ->where('posteo_id', $topic_id)
                    ->where('usuario_id', $usuario_id)
                    ->first();
        if (!$ev) {
            $apiResponse = ['error' => true, 'msg' => 'EV no existe.'];
            return response()->json($apiResponse, 200);
        }
        $preguntas = Pregunta::select('id', 'rpta_ok')
                    ->where('topic_id', $topic_id)
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
                ->where('topic_id', $topic_id)
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
                $topic = Topic::where('id',$ev->posteo_id)->select('orden')->first();
                $topic_siguiente = Topic::where('curso_id',$ev->curso_id)->whereNotIn('id',[$ev->posteo_id])->where('orden','>=',$topic->orden)->select('id')->first();
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
        $topic = Topic::with('evaluation_type', 'course')->find('id', $topic_id);

        if (!$topic) return response()->json(['data' => [], 'error' => true], 200);
        
        $config_quiz = auth()->user()->subworspace->mod_evaluaciones;

        $limit = $config_quiz['preg_x_ev'] ?? 5;
        $is_random = $topic->evaluation_type->code == 'calificada';
        $type_code = $topic->evaluation_type->code == 'calificada' ? 'selecciona' : 'texto';

        $questions = Question::getQuestionsForQuiz($topic, $limit, $is_random, $type_code);

        if ( count($questions) == 0 )
            return response()->json(['error' => true, 'data' => null], 200);

        $row = SummaryTopic::setInitialData($topic);

        $data = [   
            'nombre' => $topic->name,
            'posteo_id' => $topic->id,
            'curso_id' => $topic->course_id,
            'preguntas' => $questions,
            'tipo_evaluacion' => $topic->evaluation_type->code ?? NULL,
            'attempt' => [
                'started_at' => $row->current_quiz_started_at,
                'finishes_at' => $row->current_quiz_started_at->addHour(),
            ],
        ];

        // SummaryTopic::setUserLastTimeEvaluation($topic);
        // SummaryCourse::setUserLastTimeEvaluation($topic->course);
        // SummaryUser::setUserLastTimeEvaluation();
        
        return response()->json(['error' => false, 'data' => $data], 200);
    }

    public function preguntasIntentos_v7($topic_id = null, $user_id = null, $fuente)
    {
        if ( !$topic_id && !$user_id ) return ['error' => 2, 'data' => null];

        $topic = Topic::with('course.topics')->find($topic_id);

        $row = SummaryTopic::incrementUserAttempts($topic);

        if (!$row) return ['error' => 1, 'data' => null];

        SummaryCourse::incrementUserAttempts($topic->course);
        SummaryUser::incrementUserAttempts();

        return ['error' => 0, 'data' => $row->attempts];
    }

    public function guarda_visitas_post(Topic $topic)
    {
        $topic->load('course.topics');

        $row = SummaryTopic::incrementViews($topic);
        
        if (!$row) return ['error' => 1, 'data' => null];

        SummaryCourse::incrementViews($topic->course);

        return ['error' => 0, 'data' => $row->views];
    }

}
