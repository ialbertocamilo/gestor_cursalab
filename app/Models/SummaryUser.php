<?php

namespace App\Models;

use DB;

class SummaryUser extends Summary
{
    protected $table = 'summary_users';

    protected $fillable = [
        'last_time_evaluated_at', 'course_assigneds', 'user_id', 'attempts'
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
        $row_user = SummaryUser::getCurrentRow();

        $res_nota = SummaryTopic::select(DB::raw('AVG(IFNULL(grade, 0)) AS nota_avg'))
                        // ->whereRelation('topic.course', 'active', ACTIVE)
                        ->whereHas('topic', fn($q) => $q->where('active', ACTIVE)->whereIn('course_id', $courses_id))
                        // ->whereRelation('topic', 'active', ACTIVE)
                        ->where('user_id', $user->id)
                        ->first();

        // $nota_prom_gen = number_format((float)$res_nota->nota_avg, 2, '.', '');
        $grade_average = round($res_nota->nota_avg, 2);

        // $helper->log_marker('RG passed');
        $passed = SummaryCourse::where('user_id', $user->id)
                                ->whereRelation('status', 'code', 'aprobado')
                                // ->whereRelation('course', 'active', ACTIVE)
                                ->whereRelation('course.type', 'code', '<>', 'free')
                                ->whereIn('course_id', $courses_id)
                                ->count();

        $percent_general = ($row_user->assigned > 0) ? (($passed / $row_user->assigned) * 100) : 0;
        $percent_general = ($percent_general > 100) ? 100 : $percent_general; // maximo porcentaje = 100
        $percent_general = round($percent_general);
        
        // Calcula ranking
        $intentos_x_curso = SummaryCourse::select(DB::raw('SUM(attempts) as intentos'))
                                            // ->whereRelation('course', 'active', ACTIVE)
                                            ->whereRelation('course.type', 'code', '<>', 'free')
                                            ->where('user_id', $user->id)
                                            ->whereIn('course_id', $courses_id)

                                            // ->where('libre', 0)
                                            ->first();

        $attempts = (isset($intentos_x_curso)) ? $intentos_x_curso->intentos : 0;
        $score = User::calculate_rank($passed, $grade_average, $attempts);

        // $helper->log_marker('RG UPDATE');
        $tot_com = ($passed > $row_user->assigned) ? $row_user->assigned : $passed;

        $user_data = [
            'completed' => $tot_com,
            'grade_average' => $grade_average,
            // 'cur_asignados' => $row_user->assigned,
            'attempts' => $attempts,
            'score' => $score,
            'advanced_percentage' => $percent_general
        ];

        $row_user->update($user_data);

        return $row_user;
    }
}
