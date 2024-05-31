<?php

namespace App\Models\Mongo;
use App\Models\Media;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\Model;

class QuizAuditEvaluation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'quiz_audit_evaluations';
    protected $fillable = [
        "active_results","attempts","total_attempts","current_quiz_started_at","current_quiz_finishes_at",
        "taking_quiz","preguntas" ,"correct_answers","failed_answers","passed","answers" ,
        "grade","status_id","ev_updated","contador","tema_siguiente","curso_id",
        "tema_id","encuesta_pendiente","last_time_evaluated_at",
        "remaining_attempts","nombre_tema","nombre_curso","show_certification_to_user","passing_grade",
        "maximun_grade"
    ];
    protected function saveDataAndGenerateQR($data,$user){
        unset($data['course']);
        unset($data['curso']);
        unset($data['ev_updated_msg']);
        $data['last_time_evaluated_at'] = $data['last_time_evaluated_at']->format('Y-m-d H:i:s');
        // dd($data);
        $quiz_audit = self::create($data);
        $course_id = $data['curso_id'];
        $topic_id = $data['tema_id'];
        $route_qr = config('app.web_url')."validador-evaluacion?identificator={$quiz_audit->id}&course_id={$course_id}&topic_id={$topic_id}";
        $qr_code_string = generate_qr_code_in_base_64($route_qr,300,300,1,1);
        $str_random = Str::random(5);
        $name_image = $user->id . '-' . $quiz_audit->id . '-' . date('YmdHis') . '-' . Str::random(4).'.png';
        // Ruta donde se guardará la imagen en el servidor
        $path = 'validador-evaluacion-qr/'.$course_id.'/'.$name_image;
        Media::uploadMediaBase64(name:'', path:$path, base64:$qr_code_string,save_in_media:false);
        return ['image_qr' => get_media_url($path),'identifier' => $quiz_audit->id];
    }

    protected function validateInfoQuiz($data){
        $identifier = $data['identifier'] ?? null;
        $course_id = (int) $data['course_id'] ?? null;
        $topic_id = (int) $data['topic_id'] ?? null;
        if(is_null($identifier) || is_null($course_id) || is_null($topic_id)){
            return [];
        }
        $quiz_info = QuizAuditEvaluation::where('_id',$identifier)->where('curso_id',$course_id)->where('tema_id',$topic_id)->first();
        if($quiz_info){
            // unset($quiz_info['_id']);
            $quiz_info['indentifier'] = $quiz_info['id'];
            unset($quiz_info['answers']);
            unset($quiz_info['preguntas']);
            unset($quiz_info['updated_at']);
            unset($quiz_info['created_at']);
        }
        return $quiz_info;
    }
}
