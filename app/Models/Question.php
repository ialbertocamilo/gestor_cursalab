<?php

namespace App\Models;

use App\Imports\ExamenImport;

class Question extends BaseModel
{
    protected $fillable = [
        'topic_id', 'type_id', 'pregunta',
        'rptas_json', 'rpta_ok', 'active', 'required', 'score',
    ];

    protected $casts = [
        'rptas_json' => 'array',
        // 'score' => 'integer',
    ];

    public $defaultRelationships = [
        'type_id' => 'type',
        'topic_id' => 'topic'
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    // $current = $request->all();
    // return [__function__, __class__, $current];
    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    protected function getByTopicAndType($topic, $code)
    {
        // ->select('id', 'tipo_pregunta', 'pregunta', 'ubicacion', 'estado', 'rptas_json')
        return Question::where('active', ACTIVE)
                    ->where('topic_id', $topic->id)
                    ->whereRelation('type', 'code', $code)
                    ->get();
    }

    protected function getQuestionsForQuiz($topic, $limit = 5, $random = true, $code = 'selecciona')
    {
        $questions = Question::getByTopicAndType($topic, $code);

        if ($random) $questions = $questions->shuffle();

        if ($limit) $questions = $questions->take($limit);

        if ($random) $this->setRandomOptions($questions);

        return $questions;
    }

    protected function getQuestionsWithScoreForQuiz($topic, $limit = 5, $random = true, $code = 'selecciona')
    {
        $questions = Question::getByTopicAndType($topic, $code);

        $questionsRequired = $questions->where('required', 1)->whereNotNull('score');

        $base = 20;
        $sum_required = $questionsRequired->sum('score');

        $missing_score = $base - $sum_required;

        $questionsNotRequired = $questions->where('required', '<>', 1)->whereNotNull('score');

        $sum_not_required = $questionsNotRequired->sum('score');
        $i = 0;
$this->info('Questions count:' . count($questions));
        if ( ($sum_required + $sum_not_required) >= $base ) {

            //ORDENAR SEGUN LA CONDICION DE PUNTAJES

            if ($missing_score == 0) {

                $preguntas = $questionsRequired;

            } else {

                $val = true;

                while ($val) :

                    $res = Question::randomItem(NULL, $missing_score, $questionsNotRequired);

                    if ($res['sum'] == $missing_score)
                        $val = false;

                endwhile;

                $preguntas = $questionsRequired->merge($res['data']);
            }

            $this->setRandomOptions($preguntas);

            return $preguntas->shuffle();

        }

        return [];
    }

    protected function randomItem($limit_group, $sum_to, $questions)
    {
        $shuffled = $questions->shuffle()->all();

        $sum = 0;
        $data = [];

        foreach ($shuffled as $question) {

            $s = $question->score + $sum;

            if ($s <= $sum_to && $sum <= $sum_to) {
                $sum = $sum + $question->score;
                $data[] = $question;
            }
        }

        return compact('data', 'sum');
    }

    protected function setRandomOptions(&$questions)
    {
        foreach ($questions as $question)
        {
            $temp_questions = collect();

            foreach ($question->rptas_json as $key => $value)
            {
                $temp_questions->push(['id' => $key, 'opc' => $value]);
            }

            $question->rpta_ok = NULL;

            $question->rptas_json = $temp_questions->shuffle()->all();
        }
    }

    protected function verifyEvaluation($topic)
    {
        if (!$topic->assessable) {

            $topic->update(['evaluation_verified' => false]);

            return ['status' => false, 'message' => null];
        }

        if ($topic->assessable && $topic->evaluation_type->code == "open") {

            $topic->update(['evaluation_verified' => true]);

            return ['status' => true, 'message' => null];
        }

        $questions = $topic->questions()->get();

        $total_score = $questions->where('active', ACTIVE)->sum('score');

        $s_ev_base = 20;
        $score_missing = $sum = 0;
        $sum_not_required = $sum_required = 0;

        $current_system = $topic->qualification_type->position;

        $data = [];

        // if ($topic->evaluation_type->code == 'qualified') {

        $sum = $questions->where('active', ACTIVE)->sum('score');

        $sum_required = $questions->where('active', ACTIVE)->where('required', ACTIVE)->sum('score');

        $sum_not_required = $sum - $sum_required;
        $score_missing = $s_ev_base - $sum_required;

        $sum = calculateValueForQualification($sum, $current_system);
        $sum_required = calculateValueForQualification($sum_required, $current_system);
        $sum_not_required = calculateValueForQualification($sum_not_required, $current_system);

        $data = compact('sum', 'sum_required', 'sum_not_required', 'score_missing');
        // }

        if ($score_missing == 0) {

            // $data['score_missing'] = calculateValueForQualification()

            $topic->update(['evaluation_verified' => true]);

            $message = 'La evaluación es correcta; tomar en cuenta que su evaluación solo tiene preguntas obligatorias.';

            return ['message' => $message, 'status' => true, 'data' => $data];
        }

        $questionsNotRequired = $questions->where('active', ACTIVE)->where('required', '<>', 1);

        $i = 0;
        $missings = [];
        $verified = false;

        while ($i < 20) :

            $res = Question::getMissingScoreValue($score_missing, $questionsNotRequired);

            if ($res == 0) {

                $verified = true;
                break;
            }

            $missings[] = ['value' => $res];
            $i++;

        endwhile;

        if ($verified) {

            $message = 'La evaluación es correcta.';

        } else {

            $score_missing = collect($missings)->pluck('value')->min();

            $score_missing = calculateValueForQualification($score_missing, $current_system);

            $message = 'Se necesita ' . $score_missing . ' punto(s) para que la evaluación esté completa.';

            $data = compact('sum', 'sum_required', 'sum_not_required', 'score_missing');
        }

        $topic->update(['evaluation_verified' => $verified]);

        return ['message' => $message, 'status' => $verified, 'data' => $data];
    }

    protected function getMissingScoreValue($score_missing, $questions)
    {
        $shuffled = $questions->shuffle()->all();

        $sum = 0;

        foreach ($shuffled as $question) {

            $result = $question->score + $sum;

            if ($result <= $score_missing && $sum <= $score_missing) {
                $sum = $sum + $question->score;
            }
        }

        return $score_missing - $sum;
    }

    protected function checkScoreLeft($topic, $question_id, $data)
    {
        if (! ($data['required'] AND $data['active']) )
            return ['status' => false];

        $base = 20;

        $sum = $topic->questions()
                    ->where('active', ACTIVE)
                    ->where('required', ACTIVE)
                    ->where('id', '<>', $question_id)
                    ->sum('score');

        $status = ($sum + calculateValueForQualification($data['score'], 20, $topic->qualification_type->position)) > $base;
        $missing = $base - $sum;

        $missing = calculateValueForQualification($missing, $topic->qualification_type->position);

        $message = 'Solo le quedan ' . $missing . ' punto(s) para preguntas obligatorias.';

        return ['message' => $message, 'status' => $status];
    }

    protected function import($data)
    {
        // Load question type from database

        $selectQuestionType = Taxonomy::where('group', 'question')
                                    ->where('type', 'type')
                                    ->where('code', 'select-options')
                                    ->first();

        $writtenQuestionType = Taxonomy::where('group', 'question')
                                ->where('type', 'type')
                                ->where('code', 'written-answer')
                                ->first();

        try {

            $topic = Topic::find($data['topic_id']);
            // $topic_qualification = $topic->qualification_type;

            // Calculate total score with the existing questions

            $totalScore = Question::calculateTopicQuestionsScore($data['topic_id']);

            $model = new ExamenImport;
            $model->topic_id = $data['topic_id'];
            $model->totalScore = $totalScore;
            $model->maxScore = $topic->qualification_type->position;
            $model->selectQuestionTypeId = $selectQuestionType->id;
            $model->writtenQuestionTypeId = $writtenQuestionType->id;
            $model->isQualified = $data['isQualified'];
            $model->import($data['archivo']);

            if ($model->failures()->count()) {

                return [
                    'message' => 'Se encontraron algunos errores.',
                    'errors' => $model->failures(),
                    'status' => 'error',
                ];
            }

        } catch (\Exception $e) {
            report($e);
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Registros ingresados correctamente.'
        ];
    }

    /**
     * Calculate topic's questions total score
     */
    public static function calculateTopicQuestionsScore($topicId) {

        return Question::where('topic_id', $topicId)
                ->sum('score');
    }
}
