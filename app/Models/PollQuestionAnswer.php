<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollQuestionAnswer extends BaseModel
{
    protected $table = 'poll_question_answers';

    protected $fillable = [
        'course_id', 'poll_question_id', 'user_id', 'respuestas', 'type_id'
    ];

    public function pregunta()
    {
        return $this->belongsTo(PollQuestion::class, 'poll_question_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'course_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }


    protected function updatePollQuestionAnswers($course_id, $poll_question_id, $user_id, $question_type, $answers)
    {
        $question_type_taxonomy = Taxonomy::getFirstData('poll', 'tipo-pregunta', $question_type);

        PollQuestionAnswer::updateOrInsert(
            [
                'course_id' => $course_id, 'user_id' => $user_id,
                'poll_question_id' => $poll_question_id
            ],
            [
                'course_id' => $course_id,
                'user_id' => $user_id,
//                'poll_question_id' => $poll_question_id,
                'type_id' => $question_type_taxonomy?->id,
                'respuestas' => $answers
            ]
        );
        cache_clear_model(PollQuestionAnswer::class);
    }
}
