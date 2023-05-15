<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Topic;
use App\Models\Course;
use App\Models\School;
use App\Models\Taxonomy;
use App\Models\CourseSchool;
use Illuminate\Http\Request;
use App\Models\SummaryCourse;
use App\Models\SchoolSubworkspace;
use App\Http\Controllers\Controller;

class RestUserProgressController extends Controller
{
    public function userProgress()
    {
        $user = auth()->user();
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

        $assigned_courses_count = $assigned_courses
                ->where('type.code', '<>', 'free')
                ->count();

        $pending_courses = $assigned_courses_count - $completed_courses;
        $disapproved_courses = $summary_user ?
            $user->summary_courses()
                ->whereHas('course', fn($q) => $q
                    ->whereRelation('type', 'code', '<>', 'free')
                    ->whereIn('id', $user_courses_id->toArray()))
                ->whereRelation('status', 'code', 'desaprobado')->count()
            : 0;

        $general_percentage = $assigned_courses->count() > 0 && $summary_user ? round(($completed_courses / $assigned_courses->where('type.code', '<>', 'free')->count()) * 100) : 0;
        $general_percentage = min($general_percentage, 100);

        $response['summary_user'] = [
            'asignados' => $assigned_courses_count,
            'aprobados' => $completed_courses,
            'desaprobados' => $disapproved_courses,
            'pendientes' => $pending_courses,
            'porcentaje' => $general_percentage,
        ];

        $regular_courses = $assigned_courses->where('type.code', 'regular');
        $extracurricular_courses = $assigned_courses->where('type.code', 'extra-curricular');
        $free_courses = $assigned_courses->where('type.code', 'free');

        $response['regular_schools'] = $this->getProgressDetailSchoolsByUser($regular_courses, $user);
        $response['extracurricular_schools'] = $this->getProgressDetailSchoolsByUser($extracurricular_courses, $user);
        $response['free_schools'] = $this->getProgressDetailSchoolsByUser($free_courses, $user);

        return $this->success($response);
    }

    public function getProgressDetailSchoolsByUser($user_courses, $user)
    {
        $workspace_id = auth()->user()->subworkspace->parent_id;

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
            $school_position = $positions_schools->where('school_id', $school_id)->first()?->position;
            $school = $courses->first()->schools->where('id', $school_id)->first();
            $courses_data = $this->getSchoolProgress($courses,$positions_courses,$school_id);

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

    public function getSchoolProgress($courses,$positions_courses,$school_id)
    {
        $user = auth()->user();
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
        foreach ($courses as $course) {
            $course_position = $positions_courses->where('school_id', $school_id)->where('course_id',$course->id)->first()?->position;

            $course_name = $course->name;
            $tags = [];
            if ($workspace_id === 25) {
                $tags = $course->getCourseTagsToUCByUser($course, $user,$cycles);
                $course_name = removeUCModuleNameFromCourseName($course_name);
            }

            $course_status = Course::getCourseProgressByUser($user, $course);

            $active_course_topics = $course->topics->where('active', ACTIVE)->sortBy('position');
            $temp_topics = [];

            foreach ($active_course_topics as $topic) {

                if($course->compatible):

                    $temp_topics[] = [
                        'id' => $topic->id,
                        'name' => $topic->name,
                        'disponible' => true,
                        'nota' => null,
                        'estado' => 'aprobado',
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
                    'estado' => $topic_status['status'],
                    'estado_str' => $topic_status_arr[$topic_status['status']],
                ];
            }

            if ($course->compatible):

                $school_courses->push([
                    'id' => $course->id,
                    'name' => $course_name,
                    'position' => $course_position,
                    'nota' => $course->compatible->grade_average,
                    'estado' => 'aprobado',
                    'estado_str' => 'Convalidado',
                    'tags' => $tags,
                    'tag_ciclo' => $tags[0] ?? null,
                    'compatible' => $course->compatible?->course->only('id', 'name'),
                    'temas' => $temp_topics,
                ]);

                continue;
            endif;

            $school_courses->push([
                'id' => $course->id,
                'name' => $course_name,
                'position' => $course_position,
                'nota' => $course_status['average_grade'],
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
}
