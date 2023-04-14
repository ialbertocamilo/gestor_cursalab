<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollAnswerStoreRequest;
use App\Models\Course;
use App\Models\Poll;
use App\Models\PollQuestionAnswer;
use App\Models\SummaryCourse;
use App\Models\Taxonomy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Monolog\Handler\IFTTTHandler;

class RestCourseController extends Controller
{
    public function courses()
    {
        $user = Auth::user();
//        $courses = $user->getCurrentCourses();
        $courses = $user->getCurrentCourses(withRelations: 'course-view-app-user');

        $data = Course::getDataToCoursesViewAppByUser($user, $courses);

        return $this->successApp(['data' => $data]);
    }

    public function loadPoll(Course $course)
    {
        if ($course->hasBeenValidated())
            return ['error' => 0, 'data' => null];

        $poll = $course->polls()->with([
            'questions' => function ($q) {
                $q->with('type:id,code')
                    ->where('active', ACTIVE)
                    ->select('id', 'poll_id', 'titulo', 'type_id', 'opciones');
            }
        ])
            ->select('id', 'titulo', 'imagen', 'anonima')
            ->where('active', ACTIVE)
            ->first();

        return $this->success(compact('poll'));
    }

    public function savePollAnswers(PollAnswerStoreRequest $request)
    {
        $data = $request->validated();

        $user = auth()->user();
        $poll = Poll::find($data['enc_id']);
        $course = Course::find($data['curso']);
        $info = $data['data'];

        foreach ($info as $value_data) {
            if (!is_null($value_data) && ($value_data['tipo'] == 'multiple' || $value_data['tipo'] == 'opcion-multiple')) {
                $multiple = array();
                $ddd = array_count_values($value_data['respuesta']);
                if (!is_null($ddd)) {
                    foreach ($ddd as $key => $value) {
                        if ($value % 2 != 0) {
                            array_push($multiple, $key);
                        }
                    }
                }
                $query1 = PollQuestionAnswer::updatePollQuestionAnswers($course->id, $value_data['id'], $user->id, $value_data['tipo'], json_encode($multiple, JSON_UNESCAPED_UNICODE));
            }
            if (!is_null($value_data) && $value_data['tipo'] == 'califica') {
                // [{"preg_cal":"Califica","resp_cal":5}] <- format json
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
                $query2 = PollQuestionAnswer::updatePollQuestionAnswers($course->id, $value_data['id'], $user->id, $value_data['tipo'], json_encode($multiple, JSON_UNESCAPED_UNICODE));
            }
            if (!is_null($value_data) && $value_data['tipo'] == 'texto') {
                $query3 = PollQuestionAnswer::updatePollQuestionAnswers($course->id, $value_data['id'], $user->id, $value_data['tipo'], trim($value_data['respuesta']));
            }
            if (!is_null($value_data) && $value_data['tipo'] == 'simple') {
                $query4 = PollQuestionAnswer::updatePollQuestionAnswers($course->id, $value_data['id'], $user->id, $value_data['tipo'], $value_data['respuesta']);
            }
            if (!is_null($value_data) && $value_data['tipo'] == 'opcion-simple') {
                $query4 = PollQuestionAnswer::updatePollQuestionAnswers($course->id, $value_data['id'], $user->id, $value_data['tipo'], $value_data['respuesta']);
            }
        }

        Poll::updateSummariesAfterCompletingPoll($course, $user);

        SummaryCourse::updateUserData($course, $user);

        return $this->success(['msg' => 'Encuesta guardada.']);
    }


    public function getCertificates(Request $request)
    {
        $user = auth()->user();

        $user_courses = $user->getCurrentCourses(withRelations: 'soft');
//        $user_courses = collect($user->getCurrentCourses(response_type: 'courses-unified'));
        $user_courses_id = $user_courses->pluck('id');
        $user_compatibles_courses_id = $user_courses->whereNotNull('compatible')->pluck('compatible.course_id');

        $all_courses_id = $user_courses_id->merge($user_compatibles_courses_id);

//        $user_courses_id = array_column($user_courses, 'id');

        $query = SummaryCourse::query()
            // ->whereHas('course',function($q){
            //     $q->whereNotNull('plantilla_diploma');
            // })
            ->where('user_id', $user->id)
            ->whereIn('course_id', $all_courses_id->toArray())
            ->whereNotNull('certification_issued_at');

        if ($request->type == 'accepted')
            $query->whereNotNull('certification_accepted_at');

        if ($request->type == 'pending')
            $query->whereNull('certification_accepted_at');

        $certificates = $query->get();

        $temp = [];

        $qs = $request->q ?? NULL;

        foreach ($user_courses as $user_course) {

            if ($qs AND !stringContains($user_course->name, $qs))
                continue;

            $certificate = $certificates->where('course_id', $user_course->id)->first();

            if ($certificate) {

                $temp[] = [

                    'course_id' => $certificate->course_id,
                    'name' => $certificate->course->name,
                    'accepted' => $certificate->certification_accepted_at ? true : false,
                    'issued_at' => $certificate->certification_issued_at->format('d/m/Y'),
                    'ruta_ver' => "tools/ver_diploma/{$user->id}/{$certificate->course_id}",
                    'ruta_descarga' => "tools/dnc/{$user->id}/{$certificate->course_id}",

                    'compatible' => null,
                ];

                continue;
            }

            if ($user_course->compatible):

                $compatible_certificate = $certificates->where('course_id', $user_course->compatible->course_id)->first();

                if ($compatible_certificate):

                    $add = "?original_id={$user_course->id}";

                    $temp[] = [

                        'course_id' => $compatible_certificate->course_id,
                        'name' => $user_course->name,
                        'accepted' => $compatible_certificate->certification_accepted_at ? true : false,
                        'issued_at' => $compatible_certificate->certification_issued_at->format('d/m/Y'),
                        'ruta_ver' => "tools/ver_diploma/{$user->id}/{$compatible_certificate->course_id}{$add}",
                        'ruta_descarga' => "tools/dnc/{$user->id}/{$compatible_certificate->course_id}{$add}",

                        'compatible' => [
                            'course_id' => $compatible_certificate->course->id,
                            'name' => $compatible_certificate->course->name,
                        ],
                    ];

                endif;
            endif;
        }

        return $this->success(['data' => $temp]);
    }

    public function acceptCertification(Course $course)
    {
        $user = auth()->user();

        $row = SummaryCourse::getCurrentRow($course, $user);

        $data = ['error' => true, 'data' => ['message' => 'No encontrado']];

        if ($row and $row->certification_issued_at) {

            $row->update(['certification_accepted_at' => now()]);

            $data = ['error' => false, 'data' => ['message' => 'Aceptado']];
        }

        return $data;
    }


}
