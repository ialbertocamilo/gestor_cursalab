<?php

namespace App\Models;

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

    public function schools()
    {
        return $this->belongsToMany(School::class);
    }
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

    protected static function search($request)
    {
        $query = self::query()->where('active', true);

        if ($request->id) {
            $query::where('id', $request->id)
                  ->withCount(['schools', 'courses']);
        }

        if ($request->q) {
            $query->where('name', 'like', "%$request->q%");
        }

        $field = $request->sortBy ?? 'id';
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
    public static function countModules(int $workspaceId) {

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
    public static function countUsers(int $workspaceId) {

        $count = Workspace::query()
                 ->join('users', 'users.workspace_id', '=', 'workspaces.id')
                 ->where('workspaces.id', $workspaceId)
                 ->count();

        return $count ?? 0;
    }

    /**
     * Load user assigned workspaces, according roles
     */
    public static function loadUserWorkspaces(int $userId) {

        $userEntity = 'App\\Model\\User';
        $allowedRoles = [
            1, // super-user
            2, // config
            3  // admin
        ];
        $workspaces = Workspace::query()
                        ->join('assigned_roles', 'assigned_roles.scope', '=', 'workspaces.id')
                        ->join('users', 'users.id', '=', 'assigned_roles.entity_id')
                        ->where('assigned_roles.entity_type', $userEntity)
                        ->whereIn('assigned_roles.role_id', $allowedRoles)
                        ->where('users.id', $userId)
                        ->where('workspaces.active', ACTIVE)
                        ->select('workspaces.*')
                        ->get();

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
}
