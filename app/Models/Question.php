<?php

namespace App\Models;

class Question extends BaseModel
{
    protected $fillable = [
        'topic_id', 'type_id', 'pregunta',
        'rptas_json', 'rpta_ok',
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

    protected function getQuestionsForQuiz($topic, $limit, $random = true, $code = 'selecciona')
    {
        $questions = Question::getByTopicAndType($topic, $code);

        if ($random) {
        
            $questions = $questions->shuffle()->take($limit);
            $this->setRandomOptions($questions);
        
        } else {

            $questions->take($limit);
        }

        return $questions;
    }

    protected function setRandomOptions(&$questions)
    {
        foreach ($questions as $question)
        {
            // $rptas_json = json_decode($question->rptas_json, true);
            $temp_questions = collect();

            foreach ($question->rptas_json as $key => $value)
            {
                $temp_questions->push(['id' => $key, 'opc' => $value]);
            }

            $question->rptas_json = $temp_questions->shuffle()->all();
        }
    }
}
