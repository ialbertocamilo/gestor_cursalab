<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\School;
use App\Models\SummaryCourse;
use App\Models\Taxonomy;
use App\Models\Topic;
use Illuminate\Http\Request;

class RestUserProgressController extends Controller
{
    public function userProgress()
    {
        $user = auth()->user();
        $user->load('summary', 'summary_courses');

        $assigned_courses = $user->getCurrentCourses();
        $summary_user = $user->summary;

//        $completed_courses = $summary_user ? $summary_user->course_completed : 0;
        $completed_courses = $summary_user ?
            $user->summary_courses()
                ->whereHas('courses', fn($q) => $q->whereIn('id', $assigned_courses->pluck('id')))
                ->whereRelation('status', 'code', 'aprobado')->count()
            : 0;
        $pending_courses = $assigned_courses->count() - $completed_courses;
        $disapproved_courses = $summary_user ?
            $user->summary_courses()
                ->whereHas('courses', fn($q) => $q->whereIn('id', $assigned_courses->pluck('id')))
                ->whereRelation('status', 'code', 'desaprobado')->count()
            : 0;

        $general_percentage = $assigned_courses->count() > 0 && $summary_user ? $summary_user->advanced_percentage : 0;
        $general_percentage = min($general_percentage, 100);


        $response['summary_user'] = [
            'asignados' => $assigned_courses->count(),
            'aprobados' => $completed_courses,
            'desaprobados' => $disapproved_courses,
            'pendientes' => $pending_courses,
            'porcentaje' => $general_percentage,
        ];

        $response['progress_detail_by_school'] = $this->getProgressDetailSchoolsByUser($assigned_courses, $user);

        return $this->success($response);
    }

    public function getProgressDetailSchoolsByUser($user_courses, $user)
    {
        $schools = $user_courses->groupBy('schools.*.id');

        $data = [];
        foreach ($schools as $school_id => $courses) {
            $school = $courses->first()->schools->where('id', $school_id)->first();
            $school_status = $this->getSchoolProgressByUser($school, $courses, $user);

            $data[] = [
                'id' => $school->id,
                'name' => $school->name,
                'imagen' => $school->imagen,
                'porcentaje' => $school_status['percentage'],
//                'estado' => $school_status['status'],
//                'estado_str' => '',
                'completados' => $school_status['completed'],
                'asignados' => $courses->count(),
            ];
        }

        return ['schools' => $data];
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
//        info($arr_estados);
//        info($status);


        return [
            'status' => $status,
            'status_string' => $arr_estados[$status],
            'percentage' => $school_percentage,
            'completed' => $completed_courses,
        ];
    }

    public function getSchoolProgress(School $school)
    {
        $user = auth()->user();
        $assigned_courses = $user->getCurrentCourses();

        $schools = $assigned_courses->groupBy('schools.*.id');

        $data_school = $schools[$school->id] ?? null;

        if (!$data_school) return $this->success(['courses' => []]);

        $course_status_arr = config('courses.status');
        $topic_status_arr = config('topics.status');
        $school_courses = [];
        foreach ($data_school as $school_id => $course) {
            $course_status = Course::getCourseProgressByUser($user, $course);

            $active_course_topics = $course->topics->where('active', ACTIVE)->sortBy('position');
            $temp_topics = [];

            foreach ($active_course_topics as $topic) {
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

            $school_courses[] = [
                'id' => $course->id,
                'name' => $course->name,
                'nota' => $course_status['average_grade'],
                'estado' => $course_status['status'],
                'estado_str' => $course_status_arr[$course_status['status']],
                'temas' => $temp_topics
            ];
        }

        return $this->success(['courses' => $school_courses]);
    }
}
