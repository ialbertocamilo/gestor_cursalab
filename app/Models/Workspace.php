<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use DB;

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

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // public function schools()
    // {
    //     return $this->belongsToMany(School::class);
    // }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function criteria()
    {
        return $this->belongsToMany(Criterion::class, 'criterion_workspace');
    }

    public function criteriaValue()
    {
        return $this->belongsToMany(CriterionValue::class, 'criterion_value_workspace');
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
                 ->join('criterion_value_workspace', 'criterion_value_workspace.workspace_id', '=', 'workspaces.id')
                 ->join('criterion_values', 'criterion_values.id', '=', 'criterion_value_workspace.criterion_value_id')
                 ->where('workspaces.id', $workspaceId)
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

        $count = Workspace::query()
                 ->join('users', 'users.subworkspace_id', '=', 'workspaces.id')
                 ->where('workspaces.id', $workspaceId)
                 ->count();

        return $count ?? 0;
    }

    /**
     * Generate a query to get user's workspaces
     */
    public static function generateUserWorkspacesQuery(int $userId): Builder
    {

        $userEntity = 'App\\Model\\User';
        $allowedRoles = [
            1, // super-user
            2, // config
            3  // admin
        ];

        return Workspace::query()
                ->join('assigned_roles', 'assigned_roles.scope', '=', 'workspaces.id')
                ->join('users', 'users.id', '=', 'assigned_roles.entity_id')
                ->where('assigned_roles.entity_type', $userEntity)
                ->whereIn('assigned_roles.role_id', $allowedRoles)
                ->where('users.id', $userId)
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
     * Get workspace's id, from its module id
     *
     * @param int|null $moduleId
     * @return mixed
     */
    public static function getWorkspaceIdFromModule(?int $moduleId): mixed
    {
        $workspace = Workspace::query()
                    ->join('criterion_value_workspace', 'criterion_value_workspace.workspace_id', '=', 'workspaces.id')
                    ->join('criterion_values', 'criterion_values.id', '=', 'criterion_value_workspace.criterion_value_id')
                    ->where('criterion_values.id', $moduleId)
                    ->first();

        return $workspace?->id;
    }

    protected function searchSubWorkspace($request)
    {
        $query = self::withCount(['users']);

        $query->whereNotNull('parent_id');
        
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

                $criterion_value = $module->values()->create(['value_text' => $data['name'], 'active' => ACTIVE]);

                $data['criterion_value_id'] = $criterion_value->id;

                $subworkspace = self::create($data);

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
}
