<?php

namespace App\Services;

use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Curso;
use App\Models\Posteo;
use App\Models\SummaryTopic;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class  DashboardService {

    /**
     * Counts workspace topics
     *
     * @param int|null $workspaceId
     * @return int
     */
    public static function countTopics(?int $workspaceId): int
    {
        // When no workspace id is defined, return 0

        if (!$workspaceId) return 0;

        return Posteo::join('courses', 'courses.id', '=', 'topics.course_id')
                    ->join('course_workspace as cw', 'cw.courses_id', '=', 'courses.id')
                    ->where('cw.workspaces_id', $workspaceId)
                    ->count();
    }

    /**
     * Counts workspace courses
     *
     * @param int|null $workspaceId
     * @return int
     */
    public static function countCourses(?int $workspaceId): int
    {
        // When no workspace id is defined, return 0

        if (!$workspaceId) return 0;

        return Curso::join('course_workspace as cw', 'cw.courses_id', '=', 'courses.id')
                    ->where('cw.workspaces_id', $workspaceId)
                    ->count();
    }

    /**
     * Counts workspace users
     *
     * @param int|null $workspaceId
     * @return string
     */
    public static function countUsers(?int $workspaceId) {
        return 'falta roles';
//                                Usuario::where('rol', 'default')->when($modulo_id, function ($q) use ($modulo_id) {
//                                    $q->where('config_id', $modulo_id);
//                                })->count(),
    }

    /**
     * Counts workspace active users
     *
     * @param int|null $workspaceId
     * @return string
     */
    public static function countActiveUsers(?int $workspaceId) {
        return 'falta roles';
//                                Usuario::where('active', 1)
//                                ->where('rol', 'default')
//                                ->when($modulo_id, function ($q) use ($modulo_id) {
//                                    $q->where('config_id', $modulo_id);
//                                })->count(),
    }

    /**
     * Counts assessable topics
     *
     * @param int|null $workspaceId
     * @return int
     */
    public static function countAssessableTopics(?int $workspaceId): int
    {
        // When no workspace id is defined, return 0

        if (!$workspaceId) return 0;

        return Posteo::join('courses', 'courses.id', '=', 'topics.course_id')
                ->join('course_workspace as cw', 'cw.courses_id', '=', 'courses.id')
                ->where('cw.workspaces_id', $workspaceId)
                ->where('topics.assessable', 1)
                ->count();
    }

    /**
     * Load visits by user
     *
     * @param $workspaceId
     * @return mixed
     */
    public static function loadVisitsByUser($workspaceId)
    {
        $cache_name = 'visitas_usuarios_por_fecha-v2';

        // List of ids from users to be excluded in query.
        // In the old version of the app, users' careers
        // with the "contar_en_graficos" value were excluded,
        // Since that field no longer exists, the array in empty

        $excludedUsersIds = implode(',', []);

        $result = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_GRAPHICS,
            function () use ($excludedUsersIds, $workspaceId) {

                $data['time'] = now();
                $data['data'] = SummaryTopic::query()
                     ->join('topics', 'topics.id', '=', 'summary_topics.topic_id')
                     ->join('courses', 'courses.id', '=', 'topics.course_id')
                     ->join('course_workspace', 'course_workspace.courses_id', '=', 'courses.id')
                     ->where('course_workspace.workspaces_id', $workspaceId)
                     ->where(DB::raw('date(summary_topics.created_at)'), '>=', 'curdate() - INTERVAL 20 DAY')
                     ->select([
                         DB::raw('date(summary_topics.created_at) as fechita'),
                         DB::raw('sum(summary_topics.views) as cant'),
                     ])
                     ->groupBy('fechita')
                     ->orderBy('fechita')
                     ->get();
                return $data;
            });

        return $result;
    }

    /**
     * Load "evaluaciones" by date
     *
     * @param $workspaceId
     * @return mixed
     */
    public static function loadEvaluacionesByDate($workspaceId)
    {
        $cache_name = 'evaluaciones_por_fecha-v2';

        // List of ids from users to be excluded in query.
        // In the old version of the app, users' careers
        // with the "contar_en_graficos" value were excluded,
        // Since that field no longer exists, the array in empty

        $excludedUsersIds = implode(',', []);

        $result = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_GRAPHICS,
            function () use ($excludedUsersIds, $workspaceId) {

            $data['time'] = now();

            $data['data'] = SummaryTopic::query()
                ->join('topics', 'topics.id', '=', 'summary_topics.topic_id')
                ->join('courses', 'courses.id', '=', 'topics.course_id')
                ->join('course_workspace', 'course_workspace.courses_id', '=', 'courses.id')
                ->where('course_workspace.workspaces_id', $workspaceId)
                ->where(DB::raw('date(summary_topics.created_at)'), '>=', 'curdate() - INTERVAL 20 DAY')
                ->select([
                    DB::raw('date(summary_topics.created_at) as fechita'),
                    DB::raw('count(*) as cant'),
                ])
                ->groupBy('fechita')
                ->orderBy('fechita')
                ->get();

            return $data;
        });

        return $result;
    }

    /**
     * Load total of passed users by "botica"
     *
     * @param null $workspaceId
     * @return mixed
     */
    public static function loadTopBoticas($workspaceId): mixed
    {
        $cache_name = 'pruebas_top_boticas-v2';
        $cache_name .= $workspaceId ? "-modulo-{$workspaceId}" : '';

        // List of ids from users to be excluded in query.
        // In the old version of the app, users' careers
        // with the "contar_en_graficos" value were excluded,
        // Since that field no longer exists, the array in empty

        $excludedUsersIds = [];

        // get criterion values ids of "boticas"

        $boticas = Criterion::getValuesForSelect('botica');
        $boticasIds = $boticas->pluck('id')->toArray();

        $result = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_GRAPHICS,
            function () use ($workspaceId, $excludedUsersIds, $boticasIds) {

            $data['time'] = now();

            $data['data'] = SummaryTopic::query()
                ->join('users', 'users.id', '=', 'summary_topics.user_id')
                ->join('criterion_value_user', 'users.id', '=', 'criterion_value_user.user_id')
                ->join('criterion_values', 'criterion_values.id', '=', 'criterion_value_user.criterion_value_id')
                ->whereNotIn('users.id', $excludedUsersIds)
                ->whereIn('criterion_value_user.criterion_value_id', $boticasIds)
                ->where('summary_topics.passed', 1)
                ->where('users.workspace_id', $workspaceId)
                ->where('users.active', 1)
                // group by botica
                ->groupBy('criterion_value_user.criterion_value_id')
                ->orderBy('total_usuarios', 'DESC')
                ->limit(10)
                ->selectRaw('COUNT(users.id) as total_usuarios, criterion_values.value_text AS botica')
                ->get(['total_usuarios', 'botica']);

            return $data;
        });

        return $result;
    }
}
