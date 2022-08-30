<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class SummaryCourse extends Summary
{
    protected $table = 'summary_courses';

    protected $fillable = [
        'last_time_evaluated_at', 'user_id', 'course_id', 'status_id', 'assigneds', 'attempts', 'views'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    public static function resetUserCoursesAttempts($userId, $coursesIds)
    {
        self::whereIn('course_id', $coursesIds)
            ->where('user_id', $userId)
            ->update([
                'attempts' => 0,
                //'fuente' => 'resetm'
            ]);
    }

    /**
     * Reset attemtps of failed courses (desaprobados)
     * @param $courseId
     * @param $attemptsLimit
     * @param $scheduleDate
     * @return void
     */
    public static function resetFailedCourseAttemptsAllUsers(
        $courseId, $attemptsLimit, $scheduleDate = null
    ): void
    {

       $query = self::where('course_id', $courseId)
                    ->where('passed', 0)
                    ->where('attempts', '>=', $attemptsLimit);

       if ($scheduleDate)
           $query->where('last_time_evaluated_at', '<=', $scheduleDate);

        $query->update([
                'attempts' => 0,
                'last_time_evaluated_at' => Carbon::now()
                //'fuente' => 'resetm'
            ]);
    }

    /**
     * Reset topics attempts
     *
     * @param $courseId
     * @param $userId
     * @return Collection
     */
    public static function getCourseTopicsIds($courseId, $userId = null): Collection
    {

        $query = SummaryTopic::query()
            ->join('topics', 'topics.id', 'summary_topics.topic_id')
            ->join('courses', 'courses.id', 'topics.course_id')
            ->where('courses.id', $courseId);

        if ($userId) {
            $query->where('summary_topics.user_id', $userId);
        }

        return $query->pluck('summary_topics.topic_id');
    }

    /**
     * Reset topics attempts for all users
     *
     * @param $courseId
     * @param $attemptsLimit
     * @param null $scheduleDate
     * @return void
     */
    public static function resetCourseTopicsAttemptsAllUsers(
        $courseId, $attemptsLimit, $scheduleDate = null
    ): void
    {

        $topicsIds = SummaryTopic::query()
            ->join('topics', 'topics.id', 'summary_topics.topic_id')
            ->join('courses', 'courses.id', 'topics.course_id')
            ->where('courses.id', $courseId)
            ->pluck('summary_topics.topic_id');

        SummaryTopic::resetFailedTopicsAttemptsAllUsers(
            $topicsIds, $attemptsLimit, $scheduleDate
        );
    }

    /**
     * Update courses restarts count
     *
     * @param $courseId
     * @param $adminId
     * @param $userId
     * @return void
     */
    public static function updateCourseRestartsCount(
        $courseId, $adminId = null, $userId = null
    ): void
    {

        $query = SummaryCourse::where('course_id', $courseId);
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $summaryCourse = $query->first();

        if (!$summaryCourse) return;

        // Calculate number of restars

        $restars = $summaryCourse->restarts
            ? $summaryCourse->restarts + 1
            : 1;

        // Update record

        $summaryCourse->restarts =  $restars;
        $summaryCourse->restarter_id = $adminId;
        $summaryCourse->save();
    }


    protected function updateRanking($topic)
    {
        // $helper = new HelperController();


        // $active_topics = Posteo::where('curso_id', $topic->course_id)->where('estado', 1)->select('id','evaluable','tipo_ev')->get();
        $active_topics = $topic->course->topics->where('active', ACTIVE)->get();
        // $curso = Curso::where('id',$topic->course_id)->select('id','libre','categoria_id')->first();

        $cant_asignados = count($active_topics);
        $topics_qualified =  $active_topics->where('evaluation_type.code', 'qualified');
        $topics_open = $active_topics->where('evaluation_type.code', 'open');
        $topics_for_review_only = $active_topics->where('assessable', '<>', 1);
        // APROBADOS CALIFICADOS
        // $helper->log_marker('RxC PRUEBAS');
        $cant_aprob = 0;
        $cant_abiertos = 0;
        $cant_revisados = 0;
        $cant_desaprob = 0;

        // $asing = $helper->help_cursos_x_matricula_con_cursos_libre($usuario_id);

        // $el_curso_esta_asignado = false;
        // if(count($asing)>0){
        //     $el_curso_esta_asignado = in_array($topic->course_id,$asing);
        // }

        // if($el_curso_esta_asignado){
            if(count($topics_qualified)>0){
                $cant_aprob = DB::table('pruebas')
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->whereIn('posteo_id',$topics_qualified->pluck('id'))
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->where('pruebas.resultado', 1)
                ->where('pruebas.usuario_id', $usuario_id)
                ->where('pruebas.curso_id', $topic->course_id)
                ->count();
                $cant_desaprob = DB::table('pruebas AS u')
                    ->whereIn('posteo_id',$topics_qualified->pluck('id'))
                    ->where('u.resultado', 0)
                    ->where('u.intentos', '>=', $max_intentos)
                    ->where('u.usuario_id', $usuario_id)
                    ->where('u.curso_id', $topic->course_id)
                    ->count();
            }
            if(count($topics_open)>0){
                // APROBADOS ABIERTOS
                $cant_abiertos = DB::table('ev_abiertas')
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->whereIn('posteo_id',$topics_open->pluck('id'))
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->where('ev_abiertas.usuario_id', $usuario_id)
                ->where('ev_abiertas.curso_id', $topic->course_id)
                ->count();
            }
            if(count($topics_for_review_only)>0){
                $cant_revisados = DB::table('visitas')
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->whereIn('post_id',$topics_for_review_only->pluck('id'))
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->where('visitas.usuario_id', $usuario_id)
                ->where('visitas.curso_id', $topic->course_id)
                ->where('visitas.estado_tema', "revisado")
                ->count();
            }
            // REVISADOS (TEMAS NO EVALUABLES QUE FUERON VISTOS)
            // Cambiar estado de resumen_x_curso
            $tot_completados = $cant_aprob + $cant_abiertos + $cant_revisados;

            // Porcentaje avance por curso
            $percent_curso = ($cant_asignados > 0) ? (($tot_completados / $cant_asignados) * 100) : 0;
            $percent_curso = ($percent_curso > 100) ? 100 : $percent_curso; // Maximo porcentaje = 100

            // Valida estados
            $estado_curso = "desarrollo";
            if ($tot_completados >= $cant_asignados) {
                // $helper->log_marker('RxC Encuestas Respuestas');
                $existe_encuesta = Curso_encuesta::select('id')->where('curso_id', $topic->course_id)->first();
                if($existe_encuesta){
                    $hizo_encuesta = DB::table('encuestas_respuestas')
                    ->select('curso_id')
                    ->where('usuario_id', $usuario_id)
                    ->where('curso_id', $topic->course_id)
                    ->first();
                    if ($hizo_encuesta) {
                        $estado_curso = "aprobado";
                        // Genera diploma
                        // $helper->log_marker('RxC Diplomas');
                        Diploma::generar_diploma_x_curso_y_escuela($asing,$curso,$usuario_id);
                    } else {
                        $estado_curso = "enc_pend";
                    }
                }else {
                    // Si el curso no tiene encuesta ASOCIADA, por defecto el curso ya está aprobado (validando que todos los temas se hayan completado)
                    $estado_curso = "aprobado";
                    // Genera/valida diploma para curso y escuela
                    Diploma::generar_diploma_x_curso_y_escuela($asing,$curso,$usuario_id);
                }

            } else {
                // $helper->log_marker('RxC Pruebas tot_completados >= $cant_asignados');


                if ($cant_desaprob >= $cant_asignados) {
                    $estado_curso = "desaprobado";
                }
            }
            // nueva Nota promedio CURSO
            // $helper->log_marker('RxC nueva Nota promedio CURSO');

            $res_nota = DB::table('pruebas')
                // TOMAR EN CUENTA QUE EL TEMA ESTE ACTIVO
                ->whereIn('posteo_id',$topics_qualified->pluck('id'))
                ->select(DB::raw('AVG(IFNULL(nota, 0)) AS nota_avg'))
                ->where('pruebas.usuario_id', $usuario_id)
                ->where('pruebas.curso_id', $topic->course_id)
                ->first();

            $nota_prom_curso = number_format((float)$res_nota->nota_avg, 2, '.', '');
            $visitas = Visita::select(DB::raw('SUM(sumatoria) as suma_visitas'))
                    ->whereIn('post_id',$active_topics->pluck('id'))
                    ->where('visitas.usuario_id', $usuario_id)
                    ->where('visitas.curso_id', $topic->course_id)->first();
            $suma_visitas = (isset($visitas)) ? $visitas->suma_visitas : 0;
            /*-----------------------------------------------------------------------------------------------------------------------------------------*/
            // $helper->log_marker('RxC Update');
            if($tot_completados>0 || $cant_desaprob>0 || $nota_prom_curso>0){
                $this->crear_actualizar_resumenes($usuario_id, $topic->course_id);
            }
            Resumen_x_curso::where('usuario_id', $usuario_id)->where('curso_id', $topic->course_id)->update(array(
                'estado_rxc'=>1,
                'asignados' => $cant_asignados,
                'aprobados' => $cant_aprob,
                'realizados' => $cant_abiertos,
                'revisados' => $cant_revisados,
                'desaprobados' => $cant_desaprob,
                'nota_prom' => $nota_prom_curso,
                'estado' => $estado_curso,
                'porcentaje' => $percent_curso,
                'visitas' => $suma_visitas,
                'libre' => $curso->libre
                // 'intentos' => $suma_intentos->intentos,
            ));
            // $helper->log_marker('RxC Update FIN');
        // }else{
        //     Resumen_x_curso::where('usuario_id',$usuario_id)->where('curso_id',$topic->course_id)->update([
        //         'estado_rxc'=>0
        //     ]);
        // }
    }
}
