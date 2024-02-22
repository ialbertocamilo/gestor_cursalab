<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Media;
use App\Models\Posteo;
use App\Models\SummaryTopic;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\Usuario;
use App\Models\Workspace;
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
        if($module_id){
            $cache_name .= $module_id ? "visitas_usuarios_por_fecha-v2-{$module_id}" : '';
        }

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
                    // ->whereRelation('topic.course.workspaces','id',$workspaceId)
                    // ->whereHas('user',function($q)use($module_id,$user_cursalab){
                    //     $q->where('users.type_id','<>',$user_cursalab->id)->when($module_id ?? null, function ($q) use($module_id){
                    //         $q->where('users.subworkspace_id',$module_id);
                    //     });
                    // })
                    ->join('users', 'users.id', '=', 'summary_topics.user_id')
                     ->join('topics', 'topics.id', '=', 'summary_topics.topic_id')
                     ->join('courses', 'courses.id', '=', 'topics.course_id')
                     ->join('course_workspace', 'course_workspace.course_id', '=', 'courses.id')
                     ->where('course_workspace.workspace_id', $workspaceId)
                     ->where(DB::raw('date(summary_topics.created_at)'), '>=', DB::raw("curdate() - INTERVAL 20 DAY"))
                     ->when($module_id ?? null, function ($q) use($module_id){
                        $q->where('users.subworkspace_id',$module_id);
                    })
                     ->select([
                         DB::raw('date(summary_topics.created_at) as fechita'),
                         DB::raw('sum(summary_topics.views) as cant'),
                     ])
                     ->groupBy('fechita')
                     ->orderBy('fechita','desc')
                     ->limit(30)
                     ->get()->sortBy('fechita');
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
        if($module_id){
            $cache_name .= $module_id ? "evaluaciones_por_fecha-v2-{$module_id}" : '';
        }
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
                ->where(DB::raw('date(summary_topics.created_at)'), '>=', DB::raw("curdate() - INTERVAL 20 DAY"))
                ->select([
                    DB::raw('date(summary_topics.created_at) as fechita'),
                    DB::raw('count(*) as cant'),
                ])
                ->groupBy('fechita')
                ->orderBy('fechita','desc')
                ->limit(30)
                ->get()->sortBy('fechita');

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

    public static function withCountUsers($q, $user_cursalab, $active = null, $alias = null) 
    {
        $user_alias = is_null($alias) ? 'users' : 'users as '.$alias ;
        $platform = session('platform');
        if($platform && $platform == 'induccion'){
            return 5000;
        }
        $type_id = ($platform && $platform == 'induccion') ? Taxonomy::getFirstData('user', 'type', 'employee_onboarding')->id : Taxonomy::getFirstData('user', 'type', 'employee')->id ;
        $q->withCount([$user_alias => function ($q) use($user_cursalab, $active,$type_id) {
            $q->where('type_id',$type_id);
            if (is_null($active)) {
                $q->where('type_id','<>',$user_cursalab->id); // todos los usuarios
            }else{
                $q->where('type_id','<>',$user_cursalab->id)->where('active', $active); // activos - inactivos
            }
        }]);
    }

    public static function loadWorkspacesStatus()
    {
        // === usuario cursalab ===
        $user_cursalab = Taxonomy::getFirstData('user','type','cursalab');

        $userId = null;
        if (Auth::check()) {
            $userId = Auth::user()->id;
        }

        $query = Workspace::generateUserWorkspacesQuery($userId);
        $query->select('id', 'name', 'logo', 'limit_allowed_users', 'limit_allowed_storage', 'parent_id', 'criterion_value_id');
        $query->with(['subworkspaces' => function($q) use($user_cursalab) {
                        $q->select('id', 'criterion_value_id', 'name', 'logo', 'parent_id');
                        self::withCountUsers($q, $user_cursalab, ACTIVE, alias: 'users_count_actives');
        }]);
        self::withCountUsers($query, $user_cursalab, ACTIVE, alias: 'users_count_actives');

        return $query->get();
    }

    public static function loadSizeWorkspaces($workspaces_ids)
    {
        return Workspace::select('id', 'name')
                        ->whereIn('id', $workspaces_ids)
                        ->withSum('medias', 'size')->get();
    }

    public static function loadSizeByExtensionWorkspace($workspace_id, $key) {
        $extensions = config('constantes.extensiones');

        return Media::where('workspace_id', $workspace_id)
                    ->whereIn('ext', $extensions[$key])
                    ->sum('size');
    }

    public static function loadCountUsersWorkspaces() {
        // === usuario cursalab ===
        $user_cursalab = Taxonomy::getFirstData('user','type','cursalab');

        $workspace = get_current_workspace(); 

        $query = Workspace::where('id', $workspace->id);
        $query->select('id', 'name', 'logo', 'limit_allowed_users', 'limit_allowed_storage', 'parent_id', 'criterion_value_id');
        $query->with(['subworkspaces' => function ($q) use ($user_cursalab) {
                    $q->select('id', 'criterion_value_id', 'name', 'logo', 'parent_id');
                    self::withCountUsers($q, $user_cursalab, ACTIVE, alias: 'users_count_actives');
                    // self::withCountUsers($q, $user_cursalab, INACTIVE, alias: 'users_count_inactives');
        }]);
        self::withCountUsers($query, $user_cursalab, ACTIVE, alias: 'users_count_actives');
        // self::withCountUsers($query, $user_cursalab, INACTIVE, alias: 'users_count_inactives')
        
        return $query->first();
    }

    public static function loadCurrentWorkspaceStatus()
    {
        // === usuario cursalab ===
        $user_cursalab = Taxonomy::getFirstData('user','type','cursalab');
        
        $workspace = get_current_workspace();

        $query = Workspace::where('id', $workspace->id);

        $query->select('id', 'name', 'logo', 'limit_allowed_users', 'limit_allowed_storage', 'parent_id', 'criterion_value_id');
        $query->with(['subworkspaces' => function ($q) use ($user_cursalab) {
                    $q->select('id', 'criterion_value_id', 'name', 'logo', 'parent_id');
                    self::withCountUsers($q, $user_cursalab, ACTIVE, alias: 'users_count_actives');
                    self::withCountUsers($q, $user_cursalab, INACTIVE, alias: 'users_count_inactives');
        }]);
        self::withCountUsers($query, $user_cursalab, ACTIVE, alias: 'users_count_actives');
        self::withCountUsers($query, $user_cursalab, INACTIVE, alias: 'users_count_inactives');
        
        $query->withSum('medias', 'size');

        return $query->first();
    }

    public static function loadSubworkspaceStatus($subworkspace_id) {
        // === usuario cursalab ===
        $user_cursalab = Taxonomy::getFirstData('user','type','cursalab');

        $query = Workspace::select('id', 'name', 'logo', 'limit_allowed_users', 'parent_id')
              ->where('id', $subworkspace_id)
              ->with(['parent' => fn($q) => $q->select('id', 'limit_allowed_users', 'parent_id') ]);

        self::withCountUsers($query, $user_cursalab, ACTIVE, alias: 'users_count_actives');

        return $query->first();
    }
}
