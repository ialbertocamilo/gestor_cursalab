<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Summary;
use App\Models\SummaryCourse;
use App\Models\SummaryTopic;
use App\Models\SummaryUser;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
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

    public function __construct($data)
    {
        $this->evaluation_type = $data['evaluation_type'];
        $this->course_id = $data['course'];
        $this->topics = $data['topics'];

        $this->topic_states = Taxonomy::getData('topic', 'user-status');
        $this->source = Taxonomy::getFirstData('summary', 'source', 'massive-upload-grades');
    }

    public function collection(Collection $excelData)
    {
        //   Comment para hacer merge
        $count = count($excelData);
        $this->course = Course::find($this->course_id);

        $this->topic_states = Taxonomy::getData('topic', 'user-status');
        $this->topic_states = Taxonomy::getData('topic', 'user-status');

        for ($i = 1; $i < $count; $i++) {
            $document_user = $excelData[$i][0];
            $grade = $excelData[$i][1];

            $user = User::with('subworkspace:id,name,mod_evaluaciones,parent_id')
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

            $assigned_courses = $user->getCurrentCourses();

            if (!in_array($this->course_id, $assigned_courses->pluck('id')->toArray())) {
//                info($this->course_id, $assigned_courses->toArray());
                $this->pushNoProcesados($excelData[$i], 'El curso seleccionado no está asignado para este usuario');
                continue;
            }

            $sub_workspace_settings = $user->getSubworkspaceSetting('mod_evaluaciones');
            $topics = count($this->topics) > 0
                ? Topic::with('course')->where('course_id', $this->course_id)->whereIn('id', $this->topics)->get()
                : Topic::with('course')->where('course_id', $this->course_id)
                    ->where(function ($q) {
                        $q->where('assessable', INACTIVE); // NO evaluables
                        $q->orWhere(function ($q2) {
                            $q2->where('assessable', ACTIVE)
                                ->whereRelation('evaluation_type', 'code', 'qualified'); // Evaluables calificados
                        });
                    })
                    ->get();

            $this->uploadTopicGrades($sub_workspace_settings, $user, $topics, $excelData[$i]);
        }
    }

    public function uploadTopicGrades($sub_workspace_settings, $user, $topics, $excelData)
    {
        $min_grade = $sub_workspace_settings['nota_aprobatoria'];

        $grade = $excelData[1];

        $topic_summaries = SummaryTopic::whereIn('topic_id', $this->topics)->where('user_id', $user->id)
            ->select('attempts', 'views')
            ->get();

        $a_topic_was_created = false;

        foreach ($topics as $topic) {
            $summary = $topic_summaries->where('topic_id', $topic->id)->first();
            $summary ??= SummaryTopic::storeData($topic, $user);

            $summary_data = [
                'answers' => [],
            ];

            if ($topic->assessable && $topic->evaluation_type->code === 'qualified') {

                if ($grade > $summary->grade) {

                    $attempts = $excelData[2] ?: ($summary ? $summary->attempts : 1);
                    $views = $excelData[3] ?: ($summary ? $summary->views : 1);
                    $correct_answers = $excelData[4] ?: ($summary ? $summary->correct_answers : 1);
                    $failed_answers = $excelData[5] ?: ($summary ? $summary->failed_answers : 1);
                    $last_time_evaluated_at = $excelData[6] ?: ($summary ? $summary->last_time_evaluated_at : 1);
                    $status = $this->getNewTopicStatusByGrade($grade, $min_grade);

                    $summary_data = array_merge($summary_data, [
                        'status_id' => $status->id,
                        'source_id' => $this->source->id,

                        'passed' => $grade < $min_grade,
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
                $status = $this->topic_states->where('code', 'revisado');

                $summary_data = array_merge($summary_data, [
                    'status_id' => $status->id,
                    'source_id' => $this->source->id,

                    'views' => $views,
                    'attempts' => $attempts,
                ]);
                $a_topic_was_created = true;
                $this->storeSummaryTopic($topic, $user, $summary_data);
            }
        }

        if ($a_topic_was_created) {
            SummaryCourse::getCurrentRowOrCreate($this->course, $user);
            SummaryCourse::updateUserData($this->course, $user);

            SummaryUser::getCurrentRowOrCreate($user);
            SummaryUser::updateUserData($user);
        }
    }

    public function storeSummaryTopic($topic, $user, $data)
    {
        SummaryTopic::updateOrCreate(
            [
                'user_id' => $user->id,
                'topic_id' => $topic->id,
            ],
            $data
        );
    }

    public function getNewTopicStatusByGrade($grade, $min_grade)
    {
        $code = $grade < $min_grade ? 'aprobado' : 'desaprobado';

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
