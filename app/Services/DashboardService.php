<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\Usuario;
use App\Models\Taxonomy;
use App\Models\Criterion;
use App\Models\SummaryTopic;
use App\Models\CriterionValue;
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
                    ->join('course_workspace as cw', 'cw.course_id', '=', 'courses.id')
                    ->where('cw.workspace_id', $workspaceId)
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

        return Course::whereRelation('workspaces', 'id', $workspaceId)
                    ->count();
    }

    /**
     * Counts workspace users
     *
     * @param int|null $workspaceId
     * @return int
     */
    public static function countUsers(?int $subworkspaceId): int
    {
        $user_cursalab = Taxonomy::getFirstData('user','type','cursalab');
        if (!$subworkspaceId)
        {
            $workspace = get_current_workspace();
            $subworkspaceIds = $workspace->subworkspaces->pluck('id')->toArray();

            return User::whereIn('subworkspace_id', $subworkspaceIds)
                    ->where('type_id','<>',$user_cursalab->id)
                    ->count();
        }


        return User::where('subworkspace_id', $subworkspaceId)
                    ->where('type_id','<>',$user_cursalab->id)
                    ->count();
    }

    /**
     * Counts workspace active users
     *
     * @param int|null $workspaceId
     * @return int
     */
    public static function countActiveUsers(?int $subworkspaceId): int
    {
        $user_cursalab = Taxonomy::getFirstData('user','type','cursalab');
        if (!$subworkspaceId)
        {
            $workspace = get_current_workspace();
            $subworkspaceIds = $workspace->subworkspaces->pluck('id')->toArray();

            return User::whereIn('subworkspace_id', $subworkspaceIds)
                    ->where('type_id','<>',$user_cursalab->id)
                    ->where('active', ACTIVE)
                    ->count();
        }


        return User::where('subworkspace_id', $subworkspaceId)
                    ->where('type_id','<>',$user_cursalab->id)
                    ->where('active', ACTIVE)
                    ->count();
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
                ->join('course_workspace as cw', 'cw.course_id', '=', 'courses.id')
                ->where('cw.workspace_id', $workspaceId)
                ->where('topics.assessable', 1)
                ->count();
    }

    /**
     * Load visits by user
     *
     * @param $workspaceId
     * @return mixed
     */
    public static function loadVisitsByUser($workspaceId,$module_id)
    {


        $cache_name = 'visitas_usuarios_por_fecha-v2';
        $cache_name .= $module_id ? "visitas_usuarios_por_fecha-v2-{$module_id}" : '';

        // List of ids from users to be excluded in query.
        // In the old version of the app, users' careers
        // with the "contar_en_graficos" value were excluded,
        // Since that field no longer exists, the array in empty

        $excludedUsersIds = implode(',', []);
        $user_cursalab = Taxonomy::getFirstData('user','type','cursalab');
        $result = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_GRAPHICS,
            function () use ($excludedUsersIds, $workspaceId,$module_id,$user_cursalab) {

                $data['time'] = now();
                $data['data'] = SummaryTopic::query()
                    ->whereRelation('topic.course.workspaces','id',$workspaceId)
                    ->whereHas('user',function($q)use($module_id,$user_cursalab){
                        $q->where('users.type_id','<>',$user_cursalab->id)->when($module_id ?? null, function ($q) use($module_id){
                            $q->where('users.subworkspace_id',$module_id);
                        });
                    })
                    // ->join('users', 'users.id', '=', 'summary_topics.user_id')
                    //  ->join('topics', 'topics.id', '=', 'summary_topics.topic_id')
                    //  ->join('courses', 'courses.id', '=', 'topics.course_id')
                    //  ->join('course_workspace', 'course_workspace.course_id', '=', 'courses.id')
                    //  ->where('course_workspace.workspace_id', $workspaceId)
                     ->where(DB::raw('date(summary_topics.created_at)'), '>=', 'curdate() - INTERVAL 20 DAY')
                    //  ->when($module_id ?? null, function ($q) use($module_id){
                    //     $q->where('users.subworkspace_id',$module_id);
                    // })
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
    public static function loadEvaluacionesByDate($workspaceId,$module_id)
    {


        $cache_name = 'evaluaciones_por_fecha-v2';
        $cache_name .= $module_id ? "evaluaciones_por_fecha-v2-{$module_id}" : '';
        // List of ids from users to be excluded in query.
        // In the old version of the app, users' careers
        // with the "contar_en_graficos" value were excluded,
        // Since that field no longer exists, the array in empty

        $excludedUsersIds = implode(',', []);
        $user_cursalab = Taxonomy::getFirstData('user','type','cursalab');
        $result = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_GRAPHICS,
            function () use ($excludedUsersIds, $workspaceId,$module_id,$user_cursalab) {

            $data['time'] = now();

            $data['data'] = SummaryTopic::query()
                ->join('topics', 'topics.id', '=', 'summary_topics.topic_id')
                ->join('users', 'users.id', '=', 'summary_topics.user_id')
                ->join('courses', 'courses.id', '=', 'topics.course_id')
                ->join('course_workspace', 'course_workspace.course_id', '=', 'courses.id')
                ->where('course_workspace.workspace_id', $workspaceId)
                ->when($module_id ?? null, function ($q) use($module_id){
                    $q->where('users.subworkspace_id',$module_id);
                })
                ->where('users.type_id','<>',$user_cursalab->id)
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
                ->where('users.subworkspace_id', $workspaceId)
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
