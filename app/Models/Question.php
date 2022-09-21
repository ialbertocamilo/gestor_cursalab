<?php

namespace App\Models;

use App\Imports\ExamenImport;

class Question extends BaseModel
{
    protected $fillable = [
        'topic_id', 'type_id', 'pregunta',
        'rptas_json', 'rpta_ok', 'active',
        'required', 'score'
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

    protected function setRandomOptions(&$questions)
    {
        foreach ($questions as $question)
        {
            $temp_questions = collect();
//            info($question->id);
//            info($question->rptas_json);
            foreach ($question->rptas_json as $key => $value)
            {
//                info($key." => ".$value);
                $temp_questions->push(['id' => $key, 'opc' => $value]);
            }

            $question->rptas_json = $temp_questions->shuffle()->all();
        }
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

            $model = new ExamenImport;
            $model->topic_id = $data['topic_id'];
            $model->selectQuestionTypeId = $selectQuestionType->id;
            $model->writtenQuestionTypeId = $writtenQuestionType->id;
            $model->isQualified = $data['isQualified'];
            $model->import($data['archivo']);

            if ($model->failures()->count()) {

                return [
                    'msg' => 'Se encontraron algunos errores.',
                    'errors' => $model->failures()
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
}
