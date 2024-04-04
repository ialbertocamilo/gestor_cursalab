<?php

namespace App\Models;

use App\Models\CheckList;
use App\Mail\EmailTemplate;
use Illuminate\Support\Facades\DB;
use App\Models\Mongo\JarvisAttempt;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Workspace extends BaseModel
{
    protected $fillable = [
        'parent_id', 'criterion_value_id',
        'name', 'description', 'active',
        'logo', 'logo_negativo', 'url_powerbi',
        'plantilla_diploma',
        'codigo_matricula',
        // 'mod_evaluaciones',
        'reinicios_programado',
        'contact_support',
        'limit_allowed_users',
        'limits',
        'users_with_empty_criteria',
        'qualification_type_id',

        'logo_marca_agua',
        'marca_agua_estado',
        'notificaciones_push_chunk',
        'notificaciones_push_envio_inicio',
        'notificaciones_push_envio_intervalo',
        'criterio_id_fecha_inicio_reconocimiento',
        'limit_allowed_storage',
        'limits',
        'jarvis_configuration',
        'show_logo_in_app',
        'share_diplomas_social_media',
        'certificate_template_id',
        'dc3_configuration',
        'reminders_configuration',
        'course_configuration',
        'registro_capacitacion'
    ];

    const CUSTOM_PIVOT_FIELDS = [
        'criterion_title' => [
            'name' => 'Título de criterio',
            'type' => 'text',
        ],

        'available_in_profile' => [
            'name' => 'Perfil',
            'type' => 'boolean',
        ],

        'available_in_ranking' => [
            'name' => 'Ranking',
            'type' => 'boolean',
        ],

        'available_in_reports' => [
            'name' => 'Reportes',
            'type' => 'boolean',
        ],

        'available_in_segmentation' => [
            'name' => 'Segmentación',
            'type' => 'boolean',
        ],

        'required_in_user_creation' => [
            'name' => 'Crear Usuario',
            'type' => 'boolean',
        ],

        'available_in_user_filters' => [
            'name' => 'Filtro Usuarios',
            'type' => 'boolean',
        ],
        'avaiable_in_personal_data_guest_form'=>[
            'name' => 'Dato Personal en formulario de Invitados',
            'type' => 'boolean',
        ]
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
        // 'mod_evaluaciones' => 'array',
        'reinicios_programado' => 'array',
        'contact_support' => 'array',
        'limit_allowed_users' => 'array',
        'limits' => 'json',
        'jarvis_configuration' => 'json',
        'dc3_configuration'=>'array',
        'reminders_configuration'=>'json',
        'course_configuration'=>'json',
        'registro_capacitacion' => 'json'
    ];

    public function setRegistroCapacitacionAttribute($value)
    {
        $this->attributes['registro_capacitacion'] = json_encode($value);
    }

    public function getRegistroCapacitacionAttribute($value)
    {
        return $value ? json_decode($value) : json_decode('{"company":{}}');
    }

    public function medias() {
        return $this->hasMany(Media::class, 'workspace_id');
    }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'subworkspace_id');
    }

    public function polls()
    {
        return $this->hasMany(Poll::class, 'workspace_id');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'workspace_id');
    }

    public function push_notifications()
    {
        return $this->hasMany(PushNotification::class, 'workspace_id');
    }

    public function videotecas()
    {
        return $this->hasMany(Videoteca::class, 'workspace_id');
    }

    public function emails()
    {
        return $this->hasMany(EmailUser::class, 'workspace_id');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_subworkspace', 'subworkspace_id')->withPivot('position');
    }

    public function processes()
    {
        return $this->belongsToMany(Process::class, 'process_subworkspace', 'subworkspace_id')->withPivot('position');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function functionalities()
    {
        return $this->belongsToMany(Taxonomy::class, 'workspace_functionalities', 'workspace_id', 'functionality_id');
    }

    public function parent()
    {
        return $this->belongsTo(Workspace::class, 'parent_id');
    }

    public function criterionWorkspace()
    {
        $custom_pivot_fields = array_keys(Workspace::CUSTOM_PIVOT_FIELDS);

        return $this->belongsToMany(Criterion::class)->withPivot($custom_pivot_fields);
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

    public function qualification_type()
    {
        return $this->belongsTo(Taxonomy::class, 'qualification_type_id');
    }

    public function subworkpsace_criterion_type(array $codes) {
        return $this->criterionWorkspace()->whereHas('field_type', function($query) use ($codes) {
            $query->whereIn('code', $codes);
        })->get();
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
    public function getDc3ConfigurationAttribute($value)
    {
        if($this->parent_id){
            return null;
        }
        // Puedes realizar alguna manipulación o formato antes de devolver el valor
        if(is_null($value) || $value=='undefined'){
            $data = [];
            // $data['value_unique_population_registry_code'] = 'Clave Única de Registro de Población';
            // $data['criterion_unique_population_registry_code'] = null;
            // $data['value_specific_occupation'] = 'Ocupación específica (Catálogo Nacional Ocupaciones)';
            // $data['criterion_specific_occupation'] = null;
            $data['value_position'] = 'Puesto';
            $data['criterion_position'] = null;
            $data['subwokspace_data'] = [];
            $subworkspaces = get_subworkspaces(get_current_workspace());
            foreach ($subworkspaces as $subworkspace) {
                $data['subwokspace_data'][] = [
                    'subworkspace_id' => $subworkspace->id,
                    'name_or_social_reason' => '',
                    'shcp'=>''
                ];
            }
            return $data;
        }
        $data =json_decode($value);
        return $data;
    }
    protected static function search($request)
    {
        // Get authenticated user's id

        $userId = null;
        if (Auth::check()) {
            $userId = Auth::user()->id;
        }

        // Generate query to get workspaces according to role

        $query = self::generateUserWorkspacesQuery($userId, false);

        $query->withCount(['schools', 'courses', 'subworkspaces']);

        if ($request->id)
            $query::where('id', $request->id);

        if ($request->active == 1)
            $query->where('active', ACTIVE);

        if ($request->active == 2)
            $query->where('active', '<>', ACTIVE);

        if ($request->q)
            $query->where('name', 'like', "%$request->q%");

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
        $ids = get_subworkspaces_id($workspaceId);

        $count = Workspace::query()
            ->where('parent_id', $workspaceId)
            ->whereIn('id', $ids)
            ->count();

        return $count ?? 0;
    }

    /**
     * Count workspaces' courses
     *
     * @param int $workspaceId
     * @return int
     */
    public static function countCourses(int $workspaceId): int
    {
        // $workspace = get_current_workspace();

        $q = Course::query();
            // ->whereHas('workspaces', function ($t) use ($workspaceId) {
            //     $t->where('workspace_id', $workspaceId);
            // });


            $q->whereHas('schools.subworkspaces', function ($q) use ($workspaceId) {
                $q->whereIn('id', get_subworkspaces_id($workspaceId));
            });

        // if ($request->active == 1)
        //     $q->where('active', ACTIVE);

        // if ($request->active == 2)
        //     $q->where('active', '<>', ACTIVE);

        return $q->count();
    }

    /**
     * Count workspace's users
     *
     * @param int $workspaceId
     * @return int
     */
    public static function countUsers(int $workspaceId): int
    {
        $ids = get_subworkspaces_id($workspaceId);
        // $ids = self::loadSubWorkspacesIds($workspaceId);

        $count = User::query()
            ->whereIn('subworkspace_id', $ids)
            ->count();

        return $count ?? 0;
    }

    /**
     * Generate a query to get user's workspaces
     */
    public static function generateUserWorkspacesQuery(int $userId, $only_active = true): Builder
    {
        // When user has SuperUser role, return al workspaces

        $isSuperUser = AssignedRole::hasRole($userId, Role::SUPER_USER);
        if ($isSuperUser) {

            // Return all workspaces, excluding subworkspaces

            return Workspace::query()
                ->where('parent_id', null)
                ->when($only_active, function($q) {
                    $q->where('workspaces.active', ACTIVE);
                })
                ->where('workspaces.deleted_at', null)
                ->select('workspaces.*');
        }


        // Get user's assigned roles

        $assignedRoles = AssignedRole::getUserAssignedRoles($userId);
        // $allowedRoles = [
        //     Role::CONFIG,
        //     Role::ADMIN,
        //     Role::CONTENT_MANAGER,
        //     Role::TRAINER,
        //     Role::REPORTS
        // ];

        // Get list of workspaces the user is allowed to
        // access to, according to its role

        $workspacesIds = AssignedRole::query()
            ->join('users', 'users.id', '=', 'assigned_roles.entity_id')
            ->where('assigned_roles.entity_type', AssignedRole::USER_ENTITY)
            // ->whereIn('assigned_roles.role_id', $allowedRoles)
            ->where('users.id', $userId)
            ->select('assigned_roles.*')
            ->pluck('scope');

        return Workspace::query()
            ->whereIn('id', $workspacesIds)
            ->when($only_active, function($q) {
                $q->where('workspaces.active', ACTIVE);
            })
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

        if (session('workspace') || $request->workspace_id)
            $query->where('parent_id', $request->workspace_id ?? session('workspace')->id);

        $query->whereIn('id', current_subworkspaces_id());

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

                // Add subworkspace to current admin
                if ( ! auth()->user()->isA('super-user') ) {
                    auth()->user()->subworkspaces()->attach($subworkspace);
                }
            }

            if (!empty($data['app_menu'])) :
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

    protected function getFullAppMenu($type, $codes, $user)
    {
        $values = Taxonomy::getDataByGroupAndType('system', $type);

        $data = [];
        $assigned = [];

        foreach ($values as $value) {

            $available = in_array($value->code, $codes);

            if ($type == 'side_menu' && in_array($value->code, ['cursoslibres', 'cursosextra']) && $available) {

                $assigned = empty($assigned) ? $user->checkCoursesTypeAssigned() : $assigned;

                $data[$value->code] =  $assigned[$value->code];

            } else {

                $data[$value->code] = $available;
            }
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
    protected function infoLimitCurrentWorkspace(){
        $current_workspace = get_current_workspace();
        $active_users_count = User::onlyClientUsers()->whereRelation('subworkspace', 'parent_id', $current_workspace->id)
            ->where('active', 1)->count();
        $limit_allowed_users = $current_workspace->getLimitAllowedUsers();
        return compact('active_users_count','limit_allowed_users');
    }
    public function sendEmailByLimit($sub_workspace_id = null){
        $workspace = $this;

        $workspace_constraint = $workspace->getSettingsLimitAllowedUser();

        if (!$workspace_constraint) return false;

        $workspace_limit = $workspace->getLimitAllowedUsers();

        $q_current_active_users = User::onlyClientUsers()
            ->where('active', ACTIVE);

        if ($workspace_constraint['type'] === 'by_workspace')
            $q_current_active_users->whereRelation('subworkspace', 'parent_id', $workspace->id);

        $current_active_users_count = $q_current_active_users->count();
        $percent = floor($current_active_users_count/$workspace_limit * pow(10, 2)) / pow(10, 2);
        if( $percent > 0.97){
            $type_id = Taxonomy::where('group','email')->where('type','user')->where('code','limite_workspace')->first()?->id;
            $emails_user = EmailUser::with('user:id,email_gestor')->where('workspace_id',$workspace->id)->where('type_id',$type_id)
                                    ->where('last_percent_sent','<>',$percent)
                                    ->wherehas('user',function($q){
                                            $q->where('active',ACTIVE)->whereNotNull('email_gestor');
                                    })->get();
            if(count($emails_user) > 0){
                $emais_to_send_user = $emails_user->map( fn ($e)=> $e->user->email_gestor);
                $mail_data=[
                    'subject'=>'Limite de usuarios',
                    'workspace_name' => $workspace->name,
                    'current_active_users_count'=> $current_active_users_count,
                    'workspace_limit'=>$workspace_limit
                ];
                Mail::to($emais_to_send_user)
                ->send(new EmailTemplate('emails.email_limite_usuarios', $mail_data));
                EmailUser::whereIn('user_id',$emails_user->pluck('user_id'))->where('workspace_id',$workspace->id)->where('type_id',$type_id)->update([
                    'last_percent_sent' => $percent
                ]);
            }
        }
        return $current_active_users_count > $workspace_limit;
    }
    public function verifyLimitAllowedUsers($sub_workspace_id = null): bool
    {
        $workspace = $this;
        $platform = session('platform');
        if($platform && $platform == 'induccion'){
            return true;
        }
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
        // $workspace = get_current_workspace();
        $modules_ids = current_subworkspaces_id();

        return Workspace::select($attributes)
            ->where('active', ACTIVE)
            ->whereIn('id', $modules_ids)
            // ->where('parent_id', $workspace->id)
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

    public function replicateWithRelations($data)
    {
        $relationships = [
            'subworkspaces' => [
                'app_menu', // OK
                'main_menu', // OK
                'side_menu', // OK
                'schools.courses' => [
                    'topics' => [
                        'questions',
                        'medias',
                        'requirements',
                    ],
                    'polls',
                    'requirements'
                ],
            ],

            // 'users',

            'functionalities', // OK

            'criterionWorkspace', // criterion_workspace OK
            'criteriaValue', // criterion_value_workspace OK

            // 'videotecas' => [
            //     'modules',
            //     'tags',
            // ],

            'medias', // OK
            'polls.questions', //
        ];
        
        
        $workspace = $this->replicate();
        $workspace->push();

        $workspace->update($data);

        $workspace->push();
       
        $this->load($relationships);

        $_crit_module = Criterion::where('code', 'module')->first();
        info('duplicate polls');
        foreach ($this->polls as $_poll) {

            $poll = $workspace->polls()->create($_poll->toArray());

            $poll->questions()->createMany($_poll->questions->toArray());
        }

        $workspace->refresh();
        info('duplicate subworkspaces');

        foreach ($this->subworkspaces as $_subworkspace) {

            $_subworkspace_data = $_subworkspace->toArray();


            $criterion_value = $_crit_module->values()->create([
                'value_text' => $_subworkspace->name,
                'active' => ACTIVE
            ]);

            $_subworkspace_data['criterion_value_id'] = $criterion_value->id;

            $workspace->criteriaValue()->syncWithoutDetaching($criterion_value);
            // $_subworkspace_data['dc3_configuration'] = json_encode($_subworkspace_data['dc3_configuration']);
            $subworkspace = $workspace->subworkspaces()->create($_subworkspace_data);
            $subworkspace->app_menu()->syncWithoutDetaching($_subworkspace->app_menu);
            $subworkspace->main_menu()->syncWithoutDetaching($_subworkspace->main_menu);
            $subworkspace->side_menu()->syncWithoutDetaching($_subworkspace->side_menu);
            foreach ($_subworkspace->schools as $_school) {
                $current_subworkspaces = $workspace->subworkspaces()->get();
                $school = School::whereRelationIn('subworkspaces', 'id', $current_subworkspaces->pluck('id'))
                            ->where('external_id', $_school->id)
                            ->first();

                $school_position = ['position' => $_school->pivot?->position];

                if ( ! $school ) {

                    $school_data = $_school->toArray();
                    $school_data['external_id'] = $_school->id;

                    $school = $subworkspace->schools()->create($school_data, $school_position);
                } else {

                    $subworkspace->schools()->syncWithoutDetaching([$school->id => $school_position]);
                }

                foreach ($_school->courses as $_course) {

                    $course = Course::whereRelation('workspaces', 'id', $workspace->id)
                                ->where('external_id', $_course->id)
                                ->first();

                    if ( ! $course ) {

                        $course_data = $_course->toArray();
                        $course_data['external_id'] = $_course->id;
                        $course_data['dc3_configuration'] = json_encode($course_data['dc3_configuration'] ?? []);
                        $course_data['registro_capacitacion'] = json_encode($course_data['registro_capacitacion'] ?? []);
                        $course = $school->courses()->create($course_data);
                        foreach ($_course->topics as $_topic) {

                            $topic_data = $_topic->toArray();
                            $topic_data['external_id'] = $_topic->id;

                            $topic = $course->topics()->create($topic_data);

                            $topic->medias()->createMany($_topic->medias->toArray());
                            $topic->questions()->createMany($_topic->questions->toArray());

                            $_requirement = $_topic->requirements->first();

                            if ($_requirement) {

                                $requirement = $course->topics()->where('external_id', $_requirement->requirement_id)->first();

                                if ($requirement) {

                                    Requirement::updateOrCreate(
                                        ['model_type' => Topic::class, 'model_id' => $topic->id],
                                        ['requirement_type' => Topic::class, 'requirement_id' => $requirement->id]
                                    );
                                }
                            }
                        }

                        $workspace->courses()->attach($course);

                        $_c_requirement = $_course->requirements->first();

                        if ($_c_requirement) {

                            $c_requirement = $workspace->courses()->where('external_id', $_c_requirement->requirement_id)->first();

                            if ($c_requirement) {

                                Requirement::updateOrCreate(
                                    ['model_type' => Course::class, 'model_id' => $course->id],
                                    ['requirement_type' => Course::class, 'requirement_id' => $c_requirement->id]
                                );
                            }
                        }

                    } else {

                        $school->courses()->syncWithoutDetaching($course);
                    }
                }
            }
        }

        info('duplicate criterionWorkspace');

        $workspace->functionalities()->sync($this->functionalities);

        $workspace->criterionWorkspace()->sync($this->criterionWorkspace);
        Db::table('criterion_workspace')->where('workspace_id',$workspace->id)->where('criterion_id',$_crit_module->id)->update([
            'available_in_profile'=>1,
            'available_in_ranking'=>1,
            'available_in_reports'=>1,
            'available_in_segmentation'=>1,
            'available_in_user_creation'=>1,
            'available_in_user_filters'=>1,
            'required_in_user_creation'=>1,
            'avaiable_in_personal_data_guest_form'=>1,
        ]);
        $workspace->criteriaValue()->createMany($this->criteriaValue->where('criterion_id', '<>', $_crit_module->id)->toArray());

        $workspace->medias()->createMany($this->medias->toArray());

        $modules_id = $this->subworkspaces->pluck('criterion_value_id')->toArray();
        
        //Duplicate Announcement
        $_announcements = Announcement::whereRelationIn('criterionValues', 'criterion_value_id', $modules_id)->get();

        $modules_ids = $workspace->subworkspaces()->pluck('criterion_value_id')->toArray();
        $subworkspace_ids = $workspace->subworkspaces()->pluck('id')->toArray();
        info('duplicate _announcements');
        foreach ($_announcements as $_announcement) {

            $announcement = Announcement::create($_announcement->toArray());

            $announcement->criterionValues()->sync($modules_ids);
        }
        //Duplicate Videoteca
        info('duplicate videotecas');
        foreach ($this->videotecas as $_videoteca) {

            $_videoteca_data = $_videoteca->toArray();

            $_videoteca_data['media_id'] = NULL;
            $_videoteca_data['preview_id'] = NULL;
            $_videoteca_data['category_id'] = NULL;
            $_videoteca_data['external_id'] = $_videoteca_data['id'];

            $videoteca = $workspace->videotecas()->create($_videoteca_data);

            $videoteca->modules()->sync($subworkspace_ids);
            // $videoteca->tags()->sync($_videoteca->tags);
        }
        info('duplicate duplicateChecklistFromWorkspace');
        //Duplicate FUNCTIONALITIES
        $duplicate_functionalities = json_decode($data['duplicate']);
        if(isset($duplicate_functionalities->checklist) && $duplicate_functionalities->checklist){
            CheckList::duplicateChecklistFromWorkspace($this,$workspace);
        }
        info('duplicate duplicateBenefitsFromWorkspace');
        if(isset($duplicate_functionalities->benefits) && $duplicate_functionalities->benefits){
            Benefit::duplicateBenefitsFromWorkspace($this,$workspace);
        }
        info('duplicate duplicateCertificatesFromWorkspace');
        if(isset($duplicate_functionalities->certificates) && $duplicate_functionalities->certificates){
            Certificate::duplicateCertificatesFromWorkspace($this,$workspace);
        }
        info('duplicate duplicateVademecumsFromWorkspace');
        if(isset($duplicate_functionalities->protocols_and_documents) && $duplicate_functionalities->protocols_and_documents){
            $current_modules_ids = $this->subworkspaces->pluck('criterion_value_id')->toArray();
            Vademecum::duplicateVademecumsFromWorkspace($current_modules_ids,$workspace,$modules_ids);
        }
        return $workspace;
    }

    protected function getLimitAIConvert($topic,$type='media_convert'){
        $workspace = get_current_workspace();
        $limits = self::where('id',$workspace->id)->select('limits')->first()?->limits;
        $data = [];
        switch ($type) {
            case 'media_convert':
                $media_ia_converted = ($topic) ? MediaTema::where('ia_convert',1)->where('topic_id',$topic->id)->count() : 0;
                $limit_allowed_media_convert =  $limits['limit_allowed_media_convert'] ?? 0;
                $data = [
                    'limit_allowed_media_convert' => (int) $limit_allowed_media_convert,
                    'media_ia_converted' => $media_ia_converted ?? 0,
                ];
                break;
            case 'evaluations':
                    $limit_allowed_ia_evaluations =  $limits['limit_allowed_ia_evaluations'] ?? 0;
                    $ia_evaluations_generated =  0;
                    //Its necesary to convert all medias in text to create evaluations
                    $medias_to_convert = MediaTema::where('topic_id',$topic->id)->select('path_convert')->where('ia_convert',1)->get();
                    $count_not_null_medias_to_convert = $medias_to_convert->pluck('path_convert')->filter(function ($value) {
                        return !is_null($value);
                    })->count();
                    $isReadyToCreateAIQuestions = count($medias_to_convert) == $count_not_null_medias_to_convert;
                    $has_permission_to_use_ia_evaluation = Ability::hasAbility('course','jarvis-evaluations') && count($medias_to_convert)>0;
                    $data = [
                        'limit_allowed_ia_evaluations' => (int) $limit_allowed_ia_evaluations,
                        'ia_evaluations_generated' => JarvisAttempt::getAttempt($workspace->id),
                        'is_ready_to_create_AIQuestions'=> $isReadyToCreateAIQuestions,
                        'has_permission_to_use_ia_evaluation' => $has_permission_to_use_ia_evaluation
                    ];
                    break;
            case 'descriptions':
                $limit_descriptions_jarvis =  $limits['limit_descriptions_jarvis'] ?? 0;
                $data = [
                    'limit_descriptions_jarvis' => (int) $limit_descriptions_jarvis,
                    'ia_descriptions_generated' => JarvisAttempt::getAttempt($workspace->id,'descriptions'),
                ];
        }
        return $data;
    }
    protected function getSchoolsForTree($schools)
    {
        $data = [];

        foreach ($schools as  $school) {

            $school_children = [];
            $school_parent_key = 'school_' . $school->id;

            foreach ($school->courses as  $course) {

                $children = [];
                $course_parent_key = $school_parent_key . '-course_' . $course->id;

                foreach ($course->topics as $topic) {

                    $child_key = 'topic_' . $topic->id;

                    $children[] = [
                        'id' => $course_parent_key . '-' . $child_key,
                        'name' => '[TEMA] ' . $topic->name,
                        'icon' => 'mdi-bookmark',
                    ];
                }

                $course_parent = [
                    'id' => $course_parent_key,
                    'name' => '[CURSO] ' . $course->name,
                    'icon' => 'mdi-book',
                    'children' => $children,
                ];

                $school_children[] = $course_parent;
            }

            $parent = [
                'id' => $school_parent_key,
                'name' => '[ESCUELA] ' . $school->name,
                'icon' => 'mdi-school',
                'children' => $school_children,
            ];

            $data[] = $parent;
        }

        return $data;
    }
    public function getModalitiesCourseByWorkspace(){
        $workspace = $this;
        $modalities = Taxonomy::where('group','course')->where('type','modality')->select('id','name','code','icon','color')->get();
        return $modalities->map(function($modality)use($workspace){
            $hasFunctionality = true;
            if($modality->code == 'in-person' || $modality->code == 'virtual'){
                $modality_code = 'course-'.$modality->code;
                $hasFunctionality = $workspace->hasFunctionality($modality_code);
            }
            $modality['has_functionality'] = $hasFunctionality;
            return $modality;
        });
    }
    public function hasFunctionality($functionality_code){
        $workspace = $this;
        return boolval($workspace->functionalities()->get()->where('code',$functionality_code)->first());
    }
    protected function getAvailableForTree($_subworkspace)
    {
        $data = [];

        $workspace = get_current_workspace();

        foreach ($workspace->subworkspaces as  $subworkspace) {

            if ($subworkspace->id == $_subworkspace->id) {
                continue;
            }

            $children = [];
            $parent_key = 'subworkspace_' . $subworkspace->id;

            $parent = [
                'id' => $parent_key,
                'name' => '[MÓDULO] ' . $subworkspace->name,
                'avatar' => '',
                'icon' => 'mdi-view-grid',
                'children' => $children,
            ];

            $data[] = $parent;
        }

        return $data;
    }

    protected function setCoursesDuplication($course_ids, $_courses, $_topics, $school, $workspace, $prefix = '', $force_course_creation = false)
    {
        foreach ($course_ids as $course_id => $topic_ids) {

            $_course = $_courses->where('id', $course_id)->first();

            $course = null;

            if (!$force_course_creation) {

                $course = $school->courses()->where('name', $_course->name)->first();
            }

            if (!$course) {

                $course_data = $_course->toArray();
                $course_data['external_id'] = $_course->id;
                $course_data['name'] = $prefix . $_course->name;
                $course_data['dc3_configuration'] = json_encode($course_data['dc3_configuration'] ?? []);
                $course_data['registro_capacitacion'] = json_encode($course_data['registro_capacitacion'] ?? []);

                $course = $school->courses()->create($course_data);

                $workspace->courses()->attach($course);
            }

            foreach ($topic_ids['topics'] as $topic_id) {

                $_topic = $_topics->where('id', $topic_id)->first();

                $topic = $course->topics()->where('name', $_topic->name)->first();

                if(!$topic) {

                    $topic_data = $_topic->toArray();
                    $topic_data['external_id'] = $_topic->id;

                    $topic = $course->topics()->create($topic_data);

                    $topic->medias()->createMany($_topic->medias->toArray());
                    $topic->questions()->createMany($_topic->questions->toArray());

                    $_requirement = $_topic->requirements->first();

                    if ($_requirement) {

                        $requirement = $course->topics()->where('external_id', $_requirement->requirement_id)->first();

                        if ($requirement) {

                            Requirement::updateOrCreate(
                                ['model_type' => Topic::class, 'model_id' => $topic->id],
                                ['requirement_type' => Topic::class, 'requirement_id' => $requirement->id]
                            );
                        }
                    }
                }

            }
        }

        $_c_requirement = $_course->requirements->first();

        if ($_c_requirement) {

            $c_requirement = $workspace->courses()->where('external_id', $_c_requirement->requirement_id)->first();

            if ($c_requirement) {

                Requirement::updateOrCreate(
                    ['model_type' => Course::class, 'model_id' => $course->id],
                    ['requirement_type' => Course::class, 'requirement_id' => $c_requirement->id]
                );
            }
        }
    }

}
