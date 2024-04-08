<?php

namespace App\Http\Controllers\ApiRest;

use Carbon\Carbon;
use App\Models\Poll;
use App\Models\PollQuestionAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\Question;
use App\Models\Taxonomy;

use App\Models\SummaryUser;

use App\Models\Announcement;
use App\Models\SummaryTopic;
use App\Models\SummaryCourse;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuizzAnswerStoreRequest;


class RestQuizController extends Controller
{
    public function evaluar_preguntas(Request $request)
    {
        $user = auth()->user();
        $resultado = 0;
        //if is offlined set data when the answers was created.
        $is_offline = $request->is_offline;
        $created_at = $request->created_at;
        $topic = Topic::with('course.topics.evaluation_type')->find($request->tema);

        if (count($request->respuestas) == 0)
            return response()->json(['error' => true, 'msg' => 'Respuestas no enviadas.'], 200);

        $row = SummaryTopic::getCurrentRow($topic);

        if ($row->hasNoAttemptsLeft(null,$topic->course))
            return response()->json(['error' => true, 'msg' => 'Sin intentos restantes.'], 200);

        if (!$row)
            return response()->json(['error' => true, 'msg' => 'La evaluación no existe.'], 200);

        [$correct_answers, $failed_answers, $correct_answers_score] = Topic::evaluateAnswers($request->respuestas, $topic);

        $new_grade = $correct_answers_score;
        // $new_grade = SummaryTopic::calculateGrade($correct_answers, $failed_answers);
        $passed = SummaryTopic::hasPassed($new_grade,null,$topic->course);

        $data_ev = [
            'active_results' => (bool) $topic->active_results,
            'attempts' => $row->attempts + 1,
            'total_attempts' => $row->total_attempts + 1,
            'last_time_evaluated_at' => ($is_offline && $created_at) ? $created_at : now(),
            'current_quiz_started_at' => NULL,
            'current_quiz_finishes_at' => NULL,
            'taking_quiz' => NULL,
        ];

        // === tema: mostrar resultados ===
        $data_ev['preguntas'] = [];
        if($topic->active_results) {
            $data_ev['preguntas'] = Topic::evaluateAnswers2($request->respuestas, $topic);
        }
        // === tema: mostrar resultados ===

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

        $data_ev['contador'] = Topic::getCounter($topic);
        $data_ev['tema_siguiente'] = $next_topic->id ?? NULL;
        $data_ev['curso'] = $topic->course;
        $data_ev['curso_id'] = $topic->course_id;
        $data_ev['tema_id'] = $topic->id;
        // $data_ev['intentos_realizados'] = $row->attempts;
        $data_ev['encuesta_pendiente'] = NULL;

        $row_course = SummaryCourse::updateUserData(course:$topic->course,certification_issued_at:$created_at);
        $row_user = SummaryUser::updateUserData();

        if ($row_course->status->code == 'enc_pend') {

            $poll = $topic->course->polls()->first();
            $data_ev['encuesta_pendiente'] = $poll->id ?? NULL;
        }

        // $data_ev['new_grade'] = calculateValueForQualification($data_ev['new_grade'], $topic->qualification_type->position);
        $data_ev['grade'] = calculateValueForQualification($data_ev['grade'], $topic->qualification_type->position);

        return response()->json(['error' => false, 'data' => $data_ev], 200);
    }

    public function cargar_preguntas($topic_id,Request $request)
    {
        $topic = Topic::with('evaluation_type', 'course','course.modality:id,code')->find($topic_id);
        $code_modality = $topic->course->modality->code;
        if(($code_modality == 'in-person' || $code_modality=='virtual') && !$topic->isAccessibleEvaluation()){
            return response()->json(['error' => true, 'data' => [
                'is_accessible'=>false,
                'message' => 'La evaluación no esta disponible.'
            ]], 200);
        }
        // dd($topic->course->modality->code);
        if ($topic->course->hasBeenValidated())
            return ['error' => 0, 'data' => null];

        $is_qualified = $topic->evaluation_type->code == 'qualified';
        $is_random = $is_qualified;
        $type_code = $is_qualified ? 'select-options' : 'written-answer';

        if (!$topic) return response()->json(['data' => ['msg' => 'Not found'], 'error' => true], 200);
        if ($is_qualified AND !$topic->evaluation_verified) return response()->json(['data' => ['msg' => 'Evaluación no disponible. Intente de nuevo en unos minutos. [A]'], 'error' => true], 200);

        $row = SummaryTopic::setStartQuizData($topic);
        if(!$row && ($code_modality != 'asynchronous' || $request->is_offline)){
            $user = auth()->user();
            $row = SummaryTopic::storeData($topic, $user);
            SummaryCourse::storeData($topic->course, $user);
        }
        if (!$row)
            return response()->json(['error' => true, 'data' => ['msg' => 'Tema no iniciado.']], 200);

        // not consider open evaluation to attempts and time validations
        if ($row->hasNoAttemptsLeft(null,$topic->course) && $is_qualified)
            return response()->json(['error' => true, 'msg' => 'Sin intentos.'], 200);

        if(!$request->is_offline){
            if ($row->isOutOfTimeForQuiz() && $is_qualified && $code_modality == 'asynchronous'){
                return response()->json(['data' => ['msg' => 'Fuera de tiempo. Intente de nuevo en unos minutos.'], 'error' => true], 200);
            }
        }

        $limit = NULL;
        // $limit = auth()->user()->getSubworkspaceSetting('mod_evaluaciones', 'preg_x_ev');

        // $limit = $config_quiz['preg_x_ev'] ?? 5;

        if ($type_code == 'written-answer') {

            $questions = Question::getQuestionsForQuiz($topic, $limit, $is_random, $type_code);

        } else {

            $questions = Question::getQuestionsWithScoreForQuiz($topic, $limit, $is_random, $type_code);
        }

        if (count($questions) == 0)
            return response()->json(['error' => true, 'data' => ['msg' => 'Evaluación no disponible. Intente de nuevo en unos minutos. [B]']], 200);

        
        $status = 'started';
        if($code_modality != 'asynchronous'){
            $modality_in_person_properties = $topic->modality_in_person_properties;
            $parse_started_at = Carbon::parse($modality_in_person_properties?->evaluation->date_init);
            $parse_finishes_at = Carbon::parse($modality_in_person_properties?->evaluation->date_finish);
            $diff_in_minutes = now()->diffInMinutes($parse_finishes_at);
            $status = $modality_in_person_properties?->evaluation->status;
            $started_at = $parse_started_at->format('Y/m/d H:i:s');
            $finishes_at = $parse_finishes_at->format('Y/m/d H:i:s');
            // $diff = $finishes_at->diff($current_time);
            // $diff_in_minutes = sprintf('%02d:%02d', $diff->h, $diff->i);
        }else{
            $started_at = $row?->current_quiz_started_at->format('Y/m/d H:i:s');
            $finishes_at = $row?->current_quiz_finishes_at->format('Y/m/d H:i:s');
            $diff_in_minutes = ($started_at && $finishes_at)  ?  now()->diffInMinutes($row->current_quiz_finishes_at) : null;
        }
        $data = [
            'nombre' => $topic->name,
            'posteo_id' => $topic->id,
            'curso_id' => $topic->course_id,
            'preguntas' => $questions,
            'tipo_evaluacion' => $topic->evaluation_type->code ?? NULL,
            'attempt' => [
                'server' => now()->format('Y/m/d H:i'),
                'started_at' => $started_at,
                'finishes_at' => $finishes_at,
                'diff_in_minutes' => $diff_in_minutes,
                'status' => $status
            ],
        ];

        $userDatetimeTimestamp = request()->get('user_datetime');

        if ($userDatetimeTimestamp && $code_modality == 'asynchronous') {

            $userDatetime = Carbon::parse($userDatetimeTimestamp);
            $minutesDifference = now()->diffInMinutes($userDatetime);

            // When time difference between client and server is more
            // than 5 minutes, adjust the datetime to match clients datetime

            if ($minutesDifference > 5) {

                // The differece is negative when server datetime is greater
                // than client's datetime

                if (now()->gte($userDatetime)) {
                    $minutesDifference *= -1;
                }

                $start = $row->current_quiz_started_at->addMinutes($minutesDifference);
                $end = $row->current_quiz_finishes_at->addMinutes($minutesDifference);
                $data['attempt'] = [
                    'started_at' => $start->format('Y/m/d H:i'),
                    'finishes_at' => $end->format('Y/m/d H:i'),
                    'diff_in_minutes' => now()->addMinutes($minutesDifference)->diffInMinutes($end),
                ];
            }
        }

        // Adds 24 hours for Agile
// This part is no longer necessary since user device time is now fixed
// when is different from the one in server
//        if ((env('MULTIMARCA') == 'true' && env('CUSTOMER_ID') == '3')) {
//            $data['attempt'] = [
//                'started_at' => $row->current_quiz_started_at->format('Y/m/d H:i'),
//                'finishes_at' => now()->addHours(24)->format('Y/m/d H:i'),
//                'diff_in_minutes' => now()->diffInMinutes(now()->addHours(24))
//            ];
//        }

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

        if ($topic->course->hasBeenValidated())
            return ['error' => 0, 'data' => null];

        $row = SummaryTopic::incrementViews($topic);

        if (!$row) return ['error' => 1, 'data' => null];

        SummaryCourse::incrementViews($topic->course);

        $row_user = SummaryUser::getCurrentRow(auth()->user());

        if (!$row_user) SummaryUser::storeData(auth()->user());

        return ['error' => 0, 'data' => $row];
    }

    public function contador_tema_reseteo(Topic $topic)
    {
        if ($topic->course->hasBeenValidated())
            return ['error' => 0, 'data' => null];

        $counter = Topic::getCounter($topic);

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

    public function getFreeQuestions(Poll $poll) {

        $questions = $poll->questions()->with('type:id,code')
                     ->where('active', ACTIVE)
                     ->select('id', 'poll_id', 'titulo', 'type_id', 'opciones')
                     ->get();

        return $this->success(compact('questions'));
    }

    public function saveFreeAnswers(QuizzAnswerStoreRequest $request) {
        $data = $request->validated();

        $user = auth()->user();
        $poll = Poll::find($data['enc_id']);
        $info = $data['data'];

        foreach ($info as $value_data) {
            if (!is_null($value_data) && ($value_data['tipo'] == 'multiple' || $value_data['tipo'] == 'opcion-multiple') ) {
                $multiple = array();
                $ddd = array_count_values($value_data['respuesta']);
                if (!is_null($ddd)) {
                    foreach ($ddd as $key => $value) {
                        if ($value % 2 != 0) {
                            array_push($multiple, $key);
                        }
                    }
                }
                $query1 = PollQuestionAnswer::updatePollQuestionAnswers(NULL, $value_data['id'], $user->id, $value_data['tipo'], json_encode($multiple, JSON_UNESCAPED_UNICODE));
            }
            if (!is_null($value_data) && $value_data['tipo'] == 'califica') {
                $multiple = array();
                $array_respuestas = $value_data['respuesta'];
                $ddd = array_count_values(array_column($array_respuestas, 'resp_cal'));
                $ttt = array();
                if (!is_null($array_respuestas) && count($array_respuestas) > 0) {
                    foreach ($array_respuestas as $key => $value) {
                        if (!is_null($value)) {
                            foreach ($ddd as $key2 => $val2) {
                                if ($key2 == $value['resp_cal']) {
                                    $value['preg_cal'] = "Califica";
                                    $ttt[$value['resp_cal']] = $value;
                                }
                            }
                        }
                    }
                }
                if (!is_null($ttt) && count($ttt) > 0) {
                    foreach ($ttt as $elemento) {
                        array_push($multiple, $elemento);
                    }
                }
                $query2 = PollQuestionAnswer::updatePollQuestionAnswers(NULL, $value_data['id'], $user->id, $value_data['tipo'], json_encode($multiple, JSON_UNESCAPED_UNICODE));
            }
            if (!is_null($value_data) && $value_data['tipo'] == 'texto') {
                $query3 = PollQuestionAnswer::updatePollQuestionAnswers(NULL, $value_data['id'], $user->id, $value_data['tipo'], trim($value_data['respuesta']));
            }
            if (!is_null($value_data) && $value_data['tipo'] == 'simple') {
                $query4 = PollQuestionAnswer::updatePollQuestionAnswers(NULL, $value_data['id'], $user->id, $value_data['tipo'], $value_data['respuesta']);
            }
            if (!is_null($value_data) && $value_data['tipo'] == 'opcion-simple') {
                $query4 = PollQuestionAnswer::updatePollQuestionAnswers(NULL, $value_data['id'], $user->id, $value_data['tipo'], $value_data['respuesta']);
            }
        }

        return $this->success(['msg' => 'Encuesta guardada.']);
    }
}
