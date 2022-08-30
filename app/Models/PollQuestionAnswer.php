<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollQuestionAnswer extends BaseModel
{
    protected $table = 'poll_question_answers';

    protected $fillable = [
        'encuesta_id', 'curso_id', 'pregunta_id', 'usuario_id', 'respuestas', 'tipo_pregunta', 'created_at', 'updated_at'
    ];

    public function pregunta()
    {
        return $this->belongsTo(PollQuestion::class, 'pregunta_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    protected function updatePollQuestionAnswers($course_id, $poll_question_id, $user_id, $question_type, $answers)
    {
        $question_type_taxonomy = Taxonomy::getFirstData('poll', 'tipo-pregunta', $question_type);

        PollQuestionAnswer::updateOrInsert(
            ['course_id' => $course_id, 'user_id' => $user_id],
            [
                'course_id' => $course_id,
                'user_id' => $user_id,
                'poll_question_id' => $poll_question_id,
                'type_id' => $question_type_taxonomy?->id,
                'respuestas' => $answers
            ]
        );
    }
}
