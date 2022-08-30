<?php

namespace App\Models;

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
        $row_user = SummaryUser::getCurrentRow();

        $res_nota = DB::table('pruebas')
                        // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                    ->join('posteos', 'posteos.id', 'pruebas.posteo_id')
                    ->whereIn('pruebas.curso_id',$q_idsXcursos)
                    ->where('posteos.estado', 1)
                    // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                    ->select(DB::raw('AVG(IFNULL(pruebas.nota, 0)) AS nota_avg'))
                    ->where('pruebas.usuario_id', $usuario_id)
                    ->first();

        $nota_prom_gen = number_format((float)$res_nota->nota_avg, 2, '.', '');

        // $helper->log_marker('RG tot_completed_gen');
        $tot_completed_gen = Resumen_x_curso::where('usuario_id', $usuario_id)
                                            // TOMAR EN CUENTA SOLO LOS CURSOS ACTIVOS
                                            ->where('estado_rxc', 1)
                                            ->where('libre', 0)
                                            ->whereIn('curso_id',$q_idsXcursos)
                                            ->where('estado', 'aprobado')
                                            ->count();


        // \Log::info($row_user->assigned. '   =   '. $tot_completed_gen);
        // porcentaje general
        $percent_general = ($row_user->assigned > 0) ? (($tot_completed_gen / $row_user->assigned) * 100) : 0;
        $percent_general = ($percent_general > 100) ? 100 : $percent_general; // maximo porcentaje = 100
        $percent_general = round($percent_general);
        // Calcula ranking
        // $helper->log_marker('RG Calcula ranking');
        $intentos_x_curso = Resumen_x_curso::select(DB::raw('SUM(intentos) as intentos'))
                                            // TOMAR EN CUENTA SOLO LOS CURSOS ACTIVOS
                                            ->whereIn('curso_id',$q_idsXcursos)
                                            ->where('estado_rxc', 1)
                                            ->where('libre', 0)
                                            // TOMAR EN CUENTA SOLO LOS CURSOS ACTIVOS
                                            ->where('usuario_id', $usuario_id)
                                            ->first();

        $intentos_gen = (isset($intentos_x_curso)) ? $intentos_x_curso->intentos : 0;
        $rank_user = $this->calcular_puntos($tot_completed_gen, $nota_prom_gen, $intentos_gen);

        // $helper->log_marker('RG UPDATE');
        $tot_com = ($tot_completed_gen > $row_user->assigned) ? $row_user->assigned : $tot_completed_gen;

        Resumen_general::where('usuario_id', $usuario_id)->update(array(
            'tot_completados' => $tot_com,
            'nota_prom' => $nota_prom_gen,
            'cur_asignados' => $row_user->assigned,
            'intentos' => $intentos_gen,
            'rank' => $rank_user,
            'porcentaje' => $percent_general
        ));
    }
}
