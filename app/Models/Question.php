<?php

namespace App\Models;

class Question extends BaseModel
{
    protected $fillable = [
        'topic_id', 'type_id', 'pregunta',
        'rptas_json', 'rpta_ok', 'active', 'required', 'score',
    ];

    protected $casts = [
        'rptas_json' => 'array',
    ];

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

        $questions = $questions->take($limit);

        if ($random) $this->setRandomOptions($questions);

        return $questions;
    }

    protected function getQuestionsWithScoreForQuiz($topic, $limit = 5, $random = true, $code = 'selecciona')
    {
        $questions = Question::getByTopicAndType($topic, $code);

        $questionsRequired = $questions->where('required', 1);

        $base = 20;
        $sum_required = $questionsRequired->sum('score');

        $missing_score = $base - $sum_required;

        $questionsNotRequired = $questions->where('required', '<>', 1);

        $sum_not_required = $questionsNotRequired->sum('score');
        $i = 0;

        if ( ($sum_required + $sum_not_required) >= $base ) {
            
            //ORDENAR SEGUN LA CONDICION DE PUNTAJES
            
            $val = true;
            
            while ($val) :
                
                $res = Question::randomItem(NULL, $missing_score, $questionsNotRequired);

                if ($res['sum'] == $missing_score)
                    $val = false;
                
            endwhile;

            $preguntas = $questionsRequired->merge($res['data']);
            
            //DESORDENAR PREGUNTAS
            foreach ($preguntas as $pregunta) {

                //crear variable donde estara el array modificado
                $val_decodificado = [];

                foreach ($pregunta->rptas_json as $obj) {
                    //borrando la respuestas
                    $obj['correcta'] = null;
                    //agregadno al array
                    array_push($val_decodificado, $obj);
                }

                $shuffled = collect($val_decodificado)->shuffle()->all();

                $pregunta->rptas_json = $shuffled;
            }

            return $preguntas;

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

        $data = [];

        // if ($topic->evaluation_type->code == 'qualified') {

        $sum = $questions->where('active', ACTIVE)->sum('score');

        $sum_required = $questions->where('active', ACTIVE)->where('required', ACTIVE)->sum('score');

        $sum_not_required = $sum - $sum_required;
        $score_missing = $s_ev_base - $sum_required;

        $data = compact('sum', 'sum_required', 'sum_not_required', 'score_missing');
        // }

        if ($score_missing == 0) {

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

            $min_missing_score = collect($missings)->pluck('value')->min();
            $message = 'Se necesita ' . $min_missing_score . ' punto(s) para que la evaluación esté completa';
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

        $status = ($sum + $data['score']) > $base;
        $missing = $base - $sum;

        $message = 'Solo le quedan ' . $missing . ' punto(s) para preguntas obligatorias.';

        return ['message' => $message, 'status' => $status];
    }
}
