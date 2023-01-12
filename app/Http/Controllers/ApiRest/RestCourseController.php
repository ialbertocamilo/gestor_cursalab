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

        info('all_courses_id');
        info($all_courses_id);

//        $user_courses_id = array_column($user_courses, 'id');

        $query = SummaryCourse::with([
            'course' => [
                'compatibilities_a:id',
                'compatibilities_b:id',
                'summaries' => function ($q) use ($user) {
                    $q
                        ->with('status:id,name,code')
                        ->where('user_id', $user->id);
                },
            ]
        ])
            ->where('user_id', $user->id)
            ->whereIn('course_id', $user_courses_id)
            ->whereNotNull('certification_issued_at');

        // if ($request->q)
        //     $query->whereIn('course_id', $filtered);

        // if ($request->q)
        //     $query->whereHas('course', function ($q) use ($request) {
        //         $q->where('name', 'like', "%{$request->q}%");
        //     });

        if ($request->type == 'accepted')
            $query->whereNotNull('certification_accepted_at');

        if ($request->type == 'pending')
            $query->whereNull('certification_accepted_at');

        $certificates = $query->get();

        info('certificates');
        info($certificates);
        $temp = [];

        foreach ($user_courses as $user_course) {

            if ($user_course->compatible):

                $compatible_certificate = $certificates->where('course_id', $user_course->compatible->course_id)->first();

                if ($compatible_certificate):

                    $temp_certificate = clone $compatible_certificate;

                    $temp_certificate->compatible_of = $user_course;

                    $certificates->push($temp_certificate);

//                    dd($temp_certificate->compatible_of->name, $compatible_certificate->compatible_of?->name);

                endif;
            endif;
        }

        foreach ($certificates as $key => $certificate) {

            $qs = $request->q ?? NULL;

            if ($certificate->compatible_of) {

                $original = $certificate->compatible_of;

                $add = "?original_id={$original->id}";

                if ($qs AND !stringContains($original->name, $qs))
                    continue;

                $temp[] = [

                    'course_id' => $certificate->course_id,
                    'name' => $original->name,
                    'accepted' => $certificate->certification_accepted_at ? true : false,
                    'issued_at' => $certificate->certification_issued_at->format('d/m/Y'),
                    'ruta_ver' => "tools/ver_diploma/{$user->id}/{$certificate->course_id}{$add}",
                    'ruta_descarga' => "tools/dnc/{$user->id}/{$certificate->course_id}{$add}",

                    'compatible' => [
                        'course_id' => $certificate->course->id,
                        'name' => $certificate->course->name,
                    ],
                ];

                continue;
            }

            if ($qs AND !stringContains($certificate->course->name, $qs))
                continue;

            $temp[] = [

                'course_id' => $certificate->course_id,
                'name' => $certificate->course->name,
                'accepted' => $certificate->certification_accepted_at ? true : false,
                'issued_at' => $certificate->certification_issued_at->format('d/m/Y'),
                'ruta_ver' => "tools/ver_diploma/{$user->id}/{$certificate->course_id}",
                'ruta_descarga' => "tools/dnc/{$user->id}/{$certificate->course_id}",

                'compatible' => null,
            ];


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
