<?php

namespace App\Models;

// use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\UCMigrationData\Migration_1;
use App\Notifications\UserResetPasswordNotification;
use Clockwork\DataSource\DBALDataSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Altek\Accountant\Contracts\Identifiable;
use Altek\Accountant\Contracts\Recordable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

use Silber\Bouncer\Database\Models;

use Khsing\World\Models\Country;

use App\Traits\CustomAudit;
use App\Traits\CustomCRUD;
use App\Traits\CustomMedia;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Support\Str;

use Spatie\Image\Manipulations;

use Illuminate\Support\Facades\Mail;
use App\Mail\EmailTemplate;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Lab404\Impersonate\Models\Impersonate;
use Lab404\Impersonate\Services\ImpersonateManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Bouncer;

class User extends Authenticatable implements Identifiable, Recordable, HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndAbilities, HasPushSubscriptions;

    use \Altek\Accountant\Recordable, \Altek\Eventually\Eventually;

    use CustomCRUD, CustomAudit, CustomMedia;

    use InteractsWithMedia;

    use Notifiable;

    use Cachable;

    use SoftDeletes;

    use Impersonate;

  use HybridRelations;
    use Cachable {
        Cachable::newEloquentBuilder insteadof HybridRelations;
        Cachable::getObservableEvents insteadof \Altek\Eventually\Eventually, CustomAudit;
        Cachable::newBelongsToMany insteadof \Altek\Eventually\Eventually, CustomAudit;
        Cachable::newMorphToMany insteadof \Altek\Eventually\Eventually, CustomAudit;
    }

    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'lastname', 'surname', 'username', 'fullname', 'enable_2fa','last_pass_updated_at', 'attempts', 'attempts_lock_time', 'attempts_times_locks', 'slug', 'alias', 'person_number', 'phone_number',
        'email', 'email_gestor', 'password', 'old_passwords', 'active', 'phone', 'telephone', 'birthdate',
        'type_id', 'subworkspace_id', 'job_position_id', 'area_id', 'gender_id', 'document_type_id',
        'document', 'ruc',
        'country_id', 'district_id', 'address', 'description', 'quote',
        'external_id', 'fcm_token', 'token_firebase', 'secret_key',
        'user_relations',
        'summary_user_update', 'summary_course_update', 'summary_course_data', 'required_update_at', 'last_summary_updated_at', 'is_updating'
    ];

    // protected $with = ['roles'
    // , 'abilities'
// ];
    protected $appends = ['fullname', 'age'];
    protected $dates = ['birthdate'];

    public $defaultRelationships = [
        'type_id' => 'type',
        'subworkspace_id' => 'subworkspace'
        // 'job_position_id' => 'job_position', 'area_id' =>  'area', 'gender_id' => 'gender',
        // 'document_type_id' => 'document_type', 'country_id' => 'country', 'district_id' =>  'district'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'pivot'
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'user_relations' => 'array',
        'old_passwords' => 'array',
    ];

    protected $recordableEvents = ['created', 'updated', 'restored', 'deleted', 'forceDeleted',
        'synced', 'existingPivotUpdated', 'attached', 'detached', 'impersonated'];

    public function getIdentifier()
    {
        return $this->getKey();
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'users.' . $this->id;
    }

    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
        // return $this->getDeviceTokens();
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return $notification->webhookUrl ?? config('slack.routes.support');
    }

    public function students()
    {
        return $this->belongsToMany(
            User::class,
            'trainer_user',
            'trainer_id',
            'user_id');
    }

    public function criterion_values()
    {
        return $this->belongsToMany(CriterionValue::class);
    }

    public function criterion_user()
    {
        return $this->hasMany(CriterionValueUser::class);
    }

    public function subworkspace()
    {
        return $this->belongsTo(Workspace::class, 'subworkspace_id');
    }

    public function summary()
    {
        return $this->hasOne(SummaryUser::class);
    }

    public function course_data()
    {
        return $this->hasOne(UserCourseData::class);
    }    

    public function summary_courses()
    {
        return $this->hasMany(SummaryCourse::class);
    }

    public function summary_topics()
    {
        return $this->hasMany(SummaryTopic::class);
    }

    public function failed_topics()
    {
        return $this->hasMany(SummaryTopic::class, 'user_id')
            // ->where('passed', 0)
            ->whereNotNull('attempts')
            ->whereRelationIn('status', 'code', ['desaprobado', 'por-iniciar'])
            ->where('attempts', '<>', 0);
    }

    public function relationships()
    {
        return $this->hasMany(UserRelationship::class, 'user_id');
    }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function scopeOnlyClientUsers($q)
    {
        $q
            ->whereHas('type', fn($q) => $q->whereNotIn('code', ['cursalab']));
//            ->whereNotNull('subworkspace_id');
    }

    public function scopeFilterText($q, $filter)
    {
        $q->where(function ($q) use ($filter) {
            $q->whereRaw('document like ?', ["%{$filter}%"]);
            $q->orWhereRaw('name like ?', ["%{$filter}%"]);
            $q->orWhereRaw('email like ?', ["%{$filter}%"]);
            $q->orWhereRaw('email_gestor like ?', ["%{$filter}%"]);
            $q->orWhereRaw('lastname like ?', ["%{$filter}%"]);
            $q->orWhereRaw('surname like ?', ["%{$filter}%"]);
        });
    }

    public function getFullnameAttribute()
    {
        // if($this->fullname){
        //     return $this->fullname;
        // }
        $fullname = $this->name;
        if ($this->lastname) $fullname .= ' ' . $this->lastname;

        if ($this->surname) $fullname .= ' ' . $this->surname;

        return $fullname;
    }

    public function getLastnamesAttribute()
    {
        $lastnames = $this->lastname;

        if ($this->surname) $lastnames .= ' ' . $this->surname;

        return $lastnames;
    }

    public function getAgeAttribute()
    {
        if (isset($this->attributes['birthdate']))
            return Carbon::parse($this->attributes['birthdate'])->age;

        return null;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value))
            $this->attributes['password'] = bcrypt($value);
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1') ? 1 : 0;
    }

    public function getCriteria()
    {
        $criterion_ids = $this->criterion_values()->get()->pluck('criterion_id')->toArray();

        return Criterion::whereIn('id', $criterion_ids)->get();
    }

    public function getCriterionValue($criterion_code)
    {
        $criterion_ids = $this->criterion_values()->get()->pluck('criterion_id')->toArray();

        return Criterion::whereIn('id', $criterion_ids)->get();
    }

    public function getCriterionValueCode($criterion_code)
    {
        $criterion = Criterion::where('code', $criterion_code)->first();
        return $this->criterion_values()->where('criterion_id', $criterion->id)->first();
    }

    // public function post()
    // {
    //     return $this->hasMany(Post::class);
    // }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function getAllPermissionsAttribute()
    {
        $permissions = [];
        // foreach (Permission::all() as $permission) {
        //     if (\Auth::user()->can($permission->slug)) {
        //         $permissions[] = $permission->slug;
        //     }
        // }
        return $permissions;
    }

    // /**
    //  * Route notifications for the Slack channel.
    //  *
    //  * @param \Illuminate\Notifications\Notification $notification
    //  * @return string
    //  */
    // public function routeNotificationForSlack($notification)
    // {
    //     return config('slack.routes.support');
    // }

    public function customHasRole($role)
    {
        // return $this->roles()->where('slug', $role)->first();
    }

    public function isMasterOrAdminCursalab()
    {
        // return $this->customHasRole('master') || $this->customHasRole('gestor-lamedia');
        return true;
    }

    public function isSupervisor(): bool
    {
        $userWithSupervisorSegment = User::loadSupervisorWithSegment($this->id);
        return $userWithSupervisorSegment['segment'] !== null;
    }

    public function getActiveCycle($soft = true)
    {
        $user = $this;
        if ($soft) {
            
            $user_cycles = $user->criterion_values
            ->where('criterion.code', 'cycle')
            ->sortBy('position');

        } else {

            $user_cycles = $user->criterion_values()
            ->whereRelation('criterion', 'code', 'cycle')
            ->orderBy('position')
            ->get();
        }

        //        info("CICLOS DEL USUARIO {$user->fullname}");
        //        info($user_cycles->pluck('value_text'));
        //        info($user->criterion_values()->pluck('value_text'));

        return $user_cycles->last();
    }

    /**
     * Load supervisor user and its segment
     * @param $userId
     * @return array
     */
    public static function loadSupervisorWithSegment($userId): array
    {
        $supervisorTaxonomy = Taxonomy::where('type', 'code')
            ->where('group', 'segment')
            ->where('code', 'user-supervise')
            ->where('active', 1)
            ->first();

        $supervisorSegment = Segment::where('model_type', 'App\Models\User')
            ->where('model_id', $userId)
            ->where('code_id', $supervisorTaxonomy->id)
            ->where('active', 1)
            ->first();

        return [
            'user' => User::find($userId),
            'segment' => $supervisorSegment
        ];
    }

    public function getTrainingRole()
    {
        $user = $this;

        $is_student = EntrenadorUsuario::alumno($user->id)->first();
        $is_trainer = EntrenadorUsuario::entrenador($user->id)->first();
        if (!is_null($is_student)) {
            return 'student';
        } else if (!is_null($is_trainer)) {
            return 'trainer';
        } else {
            return null;
        }
    }

    public function updateStatusUser($active = null, $termination_date = null)
    {
        $user = $this;
        $user->active = $active;
        if ($active) {
            $data['summary_user_update'] = true;
        }
        $user->save();
        $criterion = Criterion::with('field_type:id,code')->where('code', 'termination_date')->select('id', 'field_id')->first();
        if (!$criterion) {
            return true;
        }
        $user_criterion_termination_date = $user->criterion_values()->where('criterion_id', $criterion->id)->first();
        if($user_criterion_termination_date){
            $user->criterion_values()->detach($user_criterion_termination_date->id);
        }
        if ($termination_date && !$user->active) {
            $criterion_value = CriterionValue::where('criterion_id', $criterion->id)->where('value_text', trim($termination_date))->select('id', 'value_text')->first();
            $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);
            if (!$criterion_value) {
                $data_criterion_value[$colum_name] = $termination_date;
                $data_criterion_value['value_text'] = $termination_date;
                $data_criterion_value['criterion_id'] = $criterion->id;
                $data_criterion_value['active'] = 1;
                $data_criterion_value['workspace_id'] = $user->subworkspace?->parent?->id;
                $criterion_value = CriterionValue::storeRequest($data_criterion_value);
            }
            $user->criterion_values()->syncWithoutDetaching([$criterion_value->id]);
        }
    }

    protected function setPasswordData(&$data, $update_password, $user = null)
    {
        if ($update_password && isset($data['password'])) {
            $data['last_pass_updated_at'] = now();
            $data['attempts'] = 0;
            $data['attempts_lock_time'] = NULL;
        }

        if ($update_password && $user && isset($data['password'])) {

            $old_passwords = $user->old_passwords;

            $old_passwords[] = ['password' => bcrypt($data['password']), 'added_at' => now()];

            if (count($old_passwords) > 4) {
                array_shift($old_passwords);
            }

            $data['old_passwords'] = $old_passwords;
        } 
    }

    protected function storeRequest($data, $user = null, $update_password = true, $from_massive = false)
    {
        try {

            DB::beginTransaction();
            $old_document = $user ? $user->document : null;
            if ($user) :
                if ($from_massive) {
                    $data['summary_user_update'] = true;
                }
                if (!$update_password && isset($data['password'])) {
                    unset($data['password']);
                }

                $this->setPasswordData($data, $update_password, $user);
  
                $user->update($data);

                if ($user->wasChanged('document') && ($data['document'] ?? false)):
                    $user_document = $this->syncDocumentCriterionValue(old_document: $old_document, new_document: $data['document']);
                else:
                    $user_document = CriterionValue::whereRelation('criterion', 'code', 'document')
                        ->where('value_text', $old_document)->first();
                    if (!$user_document) {
                        $user_document = $this->syncDocumentCriterionValue(old_document: null, new_document: $old_document);
                    }
                endif;
            else :
       
                $this->setPasswordData($data, $update_password, $user);

                $data['type_id'] = $data['type_id'] ?? Taxonomy::getFirstData('user', 'type', 'employee')->id;

                $user = self::create($data);
                $user_document = $this->syncDocumentCriterionValue(old_document: null, new_document: $data['document']);
            endif;

            $user->subworkspace_id = Workspace::query()
                ->where('criterion_value_id', $data['criterion_list']['module'])
                ->first()
                ?->id;

            $criterion_list_final = [];

            foreach($data['criterion_list'] as $key => $val) {
                if(!is_null($val) && !is_numeric($val) && !is_array($val)) {
                    $id_criterio = Criterion::where('code', $key)->first();
                    $id_crit_val = CriterionValue::where('value_text', $val)->where('criterion_id', $id_criterio?->id)->select('id')->first();
                    if ($id_crit_val){
                        $data['criterion_list'][$key] = $id_crit_val->id;
                    } else {
                        $current_workspace_id = get_current_workspace();
                        $data_cr['workspace_id'] = $current_workspace_id?->id;

                        $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($id_criterio);
                        $data_cr[$colum_name] = $val;
                        $data_cr['value_text'] = $val;
                        $data_cr['criterion_id'] = $id_criterio?->id;
                        $data_cr['active'] = 1;

                        CriterionValue::storeRequest($data_cr);
                        $id_crit_vala = CriterionValue::where('value_text', $val)->where('criterion_id', $id_criterio?->id)->select('id')->first();
                        $data['criterion_list'][$key] = $id_crit_vala->id;
                    }
                }
            }

            $criterion_list_final_date = [];

            foreach($data['criterion_list_final'] as $crr) {
                if(is_numeric($crr) || is_array($crr)) {
                    array_push($criterion_list_final, $crr);
                }
            }

            foreach($data['criterion_list'] as $fcr) {
                if(!is_null($fcr) && !is_array($fcr)) {
                    array_push($criterion_list_final_date, $fcr);
                }
            }

            foreach (array_diff($criterion_list_final_date, $data['criterion_list_final']) as $key => $value) {
                array_push($criterion_list_final, $value);
            }

            $data['criterion_list_final'] = $criterion_list_final;

            $user->criterion_values()
                ->sync(array_values($data['criterion_list_final']) ?? []);

            $data['criterion_list_final'][] = $user_document->id;

            $user->criterion_values()
                ->sync($data['criterion_list_final']);

            $user->save();

            if ($user && !$from_massive) {
                SummaryUser::updateUserData($user, false);
            }

            DB::commit();
        } catch (\Exception $e) {

            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }

    public function syncDocumentCriterionValue($old_document, $new_document)
    {
        $document_criterion = Criterion::where('code', 'document')->first();

        $document_value = CriterionValue::whereRelation('criterion', 'code', 'document')
            ->where('value_text', $old_document)->first();

        $criterion_value_data = [
            'value_text' => $new_document,
            'criterion_id' => $document_criterion?->id,
            'active' => ACTIVE
        ];

        $document_value = CriterionValue::storeRequest($criterion_value_data, $document_value);

        return $document_value;
    }

    protected function storeRequestFull($data = [], $user = null)
    {
        try {

            DB::beginTransaction();

            if ($user) :

                $user->update($data);

            else :

                $user = $this->create($data);

            endif;

            if ($user->isNotA('superadmin') and $user->isNotA('developer'))
                Bouncer::sync($user)->roles($data['roles'] ?? []);

            // $user->accounts()->sync($data['accounts'] ?? []);

            $user->setMediaCollection($data, 'avatar');

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            return BaseModel::errorResponse(['exception' => $e]);
        }

        return BaseModel::successResponse();
    }

    protected function search($request, $withAdvancedFilters = false)
    {
        $query = self::onlyClientUsers();
        if (auth()->user()->isA('super-user')) {
            $query = self::query();
        }

        $with = ['subworkspace'];

        if (get_current_workspace()->id == 25) {

            $with = ['subworkspace', 'criterion_values' => function ($q) {
                $q->whereIn('criterion_id', [40, 41]);
            }];
        }

        $query->with($with)->withCount('failed_topics');

        if ($request->q)
            $query->filterText($request->q);

        if ($request->active == 1)
            $query->where('active', ACTIVE);

        if ($request->active == 2)
            $query->where('active', '<>', ACTIVE);

        if ($request->workspace_id)
            $query->whereRelation('subworkspace', 'parent_id', $request->workspace_id);

        if ($request->subworkspace_id)
            $query->where('subworkspace_id', $request->subworkspace_id);

        if ($request->sub_workspaces_id)
            $query->whereIn('subworkspace_id', $request->sub_workspaces_id);

        if ($withAdvancedFilters):
            $workspace = get_current_workspace();

            $criteria_template = Criterion::select('id', 'name', 'field_id', 'code', 'multiple')
                ->with('field_type:id,name,code')
                ->whereRelation('workspaces', 'id', $workspace->id)
                ->where('is_default', INACTIVE)
                ->orderBy('name')
                ->get();

            foreach ($criteria_template as $i => $criterion) {
                $idx = $i;

                if ($request->has($criterion->code)) {
                    $code = $criterion->code;
                    $request_data = $request->$code;

                    $query->join("criterion_value_user as cvu{$idx}", function ($join) use ($request_data, $idx) {

                        $request_data = is_array($request_data) ? $request_data : [$request_data];

                        $join->on('users.id', '=', "cvu{$idx}" . '.user_id')
                            ->whereIn("cvu{$idx}" . '.criterion_value_id', $request_data);
                    });

                }
            }
        endif;

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->descending == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->paginate);
        // return $query->paginate($request->rowsPerPage);
    }

    public function getCurrentCourses(
        $with_programs = true,
        $with_direct_segmentation = true,
        $withFreeCourses = true,
        $withRelations = 'default',
        $only_ids = false,
        $response_type = 'courses-separated',
        $byCoursesId = [],
        $bySchoolsId = [],
    )
    {
        $user = $this;
        
        if ($user->hasDataUpToDate()) {

            $all_courses = $user->getCoursesDirectly();
        } else {

            $user->load(['criterion_values:id,value_text,criterion_id','criterion_values.criterion:id,code']);

            $all_courses = [];

            if ($with_direct_segmentation) {
                $this->setCoursesWithDirectSegmentation($user, $all_courses, $withFreeCourses, $response_type);
            }

            $user->course_data()->updateOrCreate(['user_id' => $user->id], [
                'courses' => $all_courses['current_courses_ids'] ?? [],
                'schools' => $all_courses['current_schools_ids'] ?? [],
                'compatibles' => $all_courses['compatibles_ids'] ?? [],
                'course_id_tags' => $all_courses['course_id_tags'] ?? [],
                'current_courses_updated_at' => now(),
            ]);
        }

        $current_courses = $all_courses['current_courses'] ?? [];
        $compatibles_courses = $all_courses['compatibles'] ?? [];
        $course_id_tags = collect($all_courses['course_id_tags']) ?? collect();
        
        if ($response_type === 'courses-unified')
            return $all_courses;
        
        $query = $this->getUserCourseSegmentationQuery($withRelations);
        
        if(count($bySchoolsId)>0){
            $byCoursesId = CourseSchool::whereIn('school_id',$bySchoolsId)->select('course_id')->pluck('course_id');
        }

        if(count($byCoursesId)>0){
            $query->whereIn('id', $byCoursesId);
        }

        $courses = $query->whereIn('id', array_column($current_courses, 'id'))->get();

        if ($only_ids)
            return array_unique(array_column($current_courses, 'id'));
        $isUserUcfp = $user->subworkspace->parent_id === 25;
        foreach ($courses as $course) {
            $compatible_course = $compatibles_courses[$course->id] ?? false;
            $course->tags = ($isUserUcfp) ? $course_id_tags->where('course_id',$course->id)->first()['tags'] : [];
            if ($compatible_course) {
                $course->compatible = $compatible_course;
            }
        }
        
        return $courses;
    }

    private function getUserCourseSegmentationQuery($withRelations)
    {
        // $relations = config("courses.user-courses-query.$withRelations");
        $user_id = auth()->user() ? auth()->user()->id : $this->id;
        $relations = config("courses.user-courses-query")($withRelations, $user_id);
        return Course::with($relations);
    }

    public function setCoursesWithDirectSegmentation($user, &$all_courses, $withFreeCourses, $response_type)
    {
        $user->loadMissing('subworkspace.parent');

        $workspace = $user->subworkspace->parent;

        $query = $this->getUserCourseSegmentationQuery('soft');

        $course_segmentations = $query->whereRelation('schools', 'active', ACTIVE)
            ->whereRelation('segments', 'active', ACTIVE)
            ->whereRelation('topics', 'active', ACTIVE)
            ->whereRelation('workspaces', 'id', $workspace->id)
            ->when(!$withFreeCourses, function ($q) {
                $q->whereRelation('type', 'code', '<>', 'free');
            })
            ->where('active', ACTIVE)
            ->get();

        $user_criteria = $user->criterion_values()->with('criterion.field_type')->get()->groupBy('criterion_id');
//        $user->active_cycle = $user->getActiveCycle();

//        $UC_rules_data = [
//            'criterion_cycle' => Criterion::where('code', 'cycle')->first(),
//            'cycle_0_value' => CriterionValue::whereRelation('criterion', 'code', 'cycle')
//                ->where('value_text', 'Ciclo 0')->first()
//        ];

        $summary_courses_compatibles = SummaryCourse::with('course:id,name')
            ->whereRelation('course', 'active', ACTIVE)
            ->where('user_id', $user->id)
            // ->whereIn('course_id', $course->compatibilities->pluck('id')->toArray())
            ->orderBy('grade_average', 'DESC')
            ->whereRelation('status', 'code', 'aprobado')
            ->get();
        $cycles = null;
        if($user->subworkspace->parent_id === 25){
            $cycles = CriterionValue::whereRelation('criterion', 'code', 'cycle')
            ->where('value_text', '<>', 'Ciclo 0')
            ->orderBy('position')->get();
        }
        foreach ($course_segmentations as $course) {

            // $segment_ids = $course->segments->pluck('id');
            // $segment_values = SegmentValue::whereIn('segment_id', $segment_ids)->get();
            
            foreach ($course->segments as $segment) {

            // $valid_rule = $this->validateUCCyclesRule($segment, $user, $UC_rules_data);
            // if (!$valid_rule) continue;

                $course_segment_criteria = $segment->values->groupBy('criterion_id');
                // $course_segment_criteria = $segment_values->where('segment_id', $segment->id)->groupBy('criterion_id');

                // info($course_segment_criteria);

                $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $course_segment_criteria);

                if ($valid_segment) :
                    $tags = [];
                    if($user->subworkspace->parent_id === 25){
                        $tags = $course->getCourseTagsToUCByUser($course, $user,$segment,$cycles);
                    }
                    // COMPATIBLE VALIDATION

                    if ($response_type === 'courses-separated') {

                        if ($user->subworkspace->parent_id === 25):

                            $compatible = $course->getCourseCompatibilityByUser($user, $summary_courses_compatibles);

                            if ($compatible):

                                $all_courses['compatibles'][$course->id] = $compatible;
                                $all_courses['compatibles_ids'][$course->id] = [ 
                                    'summary_course_id' => $compatible->id,
                                    'course_id' => $compatible->course_id,
                                ];

                            endif;

                        endif;

                        $all_courses['current_courses'][] = $course;
                        $all_courses['current_courses_ids'][] = $course->id;
                        $all_courses['course_id_tags'][] = [
                            'course_id'=>$course->id,
                            'tags' => $tags
                        ];

                        foreach ($course->schools as $key => $school) {
                            $all_courses['current_schools_ids'][$school->id][] = $course->id;
                        }

                        break;
                    }

                    if ($response_type === 'courses-unified') {

                        $compatible = null;

                        if ($user->subworkspace->parent_id === 25):

                            $compatible = $course->getCourseCompatibilityByUser($user, $summary_courses_compatibles);

                        endif;

                        $temp = $compatible ? $compatible->course : $course;

                        $all_courses[] = $temp;

                        break;
                    }

                endif;
            }
        }

        unset($user->active_cycle);
    }

    public function validateUCCyclesRule(Segment $segment, $user, $UC_rules_data): bool
    {
        if ($user->subworkspace->parent_id != 25) return true;

        $cycle_criterion = $UC_rules_data['criterion_cycle'];
        $cycle_0 = $UC_rules_data['cycle_0_value'];

        $has_criterion_cycle = $segment->values->where('criterion_id', $cycle_criterion->id)->count() > 0;

        if (!$has_criterion_cycle) return true;

        $user_active_cycle = $user->active_cycle;
        if (!$user_active_cycle) return false;

        $user_active_cycle_Ciclo_0 = $user_active_cycle->value_text === 'Ciclo 0';
        $segment_has_Ciclo_0 = $segment->values->where('criterion_value_id', $cycle_0->id);

        if (
            (!$user_active_cycle_Ciclo_0 && $segment_has_Ciclo_0->count() === 1)
            || ($user_active_cycle_Ciclo_0 && !($segment_has_Ciclo_0->count() > 0))
        )
            return false;

        return true;
    }

    public function updateLastUserLogin()
    {
        $user = $this;
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
    }

    public function updateUserDeviceVersion($request): void
    {
        $user = $this;
        if (!is_null($request['os']) && !is_null($request['version'])) {
            switch ($request['os']) {
                case 'android':
                    $user->android++;
                    $user->v_android = $request['version'];
                    break;
                case 'ios':
                    $user->ios++;
                    $user->v_ios = $request['version'];
                    break;
                case 'huawei':
                    $user->huawei++;
                    $user->v_android = $request['version'];
                    break;
                default:
                    $user->browser++;
                    break;
            }
            $user->save();
        }
    }

    public function getSubworkspaceSetting($field, $value = null)
    {
        // $user = $user ?? auth()->user();

        $settings = $this->subworkspace->$field;

        if (!$settings) return NULL;

        if ($value) return $settings[$value] ?? NULL;

        return $settings;
    }

    protected function calculate_rank($count_approved_courses, $grade_average, $attempts): float|int
    {
        if ($grade_average == 0) return 0;

        $puntos_cursos = $count_approved_courses * 150;
        $puntos_promedio = $grade_average * 100;
        $puntos_intentos = $attempts * 0.5;

        return $puntos_promedio + $puntos_cursos - $puntos_intentos;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }

    public function sendPasswordRecoveryNotification($user, $token)
    {

        $url = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email
                    ], false));

        $url_recovery = preg_replace("/.*\/password\/reset\/(.*)/", '/cambiar-contrasenia/$1', $url.'&recovery=true');
        $url_recovery = rtrim(config('auth.email.base_url_reset'), '/') . '/' . ltrim($url_recovery, '/');

        $url_coordinador = rtrim(config('auth.email.base_url_reset'), '/') . '/' . ltrim('/ayuda-coordinador', '/');

        $mail_data = [ 'subject' => 'Link de verificación',
                       'user' => $user->name.' '.$user->lastname,
                       'time' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
                       'link_recovery' => $url_recovery,
                       'link_coordinador' => $url_coordinador ];

        Mail::to($user->email)->send(new EmailTemplate('emails.reestablecer_pass', $mail_data));

    }

    public static function countActiveUsersInWorkspace($workspaceId)
    {

        return self::join('workspaces', 'users.subworkspace_id', '=', 'workspaces.id')
            ->where('workspaces.parent_id', $workspaceId)
            ->where('users.active', 1)
            ->count();
    }

    public function belongsToSegmentation($model)
    {
        if (!$model) return false;

        $model->load('segments.values');

        $user_criteria = $this->criterion_values()->with('criterion.field_type')->get()->groupBy('criterion_id');

        $valid_segment = false;

        foreach ($model->segments as $key => $segment) {

            $model_segment_criteria = $segment->values->groupBy('criterion_id');

            $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $model_segment_criteria);

            if ($valid_segment) break;
        }

        return $valid_segment;
    }

    public function load_ranking_data($criterion_code = null)
    {
        $user = $this;

        $summary_user = SummaryUser::getCurrentRow($user);

        if (!$summary_user) return null;

        $query = SummaryUser::query()
            ->whereRelation('user', 'subworkspace_id', $user->subworkspace_id);

        if ($criterion_code):
            $user_criterion_value = $user->criterion_values()
                ->whereRelation('criterion', 'code', $criterion_code)
                ->first();

            $query->whereHas(
                'user.criterion_values',
                fn($q) => $q
                    ->where('id', $user_criterion_value->id)
            );
        endif;

        $ranks_before_user = $query->whereRelation('user', 'active', ACTIVE)
            ->whereNotNull('last_time_evaluated_at')
            ->where('score', '>=', $summary_user->score ?? 0)
            ->orderBy('score', 'desc')
            ->orderBy('last_time_evaluated_at')
            ->get();

        $row = $ranks_before_user->where('user_id', $user->id)->first();

        return [
            'position' => $ranks_before_user->count(),
            'last_time_evaluated_at' => $row?->last_time_evaluated_at,
            'score' => $row?->score
        ];
    }

    // === 2FA ===
    public function generateCode2FA()
    {
        $user = $this;
        $currentRange = env('AUTH2FA_CODE_DIGITS');
        $currentMinutes = env('AUTH2FA_EXPIRE_TIME');

        $start = '1'.str_repeat('0', $currentRange - 1);
        $end = str_repeat('9', $currentRange);
        $currentCode = rand($start, $end);

        $user->timestamps = false; // no actualizar el usuario
        $user->code = $currentCode;
        $user->expires_code = now()->addMinutes($currentMinutes);

        //enviar codigo al email
        $mail_data = [ 'subject' => 'Código de verificación',
                       'code' => $currentCode,
                       'minutes' => $currentMinutes,
                       'user' => $user->name.' '.$user->lastname ];

        // enviar email
        Mail::to($user->email_gestor)
            ->send(new EmailTemplate('emails.enviar_codigo_2fa', $mail_data));

        return $user->save();
    }

    public function resetToNullCode2FA()
    {
        $user = $this;

        $user->timestamps = false; // no actualizar el usuario
        $user->code = NULL;
        $user->expires_code = NULL;

        $user->save();
    }
    // === 2FA ===

    // === RESET PASSWORD ===
    public function resetToNullResetPass()
    {
        $user = $this;

        $user->timestamps = false; // no actualizar el usuario
        $user->pass_token_updated = NULL;

        $user->save();
    }

    public function setUserPassUpdateToken($token)
    {
        $user = $this;

        $user->timestamps = false; // no actualizar el usuario
        $user->pass_token_updated = $token;
        return $user->save();
    }

    public function checkPassUpdateToken($token, $id_user)
    {
        // validamos el token para la vista 'auth.passwords.reset.blae.php'
        return $this->where('pass_token_updated', $token)
                    ->where('id', $id_user)->count();
    }

    public function updatePasswordUser($password)
    {
        $user = $this;
        $data = ['password' => $password];
        User::setPasswordData($data, true, $user);

        $user->update($data);
    }

    public function checkIfCanResetPassword($keyenv = 'GESTOR')
    {
        $user = $this;

        $currentDays = env('RESET_PASSWORD_DAYS_'.$keyenv);
        settype($currentDays, "int");

        if(is_null($user->last_pass_updated_at)) {
            return true;
        }
        //verficar la vigencia de dias
        $diferenceDays = now()->diffInDays($user->last_pass_updated_at);
        return ($diferenceDays >= $currentDays);
    }
    // === RESET PASSWORD ===

    // === INTENTOS APP/GESTOR ===
    public function userIncrementAttempts($current, $currentAttempts, $currentMinutes, $permanent = false)
    {
        $userAttempts = $current->attempts;
        $fulledAttempts = false;

        // nro de bloqueos
        if($userAttempts >= $currentAttempts) {
            $current->attempts = $currentAttempts;
            $fulledAttempts = true;
        } else {
            $current->attempts = $userAttempts + 1;
            $fulledAttempts = (($userAttempts + 1) == $currentAttempts);
        }

        // nro de bloqueos
        if($fulledAttempts) {
            $current->attempts_times_locks = $current->attempts_times_locks + 1;
        }

        $current->attempts_lock_time = now()->addMinutes($currentMinutes);
        $current->timestamps = false;

        $current->save();

        if($permanent) {
            $current->timestamps = false;
            $current->attempts_lock_time = NULL;
            $current->save();
        }

        $current['fulled_attempts'] = $fulledAttempts;
        return $current;
    }

    public function currentUserByEnviroment($keyenv, $value)
    {
        $user = $this;
        if ($keyenv == 'GESTOR') {
            return $user->where('email', $value)->first();
        }

        return $user->where('document', $value)
                    ->orWhere('username', $value)->first();
    }

    public function incrementAttempts($value, $keyenv = 'GESTOR', $permanent = false)
    {
        $currentAttempts = env('ATTEMPTS_LOGIN_MAX_'.$keyenv);
        $currentMinutes = env('ATTEMPTS_LOGIN_TIME_LOCK_'.$keyenv);

        // existe ese data-usuario
        $current = $this->currentUserByEnviroment($keyenv, $value);
        if(!$current) return NULL; // null o vacio

        $userAttempts = $current->attempts;
        $current_user = $current;

        // intentos completos
        if ($userAttempts == $currentAttempts) {
            $current_user['fulled_attempts'] = true;
            return $current_user;
        }

        return $this->userIncrementAttempts($current_user, $currentAttempts, $currentMinutes, $permanent);
    }

    public function checkTimeToReset($value, $keyenv = 'GESTOR')
    {
        $current_user = $this->currentUserByEnviroment($keyenv, $value);
        if(!$current_user) return NULL; // null o vacio

        $staticMinTime = now();
        $staticUserTime = $current_user->attempts_lock_time;

        if($staticUserTime && $staticMinTime >= $staticUserTime){
            $current_user->timestamps = false;
            $current_user->attempts = 0;
            $current_user->attempts_lock_time = NULL;

            $current_user->save();

            return $current_user;
        }

        return NULL;
    }

    public function resetAttemptsUser()
    {
        $user = $this;

        $user->timestamps = false;
        $user->attempts = 0;
        $user->attempts_lock_time = NULL;

        $user->save();
    }

    public function checkCredentialsAttempts($current, $currentAttempts, $permanent = false)
    {
        $availablePermanentBlock = ($current->attempts == $currentAttempts && is_null($current->attempts_lock_time));

        if($availablePermanentBlock && $permanent) {
            $current['fulled_attempts'] = true;
            return $current;
        }

        if($availablePermanentBlock && !$permanent) {
            $current->timestamps = false;
            $current->attempts = 0;
            $current->attempts_lock_time = NULL;

            $current->save();

            return false;
        }

        $timeCondition = (now() <= $current->attempts_lock_time);


        if($current->attempts == $currentAttempts && $timeCondition) {
            $current['fulled_attempts'] = true;
            return $current;
        }

        if($current->attempts >= $currentAttempts && $timeCondition) {

            $current->attempts = $currentAttempts;
            $current->timestamps = false;

            $current->save();
            $current['fulled_attempts'] = true;

            return $current;
        }
        return false;
    }

    public function checkAttemptManualGestor($request)
    {
        $currentAttempts = env('ATTEMPTS_LOGIN_MAX_GESTOR');

        $user = $this;
        $current = $user->where('email_gestor', $request->email)
                        ->where('active', 1)->first();

        if(!$current) return false;

        $checkEmail = ($current->email_gestor == $request->email);
        $checkPassword = Hash::check($request->password, $current->password);

        if($checkEmail && $checkPassword) {
            return $this->checkCredentialsAttempts($current, $currentAttempts);
        }
        return false;
    }

    public function checkAttemptManualApp($credentials, $permanent = false)
    {
        ['username' => $req_username,
         'password' => $req_password] = $credentials[0];

        ['document' => $req_document] = $credentials[1];

        $currentAttempts = env('ATTEMPTS_LOGIN_MAX_APP');

        $current = $this->currentUserByEnviroment('APP', $req_document);
        if(!$current) return false;

        $checkUserDoc = ($current->document == $req_document) || ($current->username == $req_username);
        $checkPassword = Hash::check($req_password, $current->password);

        if($checkUserDoc && $checkPassword) {
            return $this->checkCredentialsAttempts($current, $currentAttempts, $permanent);
        }
        return false;
    }
    // === INTENTOS APP/GESTOR ===

    public function setInitialEmail()
    {
        $user = $this;

        $user->timestamps = false;
        $user->email = '';

        $user->save();
    }

    public function setDocumentAsEmail($document, $revert = false)
    {
        $user = $this;
        $current = $user->where('document', $document)->first();

        $current->timestamps = false;

        if($revert) $current->email = '';
        else $current->email = $document;

        $current->save();
    }

    public function canImpersonate()
    {
        return $this->isAn('super-user');
    }

    public function canBeImpersonated()
    {
        return $this->isNotAn('super-user');
    }

    protected function findUserToImpersonate($value, $field, $guardName)
    {
        $user = User::whereNotNull('subworkspace_id')->where($field, $value)->first();

        if (!$user) {
            throw (new ModelNotFoundException())->setModel(
                User::class,
                $value
            );
        }

        return $user;
    }

    public function hasDataUpToDate()
    {
        // return false;
        
        $course_data = $this->course_data;

        if ($course_data) {

            if ($course_data->current_courses_updated_at > $this->required_update_at) {

                return true;
            }
        }

        return false;
    }

    public function getCoursesDirectly(): array
    {
        $course_ids = $this->course_data['courses'];
        $compatibles = $this->course_data['compatibles'];
        $compatible_ids = array_column($compatibles, 'summary_course_id');

        $courses = $this->getUserCourseSegmentationQuery('soft')
                    ->whereIn('id', $course_ids)
                    ->get();

        if ($compatible_ids) {

            $compatible_summary_courses = SummaryCourse::with('course:id,name')
                ->whereIn('id', $compatible_ids)
                ->orderBy('grade_average', 'DESC')
                ->get();
        }

        $all_courses = [];
        $all_courses['course_id_tags'] = $this->course_data['course_id_tags'];
        foreach ($courses as $key => $course) {

            $all_courses['current_courses'][] = $course;

            if ($compatible_ids) {

                // $compatible = $compatible_summary_courses->where('course_id', $course->id)->first();
                $compatible_course_row = $compatibles[$course->id]['summary_course_id'] ?? NULL;
                $compatible = $compatible_summary_courses->where('id', $compatible_course_row)->first();
                
                if ($compatible) {

                    $compatible->course->compatible_of = $course;
                    $compatible_course = $compatible;

                    $all_courses['compatibles'][$course->id] = $compatible_course;
                }
            }

        }

        return $all_courses;
    }
}
