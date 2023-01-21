<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;

class SummaryUser extends Summary
{
    protected $table = 'summary_users';

    protected $fillable = [
        'last_time_evaluated_at', 'courses_assigned', 'user_id', 'attempts', 'score',
        'grade_average', 'courses_completed', 'advanced_percentage'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    protected function updateUserData($user = null, $comes_from_evaluation = true)
    {
        $user = $user ?? auth()->user();
        
        $row_user = SummaryUser::getCurrentRow(null, $user);

        if (!$row_user) return true;

        [$courses_id, $total_courses] = SummaryUser::getTotalCoursesAndIdentifiers($user);

        $passed = SummaryCourse::getUserTotalCoursesByStatusCode($user, $courses_id, 'aprobado');
        $attempts = SummaryCourse::getUserTotalAttemptsByCourses($user, $courses_id);
        $grade_average = SummaryTopic::getUserAverageGradeByCourses($user, $courses_id);

        $completed = ($passed > $total_courses) ? $total_courses : $passed;

        $user_data = [
            'courses_assigned' => $total_courses,
            'courses_completed' => $completed,
            'grade_average' => $grade_average,
            'attempts' => $attempts,
            'score' => User::calculate_rank($passed, $grade_average, $attempts),
            'advanced_percentage' => SummaryUser::getGeneralPercentage($total_courses, $passed),
        ];

        if ($comes_from_evaluation)
            $user_data['last_time_evaluated_at'] = now();


        info($user_data);

        $row_user->update($user_data);

        return $row_user;
    }

    protected function getGeneralPercentage($total, $advance)
    {
        $percent = ($total > 0) ? (($advance / $total) * 100) : 0;
        $percent = round(($percent > 100) ? 100 : $percent); // maximo porcentaje = 100

        return $percent;
    }

    protected function getTotalCoursesAndIdentifiers($user)
    {
        $user_courses = $user->getCurrentCourses(withRelations: 'soft');

        $user_courses_id = $user_courses->whereNull('compatible')->pluck('id');
        $user_compatibles_courses_id = $user_courses->whereNotNull('compatible')->pluck('compatible.course_id');
        
        return [
            $user_courses_id->merge($user_compatibles_courses_id),
            $user_courses->count(),
        ];
    }

    protected function updateUserDataOldV1($user = null)
    {
        // info('updateUserData');
        $user = $user ?? auth()->user();
        $courses_id = $user->getCurrentCourses(withFreeCourses: false, withRelations: 'summary-user-update', only_ids: true);
        $count_courses_assigned = count($courses_id);
//        info($courses_id);
//        info("COUNT COURSES ASSIGNED :: ". $count_courses_assigned);

        $row_user = SummaryUser::getCurrentRow(null, $user);
        if(!$row_user){
            return true;
        }
        // info('updateUserData two');
        $res_nota = SummaryTopic::select(DB::raw('AVG(IFNULL(grade, 0)) AS nota_avg'))
            ->whereHas('topic', fn($q) => $q->where('active', ACTIVE)->whereIn('course_id', $courses_id))
            ->whereRelation('topic', 'assessable', ACTIVE)
            ->whereRelation('topic.evaluation_type', 'code', 'qualified')
            ->where('user_id', $user->id)
            ->first();

        $grade_average = round($res_nota->nota_avg, 2);

        $passed = SummaryCourse::where('user_id', $user->id)
            ->whereRelation('status', 'code', 'aprobado')
            ->whereRelation('course.type', 'code', '<>', 'free')
            ->whereIn('course_id', $courses_id)
            ->count();

//        info("PASSED :: ".$passed);

        $percent_general = ($count_courses_assigned > 0) ? (($passed / $count_courses_assigned) * 100) : 0;

//        info("PERCENT GENERAL :: ". $percent_general);
//        info("===========================");

        $percent_general = ($percent_general > 100) ? 100 : $percent_general; // maximo porcentaje = 100
        $percent_general = round($percent_general);

        $intentos_x_curso = SummaryCourse::select(DB::raw('SUM(attempts) as intentos'))
            // ->whereRelation('course', 'active', ACTIVE)
            ->whereRelation('course.type', 'code', '<>', 'free')
            ->where('user_id', $user->id)
            ->whereIn('course_id', $courses_id)
            ->first();

        $attempts = (isset($intentos_x_curso)) ? $intentos_x_curso->intentos : 0;
        if($attempts=='' || is_null($attempts)){
            $attempts = 0;
        }
        // info('updateUserData three');
        $score = User::calculate_rank($passed, $grade_average, $attempts);

//        $tot_com = ($passed > $row_user->courses_assigned) ? $row_user->courses_assigned : $passed;
        $tot_com = ($passed > $count_courses_assigned) ? $count_courses_assigned : $passed;

        $user_data = [
            'courses_assigned' => $count_courses_assigned,
            'courses_completed' => $tot_com,
            'grade_average' => $grade_average,
            'attempts' => $attempts,
            'score' => $score,
            'last_time_evaluated_at' => now(),
            'advanced_percentage' => $percent_general
        ];
        $row_user->update($user_data);

        return $row_user;
    }
}
