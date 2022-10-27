<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Topic;
use App\Models\Course;
use App\Models\Summary;
use App\Models\Taxonomy;
use App\Models\SummaryUser;
use App\Models\SummaryTopic;
use App\Models\SummaryCourse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Events\MassiveUploadTopicGradesProgressEvent;
use App\Http\Controllers\ApiRest\RestAvanceController;

class MassiveUploadTopicGrades implements ToCollection
{
    /**
     * @param Collection $collection
     */

    public array $data = [];
    public array $no_procesados = [];
    public mixed $course_id;
    public Course $course;
    public mixed $evaluation_type;
    public mixed $topics = [];
    public mixed $topic_states = null;
    private $updated_users_id = [];
    private $number_socket = 0;
    public function __construct($data)
    {
        $this->evaluation_type = $data['evaluation_type'];
        $this->course_id = $data['course'];
        $this->topics = $data['topics'] ?? [];
        $this->number_socket = $data['number_socket'];
        $this->topic_states = [];
        $this->source = [];
    }

    public function collection(Collection $excelData)
    {
        //   Comment para hacer merge
        $count = count($excelData);
        $this->course = Course::find($this->course_id);

        $this->topic_states = Taxonomy::getData('topic', 'user-status')->get();
        $this->source = Taxonomy::getFirstData('summary', 'source', 'massive-upload-grades');
        $this->course->load('segments.values');
        $topics = count($this->topics) > 0
        ? Topic::disableCache()->with('course')->where('course_id', $this->course_id)->whereIn('id', $this->topics)->get()
        : Topic::disableCache()->with('course')->where('course_id', $this->course_id)
            ->where(function ($q) {
                $q->where('assessable', INACTIVE); // NO evaluables
                $q->orWhere(function ($q2) { // Evaluables calificados
                    $q2->where('assessable', ACTIVE)
                        ->whereRelation('evaluation_type', 'code', 'qualified');
                });
            })
            ->get();
        $usersSegmented = $this->course->usersSegmented($this->course->segments, $type = 'get_records');
        $percent_sent = [];
        for ($i = 1; $i < $count; $i++) {
            // info('Inicio');
            $currente_percent = round(($i/$count)*100);
            if(($currente_percent==0 ||($currente_percent % 5) == 0) && !in_array($currente_percent,$percent_sent)){
                $percent_sent[] = $currente_percent;
                event(new MassiveUploadTopicGradesProgressEvent($currente_percent,$this->number_socket));
            }
            $document_user = $excelData[$i][0];
            $grade = $excelData[$i][1];
            if(!$document_user){
                $this->pushNoProcesados($excelData[$i], 'Usuario no existe');
                continue;
            }
            $user = User::disableCache()->with('subworkspace:id,name,mod_evaluaciones,parent_id')
                ->where('document', $document_user)->first();

            if (!$user) {
                $this->pushNoProcesados($excelData[$i], 'Usuario no existe');
                continue;
            }

            if (($this->course->assessable) && (count($this->topics) > 0 && $this->evaluation_type == 'assessable') && (empty($grade) || trim($grade) == "")) {
                $this->pushNoProcesados($excelData[$i], 'La nota está fuera del rango permitido');
                continue;
            }
            if (($this->course->assessable) && (count($this->topics) == 0) && (empty($grade) || trim($grade) == "")) {
                $this->pushNoProcesados($excelData[$i], 'La nota está fuera del rango permitido');
                continue;
            }
            if (($this->course->assessable) && (count($this->topics) > 0 && $this->evaluation_type == 'assessable') && ($grade < 0 || $grade > 20)) {
                $this->pushNoProcesados($excelData[$i], 'La nota está fuera del rango permitido');
                continue;
            }
            if (($this->course->assessable) && (count($this->topics) == 0) && ($grade < 0 || $grade > 20)) {
                $this->pushNoProcesados($excelData[$i], 'La nota está fuera del rango permitido');
                continue;
            }

            // $assigned_courses = $user->getCurrentCourses();
            $user_has_course = $usersSegmented->where('id',$user->id)->first();
            if(!$user_has_course){
                $this->pushNoProcesados($excelData[$i], 'El curso seleccionado no está asignado para este usuario');
                continue;
            }
            // if (!in_array($this->course_id, $assigned_courses->pluck('id')->toArray())) {
            //     $this->pushNoProcesados($excelData[$i], 'El curso seleccionado no está asignado para este usuario');
            //     continue;
            // }

            $sub_workspace_settings = $user->getSubworkspaceSetting('mod_evaluaciones');
            
//            info("TOPICS ID::");
//            info($topics->pluck('id')->toArray());
            $this->uploadTopicGrades($sub_workspace_settings, $user, $topics, $excelData[$i]);
            // info('Inicio');
        }
        Summary::updateUsersByCourse($this->course,$this->updated_users_id);
    }

    public function uploadTopicGrades($sub_workspace_settings, $user, $topics, $excelData)
    {
        $min_grade = $sub_workspace_settings['nota_aprobatoria'];

        $grade = $excelData[1];

        $topic_summaries = SummaryTopic::disableCache()->whereIn('topic_id', $topics->pluck('id')->toArray())->where('user_id', $user->id)
            ->get();
//        info("SUMMARIES ID :: ");
//        info($topic_summaries->pluck('id')->toArray());

        $a_topic_was_created = false;

        foreach ($topics as $topic) {
            $summary = $topic_summaries->where('topic_id', $topic->id)->first();
//            info("INFO SUMMARY :: ".$summary?->id);
            if (!$summary)
                $summary = SummaryTopic::storeData($topic, $user);

            $summary_data = [
                'answers' => [],
            ];

            if ($topic->assessable && $topic->evaluation_type->code === 'qualified') {

                if ($grade > $summary->grade) {

                    $attempts = $excelData[2] ?: ($summary ? $summary->attempts : 1);
                    $views = $excelData[3] ?: ($summary ? $summary->views : 1);
                    $correct_answers = $excelData[4] ?: ($summary ? $summary->correct_answers : 1);
                    $failed_answers = $excelData[5] ?: ($summary ? $summary->failed_answers : 1);
                    $last_time_evaluated_at = excelDateToDate($excelData[6]) ?: ($summary ? $summary->last_time_evaluated_at : 1);
                    $status = $this->getNewSummaryQualifiedTopicStatus($grade, $min_grade);

                    $summary_data = array_merge($summary_data, [
                        'status_id' => $status->id,
                        'source_id' => $this->source->id,

                        'passed' => $grade > $min_grade,
                        'grade' => $grade,

                        'views' => $views,
                        'attempts' => $attempts,

                        'correct_answers' => $correct_answers,
                        'failed_answers' => $failed_answers,
                        'last_time_evaluated_at' => $last_time_evaluated_at,
                    ]);
                    $a_topic_was_created = true;
                    $this->storeSummaryTopic($topic, $user, $summary_data);
                }

            } elseif (!$topic->assessable) {
                $status = $this->topic_states->where('code', 'revisado')->first();
                $views = $excelData[3] ?: ($summary ? $summary->views : 1);

                $summary_data = array_merge($summary_data, [
                    'status_id' => $status->id,
                    'source_id' => $this->source->id,

                    'views' => $views,
                ]);
                $a_topic_was_created = true;
                $this->storeSummaryTopic($topic, $user, $summary_data);
            }
        }

        if ($a_topic_was_created) {
            $this->updated_users_id[] = $user->id;
            // SummaryCourse::getCurrentRowOrCreate($this->course, $user);
            // SummaryCourse::updateUserData($this->course, $user, update_attempts: false);

            // SummaryUser::getCurrentRowOrCreate($user, $user);
            // SummaryUser::updateUserData($user);
        }
    }

    public function storeSummaryTopic($topic, $user, $data)
    {
//        info("TOPIC NAME :: " . $topic->name);
//        info("USER ID :: " . $user->id);
//        info("TOPIC ID :: " . $topic->id);
//        info($data);
        $summary = SummaryTopic::updateOrCreate(
            ['user_id' => $user->id, 'topic_id' => $topic->id],
            $data
        );
//        info($summary->id);
//        info("==================================================================");
    }

    public function getNewSummaryQualifiedTopicStatus($grade, $min_grade)
    {
        $code = $grade >= $min_grade ? 'aprobado' : 'desaprobado';

        return $this->topic_states->where('code', $code)->first();
    }

    public function pushNoProcesados($excelRaw, $info)
    {
        $this->no_procesados[] = [
            'dni' => $excelRaw[0],
            'nota' => $excelRaw[1],
            'info' => $info
        ];
    }

    public function getNoProcesados()
    {
        return $this->no_procesados;
    }
}
