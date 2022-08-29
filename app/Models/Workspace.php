<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;


class Workspace extends BaseModel
{
    protected $fillable = [
        'parent_id', 'criterion_value_id',
        'name', 'description', 'active',
        'logo', 'logo_negativo', 'url_powerbi',
        'plantilla_diploma',
        'codigo_matricula',
        'mod_evaluaciones',
        'reinicios_programado'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true,
                'unique' => true
            ]
        ];
    }

    protected $casts = [
        'mod_evaluaciones' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'subworkspace_id');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function parent()
    {
        return $this->belongsTo(Workspace::class, 'parent_id');
    }

    public function criterionWorkspace()
    {
        return $this->belongsToMany(
            Criterion::class,
            // 'criterion_workspace',
            // 'workspace_id',
            // 'criterion_id'
        );
    }

    public function criteriaValue()
    {
        return $this->belongsToMany(CriterionValue::class, 'criterion_value_workspace');
    }

    public function subworkspaces()
    {
        return $this->hasMany(Workspace::class, 'parent_id');
    }

    public function app_menu()
    {
        return $this->belongsToMany(Taxonomy::class, 'workspace_app_menu', 'workspace_id', 'menu_id')
            ->where('type', 'main_menu')
            ->select('id', 'name');
    }

    public function main_menu()
    {
        return $this->belongsToMany(Taxonomy::class, 'workspace_app_menu', 'workspace_id', 'menu_id')
            ->where('type', 'main_menu')
            ->select('id', 'name', 'code');
    }

    public function side_menu()
    {
        return $this->belongsToMany(Taxonomy::class, 'workspace_app_menu', 'workspace_id', 'menu_id')
            ->where('type', 'side_menu')
            ->select('id', 'name', 'code');
    }

    protected static function search($request)
    {
        // Get authenticated user's id

        $userId = null;
        if (Auth::check()) {
            $userId = Auth::user()->id;
        }

        // Generate query to get workspaces according to role

        $query = self::generateUserWorkspacesQuery($userId);

        if ($request->id) {
            $query::where('id', $request->id)
                ->withCount(['schools', 'courses']);
        }

        if ($request->q) {
            $query->where('name', 'like', "%$request->q%");
        }

        $field = $request->sortBy ?? 'workspaces.id';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    /**
     * Count workspaces' modules
     *
     * @param int $workspaceId
     * @return int
     */
    public static function countModules(int $workspaceId): int
    {

        $count = Workspace::query()
            ->where('parent_id', $workspaceId)
            ->count();

        return $count ?? 0;
    }

    /**
     * Count workspace's users
     *
     * @param int $workspaceId
     * @return int
     */
    public static function countUsers(int $workspaceId): int
    {

        $ids = self::loadSubWorkspacesIds($workspaceId);

        $count = User::query()
                    ->whereIn('subworkspace_id', $ids)
                    ->count();

        return $count ?? 0;
    }

    /**
     * Generate a query to get user's workspaces
     */
    public static function generateUserWorkspacesQuery(int $userId): Builder
    {
        // When user has SuperUser role, return al workspaces

        $isSuperUser = AssignedRole::hasRole($userId, Role::SUPER_USER);
        if ($isSuperUser) {

            // Return all workspaces, excluding subworkspaces

            return Workspace::query()
                            ->where('parent_id', null)
                            ->where('workspaces.active', ACTIVE)
                            ->where('workspaces.deleted_at', null)
                            ->select('workspaces.*');
        }


        // Get user's assigned roles

        $assignedRoles = AssignedRole::getUserAssignedRoles($userId);
        $allowedRoles = [
            Role::CONFIG,
            Role::ADMIN,
            Role::CONTENT_MANAGER,
            Role::TRAINER,
            Role::REPORTS
        ];

        // Get list of workspaces the user is allowed to
        // access to, according to its role

        $workspacesIds = AssignedRole::query()
                           ->join('users', 'users.id', '=', 'assigned_roles.entity_id')
                           ->where('assigned_roles.entity_type', AssignedRole::USER_ENTITY)
                           ->whereIn('assigned_roles.role_id', $allowedRoles)
                           ->where('users.id', $userId)
                           ->select('assigned_roles.*')
                           ->pluck('scope');

        return Workspace::query()
                        ->whereIn('id', $workspacesIds)
                        ->where('workspaces.active', ACTIVE)
                        ->select('workspaces.*');
    }

    /**
     * Load user assigned workspaces, according roles
     */
    public static function loadUserWorkspaces(int $userId): Collection|array
    {

        // Load user's workspaces according its roles

        $workspaces = Workspace::generateUserWorkspacesQuery($userId)->get();

        if (count($workspaces) > 0) {

            return $workspaces;

        } else {

            // Since user does not have any workspace assigned by role,
            // get its subworkspace with it subworkspace_id field

            $user = User::find($userId);
            $workspace = Workspace::find($user->subworkspace_id);

            return [$workspace];
        }
    }

    /**
     * Load ids of workspace's workspaces
     *
     * @param $workspaceId
     * @return mixed
     */
    public static function loadSubWorkspacesIds($workspaceId): mixed
    {

        return Workspace::where('active', ACTIVE)
                        ->where('parent_id', $workspaceId)
                        ->pluck('id');
    }

    /**
     * Get workspace's id, from its module id
     *
     * @param int|null $moduleId
     * @return mixed
     */
    public static function getWorkspaceIdFromModule(?int $moduleId): mixed
    {
        $subworkspace = Workspace::find($moduleId);


        // $workspace = Workspace::query()
        //     ->join('criterion_workspace', 'criterion_workspace.workspace_id', '=', 'workspaces.id')
        //     ->join('criterion_values', 'criterion_values.id', '=', 'criterion_workspace.criterion_id')
        //     ->when($moduleId ?? null, function ($q) use ($moduleId) {
        //         $q->where('criterion_workspace.criterion_id', $moduleId);
        //     })
        //     ->select('workspaces.*')
        //     ->first();

        return $workspace->parent_id ?? NULL;
    }

    protected function searchSubWorkspace($request)
    {
        $query = self::withCount(['users']);

        $query->whereNotNull('parent_id');

        info('workspace');

        info(session('workspace'));

        if (session('workspace') || $request->workspace_id)
            $query->where('parent_id', $request->workspace_id ?? session('workspace')->id);

        if ($request->q)
            $query->where('name', 'like', "%$request->q%");

        return $query->paginate($request->paginate);
    }

    protected static function storeSubWorkspaceRequest($data, $subworkspace = null)
    {
        info(session('workspace'));

        try {

            DB::beginTransaction();

            if ($subworkspace) :

                $subworkspace->update($data);

            else:

                $data['parent_id'] = session('workspace')->id ?? NULL;

                $module = Criterion::where('code', 'module')->first();

                $criterion_value = $module->values()->create([
                    'value_text' => $data['name'],
                    'active' => ACTIVE
                ]);

                $data['criterion_value_id'] = $criterion_value->id;

                $subworkspace = self::create($data);

                $workspace = get_current_workspace();
                $workspace->criteriaValue()->attach($criterion_value);

            endif;

            if (!empty($data['app_menu'])):
                $subworkspace->app_menu()->sync($data['app_menu']);
            endif;

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            info($e);

            return $e;
        }

        return $subworkspace;

    }

    protected function getFullAppMenu($type, $codes)
    {
        $values = Taxonomy::getDataByGroupAndType('system', $type);

        $data = [];

        foreach ($values as $value) {
            $data[$value->code] = in_array($value->code, $codes);
        }

        return $data;
    }
}
