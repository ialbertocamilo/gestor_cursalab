<?php

namespace App\Http\Controllers\ApiRest;

use App;
use App\Models\Workspace;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate;
use App\Http\Controllers\Controller;
use App\Http\Requests\PollAnswerStoreRequest;
use App\Models\Course;
use App\Models\Poll;
use App\Models\PollQuestionAnswer;
use App\Models\SummaryCourse;
use App\Models\Taxonomy;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Monolog\Handler\IFTTTHandler;

class RestCourseController extends Controller
{
    public function courses()
    {
        $user = Auth::user();

        // Update flag to force update users courses

        if(now()->diffInMinutes($user->required_update_at) > 60) {
            $user->required_update_at = now();
            $user->save();
        }

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

        // Take only courses with enabled certificate
        $user_courses = $user_courses->where('show_certification_to_user', 1);

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

            if ($qs AND !stringContains($user_course->name, $qs)) continue;

            $certificate = $certificates->where('course_id', $user_course->id)->first();

            if ($certificate) {

                $temp[] = [

                    'course_id' => $certificate->course_id,
                    'name' => $certificate->course->name,
                    'accepted' => $certificate->certification_accepted_at ? true : false,
                    'issued_at' => $certificate->certification_issued_at->format('d/m/Y'),
                    'ruta_ver' => "tools/ver_diploma/{$user->id}/{$certificate->course_id}",
                    'ruta_descarga' => "tools/dnc/{$user->id}/{$certificate->course_id}",
                    'user_confirms_certificate' => $certificate->course->user_confirms_certificate,
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
                        'user_confirms_certificate' => $certificate?->course?->user_confirms_certificate,
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

    public function getCertificatesV2(Request $request)
    {
        $data = [];
        $user = auth()->user();
        $qs = $request->q ?? NULL;

        $query = SummaryCourse::with('course:id,name,user_confirms_certificate')
            ->whereHas('course', function($q) use ($qs) {
                $q->where('show_certification_to_user', 1);

                if ($qs) {
                    $q->where('name', 'like', "%{$qs}%");
                }
            })
            ->where('user_id', $user->id)
            ->whereNotNull('certification_issued_at');

        if ($request->type == 'accepted')
            $query->whereNotNull('certification_accepted_at');

        if ($request->type == 'pending')
            $query->whereNull('certification_accepted_at');

        $certificates = $query->get();

        foreach ($certificates as $certificate) {

            // if ($qs AND !stringContains($certificate->course->name, $qs)) continue;

            $data[] = [
                'course_id' => $certificate->course_id,
                'name' => $certificate->course->name,
                'accepted' => $certificate->certification_accepted_at ? true : false,
                'issued_at' => $certificate->certification_issued_at->format('d/m/Y'),
                'ruta_ver' => "tools/ver_diploma/{$user->id}/{$certificate->course_id}",
                'ruta_descarga' => "tools/dnc/{$user->id}/{$certificate->course_id}",
                'user_confirms_certificate' => $certificate->course->user_confirms_certificate,
                'compatible' => null,
            ];
        }

        return $this->success(compact('data'));
    }

    public function generateRegistroCapacitacion(Request $request) {


        $user = auth()->user();
        $subworkspace = Workspace::find($user->subworkspace_id);
        $course = Course::find($request->course_id);
        $summary = SummaryCourse::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        // Encode signature with Base64 to render the template with

        $signatureData = $request->get('signature');

        $data = [
            'signatureData' => $signatureData,
            'user' => $user,
            'company'=> $subworkspace->registro_capacitacion->company,
            'course' => $course,
            'summaryCourse' => $summary
        ];

        // Render template and store generated file

        $filename = 'capacitacion-'.
                    $subworkspace->id . '-' .
                    $course->id .  '-' .
                    $user->id . '-' .
                    Str::random(5) . '.pdf';
        $filepath = Course::generateAndStoreRegistroCapacitacion($filename, $data);

        // File path should also store in user's summary course

        $summary->registro_capacitacion_path = $filepath;
        $summary->save();

        return Response::json([
            'filepath' => $filepath,
            'url' => Course::generateRegistroCapacitacionURL($filepath)
        ], 201);
    }


}
