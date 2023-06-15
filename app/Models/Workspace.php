<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
        'reinicios_programado',
        'contact_support',
        'limit_allowed_users',
        'users_with_empty_criteria'
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
        'reinicios_programado' => 'array',
        'contact_support' => 'array',
        'limit_allowed_users' => 'array'
    ];

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'subworkspace_id');
    }

    public function emails()
    {
        return $this->hasMany(EmailUser::class, 'workspace_id');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_subworkspace', 'subworkspace_id');
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
            Criterion::class
        // 'criterion_workspace',
        // 'workspace_id',
        // 'criterion_id'
        );
    }

    public function criteriaValue()
    {
        return $this->belongsToMany(CriterionValue::class, 'criterion_value_workspace');
    }

    public function module_criterion_value()
    {
        return $this->belongsTo(CriterionValue::class, 'criterion_value_id');
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
        //$subworkspace = Workspace::find($moduleId);

        $subworkspace = Workspace::where('criterion_value_id', $moduleId)
            ->first();

        return $subworkspace->id ?? NULL;

//         $workspace = Workspace::query()
//             ->join('criterion_workspace', 'criterion_workspace.workspace_id', '=', 'workspaces.id')
//             ->join('criterion_values', 'criterion_values.id', '=', 'criterion_workspace.criterion_id')
//             ->when($moduleId ?? null, function ($q) use ($moduleId) {
//                 $q->where('criterion_workspace.criterion_id', $moduleId);
//             })
//             ->select('workspaces.*')
//             ->first();
//
//        return $workspace->id ?? NULL;
    }

    /**
     * Find a subworkspace using its criterion value id
     *
     * @param $criterionValueId
     * @return Builder|Model|object|null
     */
    public static function findSubworkspaceWithCriterionValueId($criterionValueId)
    {
        return Workspace::query()
            ->where('criterion_value_id', $criterionValueId)
            ->first();
    }

    protected function searchSubWorkspace($request)
    {
        $query = self::withCount([
            'schools',
            'users' => fn($q) => $q->onlyClientUsers(),
            'users as active_users_count' => fn($q) => $q->onlyClientUsers()->where('active', ACTIVE)
        ]);

        $query->whereNotNull('parent_id');

        // info('workspace');

        // info(session('workspace'));

        if (session('workspace') || $request->workspace_id)
            $query->where('parent_id', $request->workspace_id ?? session('workspace')->id);

        if ($request->q)
            $query->where('name', 'like', "%$request->q%");

        return $query->paginate($request->paginate);
    }

    protected static function storeSubWorkspaceRequest($data, $subworkspace = null)
    {
        // info(session('workspace'));

        try {

            DB::beginTransaction();

            if ($subworkspace) {

                $subworkspace->update($data);
                if ($subworkspace->wasChanged('name')) {
                    CriterionValue::where('id', $subworkspace->criterion_value_id)->update([
                        'value_text' => $data['name'],
                    ]);
                }
            } else {

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

            };

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

    public function getSettingsLimitAllowedUser()
    {
        $workspace = $this;

        $workspace = $workspace->fresh();

        return $workspace->limit_allowed_users;
    }

    public function getLimitAllowedUsers($sub_workspace_id = null): mixed
    {
        $workspace = $this;
        $constraint = $workspace->getSettingsLimitAllowedUser();

        if (!$constraint) return null;

        $key = $sub_workspace_id ?
            array_search($sub_workspace_id, array_column($constraint['sub_workspaces'], 'sub_workspace_id'))
            : null;

        return match ($constraint['type']) {
            'by_workspace' => $constraint['quantity'],
            'by_sub_workspace' => $constraint['sub_workspaces'][$key],
            default => null
        };
    }
    public function sendEmailByLimit(){
        $workspace = $this;

        $workspace_constraint = $workspace->getSettingsLimitAllowedUser();

        if (!$workspace_constraint) return false;

        $workspace_limit = $workspace->getLimitAllowedUsers();

        $q_current_active_users = User::onlyClientUsers()
            ->where('active', ACTIVE);

        if ($workspace_constraint['type'] === 'by_workspace')
            $q_current_active_users->whereRelation('subworkspace', 'parent_id', $workspace->id);

        if ($workspace_constraint['type'] === 'by_sub_workspace' && $sub_workspace_id)
            $q_current_active_users->where('subworkspace_id', $sub_workspace_id);

        $current_active_users_count = $q_current_active_users->count();
        if($current_active_users_count/$workspace_limit < 0.9){
            $type_id = Taxonomy::where('group','email')->where('type','user')->where('code','limite_workspace')->first()?->id;
            $emais_to_send_user = Workspace::where('id',$this->current_workspace->id)->with('emails.user:id,email_gestor')->wherehas('emails.user',function($q){
                $q->where('active',ACTIVE)->whereNotNull('email_gestor');
            })->select('id','name')->where('type_id',$type_id)->get()->map( fn ($e)=> $e->user->email_gestor);
            if(count($emais_to_send_user) > 0){
                $mail_data=[
                    'subject'=>'Limite de usuarios',
                    'workspace_name' => $workspace->name,
                    'current_active_users_count'=> $current_active_users_count,
                    'workspace_limit'=>$workspace_limit
                ];
                Mail::to($emais_to_send_user)
                ->send(new EmailTemplate('emails.email_limite_usuarios', $mail_data));
            }
        }
        return true;
    }
    public function verifyLimitAllowedUsers($sub_workspace_id = null): bool
    {
        $workspace = $this;

        $workspace_constraint = $workspace->getSettingsLimitAllowedUser();

        if (!$workspace_constraint) return true;

        $workspace_limit = $workspace->getLimitAllowedUsers();

        $q_current_active_users = User::onlyClientUsers()
            ->where('active', ACTIVE);

        if ($workspace_constraint['type'] === 'by_workspace')
            $q_current_active_users->whereRelation('subworkspace', 'parent_id', $workspace->id);

        if ($workspace_constraint['type'] === 'by_sub_workspace' && $sub_workspace_id)
            $q_current_active_users->where('subworkspace_id', $sub_workspace_id);

        $current_active_users_count = $q_current_active_users->count();

//        dd($current_active_users_count, $workspace_limit);
        return $current_active_users_count < $workspace_limit;
    }

    #test functions
    public static function loadSubWorkspaces($attributes)
    {
        // $workspaceId = get_current_workspace_indexes('id');
        $workspace = get_current_workspace();

        return Workspace::select($attributes)
            ->where('active', ACTIVE)
            ->where('parent_id', $workspace->id)
            ->get();
    }

    protected function loadSubWorkspacesSiblings($subworkspace, $attributes)
    {
        return Workspace::select($attributes)
            ->where('active', ACTIVE)
            ->where('parent_id', $subworkspace->parent_id)
            ->get();
    }

    public function criterion_workspace()
    {
        return $this->hasMany(CriterionValueWorkspace::class);
    }
}
