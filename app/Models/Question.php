<?php

namespace App\Models;

class Question extends BaseModel
{
    protected $fillable = [
        'topic_id', 'type_id', 'pregunta',
        'rptas_json', 'rpta_ok', 'active'
    ];

    protected $casts = [
        'rptas_json' => 'json',
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

    protected function setRandomOptions(&$questions)
    {
        foreach ($questions as $question)
        {
            $temp_questions = collect();

            info('question->rptas_json');
            info($question->rptas_json);
            info('question->rptas_json->toArray()');
            info($question->rptas_json->toArray());

            foreach ($question->rptas_json as $key => $value)
            {
                $temp_questions->push(['id' => $key, 'opc' => $value]);
            }

            $question->rptas_json = $temp_questions->shuffle()->all();
        }
    }
}
