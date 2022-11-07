<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollQuestionAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\SummaryCourse;
use App\Models\Announcement;
use App\Models\SummaryTopic;
use App\Models\SummaryUser;
use App\Models\Taxonomy;
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

        if ($row->hasNoAttemptsLeft())
            return response()->json(['error' => true, 'msg' => 'Sin intentos restantes.'], 200);

        if (!$row)
            return response()->json(['error' => true, 'msg' => 'La evaluación no existe.'], 200);

        [$correct_answers, $failed_answers, $correct_answers_score] = Topic::evaluateAnswers($request->respuestas, $topic);

        $new_grade = $correct_answers_score;
        // $new_grade = SummaryTopic::calculateGrade($correct_answers, $failed_answers);
        $passed = SummaryTopic::hasPassed($new_grade);

        $data_ev = [
            'attempts' => $row->attempts + 1,
            'last_time_evaluated_at' => now(),
            'current_quiz_started_at' => NULL,
            'current_quiz_finishes_at' => NULL,
            'taking_quiz' => NULL,
        ];

        $next_topic = NULL;

        if ($row->hasImprovedGrade($new_grade)) {

            $data_ev = $data_ev + [
                    'correct_answers' => $correct_answers,
                    'failed_answers' => $failed_answers,
                    'passed' => $passed,
                    'answers' => $request->respuestas,
                    // 'answers' => json_encode($request->respuestas),
                    'grade' => round($new_grade, 2),
                ];

            $status_passed = Taxonomy::getFirstData('topic', 'user-status', 'aprobado');
            $status_failed = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');

            $data_ev['status_id'] = $passed ? $status_passed->id : $status_failed->id;

            $row->update($data_ev);

            $next_topic = $passed ? $topic->getNextOne() : NULL;

            $data_ev['ev_updated'] = 1;
            $data_ev['ev_updated_msg'] = "(1) Evaluación actualizada";

        } else {

            $row->update($data_ev);

            $data_ev = $data_ev + [
                    'correct_answers' => $correct_answers,
                    'failed_answers' => $failed_answers,
                    'grade' => round($new_grade, 2),
                ];

            $data_ev['ev_updated'] = 0;
            $data_ev['ev_updated_msg'] = "(0) Evaluación no actualizada (nota obtenida menor que nota existente)";
        }

        $data_ev['tema_siguiente'] = $next_topic->id ?? NULL;
        $data_ev['curso'] = $topic->course;
        $data_ev['curso_id'] = $topic->course_id;
        $data_ev['tema_id'] = $topic->id;
        // $data_ev['intentos_realizados'] = $row->attempts;
        $data_ev['encuesta_pendiente'] = NULL;

        $row_course = SummaryCourse::updateUserData($topic->course);
        $row_user = SummaryUser::updateUserData();

        if ($row_course->status->code == 'enc_pend') {

            $poll = $topic->course->polls()->first();
            $data_ev['encuesta_pendiente'] = $poll->id ?? NULL;
        }

        return response()->json(['error' => false, 'data' => $data_ev], 200);
    }

    public function cargar_preguntas($topic_id)
    {
        $topic = Topic::with('evaluation_type', 'course')->find($topic_id);

        $is_qualified = $topic->evaluation_type->code == 'qualified';
        $is_random = $is_qualified;
        $type_code = $is_qualified ? 'select-options' : 'written-answer';

        if (!$topic) return response()->json(['data' => ['msg' => 'Not found'], 'error' => true], 200);
        if ($is_qualified AND !$topic->evaluation_verified) return response()->json(['data' => ['msg' => 'Not verified'], 'error' => true], 200);

        $row = SummaryTopic::setStartQuizData($topic);

        if ($row->hasNoAttemptsLeft())
            return response()->json(['error' => true, 'msg' => 'Sin intentos.'], 200);

        if ($row->isOutOfTimeForQuiz())
            return response()->json(['data' => ['msg' => 'Fuera de tiempo'], 'error' => true], 200);

        $limit = NULL;
        // $limit = auth()->user()->getSubworkspaceSetting('mod_evaluaciones', 'preg_x_ev');

        // $limit = $config_quiz['preg_x_ev'] ?? 5;

        if ($type_code == 'written-answer') {

            $questions = Question::getQuestionsForQuiz($topic, $limit, $is_random, $type_code);

        } else {

            $questions = Question::getQuestionsWithScoreForQuiz($topic, $limit, $is_random, $type_code);
        }

        if (count($questions) == 0)
            return response()->json(['error' => true, 'data' => null], 200);

        $data = [
            'nombre' => $topic->name,
            'posteo_id' => $topic->id,
            'curso_id' => $topic->course_id,
            'preguntas' => $questions,
            'tipo_evaluacion' => $topic->evaluation_type->code ?? NULL,
            'attempt' => [
                'started_at' => $row->current_quiz_started_at->format('d/m/Y G:i a'),
                'finishes_at' => $row->current_quiz_finishes_at->format('d/m/Y G:i a'),
                'diff_in_minutes' => now()->diffInMinutes($row->current_quiz_finishes_at),
            ],
        ];

        // SummaryTopic::setUserLastTimeEvaluation($topic);
        // SummaryCourse::setUserLastTimeEvaluation($topic->course);
        // SummaryUser::setUserLastTimeEvaluation();

        return response()->json(['error' => false, 'data' => $data], 200);
    }

    // public function preguntasIntentos_v7($topic_id = null, $user_id = null, $source)
    // {
    //     if ( !$topic_id && !$user_id ) return ['error' => 2, 'data' => null];

    //     $topic = Topic::with('course.topics')->find($topic_id);

    //     $row = SummaryTopic::incrementUserAttempts($topic);

    //     if (!$row) return ['error' => 1, 'data' => null];

    //     SummaryCourse::incrementUserAttempts($topic->course);
    //     SummaryUser::incrementUserAttempts();

    //     return ['error' => 0, 'data' => $row->attempts];
    // }

    public function guarda_visitas_post(Topic $topic)
    {
        $topic->load('course.topics');

        $row = SummaryTopic::incrementViews($topic);

        if (!$row) return ['error' => 1, 'data' => null];

        SummaryCourse::incrementViews($topic->course);

        $row_user = SummaryUser::getCurrentRow(auth()->user());

        if (!$row_user) SummaryUser::storeData(auth()->user());

        return ['error' => 0, 'data' => $row];
    }

    public function contador_tema_reseteo(Topic $topic)
    {
        $topic->load('course.schools');

        $user = auth()->user()->load('subworkspace');

        $row = SummaryTopic::getCurrentRow($topic, $user);

        $counter = false;

        if ($row and $row->hasFailed() and $row->hasNoAttemptsLeft()) {

            $times = [];

            if ($topic->course->reinicios_programado)
                $times[] = $topic->course->reinicios_programado;

            // if ($topic->course->reinicios_programado)
            //     $times[] = $topic->course->reinicios_programado;

            if (auth()->user()->subworkspace->reinicios_programado)
                $times[] = auth()->user()->subworkspace->reinicios_programado;

            if (count($times) > 0) {

                $scheduled = false;
                $minutes = 0;

                foreach ($times as $time) {

                    if ($time['activado']) {

                        $scheduled = true;
                        $minutes = $time['tiempo_en_minutos'];

                        break;
                    }
                }

                if ($scheduled and $row->last_time_evaluated_at) {

                    $finishes_at = $row->last_time_evaluated_at->addMinutes($minutes);
                    $counter = $finishes_at->diff(now())->format('%y/%m/%d %H:%i:%s');
                }
            }
        }

        return ['error' => 0, 'data' => $counter];
    }

    public function preguntasRptasUsuario(Topic $topic)
    {
        $row = SummaryTopic::getCurrentRow($topic);

        $questions_id = ($row && $row->answers) ? collect($row->answers)->pluck('preg_id') : [];

        $questions = Question::where('topic_id', $topic->id)
            ->whereIn('id', $questions_id)
            ->where('active', ACTIVE)
            ->get();

        return ['error' => count($questions) == 0, 'data' => $questions];
    }

    public function guardaEvaluacionAbierta(Request $request)
    {
        $answers = json_decode(urldecode($request->rptas), true);
        $topic = Topic::with('course')->find($request->post_id);
        // $source = $request->fuente;

        if (!$topic) return ['error' => 2, 'msg' => 'Topic not found'];

        $row = SummaryTopic::getCurrentRow($topic);

        if (!$row) return ['error' => 0, 'msg' => 'Not found'];

        if ($row->answers) return ['error' => 3, 'msg' => 'Respuestas ya registradas'];

        $status_taken = Taxonomy::getFirstData('topic', 'user-status', 'realizado');

        // $answers = json_encode($answers, JSON_UNESCAPED_UNICODE);
        $row->update(['answers' => $answers, 'status_id' => $status_taken->id]);

        SummaryCourse::updateUserData($topic->course);
        SummaryUser::updateUserData();

        return ['error' => 0, 'msg' => 'Respuestas registradas'];
    }

    public function getFreePolls()
    {
        $user = auth()->user();
        $workspace = $user->subworkspace->parent;

        $polls = Poll::whereRelation('type', 'code', 'libre')
            ->whereRelation('questions', 'active', ACTIVE)
            ->where('workspace_id', $workspace->id)
            ->where('active', ACTIVE)->get();

        $temp = [];
        foreach ($polls as $poll) {
            $q_answer = PollQuestionAnswer::whereRelation('pregunta.poll', 'id', $poll->id)
                ->where('user_id', $user->id)
                ->first();

            $temp[] = [
                'id' => $poll->id,
                'tipo' => $poll->type->code,
                'titulo' => $poll->titulo,
                'imagen' => get_media_url($poll->imagen),
                'estado' => $poll->active,
                'solved_poll' => (bool)$q_answer
            ];
        }

        return $this->success(['polls' => $temp]);
    }
}
