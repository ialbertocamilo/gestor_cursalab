<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;

class SummaryUser extends Summary
{
    protected $table = 'summary_users';

    protected $fillable = [
        'last_time_evaluated_at', 'course_assigneds', 'user_id', 'attempts', 'score', 'grade_average', 'courses_completed'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    protected function updateUserData($user = null)
    {
        $user = $user ?? auth()->user();
        $courses_id = $user->getCurrentCourses()->pluck('id');
        $row_user = SummaryUser::getCurrentRow(null, $user);

        $res_nota = SummaryTopic::select(DB::raw('AVG(IFNULL(grade, 0)) AS nota_avg'))
                        ->whereHas('topic', fn($q) => $q->where('active', ACTIVE)->whereIn('course_id', $courses_id))
                        ->where('user_id', $user->id)
                        ->first();

        $grade_average = round($res_nota->nota_avg, 2);

        $passed = SummaryCourse::where('user_id', $user->id)
                                ->whereRelation('status', 'code', 'aprobado')
                                ->whereRelation('course.type', 'code', '<>', 'free')
                                ->whereIn('course_id', $courses_id)
                                ->count();

        $percent_general = ($row_user->courses_assigned > 0) ? (($passed / $row_user->courses_assigned) * 100) : 0;
        $percent_general = ($percent_general > 100) ? 100 : $percent_general; // maximo porcentaje = 100
        $percent_general = round($percent_general);

        $intentos_x_curso = SummaryCourse::select(DB::raw('SUM(attempts) as intentos'))
                                            // ->whereRelation('course', 'active', ACTIVE)
                                            ->whereRelation('course.type', 'code', '<>', 'free')
                                            ->where('user_id', $user->id)
                                            ->whereIn('course_id', $courses_id)
                                            ->first();

        $attempts = (isset($intentos_x_curso)) ? $intentos_x_curso->intentos : 0;
        $score = User::calculate_rank($passed, $grade_average, $attempts);

        $tot_com = ($passed > $row_user->courses_assigned) ? $row_user->courses_assigned : $passed;

        $user_data = [
            'courses_completed' => $tot_com,
            'grade_average' => $grade_average,
            // 'cur_asignados' => $row_user->courses_assigned,
            'attempts' => $attempts,
            'score' => $score,
            'last_time_evaluated_at' => now(),
            'advanced_percentage' => $percent_general
        ];

        $row_user->update($user_data);

        return $row_user;
    }
}
