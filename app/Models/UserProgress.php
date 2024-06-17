<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    protected function getDataProgress($user = null)
    {
        $user = $user ?? auth()->user();
        $user->load('summary', 'summary_courses');

        $assigned_courses = $user->getCurrentCourses(withRelations: 'user-progress');
        $user_courses_id = $assigned_courses->whereNull('compatible')->pluck('id');

        $user_compatibles_courses_id = $assigned_courses->whereNotNull('compatible')->pluck('id');
        $user_compatibles_courses_count = $assigned_courses->whereNotNull('compatible')->where('type.code', '<>', 'free')->count();

        $summary_user = $user->summary;

        $completed_courses = $summary_user ?
            $user->summary_courses()
                ->whereHas('course', fn($q) => $q
                    ->whereRelation('type', 'code', '<>', 'free')
                    ->whereIn('id', $user_courses_id->toArray())
                )
                ->whereRelation('status', 'code', 'aprobado')
                ->count() + $user_compatibles_courses_count
            : 0;

        $freeCourseType = Taxonomy::getFirstData('course', 'type', 'free');
        $assigned_courses_count = $assigned_courses
                ->where('type_id', '<>', $freeCourseType->id)
                ->count();
        $assigned_courses_ids = $assigned_courses->where('type_id', '<>', $freeCourseType->id)->pluck('id');


        $pending_courses = $assigned_courses_count - $completed_courses;
        $disapproved_courses = $summary_user ?
            $user->summary_courses()
                ->whereHas('course', fn($q) => $q
                    ->whereRelation('type', 'code', '<>', 'free')
                    ->whereIn('id', $user_courses_id->toArray()))
                ->whereRelation('status', 'code', 'desaprobado')->count()
            : 0;


        $general_percentage = $assigned_courses->count() > 0 && $summary_user
            ? round(($completed_courses / $assigned_courses_count) * 100)
            : 0;
        $general_percentage = min($general_percentage, 100);

        $total_certificates = Certificate::getTotalByUser($user, $assigned_courses);

        $response['summary_user'] = [
            'asignados' => $assigned_courses_count,
            'asignados_ids' => $assigned_courses_ids->toArray(),
            'aprobados' => $completed_courses,
            'desaprobados' => $disapproved_courses,
            'pendientes' => $pending_courses,
            'porcentaje' => $general_percentage,
            'diplomas' => $total_certificates,
        ];

        $regular_courses = $assigned_courses->where('type.code', 'regular');
        $extracurricular_courses = $assigned_courses->where('type.code', 'extra-curricular');
        $free_courses = $assigned_courses->where('type.code', 'free');


        $summary_courses_compatibles = SummaryCourse::with('course:id,name')
            ->whereRelation('course', 'active', ACTIVE)
            ->where('user_id', $user->id)
            // ->whereIn('course_id', $course->compatibilities->pluck('id')->toArray())
            ->orderBy('grade_average', 'DESC')
            ->whereRelation('status', 'code', 'aprobado')
            ->get();

        $response['regular_schools'] = $this->getProgressDetailSchoolsByUser($regular_courses, $user, $summary_courses_compatibles);
        $response['extracurricular_schools'] = $this->getProgressDetailSchoolsByUser($extracurricular_courses, $user, $summary_courses_compatibles);
        $response['free_schools'] = $this->getProgressDetailSchoolsByUser($free_courses, $user, $summary_courses_compatibles);

        return $response;
    }

    public function getProgressDetailSchoolsByUser($user_courses, $user = null, $summary_courses_compatibles = null)
    {
    	$user = $user ?? auth()->user();
        $workspace_id = $user->subworkspace->parent_id;

        $schools = $user_courses->groupBy('schools.*.id');

        $data = [];
        $positions_schools = SchoolSubworkspace::select('school_id','position')
                                ->where('subworkspace_id',$user->subworkspace_id)
                                ->whereIn('school_id',array_keys($schools->all()))
                                ->get();

        $positions_courses = CourseSchool::select('school_id','course_id','position')
                                ->whereIn('school_id',array_keys($schools->all()))
                                ->whereIn('course_id',$user_courses->pluck('id'))
                                ->get();

        foreach ($schools as $school_id => $courses) {
            // $school_position = $positions_schools->where('school_id', $school_id)->first()?->position;
            $school_workspace = $positions_schools->where('school_id', $school_id)->first();
            if(!$school_workspace){
                // la escuela no pertenece al módulo del usuario
                continue;
            }
            $school_position = $school_workspace?->position;

            $school = $courses->first()->schools->where('id', $school_id)->first();
            $courses_data = $this->getSchoolProgress($courses, $positions_courses, $school_id, $user, $summary_courses_compatibles);

            $school_status = $this->getSchoolProgressByUserV2($courses_data);
            // $school_status = $this->getSchoolProgressByUser($school, $courses, $user);

            // UC
            $school_name = $school->name;
            if ($workspace_id === 25) {
                $school_name = removeUCModuleNameFromCourseName($school_name);
            }

            $data[] = [
                'id' => $school->id,
//                'name' => $school->name,
                'name' => $school_name,
                'imagen' => $school->imagen,
                'porcentaje' => $school_status['percentage'],
//                'estado' => $school_status['status'],
//                'estado_str' => '',
                'completados' => $school_status['completed'],
                'asignados' => $courses->count(),
                'orden' => $school_position,
                'courses' => $courses_data
            ];
        }
        $columns = array_column($data, 'orden');
        array_multisort($columns, SORT_ASC, $data);

        return $data;
    }

    public function getSchoolProgressByUserV2($data)
    {
        $percentage = 0;

        $courses = collect($data);

        $total = $courses->count();
        $completed = $courses->where('estado', 'aprobado')->count();

        $percentage = $total ? ($completed / $total) * 100 : 0;

        return [
            // 'status' => $status,
            // 'status_string' => $arr_estados[$status],
            'percentage' => round($percentage),
            'completed' => $completed,
        ];
    }

    public function getSchoolProgressByUser(School $school, $courses, $user)
    {
        $school_percentage = 0;
        $assigned_courses_by_school = $courses->count();

        $course_completed_taxonomy = Taxonomy::getFirstData('course', 'user-status', 'aprobado');
        $summary_courses = $user->summary_courses->whereIn('course_id', $courses->pluck('id'));
        $completed_courses = $summary_courses->where('status_id', $course_completed_taxonomy->id)->count();

        if ($completed_courses > 0 && $completed_courses >= $assigned_courses_by_school) {
            $status = 'aprobado';
            $school_percentage = ($completed_courses / $assigned_courses_by_school) * 100;
        } else if ($completed_courses > 0) {
            $status = 'desarrollo';
            $school_percentage = ($completed_courses / $assigned_courses_by_school) * 100;
        } else {
            $status = 'pendiente';
        }

        $arr_estados = config('schools.arr_estados');
        $school_percentage = round($school_percentage);

        return [
            'status' => $status,
            'status_string' => $arr_estados[$status],
            'percentage' => $school_percentage,
            'completed' => $completed_courses,
        ];
    }

    public function getSchoolProgress($courses, $positions_courses, $school_id, $user = null, $summary_courses_compatibles = null)
    {
        // $user = $user ?? auth()->user();
        $workspace_id = $user->subworkspace->parent_id;
        $course_status_arr = config('courses.status');
        $topic_status_arr = config('topics.status');
        $school_courses = collect();
        $cycles = null;

        if($workspace_id === 25){
            $cycles = CriterionValue::whereRelation('criterion', 'code', 'cycle')
            ->where('value_text', '<>', 'Ciclo 0')
            ->orderBy('position')->get();
        }
        $modalities = Taxonomy::where('group', 'course')->where('type', 'modality')->select('id','code')->get();

        foreach ($courses as $course) {
            $course_position = $positions_courses->where('school_id', $school_id)->where('course_id',$course->id)->first()?->position;

            $course_name = $course->name;
            $tags = [];
            if ($workspace_id === 25) {
                // $tags = $course->getCourseTagsToUCByUser($course, $user,$cycles);
                $tags = $course->tags;
                $course_name = removeUCModuleNameFromCourseName($course_name);
            }

            $course_status = Course::getCourseProgressByUser($user, $course, $summary_courses_compatibles);

            $active_course_topics = $course->topics->where('active', ACTIVE)->sortBy('position');
            $temp_topics = [];

            foreach ($active_course_topics as $topic) {

                if($course->compatible):

                    $temp_topics[] = [
                        'id' => $topic->id,
                        'name' => $topic->name,
                        'disponible' => true,
                        'nota' => null,
                        'intentos' => null,
                        'ultima_evaluacion' => null,
                        'visitas' => null,
                        'nota_sistema' => $topic->qualification_type?->name ?? 'No definido',
                        'estado' => 'aprobado',
                        'respuestas' => [],
                        'estado_str' => 'Convalidado',
                    ];

                    continue;
                endif;

                $topic_status = Topic::getTopicProgressByUser($user, $topic);

                $temp_topics[] = [
                    'id' => $topic->id,
                    'name' => $topic->name,
                    'disponible' => $topic_status['available'],
                    'nota' => $topic_status['grade'],
                    'visitas' => $topic_status['views'],
                    'intentos' => $topic_status['total_attempts'] ?? null,
                    'ultima_evaluacion' => $topic_status['last_time_evaluated_at'] ?? null,
                    'respuestas' => $topic_status['answers'] ?? [],
                    'nota_sistema' => $topic->qualification_type?->name ?? 'No definido',
                    'estado' => $topic_status['status'],
                    'estado_str' => $topic_status_arr[$topic_status['status']],
                ];
            }

            if ($course->compatible):

                $compatible_grade = calculateValueForQualification($course->compatible->grade_average, $course->qualification_type?->position);

                $school_courses->push([
                    'id' => $course->id,
                    'image' => get_media_url($course->imagen),
                    'show_topics' => false,
                    'name' => $course_name,
                    'position' => $course_position,
                    // 'nota' => $course->compatible->grade_average,
                    'nota' => $compatible_grade,
                    'nota_sistema' => $course->qualification_type?->name ?? 'No definido',
                    'estado' => 'aprobado',
                    'estado_str' => 'Convalidado',
                    'tags' => $tags,
                    'tag_ciclo' => $tags[0] ?? null,
                    'compatible' => $course->compatible?->course->only('id', 'name'),
                    'temas' => $temp_topics,
                ]);

                continue;
            endif;
            $modality = $modalities->where('id',$course->modality_id)->first();
            $school_courses->push([
                'id' => $course->id,
                'modality_code' => $modality?->code,
                'image' => get_media_url($course->imagen),
                'show_topics' => false,
                'name' => $course_name,
                'position' => $course_position,
                'nota' => $course_status['average_grade'],
                'nota_sistema' => $course->qualification_type?->name ?? 'No definido',
                'estado' => $course_status['status'],
                'estado_str' => $course_status_arr[$course_status['status']],
                'tags' => $tags,
                'tag_ciclo' => $tags[0] ?? null,
                'compatible' => $course->compatible?->course->only('id', 'name') ?: null,
                'temas' => $temp_topics,
            ]);
        }

        if ($workspace_id === 25) {
            $school_courses = $school_courses->sortBy([
                ['tag_ciclo', 'asc'],
                ['position', 'asc'],
            ]);
        } else {
            $school_courses = $school_courses->sortBy([
                ['position', 'asc'],
                ['name', 'asc'],
            ]);
        }
        // $columns = array_column($school_courses, 'orden');
        // array_multisort($columns, SORT_ASC, $school_courses);
        return $school_courses->values()->all();
    }

    protected function setTopicQuestionData($data)
    {
        $data = $this->getAndSetTopicQuestions($data, 'regular_schools');

        return $data;
    }

    public function getAndSetTopicQuestions($data, $school_key)
    {
        if (isset($data[$school_key])) {

            foreach ($data[$school_key] as $s_key => $school) {

                $topic_ids = [];

                foreach ($school['courses'] as $course) {

                    $ids = array_column($course['temas'], 'id');
                    $topic_ids = array_merge($topic_ids, $ids);
                }

                $questions = Question::whereIn('topic_id', $topic_ids)->get();

                foreach ($school['courses'] as $c_key => $course) {

                    foreach ($course['temas'] as $t_key => $topic) {

                        $preguntas = $questions->where('topic_id', $topic['id'])->toArray();
                        $respuestas = $this->setTopicQuestionAnswers($topic['respuestas'] ?? NULL, $preguntas);

                        // $data[$school_key][$s_key]['courses'][$c_key]['temas'][$t_key]['preguntas'] = $preguntas;
                        $data[$school_key][$s_key]['courses'][$c_key]['temas'][$t_key]['respuestas'] = $respuestas;
                    }
                }
            }

            return $data;
        }
    }

    public function setTopicQuestionAnswers($answers, $questions)
    {
        if (!$answers) return [];

        $rows = [];

        foreach ($questions as $key => $question) {

            $answer = collect($answers)->where('preg_id', $question['id'])->first();

            if ($answer) {

                $right = $question['rptas_json'][$question['rpta_ok']] ?? null;
                $marked = $question['rptas_json'][$answer['opc']] ?? null;
                $is_correct =  $answer['opc'] == $question['rpta_ok'];

                $row = [
                    'pregunta' => $question['pregunta'],
                    'respuesta' => [
                        'correcta' => $right,
                        'marcada' => $marked,
                        'es_correcta' => $is_correct,
                        'puntos' => $question['score'],
                    ],
                ];

                $rows[] = $row;
            }
        }

        return $rows;
    }
}
