<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\SummaryCourse;
use App\Models\Announcement;
use App\Models\SummaryTopic;
use App\Models\SummaryUser;
use App\Models\Question;
use App\Models\Topic;


class RestQuizController extends Controller
{
    public function evaluar_preguntas(Request $request)
    {
        $user = auth()->user();
        $resultado = 0;

        $topic = Topic::with('course.topics.evaluation_type')->find($request->tema);

        if (count($request->respuestas) == 0)
            return response()->json(['error' => true, 'msg' => 'Respuestas no enviadas.'], 200);

        $row = SummaryTopic::getCurrentRow($topic);

        if (!$row)
            return response()->json(['error' => true, 'msg' => 'Evaluación no existe.'], 200);

        [$correct_answers, $failed_answers] = Topic::evaluateAnswers($request->respuestas, $topic);

        $new_grade = SummaryTopic::calculateGrade($correct_answers, $failed_answers);
        $passed = SummaryTopic::hasPassed($new_grade);

        $data_ev = [
            'correct_answers' => $correct_answers,
            'failed_answers' => $failed_answers,
            'passed' => $passed,
            'answers' => json_encode($request->respuestas),
            'grade' => round($new_grade),
            'attempts' => $row->attempts + 1,
            'last_time_evaluated_at' => now(),
        ];

        $next_topic = NULL;

        if ($row->hasImprovedGrade($new_grade)) {

            $status_passed = Taxonomy::getFirstData('topic', 'user-status', 'aprobado');
            $status_failed = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');

            $data_ev['status_id'] = $passed ? $status_passed->id : $status_failed->id;

            $row->update($data_ev);

            $next_topic = $passed ? $topic->getNextOne() : NULL;

            $data_ev['ev_updated']      = 1;
            $data_ev['ev_updated_msg']  = "(1) Evaluación actualizada";
        } else {
            $data_ev['ev_updated']      = 0;
            $data_ev['ev_updated_msg']  = "(0) Evaluación no actualizada (nota obtenida menor que nota existente)";
        }

        $data_ev['tema_siguiente'] = $next_topic->id ?? NULL;
        $data_ev['curso'] = $topic->course;
        $data_ev['curso_id'] = $topic->course_id;
        $data_ev['tema_id'] = $topic->id;
        $data_ev['intentos_realizados'] = $row->attempts;

        SummaryCourse::updateRanking($topic);

        $restAvanceController = new RestAvanceController();
        // ACTUALIZAR RESUMENES
        $restAvanceController->actualizar_resumen_x_curso($usuario_id, $row->curso_id, $config_evaluacion['nro_intentos']);
        $restAvanceController->actualizar_resumen_general($usuario_id);
        // DB::table('resumen_general')->where('usuario_id',$usuario_id)->update([
        //     'last_time_evaluated_at' => now(),
        // ]);
        // DB::table('resumen_x_curso')->where('usuario_id',$usuario_id)->where('curso_id',$row->curso_id)->update([
        //     'last_time_evaluated_at' => now(),
        // ]);
        $data_ev['encuesta_pendiente'] = Curso_encuesta::getEncuestaPendiente($user->id, $topic->course_id);

        return response()->json(['error' => false, 'data' => $data_ev], 200);
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

        $row = SummaryTopic::setStartQuizData($topic);

        if ($row->isOutOfTimeForQuiz())
            return response()->json(['data' => [], 'error' => true], 200);

        $config_quiz = auth()->user()->subworspace->mod_evaluaciones;

        $limit = $config_quiz['preg_x_ev'] ?? 5;
        $is_random = $topic->evaluation_type->code == 'qualified';
        $type_code = $topic->evaluation_type->code == 'qualified' ? 'select-options' : 'written-answer';

        $questions = Question::getQuestionsForQuiz($topic, $limit, $is_random, $type_code);

        if ( count($questions) == 0 )
            return response()->json(['error' => true, 'data' => null], 200);

        $data = [   
            'nombre' => $topic->name,
            'posteo_id' => $topic->id,
            'curso_id' => $topic->course_id,
            'preguntas' => $questions,
            'tipo_evaluacion' => $topic->evaluation_type->code ?? NULL,
            'attempt' => [
                'started_at' => $row->current_quiz_started_at,
                'finishes_at' => $row->current_quiz_finishes_at,
                'diff_in_minutes' => $row->current_quiz_finishes_at->diffInMinutes($row->current_quiz_started_at),
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

        return ['error' => 0, 'data' => $row];
    }

    public function contador_tema_reseteo(Topic $topic)
    {
        $topic->load('course');

        $row = SummaryTopic::getCurrentRow($topic);

        $counter = false;

        if ($row AND $row->hasFailed() AND $row->hasNoAttemptsLeft()) {

            $times = [];

            if ($topic->course->reinicios_programado)
                $times[] = $topic->course->reinicios_programado;

            if (auth()->user()->subworspace->reinicios_programado)
                $times[] = auth()->user()->subworspace->reinicios_programado;

            if (count($times) > 0) {
                
                $scheduled = false;
                $minutes = 0;

                foreach ($times as $time) {

                    if ($time->activado) {

                        $scheduled = true;
                        $minutes = $time->tiempo_en_minutos;

                        break;
                    }
                }

                if ($scheduled AND $row->last_time_evaluated_at) {

                    $finishes_at = $row->last_time_evaluated_at->addMinutes($minutes);
                    $counter = $finishes_at->diff(now())->format('%y/%m/%d %H:%i:%s');
                }
            }
        }

        return $counter;
    }

}
